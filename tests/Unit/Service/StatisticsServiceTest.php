<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Tests\Unit\Service;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\SuspiciousLogin\Db\LoginAddressAggregatedMapper;
use OCA\SuspiciousLogin\Db\LoginAddressMapper;
use OCA\SuspiciousLogin\Db\Model;
use OCA\SuspiciousLogin\Db\ModelMapper;
use OCA\SuspiciousLogin\Service\Statistics\AppStatistics;
use OCA\SuspiciousLogin\Service\Statistics\TrainingDataStatistics;
use OCA\SuspiciousLogin\Service\StatisticsService;
use OCA\SuspiciousLogin\Service\TrainingDataConfig;
use OCP\AppFramework\Db\DoesNotExistException;
use PHPUnit\Framework\MockObject\MockObject;

class StatisticsServiceTest extends TestCase {

	/** @var LoginAddressMapper|MockObject */
	private $loginAddressMapper;

	/** @var LoginAddressAggregatedMapper|MockObject */
	private $loginAddressAggregatedMapper;

	/** @var ModelMapper|MockObject */
	private $modelMapper;

	/** @var StatisticsService */
	private $service;

	protected function setUp(): void {
		parent::setUp();

		$this->loginAddressMapper = $this->createMock(LoginAddressMapper::class);
		$this->loginAddressAggregatedMapper = $this->createMock(LoginAddressAggregatedMapper::class);
		$this->modelMapper = $this->createMock(ModelMapper::class);

		$this->service = new StatisticsService(
			$this->loginAddressMapper,
			$this->loginAddressAggregatedMapper,
			$this->modelMapper
		);
	}

	public function testGetStatisticsNoModels() {
		$this->modelMapper->expects($this->once())
			->method('findLatest')
			->willThrowException(new DoesNotExistException(''));
		$this->modelMapper->expects($this->exactly(2))
			->method('findMostRecent')
			->with(14)
			->willReturn([]);
		$this->loginAddressMapper->expects($this->once())
			->method('getCount')
			->willReturn(50);
		$this->loginAddressAggregatedMapper->expects($this->once())
			->method('getTotalCount')
			->willReturn(150);
		$this->loginAddressAggregatedMapper->expects($this->once())
			->method('getCount')
			->willReturn(20);
		$expected = new AppStatistics(
			false,
			[],
			TrainingDataConfig::default(),
			new TrainingDataStatistics(200, 20)
		);

		$stats = $this->service->getStatistics();

		$this->assertEquals($expected, $stats);
	}

	public function testGetStatistics() {
		$model = $this->createMock(Model::class);
		$this->modelMapper->expects($this->once())
			->method('findLatest')
			->willReturn($model);
		$this->modelMapper->expects($this->exactly(2))
			->method('findMostRecent')
			->with(14)
			->willReturn([$model]);
		$this->loginAddressMapper->expects($this->once())
			->method('getCount')
			->willReturn(50);
		$this->loginAddressAggregatedMapper->expects($this->once())
			->method('getTotalCount')
			->willReturn(150);
		$this->loginAddressAggregatedMapper->expects($this->once())
			->method('getCount')
			->willReturn(20);
		$expected = new AppStatistics(
			true,
			[$model, $model],
			TrainingDataConfig::default(),
			new TrainingDataStatistics(200, 20)
		);

		$stats = $this->service->getStatistics();

		$this->assertEquals($expected, $stats);
	}
}
