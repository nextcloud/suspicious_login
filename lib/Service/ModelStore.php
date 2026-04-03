<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service;

use OCA\SuspiciousLogin\AppInfo\Application;
use OCA\SuspiciousLogin\Db\Model;
use OCA\SuspiciousLogin\Db\ModelMapper;
use OCA\SuspiciousLogin\Exception\ModelNotFoundException;
use OCP\App\IAppManager;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\Files\IAppData;
use OCP\Files\NotFoundException;
use OCP\ICacheFactory;
use OCP\ITempManager;
use Psr\Log\LoggerInterface;
use Rubix\ML\Estimator;
use Rubix\ML\Learner;
use Rubix\ML\Persistable;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use RuntimeException;
use Throwable;
use function file_get_contents;
use function file_put_contents;
use function strlen;

class ModelStore {
	public const APPDATA_MODELS_FOLDER = 'models';

	public function __construct(
		private ModelMapper $modelMapper,
		private IAppData $appData,
		private IAppManager $appManager,
		private ITempManager $tempManager,
		private ICacheFactory $cacheFactory,
		private LoggerInterface $logger,
	) {
	}

	/**
	 * @return Estimator
	 * @throws RuntimeException
	 * @throws ModelNotFoundException
	 */
	public function loadLatest(AClassificationStrategy $strategy): Estimator {
		try {
			$latestModel = $this->modelMapper->findLatest($strategy::getTypeName());
		} catch (DoesNotExistException $e) {
			$this->logger->debug("No models found. Can't load latest");
			throw new ModelNotFoundException('No models found', 0, $e);
		}
		return $this->load($latestModel->getId());
	}

	private function getCacheKey(int $id): string {
		return "suspicious_login_model_$id";
	}

	private function getCached(int $id): ?string {
		if (!$this->cacheFactory->isLocalCacheAvailable()) {
			return null;
		}
		$cache = $this->cacheFactory->createLocal();

		return $cache->get(
			$this->getCacheKey($id)
		);
	}

	private function cache(int $id, string $serialized): void {
		if (!$this->cacheFactory->isLocalCacheAvailable()) {
			return;
		}
		$cache = $this->cacheFactory->createLocal();
		$cache->set($this->getCacheKey($id), $serialized);
	}

	/**
	 * @return Estimator
	 * @throws RuntimeException
	 * @throws ModelNotFoundException
	 */
	public function load(int $id): Estimator {
		$cached = $this->getCached($id);
		if ($cached !== null) {
			$this->logger->debug("using cached model $id");

			$serialized = $cached;
		} else {
			$this->logger->debug("loading model $id from app data");

			try {
				$modelsFolder = $this->appData->getFolder(self::APPDATA_MODELS_FOLDER);
				$modelFile = $modelsFolder->getFile((string)$id);
			} catch (NotFoundException $e) {
				$this->logger->error("Could not load classifier model $id: " . $e->getMessage());
				throw new ModelNotFoundException("Could not load model $id", 0, $e);
			}

			$serialized = $modelFile->getContent();

			$this->cache($id, $serialized);
		}

		$this->logger->debug('seralized model size: ' . strlen($serialized));

		// Inefficient, but we can't get the real path from app data as it might
		// not be a local file
		$tmpFile = $this->tempManager->getTemporaryFile();
		file_put_contents($tmpFile, $serialized);

		try {
			$learner = PersistentModel::load(new Filesystem($tmpFile));
		} catch (RuntimeException $e) {
			$this->logger->error("Could not deserialize persisted model $id: " . $e->getMessage());

			throw $e;
		}

		return $learner;
	}

	/**
	 * @param Learner $estimator (Must implement Persistable)
	 * @todo encapsulate in transaction to prevent inconsistencies
	 */
	public function persist(Learner $estimator, Model $model) {
		if (!($estimator instanceof Persistable)) {
			throw new RuntimeException('Estimator is not persistable');
		}

		$model->setType(get_class($estimator));
		$model->setAppVersion($this->appManager->getAppVersion(Application::APP_ID));

		$this->modelMapper->insert($model);
		try {
			$modelsFolder = $this->appData->getFolder(self::APPDATA_MODELS_FOLDER);
		} catch (NotFoundException $e) {
			$this->logger->info('App data models folder does not exist. Creating it');
			$modelsFolder = $this->appData->newFolder(self::APPDATA_MODELS_FOLDER);
		}

		try {
			$modelFile = $modelsFolder->newFile((string)$model->getId());

			// Inefficient, but we can't get the real path from app data as it might
			// not be a local file
			$tmpFile = $this->tempManager->getTemporaryFile();
			$persistentModel = new PersistentModel($estimator, new Filesystem($tmpFile));
			$persistentModel->save();

			$modelFile->putContent(file_get_contents($tmpFile));
		} catch (Throwable $e) {
			$this->logger->error('Could not save persisted estimator to storage, reverting', [
				'exception' => $e,
			]);
			$this->modelMapper->delete($model);
			throw $e;
		}
	}

	/**
	 * Remove old models, keeping only the `$numberOfModelsToKeep` ones
	 * for each used address type (IPv4 / IPv6).
	 *
	 * @throws \OCP\DB\Exception
	 * @throws \OCP\Files\NotPermittedException
	 */
	public function cleanup(int $numberOfModelsToKeep): void {
		$oldModels = array_merge(
			$this->modelMapper->findOld($numberOfModelsToKeep, Ipv4Strategy::getTypeName()),
			$this->modelMapper->findOld($numberOfModelsToKeep, IpV6Strategy::getTypeName())
		);

		if (empty($oldModels)) {
			$this->logger->debug('No old models to clean up');
			return;
		}

		try {
			$modelsFolder = $this->appData->getFolder(self::APPDATA_MODELS_FOLDER);
		} catch (NotFoundException) {
			$this->logger->debug('Models folder does not exist, skipping file deletion');
		}

		if ($this->cacheFactory->isLocalCacheAvailable()) {
			$cache = $this->cacheFactory->createLocal();
		}

		foreach ($oldModels as $oldModel) {
			$id = $oldModel->getId();
			$this->logger->debug("Cleaning up old model $id");

			// Remove the model file from app data
			if (isset($modelsFolder)) {
				try {
					$modelFile = $modelsFolder->getFile((string)$id);
					$modelFile->delete();
				} catch (NotFoundException) {
					$this->logger->debug("Model file $id not found in app data, skipping file deletion");
				}
			}

			// Evict from cache
			if (isset($cache)) {
				$cache->remove($this->getCacheKey($id));
			}

			// Remove the DB record
			$this->modelMapper->delete($oldModel);
		}

		$numberOfOldModels = count($oldModels);
		$this->logger->info("Cleaned up {$numberOfOldModels} old model(s)");
	}
}
