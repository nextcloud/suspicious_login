<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use OCA\SuspiciousLogin\Service\IpV6Strategy;

class IpV6StrategyTest extends TestCase {
	private IpV6Strategy $strategy;

	protected function setUp(): void {
		parent::setUp();
		$this->strategy = new IpV6Strategy();
	}

	public function testGenerateRandomIpIsInGlobalUnicastRange(): void {
		for ($i = 0; $i < 100; $i++) {
			$ip = $this->strategy->generateRandomIp();
			$firstGroup = hexdec(explode(':', $ip)[0]);
			self::assertGreaterThanOrEqual(0x2000, $firstGroup, "IP $ip is below 2000::");
			self::assertLessThanOrEqual(0x2fff, $firstGroup, "IP $ip is above 2fff::");
		}
	}

	public function testGenerateRandomIpHasEightGroups(): void {
		$ip = $this->strategy->generateRandomIp();
		self::assertCount(8, explode(':', $ip));
	}
}
