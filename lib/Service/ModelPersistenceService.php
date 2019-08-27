<?php

declare(strict_types=1);

/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\SuspiciousLogin\Service;

use function file_get_contents;
use function file_put_contents;
use OCA\SuspiciousLogin\AppInfo\Application;
use OCA\SuspiciousLogin\Db\Model;
use OCA\SuspiciousLogin\Db\ModelMapper;
use OCA\SuspiciousLogin\Exception\ServiceException;
use OCP\App\IAppManager;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\Files\IAppData;
use OCP\Files\NotFoundException;
use OCP\ICacheFactory;
use OCP\ILogger;
use OCP\ITempManager;
use Phpml\Estimator;
use Phpml\Exception\SerializeException;
use Phpml\ModelManager;
use function strlen;

class ModelPersistenceService {

	const APPDATA_MODELS_FOLDER = 'models';

	/** @var ModelManager */
	private $modelManager;

	/** @var ModelMapper */
	private $modelMapper;

	/** @var IAppData */
	private $appData;

	/** @var IAppManager */
	private $appManager;

	/** @var ITempManager */
	private $tempManager;

	/** @var ICacheFactory */
	private $cacheFactory;

	/** @var ILogger */
	private $logger;

	public function __construct(ModelManager $modelManager,
								ModelMapper $modelMapper,
								IAppData $appData,
								IAppManager $appManager,
								ITempManager $tempManager,
								ICacheFactory $cachFactory,
								ILogger $logger) {
		$this->modelManager = $modelManager;
		$this->appData = $appData;
		$this->appManager = $appManager;
		$this->modelMapper = $modelMapper;
		$this->tempManager = $tempManager;
		$this->cacheFactory = $cachFactory;
		$this->logger = $logger;
	}

	/**
	 * @return Estimator
	 * @throws SerializeException
	 * @throws ServiceException
	 */
	public function loadLatest(): Estimator {
		try {
			$latestModel = $this->modelMapper->findLatest();
		} catch (DoesNotExistException $e) {
			$this->logger->debug("No models found. Can't load latest");
			throw new ServiceException("No models found", 0, $e);
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

	public function load(int $id): Estimator {
		$cached = $this->getCached($id);
		if (!is_null($cached)) {
			$this->logger->debug("using cached model $id");

			$serialized = $cached;
		} else {
			$this->logger->debug("loading model $id from app data");

			try {
				$modelsFolder = $this->appData->getFolder(self::APPDATA_MODELS_FOLDER);
				$modelFile = $modelsFolder->getFile((string)$id);
			} catch (NotFoundException $e) {
				$this->logger->error("Could not load classifier model $id: " . $e->getMessage());
				throw new ServiceException("Could not load model $id", 0, $e);
			}

			$serialized = $modelFile->getContent();

			$this->cache($id, $serialized);
		}

		$this->logger->debug("seralized model size: " . strlen($serialized));

		// Inefficient, but we can't get the real path from app data as it might
		// not be a local file
		$tmpFile = $this->tempManager->getTemporaryFile();
		file_put_contents($tmpFile, $serialized);

		try {
			$estimator = $this->modelManager->restoreFromFile($tmpFile);
		} catch (SerializeException $e) {
			$this->logger->error("Could not deserialize persisted model $id: " . $e->getMessage());

			throw new SerializeException("Could not deserialize persisted model $id", 0, $e);
		}

		return $estimator;
	}

	/**
	 * @todo encapsulate in transaction to prevent inconsistencies
	 */
	public function persist(Estimator $estimator, Model $model) {
		$model->setType(get_class($estimator));
		$model->setAppVersion($this->appManager->getAppVersion(Application::APP_ID));

		$this->modelMapper->insert($model);
		try {
			$modelsFolder = $this->appData->getFolder(self::APPDATA_MODELS_FOLDER);
		} catch (NotFoundException $e) {
			$this->logger->info("App data models folder does not exist. Creating it");
			$modelsFolder = $this->appData->newFolder(self::APPDATA_MODELS_FOLDER);
		}

		$modelFile = $modelsFolder->newFile($model->getId());

		// Inefficient, but we can't get the real path from app data as it might
		// not be a local file
		$tmpFile = $this->tempManager->getTemporaryFile();
		$this->modelManager->saveToFile($estimator, $tmpFile);

		$modelFile->putContent(file_get_contents($tmpFile));
	}

}
