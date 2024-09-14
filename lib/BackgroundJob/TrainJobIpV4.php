<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\BackgroundJob;

use OCA\SuspiciousLogin\Exception\InsufficientDataException;
use OCA\SuspiciousLogin\Service\Ipv4Strategy;
use OCA\SuspiciousLogin\Service\TrainingDataConfig;
use OCA\SuspiciousLogin\Service\TrainService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use Psr\Log\LoggerInterface;
use Throwable;

class TrainJobIpV4 extends TimedJob {

	public function __construct(
		private TrainService $trainService,
		private LoggerInterface $logger,
		ITimeFactory $time,
	) {
		parent::__construct($time);

		$this->setInterval(24 * 60 * 60);
		/**
		 * @todo remove checks with 24+
		 */
		if (defined('\OCP\BackgroundJob\IJob::TIME_INSENSITIVE') && method_exists($this, 'setTimeSensitivity')) {
			$this->setTimeSensitivity(self::TIME_INSENSITIVE);
		}
	}

	/**
	 * @param $argument
	 *
	 * @return void
	 */
	protected function run($argument) {
		try {
			$strategy = new Ipv4Strategy();
			$this->trainService->train(
				$strategy->getDefaultMlpConfig(),
				TrainingDataConfig::default(),
				$strategy
			);
		} catch (InsufficientDataException $ex) {
			$this->logger->info('No suspicious login model for IPv4 trained because of insufficient data', ['exception' => $ex]);
		} catch (Throwable $ex) {
			$this->logger->error('Caught unknown error during IPv4 background training', ['exception' => $ex]);
		}
	}
}
