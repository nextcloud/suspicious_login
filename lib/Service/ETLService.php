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

namespace OCA\SuspiciousLogin\Service;

use Generator;
use OCA\SuspiciousLogin\Db\LoginAddressAggregatedMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\ILogger;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ETLService {
	public const MAX_BATCH_SIZE = 10000;

	/** @var IDBConnection */
	private $db;

	/** @var LoginAddressAggregatedMapper */
	private $aggregatedMapper;

	/** @var ILogger */
	private $logger;

	public function __construct(IDBConnection $db,
								LoginAddressAggregatedMapper $aggregatedMapper,
								ILogger $logger) {
		$this->db = $db;
		$this->aggregatedMapper = $aggregatedMapper;
		$this->logger = $logger;
	}

	private function getRaw(int $max, OutputInterface $output = null): Generator {
		if (!is_null($output)) {
			$progress = new ProgressBar($output);
		}
		$cnt = 0;
		$selectQuery = $this->db->getQueryBuilder()
			->select('id', 'ip', 'uid', 'created_at')
			->from('login_address')
			->setMaxResults(100);

		do {
			$data = $selectQuery->execute();
			$rows = $data->fetchAll();
			foreach ($rows as $row) {
				yield $row;
				$cnt++;
				if (!is_null($output)) {
					$progress->advance();
				}
			}
			$this->logger->debug($cnt . ' rows read for ETL');
		} while ($cnt < $max && !empty($rows));

		if (!is_null($output)) {
			$progress->finish();
		}
	}

	/**
	 * Extract raw login data and feed it into the aggregated table
	 */
	public function extractAndTransform(int $max = self::MAX_BATCH_SIZE, OutputInterface $output = null) {
		$this->logger->debug('starting login data ETL process');
		$this->db->beginTransaction();

		$insert = $this->db->getQueryBuilder();
		$insert
			->insert('login_ips_aggregated')
			->values([
				'uid' => $insert->createParameter('uid'),
				'ip' => $insert->createParameter('ip'),
				'seen' => $insert->createParameter('seen'),
				'first_seen' => $insert->createParameter('ts'),
				'last_seen' => $insert->createParameter('ts'),
			]);
		$select = $this->db->getQueryBuilder();
		$select
			->select('seen', 'first_seen', 'last_seen')
			->from('login_ips_aggregated')
			->where($select->expr()->eq('uid', $select->createParameter('uid')))
			->andWhere($select->expr()->eq('ip', $select->createParameter('ip')));
		$update = $this->db->getQueryBuilder();
		$update
			->update('login_ips_aggregated')
			->set('seen', $update->createParameter('seen'))
			->set('first_seen', $update->createParameter('first_seen'))
			->set('last_seen', $update->createParameter('last_seen'))
			->where($update->expr()->eq('uid', $update->createParameter('uid')))
			->andWhere($update->expr()->eq('ip', $update->createParameter('ip')));
		$cleanUp = $this->db->getQueryBuilder();
		$cleanUp
			->delete('login_address')
			->where($select->expr()->eq('id', $select->createParameter('id')));

		foreach ($this->getRaw($max, $output) as $row) {
			$select
				->setParameter('uid', $row['uid'])
				->setParameter('ip', $row['ip']);
			$result = $select->execute();
			$existing = $result->fetch();
			$result->closeCursor();
			if (empty($existing)) {
				$insert->setParameter('uid', $row['uid']);
				$insert->setParameter('ip', $row['ip']);
				$insert->setParameter('seen', 1, IQueryBuilder::PARAM_INT);
				$insert->setParameter('ts', $row['created_at'], IQueryBuilder::PARAM_INT);
				$insert->execute();
			} else {
				$update
					->setParameter('uid', $row['uid'])
					->setParameter('ip', $row['ip'])
					->setParameter('seen', $existing['seen'] + 1)
					->setParameter('first_seen', min($existing['first_seen'], $row['created_at']))
					->setParameter('last_seen', max($existing['last_seen'], $row['created_at']));
				$update->execute();

				$cleanUp
					->setParameter('id', $row['id']);
				$cleanUp->execute();
			}
		}
		$this->logger->debug('finished login data ETL process, sending transaction commit');
		$this->db->commit();
		$this->logger->debug('ETL finished');
	}
}
