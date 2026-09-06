<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\BackgroundJob;

use OCA\SuspiciousLogin\Service\ModelStore;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use Psr\Log\LoggerInterface;
use Throwable;

class CleanupModelsJob extends TimedJob {

	/**
	 * @var int Number of most recent models to keep per address type.
	 *          Should match the number of models consumed by the statistics
	 *          API.
	 */
	private const MODELS_TO_KEEP = 14;

	public function __construct(
		private readonly ModelStore $modelStore,
		private readonly LoggerInterface $logger,
		ITimeFactory $time,
	) {
		parent::__construct($time);

		// Run once per day
		$this->setInterval(24 * 60 * 60);
		$this->setTimeSensitivity(self::TIME_INSENSITIVE);
		$this->allowParallelRuns = false;
	}

	#[\Override]
	protected function run($argument): void {
		try {
			$this->modelStore->cleanup(self::MODELS_TO_KEEP);
		} catch (Throwable $e) {
			$this->logger->error('Error during model cleanup: ' . $e->getMessage(), [
				'exception' => $e,
			]);
		}
	}
}
