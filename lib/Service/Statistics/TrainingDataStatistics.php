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

	public function __construct(
		private readonly int $loginsCaptured,
		private readonly int $loginsAggregated,
	) {
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

	#[\Override]
	#[ReturnTypeWillChange]
	public function jsonSerialize(): array {
		return [
			'loginsCaptured' => $this->getLoginsCaptured(),
			'loginsAggregated' => $this->getLoginsAggregated(),
		];
	}
}
