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

use OCA\SuspiciousLogin\Db\Model;
use OCA\SuspiciousLogin\Exception\InsufficientDataException;
use OCA\SuspiciousLogin\Exception\ServiceException;
use OCA\SuspiciousLogin\Service\AClassificationStrategy;
use OCA\SuspiciousLogin\Service\TrainingDataConfig;
use OCA\SuspiciousLogin\Service\TrainingDataSet;
use OCA\SuspiciousLogin\Service\TrainingResult;
use OCP\AppFramework\Utility\ITimeFactory;
use Rubix\ML\Classifiers\MultilayerPerceptron;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use Rubix\ML\NeuralNet\ActivationFunctions\Sigmoid;
use Rubix\ML\NeuralNet\Layers\Activation;
use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Optimizers\Adam;
use function array_map;
use function range;

class Trainer {
	public const LABEL_POSITIVE = 'y';
	public const LABEL_NEGATIVE = 'n';

	/** @var ITimeFactory */
	private $timeFactory;

	public function __construct(ITimeFactory $timeFactory) {
		$this->timeFactory = $timeFactory;
	}

	/**
	 * @param Config $config
	 * @param TrainingDataConfig $dataConfig
	 * @param AClassificationStrategy $strategy
	 *
	 * @retrun TrainingResult
	 *
	 * @throws ServiceException
	 * @throws InsufficientDataException
	 */
	public function train(Config $config,
						  TrainingDataSet $dataSet,
						  AClassificationStrategy $strategy): TrainingResult {
		$start = $this->timeFactory->getDateTime();
		$layers = array_map(function () use ($strategy) {
			return new Dense($strategy->getSize());
		}, range(0, $config->getLayers() - 2));
		$layers[] = new Activation(new Sigmoid());
		$classifier = new MultilayerPerceptron(
			$layers,
			128,
			new Adam($config->getLearningRate()),
			1e-4,
			$config->getEpochs()
		);
		$classifier->train($dataSet->getTrainingData());
		$finished = $this->timeFactory->getDateTime();
		$elapsed = $finished->getTimestamp() - $start->getTimestamp();

		$predicted = $classifier->predict($dataSet->getValidationData());
		$reportGenerator = new MulticlassBreakdown();
		$report = $reportGenerator->generate($predicted, $dataSet->getValidationData()->labels());

		$model = new Model();
		$model->setSamplesPositive($dataSet->getNumPositives());
		$model->setSamplesShuffled($dataSet->getNumShuffledNegatives());
		$model->setSamplesRandom($dataSet->getNumRandomNegatives());
		$model->setEpochs($config->getEpochs());
		$model->setLayers($config->getLayers());
		$model->setVectorDim($strategy->getSize());
		$model->setLearningRate($config->getLearningRate());
		$model->setPrecisionY($report['classes'][self::LABEL_POSITIVE]['precision']);
		$model->setPrecisionN($report['classes'][self::LABEL_NEGATIVE]['precision']);
		$model->setRecallY($report['classes'][self::LABEL_POSITIVE]['recall']);
		$model->setRecallN($report['classes'][self::LABEL_NEGATIVE]['recall']);
		$model->setDuration($elapsed);
		$model->setAddressType($strategy::getTypeName());
		$model->setCreatedAt($this->timeFactory->getTime());

		return new TrainingResult($classifier, $model, $report);
	}
}
