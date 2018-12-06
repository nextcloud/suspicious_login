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

use function array_diff;
use function array_filter;
use function array_keys;
use function array_search;
use Exception;
use function random_int;

class NegativeSampleGenerator {

	private function getUniqueIPsPerUser(DataSet $positives): array {
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
					return array_search($ip, $otherIps) === false;
				}
			});
		}

		return $uniqueIps;
	}

	private function findRandomIp(string $uid, array $uniqueIps, int $maxRec = 10): string {
		if ($maxRec === 0) {
			throw new Exception("Could not generate negative sample off real data for $uid. Is there enough data for training?");
		}

		$rand = random_int(0, count($uniqueIps) - 1);
		$randUid = array_keys($uniqueIps)[$rand];
		if ($randUid === $uid) {
			return $this->findRandomIp($uid, $uniqueIps, $maxRec - 1);
		}
		$randIdx = random_int(0, count($uniqueIps[$randUid]) - 1);
		return $uniqueIps[$randUid][$randIdx];
	}

	private function generateFromRealData(string $uid, array $uniqueIps): array {
		return [
			'uid' => $uid,
			'ip' => implode('.', [
				random_int(0, 255),
				random_int(0, 255),
				random_int(0, 255),
				random_int(0, 255),
			]),
			'label' => MLPTrainer::LABEL_NEGATIVE,
		];
	}

	private function generateRandom(string $uid): array {
		return [
			'uid' => $uid,
			'ip' => implode('.', [
				random_int(0, 255),
				random_int(0, 255),
				random_int(0, 255),
				random_int(0, 255),
			]),
			'label' => MLPTrainer::LABEL_NEGATIVE,
		];
	}

	/**
	 * @param DataSet $positives
	 * @param int $num
	 *
	 * @todo generate negative samples by mixing unrelated positive uids and positive ips
	 *
	 * @return DataSet
	 */
	public function generateRandomFromPositiveSamples(DataSet $positives, int $num): DataSet {
		$max = count($positives);

		return new DataSet(array_map(function (int $id) use ($positives, $max) {
			return $this->generateRandom($positives[$id % $max]->getUid());
		}, range(0, $num - 1)));
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

		return new DataSet(array_map(function (int $id) use ($uniqueIps, $positives, $max) {
			return $this->generateFromRealData($positives[$id % $max]->getUid(), $uniqueIps);
		}, range(0, $num - 1)));
	}

}
