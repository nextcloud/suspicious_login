<?php declare(strict_types=1);

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
use OCA\SuspiciousLogin\Db\SuspiciousLogin;
use OCA\SuspiciousLogin\Db\SuspiciousLoginMapper;
use OCA\SuspiciousLogin\Service\EstimatorService;
use OCA\SuspiciousLogin\Service\Ipv4Strategy;
use OCA\SuspiciousLogin\Service\LoginClassifier;
use OCA\SuspiciousLogin\Service\TrainingDataConfig;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\ILogger;
use OCP\IRequest;
use PHPUnit\Framework\MockObject\MockObject;

class LoginClassifierTest extends TestCase {

	/** @var EstimatorService|MockObject */
	private $estimatorService;

	/** @var IRequest|MockObject */
	private $request;

	/** @var SuspiciousLoginMapper|MockObject */
	private $mapper;

	/** @var ILogger|MockObject */
	private $logger;

	/** @var ITimeFactory|MockObject */
	private $timeFactory;

	/** @var IEventDispatcher|MockObject */
	private $dispatcher;
	/** @var LoginClassifier */
	private $classifier;

	protected function setUp() {
		parent::setUp();

		$this->estimatorService = $this->createMock(EstimatorService::class);
		$this->request = $this->createMock(IRequest::class);
		$this->logger = $this->createMock(ILogger::class);
		$this->mapper = $this->createMock(SuspiciousLoginMapper::class);
		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->dispatcher = $this->createMock(IEventDispatcher::class);

		$this->classifier = new LoginClassifier(
			$this->estimatorService,
			$this->request,
			$this->logger,
			$this->mapper,
			$this->timeFactory,
			$this->dispatcher
		);
	}

	public function testProcessAlertAlreadySent(): void {
		$this->timeFactory->method('getTime')->willReturn(1000000);
		$this->estimatorService->expects($this->once())
			->method('predict')
			->with('user', '1.2.3.4', $this->equalTo(new Ipv4Strategy()))
			->willReturn(false);
		$this->mapper->expects($this->at(1))
			->method('findRelated')
			->with(
				'user',
				'1.2.3.4',
				$this->anything(),
				1000000-60*60*24*TrainingDataConfig::default()->getThreshold()
			)
			->willReturn([
				new SuspiciousLogin(),
			]);
		$this->dispatcher->expects($this->never())
			->method('dispatch');

		$this->classifier->process('user', '1.2.3.4');
	}

	public function testProcessTwoDayPeakReached(): void {
		$this->timeFactory->method('getTime')->willReturn(1000000);
		$this->estimatorService->expects($this->once())
			->method('predict')
			->with('user', '1.2.3.4', $this->equalTo(new Ipv4Strategy()))
			->willReturn(false);
		$this->mapper->expects($this->at(1))
			->method('findRelated')
			->with(
				'user',
				'1.2.3.4',
				$this->anything(),
				1000000-60*60*24*TrainingDataConfig::default()->getThreshold()
			)
			->willReturn([]);
		$this->mapper->expects($this->at(2))
			->method('findRecentByUid')
			->with(
				'user',
				1000000-60*60*24*2
			)
			->willReturn(array_fill(0, 25, new SuspiciousLogin()));
		$this->dispatcher->expects($this->never())
			->method('dispatch');

		$this->classifier->process('user', '1.2.3.4');
	}

	public function testProcessHourlyPeakReached(): void {
		$this->timeFactory->method('getTime')->willReturn(1000000);
		$this->estimatorService->expects($this->once())
			->method('predict')
			->with('user', '1.2.3.4', $this->equalTo(new Ipv4Strategy()))
			->willReturn(false);
		$this->mapper->expects($this->at(2))
			->method('findRecentByUid')
			->with(
				'user',
				1000000-60*60*24*2
			)
			->willReturn(array_fill(0, 7, new SuspiciousLogin()));
		$this->mapper->expects($this->at(3))
			->method('findRecentByUid')
			->with(
				'user',
				1000000-60*60
			)
			->willReturn(array_fill(0, 5, new SuspiciousLogin()));
		$this->dispatcher->expects($this->never())
			->method('dispatch');

		$this->classifier->process('user', '1.2.3.4');
	}

	public function testProcessNoPeakReached(): void {
		$this->timeFactory->method('getTime')->willReturn(1000000);
		$this->estimatorService->expects($this->once())
			->method('predict')
			->with('user', '1.2.3.4', $this->equalTo(new Ipv4Strategy()))
			->willReturn(false);
		$this->mapper->expects($this->at(2))
			->method('findRecentByUid')
			->with(
				'user',
				1000000-60*60*24*2
			)
			->willReturn(array_fill(0, 7, new SuspiciousLogin()));
		$this->mapper->expects($this->at(3))
			->method('findRecentByUid')
			->with(
				'user',
				1000000-60*60
			)
			->willReturn(array_fill(0, 1, new SuspiciousLogin()));
		$this->dispatcher->expects($this->once())
			->method('dispatch');

		$this->classifier->process('user', '1.2.3.4');
	}

}
