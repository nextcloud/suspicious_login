<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\BackgroundJob;

use OCA\SuspiciousLogin\Service\ETLService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;

class ETLJob extends TimedJob {

	public function __construct(
		private readonly ETLService $etlService,
		ITimeFactory $time,
	) {
		parent::__construct($time);

		$this->setInterval(60 * 60);
	}

	/**
	 * @param $argument
	 *
	 * @return void
	 */
	#[\Override]
	protected function run($argument) {
		$this->etlService->extractAndTransform();
	}
}
