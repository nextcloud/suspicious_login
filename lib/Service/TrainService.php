<?php

declare(strict_types=1);

/*
 * @copyright 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
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

use OCA\SuspiciousLogin\Db\Model;
use OCA\SuspiciousLogin\Exception\InsufficientDataException;
use OCA\SuspiciousLogin\Exception\ServiceException;
use OCA\SuspiciousLogin\Service\MLP\Config;
use OCA\SuspiciousLogin\Service\MLP\Trainer;

class TrainService {

	/** @var DataLoader */
	private $dataLoader;

	/** @var Trainer */
	private $trainer;

	/** @var ModelStore */
	private $store;

	public function __construct(DataLoader $dataLoader,
								Trainer $trainer,
								ModelStore $store) {
		$this->trainer = $trainer;
		$this->store = $store;
		$this->dataLoader = $dataLoader;
	}

	/**
	 * @param Config $config
	 * @param TrainingDataConfig $dataConfig
	 * @param AClassificationStrategy $strategy
	 *
	 * @return Model
	 *
	 * @throws InsufficientDataException
	 * @throws ServiceException
	 */
	public function train(Config $config,
						  TrainingDataConfig $dataConfig,
						  AClassificationStrategy $strategy): Model {
		// Load
		$collectedData = $this->dataLoader->loadTrainingAndValidationData(
			$dataConfig,
			$strategy
		);
		$data = $this->dataLoader->generateRandomShuffledData(
			$collectedData,
			$config,
			$strategy
		);

		// Train
		$result = $this->trainer->train(
			$config,
			$data,
			$strategy
		);

		// Persist
		$this->store->persist(
			$result->getClassifier(),
			$result->getModel()
		);

		return $result->getModel();
	}
}
