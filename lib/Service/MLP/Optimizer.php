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

namespace OCA\SuspiciousLogin\Service\MLP;

use OCA\SuspiciousLogin\Service\IClassificationStrategy;
use function array_map;
use function array_sum;
use function mt_getrandmax;
use function mt_rand;
use OCA\SuspiciousLogin\Db\Model;
use OCA\SuspiciousLogin\Service\TrainingDataConfig;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Optimize the MLP trainer with simulated annealing
 */
class Optimizer {

	const INITIAL_STEP_WIDTH = 0.8;
	const STEP_WIDTH_FACTOR = 0.985;

	/** @var Trainer */
	private $trainer;

	private $parameterMaximum = [
		'epochs' => [50, 1000],
		'layers' => [6, 14],
		'shuffledNegativeRate' => [0.1, 1.5],
		'randomNegativeRate' => [0.1, 1.5],
		'learningRate' => [0.001, 0.1],
	];

	public function __construct(Trainer $trainer) {
		$this->trainer = $trainer;
	}

	private function printConfig(int $epoch,
								 float $stepWidth,
								 Config $config,
								 OutputInterface $output) {
		$epochs = sprintf("%4d", $config->getEpochs());
		$layers = sprintf("%2d", $config->getLayers());
		$shuffledRate = sprintf("%1.3f", $config->getShuffledNegativeRate());
		$randomRate = sprintf("%1.3f", $config->getRandomNegativeRate());
		$learningRate = sprintf("%1.3f", $config->getLearningRate());

		$output->writeln("Epoch $epoch: epochs=$epochs layers=$layers shuffledRate=$shuffledRate randomRate=$randomRate, learningRate=$learningRate");
		$output->writeln("  Step width for next config neighbor: $stepWidth");
	}

	/**
	 * @param Model[] ...$models
	 */
	private function getAverageCost(...$models): float {
		$costs = array_map(function (Model $model) {
			return ($model->getPrecisionN() + $model->getRecallN()) / 2;
		}, $models);

		return array_sum($costs) / count($costs);
	}

	private function getRandomIntParam(int $current,
									   int $min,
									   int $max,
									   float $stepWidth): int {
		$range = $max - $min;
		$newVal = $current
			+ $stepWidth * $range * mt_rand(0, mt_getrandmax()) / mt_getrandmax()
			- $stepWidth * $range / 2;
		return (int)max($min, min($max, $newVal));
	}

	private function getRandomFloatParam(float $current,
										 float $min,
										 float $max,
										 float $stepWidth): float {
		$range = $max - $min;
		$newVal = $current
			+ $stepWidth * $range * mt_rand(0, mt_getrandmax()) / mt_getrandmax()
			- $stepWidth * $range / 2;
		return max($min, min($max, $newVal));
	}

	private function getNeighborConfig(Config $config, float $stepWidth): Config {
		return $config
			->setEpochs(
				$this->getRandomIntParam(
					$config->getEpochs(),
					$this->parameterMaximum['epochs'][0],
					$this->parameterMaximum['epochs'][1],
					$stepWidth
				)
			)
			->setLayers(
				$this->getRandomIntParam(
					$config->getLayers(),
					$this->parameterMaximum['layers'][0],
					$this->parameterMaximum['layers'][1],
					$stepWidth
				)
			)
			->setShuffledNegativeRate(
				$this->getRandomFloatParam(
					$config->getShuffledNegativeRate(),
					$this->parameterMaximum['shuffledNegativeRate'][0],
					$this->parameterMaximum['shuffledNegativeRate'][1],
					$stepWidth
				)
			)
			->setRandomNegativeRate(
				$this->getRandomFloatParam(
					$config->getRandomNegativeRate(),
					$this->parameterMaximum['randomNegativeRate'][0],
					$this->parameterMaximum['randomNegativeRate'][1],
					$stepWidth
				)
			)
			->setLearningRate(
				$this->getRandomFloatParam(
					$config->getLearningRate(),
					$this->parameterMaximum['learningRate'][0],
					$this->parameterMaximum['learningRate'][1],
					$stepWidth
				)
			);
	}

	public function optimize(int $maxEpochs,
							 IClassificationStrategy $strategy,
							 OutputInterface $output,
							 Config $initialConfig = null) {
		$epochs = 0;
		$stepWidth = self::INITIAL_STEP_WIDTH;
		// Start with random config if none was passed (breadth-first search)
		$config = $initialConfig ?? $this->getNeighborConfig($strategy->getDefaultMlpConfig(), $stepWidth);
		$dataConfig = TrainingDataConfig::default();

		$output->writeln("<fg=green>Optimizing a MLP trainer in $maxEpochs steps. Enjoy your coffee!</>");
		$output->writeln("");

		$this->printConfig($epochs, $stepWidth, $config, $output);
		$best = $this->getAverageCost(
			$this->trainer->train($config, $dataConfig, $strategy),
			$this->trainer->train($config, $dataConfig, $strategy),
			$this->trainer->train($config, $dataConfig, $strategy),
			$this->trainer->train($config, $dataConfig, $strategy),
			$this->trainer->train($config, $dataConfig, $strategy)
		);

		while ($epochs < $maxEpochs) {
			$epochs++;

			$newConfig = $this->getNeighborConfig($config, $stepWidth);
			$this->printConfig($epochs, $stepWidth, $newConfig, $output);
			$cost = $this->getAverageCost(
				$this->trainer->train($newConfig, $dataConfig, $strategy),
				$this->trainer->train($newConfig, $dataConfig, $strategy),
				$this->trainer->train($newConfig, $dataConfig, $strategy),
				$this->trainer->train($newConfig, $dataConfig, $strategy),
				$this->trainer->train($newConfig, $dataConfig, $strategy)
			);

			if ($cost > $best) {
				$output->writeln("  <fg=green>Found better configuration: $best->$cost</>");
				$best = $cost;
				$config = $newConfig;
			} else {
				$output->writeln("  <fg=red>Result got worse: $best->$cost</>");
			}

			$stepWidth = $stepWidth * self::STEP_WIDTH_FACTOR;
		}
	}

}
