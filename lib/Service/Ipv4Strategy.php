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
	public static function getTypeName(): string {
		return 'ipv4';
	}

	public function hasSufficientData(LoginAddressAggregatedMapper $loginAddressMapper, int $validationDays): bool {
		return $loginAddressMapper->hasSufficientIpV4Data($validationDays);
	}

	public function findHistoricAndRecent(LoginAddressAggregatedMapper $loginAddressMapper, int $validationThreshold, int $maxAge): array {
		return $loginAddressMapper->findHistoricAndRecentIpv4($validationThreshold, $maxAge);
	}

	public function generateRandomIpVector(): array {
		$ip = $this->generateRandomIp();
		$splitIp = explode('.', $ip);
		return $this->numStringsToBitArray($splitIp, 10, 8);
	}

	protected function ipToVec(string $ip): array {
		return $this->numStringsToBitArray(explode('.', $ip), 10, 8);
	}

	public function generateRandomIp(): string {
		// 000/8 is reserved for local identification
		$prefix = random_int(1, 255 - 18);

		// 010/8 is reserved for private use
		if ($prefix >= 10) {
			$prefix += 1;
		}
		// 127/8 is reserved for loopback
		if ($prefix >= 127) {
			$prefix += 1;
		}
		// 224/8 - 239/8 (224/4) is used for multicast.
		if ($prefix >= 224) {
			$prefix += 16;
		}

		return $prefix . implode('.', array_map(function (int $index) {
			return random_int(0, 255);
		}, range(1, 3)));
	}

	public function getSize(): int {
		return 16 + 32;
	}

	public function getDefaultMlpConfig(): Config {
		return Config::default();
	}
}
