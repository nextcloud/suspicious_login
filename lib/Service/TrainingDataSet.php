<?php

declare(strict_types=1);

/*
 * @copyright 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\SuspiciousLogin\Service;

use Rubix\ML\Datasets\Labeled;

/**
 * @psalm-immutable
 */
class TrainingDataSet {

	/**
	 * @var Labeled
	 */
	private $trainingData;
	/**
	 * @var Labeled
	 */
	private $validationData;
	/**
	 * @var int
	 */
	private $numPositives;
	/**
	 * @var int
	 */
	private $numShuffledNegatives;
	/**
	 * @var int
	 */
	private $numRandomNegatives;

	public function __construct(Labeled $trainingData,
								Labeled $validationData,
								int $numPositives,
								int $numShuffledNegatives,
								int $numRandomNegatives) {
		$this->trainingData = $trainingData;
		$this->validationData = $validationData;
		$this->numPositives = $numPositives;
		$this->numShuffledNegatives = $numShuffledNegatives;
		$this->numRandomNegatives = $numRandomNegatives;
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
