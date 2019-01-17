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

class LoginAddressAggregatedMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'login_address_aggregated');
	}

	public function findAll() {
		$qb = $this->db->getQueryBuilder();

		$query = $qb
			->select('uid', 'ip', 'seen', 'first_seen', 'last_seen')
			->from($this->getTableName());

		return $this->findEntities($query);
	}

	private function findHistoric(int $threshold, int $maxAge): array {
		$qb = $this->db->getQueryBuilder();

		$query = $qb
			->select('uid', 'ip', 'seen', 'first_seen', 'last_seen')
			->from($this->getTableName())
			->where($qb->expr()->gte('last_seen', $qb->createNamedParameter($maxAge)))
			->andWhere($qb->expr()->lte('last_seen', $qb->createNamedParameter($threshold)));

		return $this->findEntities($query);
	}

	private function findRecent(int $threshold): array {
		$qb = $this->db->getQueryBuilder();

		$query = $qb
			->select('uid', 'ip', 'seen', 'first_seen', 'last_seen')
			->from($this->getTableName())
			->andWhere($qb->expr()->gt('last_seen', $qb->createNamedParameter($threshold)));

		return $this->findEntities($query);
	}

	public function findHistoricAndRecent(int $threshold, int $maxAge = 0): array {
		return [
			$this->findHistoric($threshold, $maxAge),
			$this->findRecent($threshold),
		];
	}

}
