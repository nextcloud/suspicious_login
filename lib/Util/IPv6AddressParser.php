<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Util;

final class IPv6AddressParser {
	/**
	 * As described in RFC 4007, IPv6 addresses may include an optional
	 * scope identifier attached to the end (e.g., `%eth1`). We must
	 * filter out the scope, as the current model doesn't support
	 * processing this information.
	 */
	public static function stripScopeIdentifier(string $address): string {
		$delimiterPosition = strpos($address, '%');
		$scopeIdentifierExists = $delimiterPosition !== false;

		return $scopeIdentifierExists
			? substr($address, 0, $delimiterPosition)
			: $address;
	}
}
