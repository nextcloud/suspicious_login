<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service;

use OCA\SuspiciousLogin\Db\LoginAddressAggregated;
use OCA\SuspiciousLogin\Db\LoginAddressAggregatedMapper;
use OCA\SuspiciousLogin\Exception\InsufficientDataException;
use OCA\SuspiciousLogin\Service\MLP\Config;
use OCA\SuspiciousLogin\Service\MLP\Trainer;
use Rubix\ML\Datasets\Dataset;
use Rubix\ML\Datasets\Labeled;
use function array_fill;
use function array_map;
use function array_merge;
use function floor;
use function log;
use function max;

class DataLoader {

	/** @var LoginAddressAggregatedMapper */
	private $loginAddressMapper;

	/** @var NegativeSampleGenerator */
	private $negativeSampleGenerator;

	public function __construct(LoginAddressAggregatedMapper $loginAddressMapper,
		NegativeSampleGenerator $negativeSampleGenerator) {
		$this->loginAddressMapper = $loginAddressMapper;
		$this->negativeSampleGenerator = $negativeSampleGenerator;
	}

	/**
	 * @param TrainingDataConfig $dataConfig
	 * @param AClassificationStrategy $strategy
	 *
	 * @throws InsufficientDataException
	 */
	public function loadTrainingAndValidationData(TrainingDataConfig $dataConfig,
		AClassificationStrategy $strategy): CollectedData {
		$validationThreshold = $dataConfig->getNow() - $dataConfig->getThreshold() * 60 * 60 * 24;
		$maxAge = $dataConfig->getMaxAge() === -1 ? 0 : $dataConfig->getNow() - $dataConfig->getMaxAge() * 60 * 60 * 24;

		if (!$strategy->hasSufficientData($this->loginAddressMapper, $maxAge)) {
			throw new InsufficientDataException("Not enough data for the specified maximum age");
		}
		[$historyRaw, $recentRaw] = $strategy->findHistoricAndRecent(
			$this->loginAddressMapper,
			$validationThreshold,
			$maxAge
		);
		\OCP\Log\logger('suspicious_login')->debug('Loaded ' . count($historyRaw) . ' historic and ' . count($recentRaw) . ' recent rows with strategy ' . $strategy::class);
		if (empty($historyRaw)) {
			throw new InsufficientDataException("No historic data available");
		}
		if (empty($recentRaw)) {
			throw new InsufficientDataException("No recent data available");
		}

		$positives = $this->addressesToDataSet($historyRaw, $strategy);
		\OCP\Log\logger('suspicious_login')->debug('Converted historic data to dataset');
		$validationPositives = $this->addressesToDataSet($recentRaw, $strategy);
		\OCP\Log\logger('suspicious_login')->debug('Converted recent data to dataset');

		return new CollectedData(
			$positives,
			$validationPositives
		);
	}

	/**
	 * @param list<LoginAddressAggregated> $loginAddresses
	 */
	private function addressesToDataSet(array $loginAddresses, AClassificationStrategy $strategy): Labeled {
		$deep = array_map(function (LoginAddressAggregated $addr) use ($strategy) {
			$multiplier = (int)log((int)$addr->getSeen(), 2);
			return array_fill(0, $multiplier, $strategy->newVector($addr->getUid(), $addr->getIp()));
		}, $loginAddresses);
		$samples = array_merge(...$deep);

		return new Labeled(
			$samples,
			array_fill(0, count($samples), Trainer::LABEL_POSITIVE)
		);
	}

	/**
	 * @param Dataset $validationPositives
	 * @param Dataset $positives
	 * @param Config $config
	 * @param AClassificationStrategy $strategy
	 *
	 * @return TrainingDataSet
	 */
	public function generateRandomShuffledData(CollectedData $collectedData,
		Config $config,
		AClassificationStrategy $strategy): TrainingDataSet {
		$numPositives = count($collectedData->getTrainingPositives());
		$numValidation = count($collectedData->getValidationPositives());
		$numRandomNegatives = max((int)floor($numPositives * $config->getRandomNegativeRate()), 1);
		$numShuffledNegative = max((int)floor($numPositives * $config->getShuffledNegativeRate()), 1);
		$randomNegatives = $this->negativeSampleGenerator->generateRandomFromPositiveSamples($collectedData->getTrainingPositives(), $numRandomNegatives, $strategy);
		$shuffledNegatives = $this->negativeSampleGenerator->generateShuffledFromPositiveSamples($collectedData->getTrainingPositives(), $numShuffledNegative);

		// Validation negatives are generated from all data (to have all UIDs), but shuffled
		$all = $collectedData->getTrainingPositives()->merge($collectedData->getValidationPositives());
		$all->randomize();
		$validationNegatives = $this->negativeSampleGenerator->generateRandomFromPositiveSamples($all, $numValidation, $strategy);
		$validationSamples = $collectedData->getValidationPositives()->merge($validationNegatives);

		$allSamples = $collectedData->getTrainingPositives()->merge($randomNegatives)->merge($shuffledNegatives);
		$allSamples->randomize();

		return new TrainingDataSet(
			$allSamples,
			$validationSamples,
			$numPositives,
			$numShuffledNegative,
			$numRandomNegatives
		);
	}
}
