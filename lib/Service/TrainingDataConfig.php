<?php

declare(strict_types=1);

/**
 * @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
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

use JsonSerializable;
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

	public static function default(int $time = null) {
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

	public function jsonSerialize() {
		return [
			'maxAge' => $this->getMaxAge(),
			'threshold' => $this->getThreshold(),
			'now' => $this->getNow(),
		];
	}
}
