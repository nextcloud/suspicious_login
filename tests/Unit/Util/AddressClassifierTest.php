<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Tests\Unit\Util;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\SuspiciousLogin\Util\AddressClassifier;

class AddressClassifierTest extends TestCase {
	public function ipV4Data(): array {
		return [
			['1.2.3.4', true],
			['12.34.56.78', true],
			['12.34.56.', false],
			['123.123.123.123', true],
			['1234.1234.1234.1234', false],
			['::1', false],
		];
	}

	/**
	 * @dataProvider ipV4Data
	 */
	public function testIpV4($address, $expected) {
		$isIpV4 = AddressClassifier::isIpV4($address);

		$this->assertEquals($expected, $isIpV4);
	}

	public function ipV6Data(): array {
		return [
			['1.2.3.4', false],
			['12.34.56.78', false],
			['12.34.56.', false],
			['123.123.123.123', false],
			['1234.1234.1234.1234', false],
			['::1', true],
			['2001:0db8:85a3:0000:0000:8a2e:0370:7334', true],
			['::ffff:192.0.2.128', true],
		];
	}

	/**
	 * @dataProvider ipV6Data
	 */
	public function testIpV6($address, $expected) {
		$isIpV4 = AddressClassifier::isIpV6($address);

		$this->assertEquals($expected, $isIpV4);
	}
}
