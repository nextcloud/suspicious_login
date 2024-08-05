<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0Date20181214083108 extends SimpleMigrationStep {

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

		$table = $schema->getTable('suspicious_login_model');
		$table->getColumn('type')
			->setLength(128);
		$table->getColumn('learning_rate')
			->setPrecision(10)
			->setDefault(null)
			->setScale(5);
		$table->getColumn('precision_y')
			->setPrecision(10)
			->setDefault(null)
			->setScale(5);
		$table->getColumn('precision_n')
			->setPrecision(10)
			->setDefault(null)
			->setScale(5);
		$table->getColumn('recall_y')
			->setPrecision(10)
			->setDefault(null)
			->setScale(5);
		$table->getColumn('recall_n')
			->setPrecision(10)
			->setDefault(null)
			->setScale(5);

		return $schema;
	}
}
