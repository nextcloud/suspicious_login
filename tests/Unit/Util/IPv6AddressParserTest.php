<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Tests\Unit\Util;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\SuspiciousLogin\Util\IPv6AddressParser;

class IPv6AddressParserTest extends TestCase {
	public static function stripScopeIdentifierData(): array {
		return [
			// No scope identifier – address returned unchanged
			['::1', '::1'],
			['fe80::1', 'fe80::1'],
			['2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
			// Typical scope identifier
			['fe80::1%eth0', 'fe80::1'],
			['fe80::1%lo', 'fe80::1'],
			// Trailing % (empty zone id)
			['fe80::1%', 'fe80::1'],
			// Multiple % – only the first delimiter counts
			['fe80::1%eth0%extra', 'fe80::1'],
			// Non-IPv6 input that contains %
			['1.2.3.4%eth0', '1.2.3.4'],
			['example.com%zone', 'example.com'],
			// Empty string
			['', ''],
		];
	}

	/**
	 * @dataProvider stripScopeIdentifierData
	 */
	public function testStripScopeIdentifier(string $input, string $expected): void {
		$result = IPv6AddressParser::stripScopeIdentifier($input);

		$this->assertSame($expected, $result);
	}
}
