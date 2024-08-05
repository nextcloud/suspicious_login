<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version4002Date20220922094803 extends SimpleMigrationStep {
	/**
	 * @var IDBConnection
	 */
	protected $connection;

	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 *
	 * @return ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$table = $schema->createTable('login_ips_aggregated');
		$table->addColumn('id', 'integer', [
			'autoincrement' => true,
			'notnull' => true,
			'length' => 4,
		]);
		$table->addColumn('uid', 'string', [
			'notnull' => true,
			'length' => 64,
		]);
		$table->addColumn('ip', 'string', [
			'notnull' => true,
			'length' => 64,
		]);
		$table->addColumn('seen', 'integer', [
			'notnull' => true,
			'length' => 4,
		]);
		$table->addColumn('first_seen', 'integer', [
			'notnull' => true,
			'length' => 4,
		]);
		$table->addColumn('last_seen', 'integer', [
			'notnull' => true,
			'length' => 4,
		]);
		$table->setPrimaryKey(['id']);
		$table->addUniqueIndex(['uid', 'ip']);

		return $schema;
	}

	public function postSchemaChange(IOutput $output, \Closure $schemaClosure, array $options): void {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();
		if (!$schema->hasTable('login_address_aggregated')) {
			return;
		}

		$insert = $this->connection->getQueryBuilder();
		$insert->insert('login_ips_aggregated')
			->values([
				'uid' => $insert->createParameter('uid'),
				'ip' => $insert->createParameter('ip'),
				'seen' => $insert->createParameter('seen'),
				'first_seen' => $insert->createParameter('first_seen'),
				'last_seen' => $insert->createParameter('last_seen'),
			]);

		$select = $this->connection->getQueryBuilder();
		$select->select('*')
			->from('login_address_aggregated')
			->where($select->expr()->gt('id', $select->createParameter('offset')))
			->orderBy('id', 'ASC')
			->setMaxResults(1000);

		$offset = -1;
		while ($offset !== 0) {
			$offset = $this->chunkedCopying($insert, $select, max($offset, 0));
		}
	}

	protected function chunkedCopying(IQueryBuilder $insert, IQueryBuilder $select, int $offset): int {
		$select->setParameter('offset', $offset);
		$newOffset = 0;

		$this->connection->beginTransaction();
		$result = $select->executeQuery();
		while ($row = $result->fetch()) {
			$insert
				->setParameter('uid', $row['uid'])
				->setParameter('ip', $row['ip'])
				->setParameter('seen', (int) $row['seen'], IQueryBuilder::PARAM_INT)
				->setParameter('first_seen', (int) $row['first_seen'], IQueryBuilder::PARAM_INT)
				->setParameter('last_seen', (int) $row['last_seen'], IQueryBuilder::PARAM_INT)
			;
			$insert->executeStatement();

			$newOffset = (int) $row['id'];
		}
		$result->closeCursor();
		$this->connection->commit();

		return $newOffset;
	}
}
