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

	/** @var ETLService */
	private $etlService;

	public function __construct(ETLService $etlService,
		ITimeFactory $time) {
		parent::__construct($time);

		$this->setInterval(60 * 60);
		$this->etlService = $etlService;
	}

	/**
	 * @param $argument
	 *
	 * @return void
	 */
	protected function run($argument) {
		$this->etlService->extractAndTransform();
	}
}
