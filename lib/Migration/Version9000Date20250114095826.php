<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Migration;

use Closure;
use OCA\SuspiciousLogin\BackgroundJob\TrainIpV4OnceJob;
use OCA\SuspiciousLogin\BackgroundJob\TrainIpV6OnceJob;
use OCP\BackgroundJob\IJobList;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version9000Date20250114095826 extends SimpleMigrationStep {

	public function __construct(private IJobList $jobList) {
	}

	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		$this->jobList->add(TrainIpV4OnceJob::class);
		$this->jobList->add(TrainIpV6OnceJob::class);
	}
}
