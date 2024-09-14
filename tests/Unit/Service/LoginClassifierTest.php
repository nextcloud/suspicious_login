<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
use OCP\IRequest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use function array_fill;

class LoginClassifierTest extends TestCase {

	/** @var EstimatorService|MockObject */
	private $estimatorService;

	/** @var IRequest|MockObject */
	private $request;

	/** @var SuspiciousLoginMapper|MockObject */
	private $mapper;

	private LoggerInterface&MockObject $logger;

	/** @var ITimeFactory|MockObject */
	private $timeFactory;

	/** @var IEventDispatcher|MockObject */
	private $dispatcher;
	/** @var LoginClassifier */
	private $classifier;

	protected function setUp(): void {
		parent::setUp();

		$this->estimatorService = $this->createMock(EstimatorService::class);
		$this->request = $this->createMock(IRequest::class);
		$this->logger = $this->createMock(LoggerInterface::class);
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
		$this->estimatorService->expects(self::once())
			->method('predict')
			->with('user', '1.2.3.4', self::equalTo(new Ipv4Strategy()))
			->willReturn(false);
		$this->mapper->expects(self::once())
			->method('findRelated')
			->with(
				'user',
				'1.2.3.4',
				self::anything(),
				1000000 - 60 * 60 * 24 * TrainingDataConfig::default()->getThreshold()
			)
			->willReturn([
				new SuspiciousLogin(),
			]);
		$this->dispatcher->expects(self::never())
			->method('dispatchTyped');

		$this->classifier->process('user', '1.2.3.4');
	}

	public function testProcessTwoDayPeakReached(): void {
		$this->timeFactory->method('getTime')->willReturn(1000000);
		$this->estimatorService->expects(self::once())
			->method('predict')
			->with('user', '1.2.3.4', self::equalTo(new Ipv4Strategy()))
			->willReturn(false);
		$this->mapper->expects(self::once())
			->method('findRelated')
			->with(
				'user',
				'1.2.3.4',
				self::anything(),
				1000000 - 60 * 60 * 24 * TrainingDataConfig::default()->getThreshold()
			)
			->willReturn([]);
		$this->mapper->expects(self::once())
			->method('findRecentByUid')
			->with(
				'user',
				1000000 - 60 * 60 * 24 * 2
			)
			->willReturn(array_fill(0, 25, new SuspiciousLogin()));
		$this->dispatcher->expects(self::never())
			->method('dispatchTyped');

		$this->classifier->process('user', '1.2.3.4');
	}

	public function testProcessHourlyPeakReached(): void {
		$this->timeFactory->method('getTime')->willReturn(1000000);
		$this->estimatorService->expects(self::once())
			->method('predict')
			->with('user', '1.2.3.4', self::equalTo(new Ipv4Strategy()))
			->willReturn(false);
		$this->mapper->expects(self::exactly(2))
			->method('findRecentByUid')
			->willReturnMap([
				[
					'user',
					1000000 - 60 * 60 * 24 * 2,
					array_fill(0, 7, new SuspiciousLogin())
				],
				[
					'user',
					1000000 - 60 * 60,
					array_fill(0, 5, new SuspiciousLogin())
				]
			]);
		$this->dispatcher->expects(self::never())
			->method('dispatchTyped');

		$this->classifier->process('user', '1.2.3.4');
	}

	public function testProcessNoPeakReached(): void {
		$this->timeFactory->method('getTime')->willReturn(1000000);
		$this->estimatorService->expects(self::once())
			->method('predict')
			->with('user', '1.2.3.4', self::equalTo(new Ipv4Strategy()))
			->willReturn(false);
		$this->mapper->expects(self::exactly(2))
			->method('findRecentByUid')
			->willReturnMap([
				[
					'user',
					1000000 - 60 * 60 * 24 * 2,
					array_fill(0, 7, new SuspiciousLogin())
				],
				[
					'user',
					1000000 - 60 * 60,
					array_fill(0, 1, new SuspiciousLogin())
				]
			]);
		$this->dispatcher->expects(self::once())
			->method('dispatchTyped');

		$this->classifier->process('user', '1.2.3.4');
	}
}
