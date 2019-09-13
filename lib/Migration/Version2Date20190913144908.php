<?php

declare(strict_types=1);

namespace OCA\SuspiciousLogin\Migration;

use Closure;
use OCA\SuspiciousLogin\Service\Ipv4Strategy;
use OCA\SuspiciousLogin\Service\NotificationState;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version2Date20190913144908 extends SimpleMigrationStep {

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
		$table->addColumn('notification_state', 'integer', [
			'notnull' => false,
			'length' => 4,
		]);

		return $schema;
	}

}
