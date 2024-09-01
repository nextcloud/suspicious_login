<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service;

use OCA\SuspiciousLogin\Db\LoginAddressAggregatedMapper;
use OCA\SuspiciousLogin\Db\LoginAddressMapper;
use OCA\SuspiciousLogin\Db\ModelMapper;
use OCA\SuspiciousLogin\Service\Statistics\AppStatistics;
use OCA\SuspiciousLogin\Service\Statistics\TrainingDataStatistics;
use OCP\AppFramework\Db\DoesNotExistException;

class StatisticsService {

	/** @var LoginAddressMapper */
	private $loginAddressMapper;

	/** @var LoginAddressAggregatedMapper */
	private $loginAddressAggregatedMapper;

	/** @var ModelMapper */
	private $modelMapper;

	public function __construct(LoginAddressMapper $loginAddressMapper,
		LoginAddressAggregatedMapper $loginAddressAggregatedMapper,
		ModelMapper $modelMapper) {
		$this->loginAddressMapper = $loginAddressMapper;
		$this->loginAddressAggregatedMapper = $loginAddressAggregatedMapper;
		$this->modelMapper = $modelMapper;
	}

	public function getStatistics(): AppStatistics {
		return new AppStatistics(
			$this->isActive(),
			array_merge(
				$this->modelMapper->findMostRecent(14, Ipv4Strategy::getTypeName()),
				$this->modelMapper->findMostRecent(14, IpV6Strategy::getTypeName())
			),
			TrainingDataConfig::default(),
			$this->getTrainingDataStats()
		);
	}

	protected function isActive(): bool {
		try {
			$this->modelMapper->findLatest(Ipv4Strategy::getTypeName());
			return true;
		} catch (DoesNotExistException $ex) {
			return false;
		}
	}

	private function getTrainingDataStats(): TrainingDataStatistics {
		return new TrainingDataStatistics(
			$this->loginAddressMapper->getCount() + $this->loginAddressAggregatedMapper->getTotalCount(),
			$this->loginAddressAggregatedMapper->getCount()
		);
	}
}
