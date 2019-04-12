<?php

declare(strict_types=1);

/**
 * @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\SuspiciousLogin\Tests\Unit\Service;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\SuspiciousLogin\Db\LoginAddressAggregatedMapper;
use OCA\SuspiciousLogin\Db\LoginAddressMapper;
use OCA\SuspiciousLogin\Db\Model;
use OCA\SuspiciousLogin\Db\ModelMapper;
use OCA\SuspiciousLogin\Service\AdminService;
use OCA\SuspiciousLogin\Service\Statistics\AppStatistics;
use OCA\SuspiciousLogin\Service\Statistics\TrainingDataStatistics;
use OCA\SuspiciousLogin\Service\TrainingDataConfig;
use OCP\AppFramework\Db\DoesNotExistException;
use PHPUnit\Framework\MockObject\MockObject;

class AdminServiceTest extends TestCase {

	/** @var LoginAddressMapper|MockObject */
	private $loginAddressMapper;

	/** @var LoginAddressAggregatedMapper|MockObject */
	private $loginAddressAggregatedMapper;

	/** @var ModelMapper|MockObject */
	private $modelMapper;

	/** @var AdminService */
	private $service;

	protected function setUp() {
		parent::setUp();

		$this->loginAddressMapper = $this->createMock(LoginAddressMapper::class);
		$this->loginAddressAggregatedMapper = $this->createMock(LoginAddressAggregatedMapper::class);
		$this->modelMapper = $this->createMock(ModelMapper::class);

		$this->service = new AdminService(
			$this->loginAddressMapper,
			$this->loginAddressAggregatedMapper,
			$this->modelMapper
		);
	}

	public function testGetStatisticsNoModels() {
		$this->modelMapper->expects($this->once())
			->method('findLatest')
			->willThrowException(new DoesNotExistException(''));
		$this->modelMapper->expects($this->once())
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
		$this->modelMapper->expects($this->once())
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
			[$model],
			TrainingDataConfig::default(),
			new TrainingDataStatistics(200, 20)
		);

		$stats = $this->service->getStatistics();

		$this->assertEquals($expected, $stats);
	}
}
