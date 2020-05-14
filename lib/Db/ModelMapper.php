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

namespace OCA\SuspiciousLogin\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class ModelMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'suspicious_login_model');
	}

	public function find(int $id): Model {
		$qb = $this->db->getQueryBuilder();

		$query = $qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));

		return $this->findEntity($query);
	}

	/**
	 * @param string $addressType
	 *
	 * @return Model
	 * @throws DoesNotExistException
	 */
	public function findLatest(string $addressType): Model {
		$qb = $this->db->getQueryBuilder();

		$query = $qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('address_type', $qb->createNamedParameter($addressType)))
			->orderBy('created_at', 'desc')
			->setMaxResults(1);

		return $this->findEntity($query);
	}

	/**
	 * @param int $max maximum number of models
	 * @param string $addressType
	 *
	 * @return Model[]
	 */
	public function findMostRecent(int $max, string $addressType): array {
		$qb = $this->db->getQueryBuilder();

		$query = $qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('address_type', $qb->createNamedParameter($addressType)))
			->orderBy('created_at', 'desc')
			->setMaxResults($max);

		return $this->findEntities($query);
	}
}
