<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

/**
 * @extends QBMapper<LoginAddressAggregated>
 */
class LoginAddressAggregatedMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'login_ips_aggregated');
	}

	public function findAllIpV4() {
		$qb = $this->db->getQueryBuilder();

		$query = $qb
			->select('uid', 'ip', 'seen', 'first_seen', 'last_seen')
			->from($this->getTableName())
			->where($qb->expr()->like('ip', $qb->createNamedParameter('_%._%._%._%')));

		return $this->findEntities($query);
	}

	/**
	 * Check if data exists that has been seen before $start
	 *
	 * This allows checking if the specific max age of data rows will be filled
	 * with actual data, e.g. shortly after the app was installed and little/no
	 * data has been collected.
	 *
	 * @param int $start
	 *
	 * @return bool
	 */
	public function hasSufficientIpV4Data(int $start): bool {
		$qb = $this->db->getQueryBuilder();

		$query = $qb
			->select($qb->createFunction('COUNT(*)'))
			->from($this->getTableName())
			->where($qb->expr()->andX(
				$qb->expr()->like('ip', $qb->createNamedParameter('_%._%._%._%')),
				$qb->expr()->lte('first_seen', $qb->createNamedParameter($start))
			));

		$result = $query->execute();
		$count = (int)$result->fetchColumn();
		$result->closeCursor();

		return $count > 0;
	}

	private function findHistoricIpv4(int $threshold, int $maxAge): array {
		$qb = $this->db->getQueryBuilder();

		$query = $qb
			->select('uid', 'ip', 'seen', 'first_seen', 'last_seen')
			->from($this->getTableName())
			->where($qb->expr()->andX(
				$qb->expr()->like('ip', $qb->createNamedParameter('_%._%._%._%')),
				$qb->expr()->gte('last_seen', $qb->createNamedParameter($maxAge)),
				$qb->expr()->lte('first_seen', $qb->createNamedParameter($threshold))
			))
			->orderBy('last_seen', 'DESC') // Use most recent data in case of limiting
			->setMaxResults(15_000); // More data will like exhaust memory

		return $this->findEntities($query);
	}

	private function findRecentIpV4(int $threshold): array {
		$qb = $this->db->getQueryBuilder();

		$query = $qb
			->select('uid', 'ip', 'seen', 'first_seen', 'last_seen')
			->from($this->getTableName())
			->where($qb->expr()->andX(
				$qb->expr()->like('ip', $qb->createNamedParameter('_%._%._%._%')),
				$qb->expr()->gt('last_seen', $qb->createNamedParameter($threshold))
			))
			->orderBy('last_seen', 'DESC') // Use most recent data in case of limiting
			->setMaxResults(3_000); // More data will like exhaust memory;

		return $this->findEntities($query);
	}

	public function findHistoricAndRecentIpv4(int $threshold, int $maxAge = 0): array {
		return [
			$this->findHistoricIpv4($threshold, $maxAge),
			$this->findRecentIpV4($threshold),
		];
	}

	public function hasSufficientIpV6Data(int $start) {
		$qb = $this->db->getQueryBuilder();

		$query = $qb
			->select($qb->createFunction('COUNT(*)'))
			->from($this->getTableName())
			->where($qb->expr()->andX(
				$qb->expr()->notLike('ip', $qb->createNamedParameter('_%._%._%._%')),
				$qb->expr()->lte('first_seen', $qb->createNamedParameter($start))
			));

		$result = $query->execute();
		$count = (int)$result->fetchColumn();
		$result->closeCursor();

		return $count > 0;
	}

	public function getCount(): int {
		$qb = $this->db->getQueryBuilder();

		$qb->select($qb->createFunction('COUNT(*)'))
			->from($this->getTableName());
		$result = $qb->execute();
		$cnt = $result->fetchColumn();
		$result->closeCursor();

		return (int)$cnt;
	}

	public function getTotalCount(): int {
		$qb = $this->db->getQueryBuilder();

		$qb->select($qb->createFunction('SUM(seen)'))
			->from($this->getTableName());
		$result = $qb->execute();
		$cnt = $result->fetchColumn();
		$result->closeCursor();

		return (int)$cnt;
	}

	private function findHistoricIpv6(int $threshold, int $maxAge): array {
		$qb = $this->db->getQueryBuilder();

		$query = $qb
			->select('uid', 'ip', 'seen', 'first_seen', 'last_seen')
			->from($this->getTableName())
			->where($qb->expr()->andX(
				$qb->expr()->notLike('ip', $qb->createNamedParameter('_%._%._%._%')),
				$qb->expr()->gte('last_seen', $qb->createNamedParameter($maxAge)),
				$qb->expr()->lte('first_seen', $qb->createNamedParameter($threshold))
			))
			->orderBy('last_seen', 'DESC') // Use most recent data in case of limiting
			->setMaxResults(15_000); // More data will like exhaust memory;

		return $this->findEntities($query);
	}

	private function findRecentIpV6(int $threshold): array {
		$qb = $this->db->getQueryBuilder();

		$query = $qb
			->select('uid', 'ip', 'seen', 'first_seen', 'last_seen')
			->from($this->getTableName())
			->where($qb->expr()->andX(
				$qb->expr()->notLike('ip', $qb->createNamedParameter('_%._%._%._%')),
				$qb->expr()->gt('last_seen', $qb->createNamedParameter($threshold))
			))
			->orderBy('last_seen', 'DESC') // Use most recent data in case of limiting
			->setMaxResults(3_000); // More data will like exhaust memory

		return $this->findEntities($query);
	}

	public function findHistoricAndRecentIpv6(int $threshold, int $maxAge = 0) {
		return [
			$this->findHistoricIpv6($threshold, $maxAge),
			$this->findRecentIpV6($threshold),
		];
	}
}
