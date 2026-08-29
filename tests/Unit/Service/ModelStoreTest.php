<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Tests\Unit\Service;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\SuspiciousLogin\Db\Model;
use OCA\SuspiciousLogin\Db\ModelMapper;
use OCA\SuspiciousLogin\Service\Ipv4Strategy;
use OCA\SuspiciousLogin\Service\IpV6Strategy;
use OCA\SuspiciousLogin\Service\ModelStore;
use OCP\App\IAppManager;
use OCP\Files\IAppData;
use OCP\Files\NotFoundException;
use OCP\Files\SimpleFS\ISimpleFile;
use OCP\Files\SimpleFS\ISimpleFolder;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\ITempManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class ModelStoreTest extends TestCase {

	private ModelMapper&MockObject $modelMapper;
	private IAppData&MockObject $appData;
	private IAppManager&MockObject $appManager;
	private ITempManager&MockObject $tempManager;
	private ICacheFactory&MockObject $cacheFactory;
	private LoggerInterface&MockObject $logger;
	private ModelStore $modelStore;

	protected function setUp(): void {
		parent::setUp();

		$this->modelMapper = $this->createMock(ModelMapper::class);
		$this->appData = $this->createMock(IAppData::class);
		$this->appManager = $this->createMock(IAppManager::class);
		$this->tempManager = $this->createMock(ITempManager::class);
		$this->cacheFactory = $this->createMock(ICacheFactory::class);
		$this->logger = $this->createMock(LoggerInterface::class);

		$this->modelStore = new ModelStore(
			$this->modelMapper,
			$this->appData,
			$this->appManager,
			$this->tempManager,
			$this->cacheFactory,
			$this->logger,
		);
	}

	private function createModelWithId(int $id): Model {
		$model = new Model();
		$model->setId($id);
		return $model;
	}

	private function expectFindOldReturns(int $keep, array $ipv4Models, array $ipv6Models): void {
		$this->modelMapper->expects(self::exactly(2))
			->method('findOld')
			->willReturnMap([
				[$keep, Ipv4Strategy::getTypeName(), $ipv4Models],
				[$keep, IpV6Strategy::getTypeName(), $ipv6Models],
			]);
	}

	private static function expectedCacheKey(int $id): string {
		return "suspicious_login_model_$id";
	}

	public function testCleanupDoesNothingWhenNoOldModels(): void {
		$this->expectFindOldReturns(14, [], []);

		$this->appData->expects(self::never())
			->method('getFolder');
		$this->modelMapper->expects(self::never())
			->method('delete');

		$this->modelStore->cleanup(14);
	}

	public function testCleanupRemovesOldModelsFromBothStrategies(): void {
		$model1 = $this->createModelWithId(1);
		$model2 = $this->createModelWithId(2);

		$this->expectFindOldReturns(14, [$model1], [$model2]);

		$file1 = $this->createMock(ISimpleFile::class);
		$file1->expects(self::once())->method('delete');
		$file2 = $this->createMock(ISimpleFile::class);
		$file2->expects(self::once())->method('delete');

		$modelsFolder = $this->createMock(ISimpleFolder::class);
		$modelsFolder->expects(self::exactly(2))
			->method('getFile')
			->willReturnMap([
				['1', $file1],
				['2', $file2],
			]);

		$this->appData->expects(self::once())
			->method('getFolder')
			->with(ModelStore::APPDATA_MODELS_FOLDER)
			->willReturn($modelsFolder);

		$this->cacheFactory->method('isLocalCacheAvailable')->willReturn(false);

		$deletedModels = [];
		$this->modelMapper->expects(self::exactly(2))
			->method('delete')
			->willReturnCallback(function (Model $model) use (&$deletedModels) {
				$deletedModels[] = $model;
				return $model;
			});

		$this->modelStore->cleanup(14);

		self::assertSame([$model1, $model2], $deletedModels);
	}

	public function testCleanupDeletesDbRecordWhenModelFileNotFound(): void {
		$model = $this->createModelWithId(42);

		$this->expectFindOldReturns(14, [$model], []);

		$modelsFolder = $this->createMock(ISimpleFolder::class);
		$modelsFolder->expects(self::once())
			->method('getFile')
			->with('42')
			->willThrowException(new NotFoundException());

		$this->appData->expects(self::once())
			->method('getFolder')
			->with(ModelStore::APPDATA_MODELS_FOLDER)
			->willReturn($modelsFolder);

		$this->cacheFactory->method('isLocalCacheAvailable')->willReturn(false);

		$this->modelMapper->expects(self::once())
			->method('delete')
			->with($model);

		$this->modelStore->cleanup(14);
	}

	public function testCleanupDeletesDbRecordWhenModelsFolderNotFound(): void {
		$model = $this->createModelWithId(10);

		$this->expectFindOldReturns(14, [$model], []);

		$this->appData->expects(self::once())
			->method('getFolder')
			->with(ModelStore::APPDATA_MODELS_FOLDER)
			->willThrowException(new NotFoundException());

		$this->cacheFactory->method('isLocalCacheAvailable')->willReturn(false);

		$this->modelMapper->expects(self::once())
			->method('delete')
			->with($model);

		$this->modelStore->cleanup(14);
	}

	public function testCleanupRemovesOnlyIpv6Models(): void {
		$model = $this->createModelWithId(5);

		$this->expectFindOldReturns(14, [], [$model]);

		$file = $this->createMock(ISimpleFile::class);
		$file->expects(self::once())->method('delete');

		$modelsFolder = $this->createMock(ISimpleFolder::class);
		$modelsFolder->expects(self::once())
			->method('getFile')
			->with('5')
			->willReturn($file);

		$this->appData->expects(self::once())
			->method('getFolder')
			->with(ModelStore::APPDATA_MODELS_FOLDER)
			->willReturn($modelsFolder);

		$this->cacheFactory->method('isLocalCacheAvailable')->willReturn(false);

		$this->modelMapper->expects(self::once())
			->method('delete')
			->with($model);

		$this->modelStore->cleanup(14);
	}

	public function testCleanupAbortsWhenDeleteThrowsException(): void {
		$model1 = $this->createModelWithId(1);
		$model2 = $this->createModelWithId(2);

		$this->expectFindOldReturns(14, [$model1, $model2], []);

		$file1 = $this->createMock(ISimpleFile::class);
		$file1->expects(self::once())->method('delete');

		$modelsFolder = $this->createMock(ISimpleFolder::class);
		$modelsFolder->expects(self::once())
			->method('getFile')
			->with('1')
			->willReturn($file1);

		$this->appData->expects(self::once())
			->method('getFolder')
			->with(ModelStore::APPDATA_MODELS_FOLDER)
			->willReturn($modelsFolder);

		$this->cacheFactory->method('isLocalCacheAvailable')->willReturn(false);

		$exception = new \Exception('DB error');
		$this->modelMapper->expects(self::once())
			->method('delete')
			->with($model1)
			->willThrowException($exception);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('DB error');

		$this->modelStore->cleanup(14);
	}

	public function testCleanupPassesKeepValueToMapper(): void {
		$this->expectFindOldReturns(3, [], []);

		$this->modelStore->cleanup(3);
	}

	public static function cacheAvailabilityDataProvider(): array {
		return [
			'cache available' => [true],
			'cache unavailable' => [false],
		];
	}

	#[DataProvider('cacheAvailabilityDataProvider')]
	public function testCleanupHandlesCacheEviction(bool $cacheAvailable): void {
		$model = $this->createModelWithId(7);

		$this->expectFindOldReturns(14, [$model], []);

		$modelsFolder = $this->createMock(ISimpleFolder::class);
		$file = $this->createMock(ISimpleFile::class);
		$file->expects(self::once())->method('delete');
		$modelsFolder->method('getFile')->with('7')->willReturn($file);

		$this->appData->method('getFolder')
			->with(ModelStore::APPDATA_MODELS_FOLDER)
			->willReturn($modelsFolder);

		$this->cacheFactory->method('isLocalCacheAvailable')->willReturn($cacheAvailable);

		if ($cacheAvailable) {
			$cache = $this->createMock(ICache::class);
			$cache->expects(self::once())
				->method('remove')
				->with(self::expectedCacheKey(7));
			$this->cacheFactory->method('createLocal')->willReturn($cache);
		} else {
			$this->cacheFactory->expects(self::never())->method('createLocal');
		}

		$this->modelMapper->expects(self::once())
			->method('delete')
			->with($model);

		$this->modelStore->cleanup(14);
	}
}
