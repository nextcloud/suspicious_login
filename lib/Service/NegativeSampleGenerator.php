<?php

declare(strict_types=1);

/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\SuspiciousLogin\Service;

use Rubix\ML\Datasets\Dataset;
use Rubix\ML\Datasets\Labeled;
use function array_fill;
use function array_filter;
use function array_map;
use OCA\SuspiciousLogin\Service\MLP\Trainer;
use function array_merge;
use function array_slice;
use function random_int;
use function range;

class NegativeSampleGenerator {
	private function getUniqueIPsPerUser(Dataset $positives): array {
		$ips = [];

		// First, let's map (uid,ip) to uid -> [ip]
		$max = count($positives);
		for ($i = 0; $i < $max; $i++) {
			$positive = $positives[$i];
			if (!isset($ips[$positive->getUid()])) {
				$ips[$positive->getUid()] = [
					$positive->getIp(),
				];
			} else {
				$ips[$positive->getUid()][] = $positive->getIp();
			}
		}

		$uniqueIps = [];
		foreach ($ips as $uid => $userIps) {
			$uniqueIps[$uid] = array_filter($userIps, function (string $ip) use ($ips, $uid) {
				foreach ($ips as $other => $otherIps) {
					if ($other === $uid) {
						return false;
					}

					// If the IP is not found for other users it's unique
					return !in_array($ip, $otherIps);
				}
			});
		}

		return $uniqueIps;
	}

	private function generateFromRealData(string $uid, array $uniqueIps, bool $v4 = true): array {
		return [
			'uid' => $uid,
			'ip' => $uniqueIps[random_int(0, count($uniqueIps) - 1)],
			'label' => Trainer::LABEL_NEGATIVE,
		];
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
	public function generateShuffledFromPositiveSamples(DataSet $positives, int $num, bool $v4 = true): DataSet {
		$max = count($positives);
		$uniqueIps = $this->getUniqueIPsPerUser($positives);

		return new Labeled(
			array_map(function (int $id) use ($v4, $uniqueIps, $positives, $max) {
				return $this->generateFromRealData($positives[$id % $max]->getUid(), $uniqueIps, $v4);
			}, range(0, $num - 1)),
			array_fill(0, $num, Trainer::LABEL_NEGATIVE)
		);
	}
}
