<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service;

use Rubix\ML\Datasets\Labeled;

class CollectedData {
	public function __construct(
		private readonly Labeled $trainingPositives,
		private readonly Labeled $validationPositives,
	) {
	}

	public function getTrainingPositives(): Labeled {
		return $this->trainingPositives;
	}

	public function getValidationPositives(): Labeled {
		return $this->validationPositives;
	}
}
