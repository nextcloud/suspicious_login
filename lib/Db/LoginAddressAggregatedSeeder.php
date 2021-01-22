<?php

declare(strict_types=1);

/*
 * @copyright 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\SuspiciousLogin\Db;

use OCA\SuspiciousLogin\Service\AClassificationStrategy;
use OCP\IDBConnection;

class LoginAddressAggregatedSeeder {

	/** @var IDBConnection */
	private $db;

	/** @var LoginAddressAggregatedMapper */
	private $mapper;

	public function __construct(IDBConnection $db,
								LoginAddressAggregatedMapper $mapper) {
		$this->db = $db;
		$this->mapper = $mapper;
	}

	public function seed(AClassificationStrategy $strategy): int {
		$now = time();

		$this->db->beginTransaction();
		$numberOfUsers = 20;
		$maxRowsPerUser = 10;
		$total = 0;
		for ($i = 0; $i <= $numberOfUsers; $i++) {
			$numberOfRows = random_int((int) ($maxRowsPerUser * 0.3), (int) ($maxRowsPerUser * 1.3));
			for ($j = 0; $j <= $numberOfRows; $j++) {
				$this->insertRow($now, $i, $strategy);
				$total++;
			}
		}

		$this->db->commit();

		return $total;
	}

	/**
	 * @param int $now
	 * @param int $userId
	 * @param AClassificationStrategy $strategy
	 */
	private function insertRow(int $now, int $userId, AClassificationStrategy $strategy): void {
		$address = new LoginAddressAggregated();
		$address->setUid("suspicious-user-$now-$userId");
		$address->setIp($strategy->generateRandomIp());
		$address->setSeen(random_int(1, 20));
		$firstSeen = random_int($now - 3600 * 24 * 120, $now);
		$address->setFirstSeen($firstSeen);
		$address->setLastSeen(random_int($firstSeen + 1, $now));
		$this->mapper->insert($address);
	}
}
