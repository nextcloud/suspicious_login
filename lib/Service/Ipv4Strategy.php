<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service;

use OCA\SuspiciousLogin\Db\LoginAddressAggregatedMapper;
use OCA\SuspiciousLogin\Service\MLP\Config;
use function array_map;
use function explode;

class Ipv4Strategy extends AClassificationStrategy {
	#[\Override]
	public static function getTypeName(): string {
		return 'ipv4';
	}

	#[\Override]
	public function hasSufficientData(LoginAddressAggregatedMapper $loginAddressMapper, int $validationDays): bool {
		return $loginAddressMapper->hasSufficientIpV4Data($validationDays);
	}

	#[\Override]
	public function findHistoricAndRecent(LoginAddressAggregatedMapper $loginAddressMapper, int $validationThreshold, int $maxAge): array {
		return $loginAddressMapper->findHistoricAndRecentIpv4($validationThreshold, $maxAge);
	}

	#[\Override]
	public function generateRandomIpVector(): array {
		$ip = $this->generateRandomIp();
		$splitIp = explode('.', $ip);
		return $this->numStringsToBitArray($splitIp, 10, 8);
	}

	#[\Override]
	protected function ipToVec(string $ip): array {
		return $this->numStringsToBitArray(explode('.', $ip), 10, 8);
	}

	#[\Override]
	public function generateRandomIp(): string {
		// Exclude: 0/8 (reserved), 10/8 (RFC 1918), 127/8 (loopback), 224-255 (multicast/reserved).
		// 221 = 256 - 35 excluded first octets. Max after two +1 shifts: 221+2=223, so 224-255 never appear.
		$prefix = random_int(1, 221);
		if ($prefix >= 10) {
			$prefix += 1;
		}
		if ($prefix >= 127) {
			$prefix += 1;
		}

		// Exclude 172.16.0.0/12 and 192.168.0.0/16 (RFC 1918) via rejection sampling on the second octet.
		do {
			$second = random_int(0, 255);
		} while (($prefix === 172 && $second >= 16 && $second <= 31)
			|| ($prefix === 192 && $second === 168));

		return $prefix . '.' . $second . '.' . random_int(0, 255) . '.' . random_int(0, 255);
	}

	#[\Override]
	public function getSize(): int {
		return 16 + 32;
	}

	#[\Override]
	public function getDefaultMlpConfig(): Config {
		return Config::default();
	}
}
