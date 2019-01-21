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

use OCA\SuspiciousLogin\Db\SuspiciousLogin;
use OCA\SuspiciousLogin\Db\SuspiciousLoginMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\ILogger;
use Throwable;

class LoginClassifier {

	/** @var EstimatorService */
	private $estimator;

	/** @var ILogger */
	private $logger;

	/** @var SuspiciousLoginMapper */
	private $mapper;

	/** @var ITimeFactory */
	private $timeFactory;

	public function __construct(EstimatorService $estimator,
								ILogger $logger,
								SuspiciousLoginMapper $mapper,
								ITimeFactory $timeFactory) {
		$this->estimator = $estimator;
		$this->logger = $logger;
		$this->mapper = $mapper;
		$this->timeFactory = $timeFactory;
	}

	public function process(string $uid, string $ip) {
		if ($this->estimator->predict($uid, $ip)) {
			// All good, carry on!
			return;
		}

		$this->logger->warning("detected a login from a suspicious login. user=$uid ip=$ip");
		try {
			$entity = new SuspiciousLogin();
			$entity->setUid($uid);
			$entity->setIp($ip);
			$entity->setCreatedAt($this->timeFactory->getTime());

			$this->mapper->insert($entity);
		} catch (Throwable $ex) {
			$this->logger->critical("could not save the details of a suspicious login");
			$this->logger->logException($ex);
		}
	}

}
