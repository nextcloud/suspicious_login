<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service\Statistics;

use JsonSerializable;
use ReturnTypeWillChange;

class TrainingDataStatistics implements JsonSerializable {

	/** @var int */
	private $loginsCaptured;

	/** @var int */
	private $loginsAggregated;

	public function __construct(int $loginsCaptured,
		int $loginsAggregated) {
		$this->loginsCaptured = $loginsCaptured;
		$this->loginsAggregated = $loginsAggregated;
	}

	/**
	 * @return int
	 */
	public function getLoginsCaptured(): int {
		return $this->loginsCaptured;
	}

	/**
	 * @return int
	 */
	public function getLoginsAggregated(): int {
		return $this->loginsAggregated;
	}

	#[ReturnTypeWillChange]
	public function jsonSerialize(): array {
		return [
			'loginsCaptured' => $this->getLoginsCaptured(),
			'loginsAggregated' => $this->getLoginsAggregated(),
		];
	}
}
