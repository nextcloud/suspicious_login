<?php

declare(strict_types=1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
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
 *
 */

namespace OCA\SuspiciousLogin\Service\MLP;

use OCA\SuspiciousLogin\Db\LoginAddressAggregatedMapper;
use OCA\SuspiciousLogin\Db\Model;
use OCA\SuspiciousLogin\Exception\InsufficientDataException;
use OCA\SuspiciousLogin\Exception\ServiceException;
use OCA\SuspiciousLogin\Service\DataSet;
use OCA\SuspiciousLogin\Service\IClassificationStrategy;
use OCA\SuspiciousLogin\Service\ModelPersistenceService;
use OCA\SuspiciousLogin\Service\NegativeSampleGenerator;
use OCA\SuspiciousLogin\Service\TrainingDataConfig;
use OCP\AppFramework\Utility\ITimeFactory;
use Phpml\Classification\MLPClassifier;
use Phpml\Metric\ClassificationReport;

class Trainer {
	public const LABEL_POSITIVE = 'y';
	public const LABEL_NEGATIVE = 'n';

	/** @var LoginAddressAggregatedMapper */
	private $loginAddressMapper;

	/** @var NegativeSampleGenerator */
	private $negativeSampleGenerator;

	/** @var ITimeFactory */
	private $timeFactory;

	/** @var ModelPersistenceService */
	private $persistenceService;

	public function __construct(LoginAddressAggregatedMapper $loginAddressMapper,
								NegativeSampleGenerator $negativeSampleGenerator,
								ITimeFactory $timeFactory,
								ModelPersistenceService $persistenceService) {
		$this->loginAddressMapper = $loginAddressMapper;
		$this->negativeSampleGenerator = $negativeSampleGenerator;
		$this->timeFactory = $timeFactory;
		$this->persistenceService = $persistenceService;
	}

	/**
	 * @param Config $config
	 * @param int $validationThreshold
	 * @param int $maxAge
	 *
	 * @throws ServiceException
	 * @throws InsufficientDataException
	 *
	 * @return Model
	 */
	public function train(Config $config,
						  TrainingDataConfig $dataConfig,
						  IClassificationStrategy $strategy): Model {
		$testingDays = $dataConfig->getNow() - $dataConfig->getThreshold() * 60 * 60 * 24;
		$validationDays = $dataConfig->getMaxAge() === -1 ? 0 : $dataConfig->getNow() - $dataConfig->getMaxAge() * 60 * 60 * 24;

		if (!$strategy->hasSufficientData($this->loginAddressMapper, $validationDays)) {
			throw new InsufficientDataException("Not enough data for the specified maximum age");
		}
		list($historyRaw, $recentRaw) = $strategy->findHistoricAndRecent(
			$this->loginAddressMapper,
			$testingDays,
			$validationDays
		);
		if (empty($historyRaw)) {
			throw new InsufficientDataException("No historic data available");
		}
		if (empty($recentRaw)) {
			throw new InsufficientDataException("No recent data available");
		}
		$positives = DataSet::fromLoginAddresses($historyRaw, $strategy);
		$validationPositives = DataSet::fromLoginAddresses($recentRaw, $strategy);
		$numValidation = count($validationPositives);
		$numPositives = count($positives);
		$numRandomNegatives = max((int)floor($numPositives * $config->getRandomNegativeRate()), 1);
		$numShuffledNegative = max((int)floor($numPositives * $config->getShuffledNegativeRate()), 1);
		$randomNegatives = $this->negativeSampleGenerator->generateRandomFromPositiveSamples($positives, $numRandomNegatives, $strategy);
		$shuffledNegatives = $this->negativeSampleGenerator->generateRandomFromPositiveSamples($positives, $numRandomNegatives, $strategy);

		// Validation negatives are generated from all data (to have all UIDs), but shuffled
		$all = $positives->merge($validationPositives);
		$all->shuffle();
		$validationNegatives = $this->negativeSampleGenerator->generateRandomFromPositiveSamples($all, $numValidation, $strategy);
		$validationSamples = $validationPositives->merge($validationNegatives);

		$allSamples = $positives->merge($randomNegatives)->merge($shuffledNegatives);
		$allSamples->shuffle();

		$start = $this->timeFactory->getDateTime();
		$classifier = new MLPClassifier(
			$strategy->getSize(),
			[$config->getLayers()],
			['y', 'n'],
			$config->getEpochs(),
			null,
			$config->getLearningRate()
		);
		$classifier->train(
			$allSamples->asTrainingData(),
			$allSamples->getLabels()
		);
		$finished = $this->timeFactory->getDateTime();
		$elapsed = $finished->getTimestamp() - $start->getTimestamp();

		$predicted = $classifier->predict($validationSamples->asTrainingData());
		$result = new ClassificationReport($validationSamples->getLabels(), $predicted);

		$model = new Model();
		$model->setSamplesPositive($numPositives);
		$model->setSamplesShuffled($numShuffledNegative);
		$model->setSamplesRandom($numRandomNegatives);
		$model->setEpochs($config->getEpochs());
		$model->setLayers($config->getLayers());
		$model->setVectorDim($strategy->getSize());
		$model->setLearningRate($config->getLearningRate());
		$model->setPrecisionY($result->getPrecision()['y']);
		$model->setPrecisionN($result->getPrecision()['n']);
		$model->setRecallY($result->getRecall()['y']);
		$model->setRecallN($result->getRecall()['n']);
		$model->setDuration($elapsed);
		$model->setAddressType($strategy::getTypeName());
		$model->setCreatedAt($this->timeFactory->getTime());
		$this->persistenceService->persist($classifier, $model);

		return $model;
	}
}
