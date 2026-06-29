<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use OCA\SuspiciousLogin\Service\Ipv4Strategy;

class Ipv4StrategyTest extends TestCase {
	private Ipv4Strategy $strategy;

	protected function setUp(): void {
		parent::setUp();
		$this->strategy = new Ipv4Strategy();
	}

	public function testGenerateRandomIpExcludesReservedFirstOctets(): void {
		$excluded = array_merge([0, 10, 127], range(224, 255));
		for ($i = 0; $i < 500; $i++) {
			$ip = $this->strategy->generateRandomIp();
			$firstOctet = (int)explode('.', $ip)[0];
			self::assertNotContains($firstOctet, $excluded, "IP $ip has excluded first octet $firstOctet");
		}
	}

	public function testGenerateRandomIpExcludesRfc1918Subnets(): void {
		for ($i = 0; $i < 500; $i++) {
			$ip = $this->strategy->generateRandomIp();
			[$first, $second] = array_map('intval', explode('.', $ip));
			self::assertFalse(
				$first === 172 && $second >= 16 && $second <= 31,
				"IP $ip is in 172.16.0.0/12"
			);
			self::assertFalse(
				$first === 192 && $second === 168,
				"IP $ip is in 192.168.0.0/16"
			);
		}
	}

	public function testGenerateRandomIpHasFourOctets(): void {
		$ip = $this->strategy->generateRandomIp();
		self::assertCount(4, explode('.', $ip));
	}

	public function testGenerateRandomIpBoundaryFirstOctets(): void {
		// Run many times to hit boundary values via statistical coverage
		$seen = [];
		for ($i = 0; $i < 5000; $i++) {
			$ip = $this->strategy->generateRandomIp();
			$seen[(int)explode('.', $ip)[0]] = true;
		}
		// Boundaries around skipped values should be reachable
		self::assertArrayHasKey(9, $seen, 'First octet 9 (just before 10) should be reachable');
		self::assertArrayHasKey(11, $seen, 'First octet 11 (just after 10) should be reachable');
		self::assertArrayHasKey(126, $seen, 'First octet 126 (just before 127) should be reachable');
		self::assertArrayHasKey(128, $seen, 'First octet 128 (just after 127) should be reachable');
		self::assertArrayHasKey(223, $seen, 'First octet 223 (just before excluded 224) should be reachable');
	}
}
