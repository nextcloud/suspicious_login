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

use OCA\SuspiciousLogin\Exception\ServiceException;
use OCP\ILogger;
use Rubix\ML\Datasets\Unlabeled;
use RuntimeException;

class EstimatorService {

	/** @var ModelStore */
	private $modelStore;

	/** @var ILogger */
	private $logger;

	public function __construct(ModelStore $modelStore,
								ILogger $logger) {
		$this->modelStore = $modelStore;
		$this->logger = $logger;
	}

	/**
	 * @param string $uid
	 * @param string $ip
	 * @param int|null $modelId
	 *
	 * @return bool
	 * @throws ServiceException
	 */
	public function predict(string $uid, string $ip, AClassificationStrategy $strategy, int $modelId = null): bool {
		try {
			if ($modelId === null) {
				$this->logger->debug("loading latest model");

				$estimator = $this->modelStore->loadLatest($strategy);
			} else {
				$this->logger->debug("loading model $modelId");

				$estimator = $this->modelStore->load($modelId);
			}
		} catch (RuntimeException $e) {
			throw new ServiceException("Could not load model $modelId to classify UID $uid and IP $ip: " . $e->getMessage(), $e->getCode(), $e);
		}

		$data = new Unlabeled([
			$strategy->newVector($uid, $ip),
		]);

		$predictions = $estimator->predict($data);

		return $predictions[0] === 'y';
	}
}
