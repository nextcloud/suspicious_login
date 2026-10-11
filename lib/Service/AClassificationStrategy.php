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
	/** @psalm-pure */
	abstract public static function getTypeName(): string;

	/** @psalm-impure */
	abstract public function hasSufficientData(LoginAddressAggregatedMapper $loginAddressMapper, int $validationDays): bool;

	/**
	 * @psalm-impure
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
	 * @psalm-pure
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

	/**
	 * @psalm-param 10|16 $base
	 * @psalm-param 4|8 $padding
	 */
	protected function numStringsToBitArray(array $strings, int $base, int $padding): array {
		$converted = array_reduce(array_map(fn (string $s) => $this->numStringToBitArray($s, $base, $padding), $strings), array_merge(...), []);
		return array_map(fn ($x) => (int)$x, $converted);
	}

	/** @psalm-pure */
	protected function numStringToBitArray(string $s, int $base, int $padding): array {
		$bin = base_convert($s, $base, 2);
		// make sure we get 00000000 to 11111111
		$padded = str_pad($bin, $padding, '0', STR_PAD_LEFT);
		return str_split($padded);
	}

	/** @psalm-impure */
	abstract public function generateRandomIp(): string;

	/** @psalm-pure */
	abstract public function getSize(): int;

	/** @psalm-mutation-free */
	abstract public function getDefaultMlpConfig(): Config;
}
