<?php

declare(strict_types=1);

/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\SuspiciousLogin\Service\MLP;

class Config {

	/** @var int */
	private $epochs;

	/** @var int */
	private $layers;

	/** @var float */
	private $shuffledNegativeRate;

	/** @var float */
	private $randomNegativeRate;

	/** @var float */
	private $learningRate;

	public function __construct(int $epochs,
								int $layers,
								float $shuffledNegativeRate,
								float $randomNegativeRate,
								float $learningRate) {
		$this->epochs = $epochs;
		$this->layers = $layers;
		$this->shuffledNegativeRate = $shuffledNegativeRate;
		$this->randomNegativeRate = $randomNegativeRate;
		$this->learningRate = $learningRate;
	}

	public static function default() {
		return new static(
			250,
			10,
			1.0,
			1.0,
			0.05
		);
	}

	/**
	 * @return int
	 */
	public function getEpochs(): int {
		return $this->epochs;
	}

	/**
	 * @param int $epochs
	 */
	public function setEpochs(int $epochs) {
		$this->epochs = $epochs;
	}

	/**
	 * @return int
	 */
	public function getLayers(): int {
		return $this->layers;
	}

	/**
	 * @param int $layers
	 */
	public function setLayers(int $layers) {
		$this->layers = $layers;
	}

	/**
	 * @return float
	 */
	public function getShuffledNegativeRate(): float {
		return $this->shuffledNegativeRate;
	}

	/**
	 * @param float $shuffledNegativeRate
	 */
	public function setShuffledNegativeRate(float $shuffledNegativeRate) {
		$this->shuffledNegativeRate = $shuffledNegativeRate;
	}

	/**
	 * @return float
	 */
	public function getRandomNegativeRate(): float {
		return $this->randomNegativeRate;
	}

	/**
	 * @param float $randomNegativeRate
	 */
	public function setRandomNegativeRate(float $randomNegativeRate) {
		$this->randomNegativeRate = $randomNegativeRate;
	}

	/**
	 * @return float
	 */
	public function getLearningRate(): float {
		return $this->learningRate;
	}

	/**
	 * @param float $learningRate
	 */
	public function setLearningRate(float $learningRate) {
		$this->learningRate = $learningRate;
	}

}
