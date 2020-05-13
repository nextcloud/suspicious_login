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
