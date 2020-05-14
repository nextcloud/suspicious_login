<?php

declare(strict_types=1);

/**
 * @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
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

use OCA\SuspiciousLogin\Db\LoginAddressAggregatedMapper;
use OCA\SuspiciousLogin\Db\LoginAddressMapper;
use OCA\SuspiciousLogin\Db\ModelMapper;
use OCA\SuspiciousLogin\Service\Statistics\AppStatistics;
use OCA\SuspiciousLogin\Service\Statistics\TrainingDataStatistics;
use OCP\AppFramework\Db\DoesNotExistException;

class AdminService {

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
