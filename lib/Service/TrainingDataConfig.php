<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service;

use JsonSerializable;
use ReturnTypeWillChange;
use function time;

class TrainingDataConfig implements JsonSerializable {

	/** @var int */
	private $maxAge;

	/** @var int */
	private $threshold;

	/** @var int */
	private $now;

	public function __construct(int $maxAge,
		int $threshold,
		int $now) {
		$this->maxAge = $maxAge;
		$this->threshold = $threshold;
		$this->now = $now;
	}

	public static function default(?int $time = null) {
		return new self(60, 7, $time ?? time());
	}

	/**
	 * @return int
	 */
	public function getMaxAge(): int {
		return $this->maxAge;
	}

	/**
	 * @param int $maxAge
	 *
	 * @return TrainingDataConfig
	 */
	public function setMaxAge(int $maxAge): TrainingDataConfig {
		$clone = clone $this;
		$clone->maxAge = $maxAge;
		return $clone;
	}

	/**
	 * @return int
	 */
	public function getThreshold(): int {
		return $this->threshold;
	}

	/**
	 * @param int $threshold
	 *
	 * @return TrainingDataConfig
	 */
	public function setThreshold(int $threshold): TrainingDataConfig {
		$clone = clone $this;
		$clone->threshold = $threshold;
		return $clone;
	}

	/**
	 * @return int
	 */
	public function getNow(): int {
		return $this->now;
	}

	/**
	 * @param int $now
	 *
	 * @return TrainingDataConfig
	 */
	public function setNow(int $now): TrainingDataConfig {
		$clone = clone $this;
		$clone->now = $now;
		return $clone;
	}

	#[ReturnTypeWillChange]
	public function jsonSerialize(): array {
		return [
			'maxAge' => $this->getMaxAge(),
			'threshold' => $this->getThreshold(),
			'now' => $this->getNow(),
		];
	}
}
