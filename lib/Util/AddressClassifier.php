<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Util;

use function filter_var;

class AddressClassifier {
	public static function isIpV4(string $address): bool {
		return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
	}

	public static function isIpV6(string $address): bool {
		return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
	}
}
