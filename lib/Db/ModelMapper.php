<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Model>
 */
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
