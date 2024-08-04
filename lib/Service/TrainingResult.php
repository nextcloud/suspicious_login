<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service;

use OCA\SuspiciousLogin\Db\Model;
use Rubix\ML\Estimator;
use Rubix\ML\Report;

class TrainingResult {

	/** @var Estimator */
	private $classifier;

	/** @var Model */
	private $model;

	/** @var Report */
	private $report;

	public function __construct(Estimator $classifier,
		Model $model,
		Report $report) {
		$this->classifier = $classifier;
		$this->model = $model;
		$this->report = $report;
	}

	public function getClassifier(): Estimator {
		return $this->classifier;
	}

	public function getModel(): Model {
		return $this->model;
	}

	public function getReport(): Report {
		return $this->report;
	}
}
