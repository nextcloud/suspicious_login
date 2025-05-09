<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\BackgroundJob;

use OCA\SuspiciousLogin\Exception\InsufficientDataException;
use OCA\SuspiciousLogin\Service\Ipv4Strategy;
use OCA\SuspiciousLogin\Service\TrainingDataConfig;
use OCA\SuspiciousLogin\Service\TrainService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\QueuedJob;
use Psr\Log\LoggerInterface;
use Throwable;

class TrainIpV4OnceJob extends QueuedJob {

	public function __construct(
		private TrainService $trainService,
		private LoggerInterface $logger,
		ITimeFactory $time,
	) {
		parent::__construct($time);
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
