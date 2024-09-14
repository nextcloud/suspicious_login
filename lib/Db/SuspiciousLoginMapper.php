<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

/**
 * @extends QBMapper<SuspiciousLogin>
 */
class SuspiciousLoginMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'suspicious_login');
	}

	public function findRelated(string $uid, string $ip, SuspiciousLogin $login, int $start): array {
		$qb = $this->db->getQueryBuilder();

		$query = $qb
			->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid)))
			->andWhere($qb->expr()->eq('ip', $qb->createNamedParameter($ip)))
			->andWhere($qb->expr()->gte('created_at', $qb->createNamedParameter($start)))
			->andWhere($qb->expr()->neq('id', $qb->createNamedParameter($login->getId())));

		return $this->findEntities($query);
	}

	public function findRecentByUid(string $uid, int $start) {
		$qb = $this->db->getQueryBuilder();

		$query = $qb
			->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid)))
			->andWhere($qb->expr()->gte('created_at', $qb->createNamedParameter($start)));

		return $this->findEntities($query);
	}
}
