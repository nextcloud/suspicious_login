<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Exception;

class InsufficientDataException extends ServiceException {
	public function __construct(string $message = "") {
		parent::__construct(
			$message === "" ? "Insufficient data" : "Insufficient data: $message"
		);
	}
}
