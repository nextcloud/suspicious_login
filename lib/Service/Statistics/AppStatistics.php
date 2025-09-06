<?php

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service\Statistics;

use JsonSerializable;
use OCA\SuspiciousLogin\Db\Model;
use OCA\SuspiciousLogin\Service\TrainingDataConfig;
use ReturnTypeWillChange;

class AppStatistics implements JsonSerializable {

	/** @var bool */
	private $active;

	/** @var Model[] */
	private $recentModels;

	/** @var TrainingDataConfig */
	private $trainingDataConfig;

	/** @var TrainingDataStatistics */
	private $trainingDataStatistics;

	public function __construct(bool $active,
		array $recentModels,
		TrainingDataConfig $trainingDataConfig,
		TrainingDataStatistics $trainingDataStatistics) {
		$this->active = $active;
		$this->recentModels = $recentModels;
		$this->trainingDataConfig = $trainingDataConfig;
		$this->trainingDataStatistics = $trainingDataStatistics;
	}

	/**
	 * @return bool
	 */
	public function isActive(): bool {
		return $this->active;
	}

	/**
	 * @return Model[]
	 */
	public function getRecentModels(): array {
		return $this->recentModels;
	}

	/**
	 * @return TrainingDataConfig
	 */
	public function getTrainingDataConfig(): TrainingDataConfig {
		return $this->trainingDataConfig;
	}

	/**
	 * @return TrainingDataStatistics
	 */
	public function getTrainingDataStatistics(): TrainingDataStatistics {
		return $this->trainingDataStatistics;
	}

	#[ReturnTypeWillChange]
	public function jsonSerialize(): array {
		return [
			'active' => $this->isActive(),
			'recentModels' => $this->getRecentModels(),
			'trainingDataConfig' => $this->getTrainingDataConfig(),
			'trainingDataStats' => $this->getTrainingDataStatistics(),
		];
	}
}
