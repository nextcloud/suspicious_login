<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service;

use OCA\SuspiciousLogin\Service\MLP\Trainer;
use Rubix\ML\Datasets\Dataset;
use Rubix\ML\Datasets\Labeled;
use function array_fill;
use function array_filter;
use function array_map;
use function array_merge;
use function array_slice;
use function random_int;
use function range;
use function str_split;

class NegativeSampleGenerator {
	/**
	 * Get IP vectors exclusively used by one user.
	 * Includes the user vector in second dimension of the returned array.
	 */
	private function getUniqueIPsPerUser(Dataset $positives): array {
		$map = [];

		// First, let's map (uid,ip) to id -> uid[]
		$max = count($positives);
		for ($i = 0; $i < $max; $i++) {
			$positive = $positives->sample($i);
			$uidVecStr = implode('', array_slice($positive, 0, 16));
			$ipVecStr = implode('', array_slice($positive, 16));
			if (!isset($map[$ipVecStr])) {
				$map[$ipVecStr] = [
					$uidVecStr,
				];
			} elseif (!in_array($uidVecStr, $map[$ipVecStr])) {
				$map[$ipVecStr][] = $uidVecStr;
			}
		}

		$uniqueMap = array_filter($map, function (array $uidVecs) {
			return count($uidVecs) === 1;
		});

		return array_map(function (string $ipVecStr): array {
			// Split the IP vec again, but also past the digits
			return array_map(function (string $c): int {
				return (int)$c;
			}, str_split($ipVecStr));
		}, array_keys($uniqueMap));
	}

	private function generateFromRealData(array $uidVec, array $uniqueIps): array {
		return array_merge(
			$uidVec,
			empty($uniqueIps) ? [] : $uniqueIps[random_int(0, count($uniqueIps) - 1)]
		);
	}

	private function generateRandom(array $uidVec, AClassificationStrategy $strategy): array {
		return array_merge(
			$uidVec,
			$strategy->generateRandomIpVector()
		);
	}

	/**
	 * @param DataSet $positives
	 * @param int $num
	 *
	 * @return DataSet
	 */
	public function generateRandomFromPositiveSamples(Dataset $positives, int $num, AClassificationStrategy $strategy): DataSet {
		$max = count($positives);
		return new Labeled(
			array_map(function (int $id) use ($strategy, $positives, $max) {
				return $this->generateRandom(array_slice($positives[$id % $max], 0, 16), $strategy);
			}, range(0, $num - 1)),
			array_fill(0, $num, Trainer::LABEL_NEGATIVE)
		);
	}

	/**
	 * @param DataSet $positives
	 * @param int $num
	 *
	 * @return DataSet
	 */
	public function generateShuffledFromPositiveSamples(DataSet $positives, int $num): DataSet {
		$max = count($positives);
		$uniqueIps = $this->getUniqueIPsPerUser($positives);

		return new Labeled(
			array_map(function (int $id) use ($uniqueIps, $positives, $max) {
				$sample = $positives->sample($id % $max);
				return $this->generateFromRealData(array_slice($sample, 0, 16), $uniqueIps);
			}, range(0, $num - 1)),
			array_fill(0, $num, Trainer::LABEL_NEGATIVE)
		);
	}
}
