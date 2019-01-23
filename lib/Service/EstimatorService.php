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

class EstimatorService {

	/** @var ModelPersistenceService */
	private $persistenceService;

	/** @var ILogger */
	private $logger;

	public function __construct(ModelPersistenceService $persistenceService,
								ILogger $logger) {
		$this->persistenceService = $persistenceService;
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
	public function predict(string $uid, string $ip, int $modelId = null): bool {
		if (is_null($modelId)) {
			$estimatorModel = $this->persistenceService->loadLatest();
		} else {
			$estimatorModel = $this->persistenceService->load($modelId);
		}

		$data = new DataSet([
			[
				'uid' => $uid,
				'ip' => $ip,
				'label' => '',
			]
		]);

		$predictions = $estimatorModel->predict($data->asTrainingData());

		return $predictions[0] === 'y';
	}

}
