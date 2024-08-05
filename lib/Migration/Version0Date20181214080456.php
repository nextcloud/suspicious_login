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

class Version0Date20181214080456 extends SimpleMigrationStep {

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
		$table->addColumn('type', 'string', [
			'notnull' => false,
			'length' => 32,
		]);
		$table->addColumn('app_version', 'integer', [
			'notnull' => false,
			'length' => 32,
		]);
		$table->addColumn('samples_positive', 'integer', [
			'notnull' => false,
			'length' => 4,
		]);
		$table->addColumn('samples_shuffled', 'integer', [
			'notnull' => false,
			'length' => 4,
		]);
		$table->addColumn('samples_random', 'integer', [
			'notnull' => false,
			'length' => 4,
		]);
		$table->addColumn('epochs', 'integer', [
			'notnull' => false,
			'length' => 4,
		]);
		$table->addColumn('layers', 'integer', [
			'notnull' => false,
			'length' => 4,
		]);
		$table->addColumn('vector_dim', 'integer', [
			'notnull' => false,
			'length' => 4,
		]);
		$table->addColumn('learning_rate', 'decimal', [
			'notnull' => false,
			'length' => 4,
		]);
		$table->addColumn('precision_y', 'decimal', [
			'notnull' => false,
			'length' => 4,
		]);
		$table->addColumn('precision_n', 'decimal', [
			'notnull' => false,
			'length' => 4,
		]);
		$table->addColumn('recall_y', 'decimal', [
			'notnull' => false,
			'length' => 4,
		]);
		$table->addColumn('recall_n', 'decimal', [
			'notnull' => false,
			'length' => 4,
		]);
		$table->addColumn('duration', 'integer', [
			'notnull' => false,
			'length' => 4,
		]);

		return $schema;
	}
}
