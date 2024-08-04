<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
