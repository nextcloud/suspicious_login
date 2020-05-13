<?php

declare(strict_types=1);

namespace OCA\SuspiciousLogin\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version0Date20190123142603 extends SimpleMigrationStep {

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

		$table = $schema->getTable('suspicious_login');
		$table->addColumn('request_id', 'string', [
			'notnull' => false,
			'length' => 64,
		]);
		$table->addColumn('url', 'string', [
			'notnull' => false,
			'length' => 1024,
		]);

		return $schema;
	}
}
