<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\BackgroundJob;

use OCA\SuspiciousLogin\Exception\InsufficientDataException;
use OCA\SuspiciousLogin\Service\IpV6Strategy;
use OCA\SuspiciousLogin\Service\TrainingDataConfig;
use OCA\SuspiciousLogin\Service\TrainService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCP\ILogger;
use Throwable;

class TrainJobIpV6 extends TimedJob {

	/** @var TrainService */
	private $trainService;

	/** @var ILogger */
	private $logger;

	public function __construct(TrainService $trainService,
		ILogger $logger,
		ITimeFactory $time) {
		parent::__construct($time);

		$this->setInterval(24 * 60 * 60);
		/**
		 * @todo remove checks with 24+
		 */
		if (defined('\OCP\BackgroundJob\IJob::TIME_INSENSITIVE') && method_exists($this, 'setTimeSensitivity')) {
			$this->setTimeSensitivity(self::TIME_INSENSITIVE);
		}
		$this->trainService = $trainService;
		$this->logger = $logger;
	}

	/**
	 * @param $argument
	 *
	 * @return mixed
	 */
	protected function run($argument) {
		try {
			$strategy = new IpV6Strategy();
			$this->trainService->train(
				$strategy->getDefaultMlpConfig(),
				TrainingDataConfig::default(),
				$strategy
			);
		} catch (InsufficientDataException $ex) {
			$this->logger->logException($ex, [
				'level' => ILogger::INFO,
				'message' => 'No suspicious login model for IPv6 trained because of insufficient data',
			]);
		} catch (Throwable $ex) {
			$this->logger->logException($ex, [
				'level' => ILogger::ERROR,
				'message' => 'Caught unknown error during IPv6 background training',
			]);
		}
	}
}
