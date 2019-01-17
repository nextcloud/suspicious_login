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

namespace OCA\SuspiciousLogin\Service;

use function array_slice;
use OCA\SuspiciousLogin\Db\LoginAddressAggregatedMapper;
use OCA\SuspiciousLogin\Db\Model;
use OCP\AppFramework\Utility\ITimeFactory;
use Phpml\Classification\MLPClassifier;
use Phpml\Metric\ClassificationReport;
use Symfony\Component\Console\Output\OutputInterface;

class MLPTrainer {

	const LABEL_POSITIVE = 'y';
	const LABEL_NEGATIVE = 'n';

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

	public function train(OutputInterface $output,
						  float $shuffledNegativeRate,
						  float $randomNegativeRate,
						  int $epochs,
						  int $layers,
						  float $learningRate,
						  int $validationThreshold = 7,
						  int $maxAge = -1): Model {
		$testingDays = $this->timeFactory->getTime() - $validationThreshold * 60 * 60 * 24;
		$validationDays = $maxAge === -1 ? 0 : $this->timeFactory->getTime() - $maxAge * 60 * 60 * 24;
		list($historyRaw, $recentRaw) = $this->loginAddressMapper->findHistoricAndRecent(
			$testingDays,
			$validationDays
		);
		$positives = DataSet::fromLoginAddresses($historyRaw);
		$validationPositives = DataSet::fromLoginAddresses($recentRaw);
		$numValidation = count($validationPositives);
		$numPositives = count($positives);
		$numRandomNegatives = max((int)floor($numPositives * $randomNegativeRate), 1);
		$numShuffledNegative = max((int)floor($numPositives * $shuffledNegativeRate), 1);
		$randomNegatives = $this->negativeSampleGenerator->generateRandomFromPositiveSamples($positives, $numRandomNegatives);
		$shuffledNegatives = $this->negativeSampleGenerator->generateRandomFromPositiveSamples($positives, $numRandomNegatives);

		// Validation negatives are generated from all data (to have all UIDs), but shuffled
		$all = $positives->merge($validationPositives);
		$all->shuffle();
		$validationNegatives = $this->negativeSampleGenerator->generateRandomFromPositiveSamples($all, $numValidation);
		$validationSamples = $validationPositives->merge($validationNegatives);

		$total = $numPositives + $numRandomNegatives + $numShuffledNegative;
		$output->writeln("Got $total samples for training: $numPositives positive, $numRandomNegatives random negative and $numShuffledNegative shuffled negative");
		$output->writeln("Got $numValidation positive and $numValidation negative samples for validation");
		$output->writeln("Number of epochs: " . $epochs);
		$output->writeln("Number of hidden layers: " . $layers);
		$output->writeln("Learning rate: " . $learningRate);
		$output->writeln("Vecor dimensions: " . UidIPVector::SIZE);

		$allSamples = $positives->merge($randomNegatives)->merge($shuffledNegatives);
		$allSamples->shuffle();

		$output->writeln("Start training");
		$start = $this->timeFactory->getDateTime();
		$classifier = new MLPClassifier(UidIPVector::SIZE, [$layers], ['y', 'n'], $epochs, null, $learningRate);
		$classifier->train(
			$allSamples->asTrainingData(),
			$allSamples->getLabels()
		);
		$finished = $this->timeFactory->getDateTime();
		$elapsed = $finished->getTimestamp() - $start->getTimestamp();
		$output->writeln("Training finished after " . $elapsed . "s");

		$output->writeln("");
		$output->writeln("Run predictions on test data set");
		$predicted = $classifier->predict($validationSamples->asTrainingData());
		$result = new ClassificationReport($validationSamples->getLabels(), $predicted);
		$output->writeln("Predictions calculated");

		$output->writeln("");
		$output->writeln("Persisting trained model");
		$model = new Model();
		$model->setSamplesPositive($numPositives);
		$model->setSamplesShuffled($numShuffledNegative);
		$model->setSamplesRandom($numRandomNegatives);
		$model->setEpochs($epochs);
		$model->setLayers($layers);
		$model->setVectorDim(UidIPVector::SIZE);
		$model->setLearningRate($learningRate);
		$model->setPrecisionY($result->getPrecision()['y']);
		$model->setPrecisionN($result->getPrecision()['n']);
		$model->setRecallY($result->getRecall()['y']);
		$model->setRecallN($result->getRecall()['n']);
		$model->setDuration($elapsed);
		$model->setCreatedAt($this->timeFactory->getTime());
		$this->persistenceService->persist($classifier, $model);
		$modelId = $model->getId();
		$output->writeln("Model $modelId persisted");

		return $model;
	}

}
