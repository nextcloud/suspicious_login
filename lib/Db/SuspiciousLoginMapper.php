<?php

declare(strict_types=1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
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
 *
 */

namespace OCA\SuspiciousLogin\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

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
