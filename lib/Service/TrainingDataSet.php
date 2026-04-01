<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service;

use Rubix\ML\Datasets\Labeled;

/**
 * @psalm-immutable
 */
class TrainingDataSet {

	public function __construct(
		private readonly Labeled $trainingData,
		private readonly Labeled $validationData,
		private readonly int $numPositives,
		private readonly int $numShuffledNegatives,
		private readonly int $numRandomNegatives,
	) {
	}

	/**
	 * @return Labeled
	 */
	public function getTrainingData(): Labeled {
		return $this->trainingData;
	}

	/**
	 * @return Labeled
	 */
	public function getValidationData(): Labeled {
		return $this->validationData;
	}

	/**
	 * @return int
	 */
	public function getNumPositives(): int {
		return $this->numPositives;
	}

	/**
	 * @return int
	 */
	public function getNumShuffledNegatives(): int {
		return $this->numShuffledNegatives;
	}

	/**
	 * @return int
	 */
	public function getNumRandomNegatives(): int {
		return $this->numRandomNegatives;
	}
}
