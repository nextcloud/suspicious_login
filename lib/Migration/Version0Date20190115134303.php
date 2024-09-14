<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0Date20190115134303 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 *
	 * @return ISchemaWrapper|null
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		/**
		 * Replaced by Version4002Date20220922094803 for Oracle support
		 * $table = $schema->createTable('login_address_aggregated');
		 * $table->addColumn('id', 'integer', [
		 * 'autoincrement' => true,
		 * 'notnull' => true,
		 * 'length' => 4,
		 * ]);
		 * $table->addColumn('uid', 'string', [
		 * 'notnull' => true,
		 * 'length' => 64,
		 * ]);
		 * $table->addColumn('ip', 'string', [
		 * 'notnull' => true,
		 * 'length' => 64,
		 * ]);
		 * $table->addColumn('seen', 'integer', [
		 * 'notnull' => true,
		 * 'length' => 4,
		 * ]);
		 * $table->addColumn('first_seen', 'integer', [
		 * 'notnull' => true,
		 * 'length' => 4,
		 * ]);
		 * $table->addColumn('last_seen', 'integer', [
		 * 'notnull' => true,
		 * 'length' => 4,
		 * ]);
		 * $table->setPrimaryKey(['id']);
		 * $table->addUniqueIndex(['uid', 'ip']);
		 *
		 * return $schema;
		 */
		return null;
	}
}
