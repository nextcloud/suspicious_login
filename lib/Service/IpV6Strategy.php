<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service;

use InvalidArgumentException;
use OCA\SuspiciousLogin\Db\LoginAddressAggregatedMapper;
use OCA\SuspiciousLogin\Service\MLP\Config;
use function array_map;
use function base_convert;
use function bin2hex;
use function implode;
use function str_pad;
use function str_split;
use function substr;

class IpV6Strategy extends AClassificationStrategy {
	public static function getTypeName(): string {
		return 'ipv6';
	}

	public function hasSufficientData(LoginAddressAggregatedMapper $loginAddressMapper, int $validationDays): bool {
		return $loginAddressMapper->hasSufficientIpV6Data($validationDays);
	}

	public function findHistoricAndRecent(LoginAddressAggregatedMapper $loginAddressMapper, int $validationThreshold, int $maxAge): array {
		return $loginAddressMapper->findHistoricAndRecentIpv6($validationThreshold, $maxAge);
	}

	protected function ipToVec(string $ip): array {
		$addr = inet_pton($ip);
		if ($addr === false) {
			throw new InvalidArgumentException('Invalid IPv6 address');
		}

		$hex = bin2hex($addr);
		$padded = str_pad($hex, 32, '0', STR_PAD_LEFT);
		$binString = implode('', array_map(function (string $h) {
			return str_pad(base_convert($h, 16, 2), 4, '0', STR_PAD_LEFT);
		}, str_split($padded)));
		$mostSign = substr($binString, 0, 64);

		return array_map(
			function (string $bit) {
				return (int)$bit;
			},
			str_split($mostSign)
		);
	}

	public function generateRandomIp(): string {
		//  The whole IPv6 address space is too large. Randomly generated addresses
		// add too much noise to the training data, so we have to constrain it on
		// the addresses that could theoretically be used for logins.
		//  fe80::/64 (Link Local Addresses) might never be "suspicious" logins, as it
		// will always be just a "local" login.
		//  fc00::/7 (Unicast Local Addresses) could be used for company deployments.
		// However, the users logging in are always coming from the "inside". These
		// logins *could* be suspicious but it depends a lot on the setup.
		//  2000::/3 (Global Unicast Addresses) is the range we are most interested in,
		// as this is *the* internet. However, only 2000::/4 is allocated so far and it
		// might take ages until the first 3000::/4 block could be considered to be
		// allocated somewhere — maybe for Mars? So we focus only on 2000::/4 addresses.
		$ip = '2' . str_pad(base_convert((string)random_int(0, 2 ** 12 - 1), 10, 16), 3, '0', STR_PAD_LEFT) . ':';
		$ip .= implode(':', array_map(function (int $index) {
			return base_convert((string)random_int(0, 2 ** 16 - 1), 10, 16);
		}, range(1, 7)));

		return $ip;
	}

	public function getSize(): int {
		return 16 + 64;
	}

	public function getDefaultMlpConfig(): Config {
		return Config::default()->setEpochs(20);
	}
}
