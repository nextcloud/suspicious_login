<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service\MLP;

/**
 * @psalm-immutable
 */
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
			330,
			2,
			0.005,
			2,
			0.007
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
	 *
	 * @return Config
	 */
	public function setEpochs(int $epochs): Config {
		$clone = clone $this;
		$clone->epochs = $epochs;
		return $clone;
	}

	/**
	 * @return int
	 */
	public function getLayers(): int {
		return $this->layers;
	}

	/**
	 * @param int $layers
	 *
	 * @return Config
	 */
	public function setLayers(int $layers): Config {
		$clone = clone $this;
		$clone->layers = $layers;
		return $clone;
	}

	/**
	 * @return float
	 */
	public function getShuffledNegativeRate(): float {
		return $this->shuffledNegativeRate;
	}

	/**
	 * @param float $shuffledNegativeRate
	 *
	 * @return Config
	 */
	public function setShuffledNegativeRate(float $shuffledNegativeRate): Config {
		$clone = clone $this;
		$clone->shuffledNegativeRate = $shuffledNegativeRate;
		return $clone;
	}

	/**
	 * @return float
	 */
	public function getRandomNegativeRate(): float {
		return $this->randomNegativeRate;
	}

	/**
	 * @param float $randomNegativeRate
	 *
	 * @return Config
	 */
	public function setRandomNegativeRate(float $randomNegativeRate): Config {
		$clone = clone $this;
		$clone->randomNegativeRate = $randomNegativeRate;
		return $clone;
	}

	/**
	 *
	 * @return float
	 */
	public function getLearningRate(): float {
		return $this->learningRate;
	}

	/**
	 * @param float $learningRate
	 *
	 * @return Config
	 */
	public function setLearningRate(float $learningRate): Config {
		$clone = clone $this;
		$clone->learningRate = $learningRate;
		return $clone;
	}
}
