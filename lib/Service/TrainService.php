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

	/** @psalm-mutation-free */
	public function __construct(
		private readonly DataLoader $dataLoader,
		private readonly Trainer $trainer,
		private readonly ModelStore $store,
	) {
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
