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

namespace OCA\SuspiciousLogin\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version0Date20190115134303 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 *
	 * @return ISchemaWrapper
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
