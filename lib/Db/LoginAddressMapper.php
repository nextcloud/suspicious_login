<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

/**
 * @extends QBMapper<LoginAddress>
 */
class LoginAddressMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'login_address');
	}

	public function getCount(): int {
		$qb = $this->db->getQueryBuilder();

		$qb->select($qb->createFunction('COUNT(*)'))
			->from($this->getTableName());
		$result = $qb->execute();
		$cnt = $result->fetchColumn();
		$result->closeCursor();

		return (int)$cnt;
	}
}
