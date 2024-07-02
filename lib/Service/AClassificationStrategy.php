<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service;

use OCA\SuspiciousLogin\Db\LoginAddressAggregated;
use OCA\SuspiciousLogin\Db\LoginAddressAggregatedMapper;
use OCA\SuspiciousLogin\Service\MLP\Config;
use function array_map;
use function array_merge;
use function array_reduce;
use function base_convert;
use function str_pad;
use function str_split;

abstract class AClassificationStrategy {
	abstract public static function getTypeName(): string;

	abstract public function hasSufficientData(LoginAddressAggregatedMapper $loginAddressMapper, int $validationDays): bool;

	/**
	 * @return LoginAddressAggregated[][]
	 */
	abstract public function findHistoricAndRecent(LoginAddressAggregatedMapper $loginAddressMapper, int $validationThreshold, int $maxAge): array;

	/**
	 * @return int[]
	 */
	public function newVector(string $uid, string $ip): array {
		return array_merge(
			$this->uidAsFeatureVector($uid),
			$this->ipToVec($ip)
		);
	}

	/**
	 * @return float[]
	 */
	public function generateRandomIpVector(): array {
		return $this->ipToVec($this->generateRandomIp());
	}

	/**
	 * @param string $ip
	 *
	 * @return int[]
	 */
	abstract protected function ipToVec(string $ip): array;

	/**
	 * @param string $uid
	 *
	 * @return int[]
	 */
	protected function uidAsFeatureVector(string $uid): array {
		// TODO: just convert to binary and do substr of that
		$splitHash = str_split(
			substr(
				md5($uid),
				0,
				4
			)
		);
		return $this->numStringsToBitArray($splitHash, 16, 4);
	}

	protected function numStringsToBitArray(array $strings, $base, $padding): array {
		$converted = array_reduce(array_map(function (string $s) use ($base, $padding) {
			return $this->numStringToBitArray($s, $base, $padding);
		}, $strings), 'array_merge', []);
		return array_map(function ($x) {
			return (int)$x;
		}, $converted);
	}

	protected function numStringToBitArray(string $s, int $base, int $padding): array {
		$bin = base_convert($s, $base, 2);
		// make sure we get 00000000 to 11111111
		$padded = str_pad($bin, $padding, '0', STR_PAD_LEFT);
		return str_split($padded);
	}

	abstract public function generateRandomIp(): string;

	abstract public function getSize(): int;

	abstract public function getDefaultMlpConfig(): Config;
}
