<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
