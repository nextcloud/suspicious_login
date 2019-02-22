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

namespace OCA\SuspiciousLogin\Command;

use OCA\SuspiciousLogin\Exception\InsufficientDataException;
use OCA\SuspiciousLogin\Exception\ServiceException;
use OCA\SuspiciousLogin\Service\MLP\Config;
use OCA\SuspiciousLogin\Service\MLP\Trainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Train extends Command {

	use ModelStatistics;

	/** @var Trainer */
	private $trainer;

	public function __construct(Trainer $optimizer) {
		parent::__construct("suspiciouslogin:train");
		$this->trainer = $optimizer;

		$this->addOption(
			'epochs',
			'e',
			InputOption::VALUE_OPTIONAL,
			"number of epochs to train",
			250
		);
		$this->addOption(
			'layers',
			'l',
			InputOption::VALUE_OPTIONAL,
			"number of hidden layers",
			10
		);
		$this->addOption(
			'shuffled',
			null,
			InputOption::VALUE_OPTIONAL,
			"ratio of shuffled negative samples",
			1.0
		);
		$this->addOption(
			'random',
			null,
			InputOption::VALUE_OPTIONAL,
			"ratio of random negative samples",
			1.0
		);
		$this->addOption(
			'learn-rate',
			null,
			InputOption::VALUE_OPTIONAL,
			"learning rate",
			0.05
		);
		$this->addOption(
			'validation-threshold',
			null,
			InputOption::VALUE_OPTIONAL,
			"determines how much of the most recent data is used for validation. the default is one week",
			7
		);
		$this->addOption(
			'max-age',
			null,
			InputOption::VALUE_OPTIONAL,
			"determines the maximum age of test data",
			60
		);
		$this->registerStatsOption();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$config = Config::default();
		if ($input->hasOption('epochs')) {
			$config = $config->setEpochs((int)$input->getOption('epochs'));
		}
		if ($input->hasOption('layers')) {
			$config = $config->setLayers((int)$input->getOption('layers'));
		}
		if ($input->hasOption('shuffled')) {
			$config = $config->setShuffledNegativeRate((float)$input->getOption('shuffled'));
		}
		if ($input->hasOption('random')) {
			$config = $config->setRandomNegativeRate((float)$input->getOption('random'));
		}
		if ($input->hasOption('learn-rate')) {
			$config = $config->setLearningRate((float)$input->getOption('learn-rate'));
		}

		try {
			$model = $this->trainer->train(
				$config,
				(int)$input->getOption('validation-threshold'),
				(int)$input->getOption('max-age')
			);
			$this->printModelStatistics($model, $input, $output);
		} catch (InsufficientDataException $ex) {
			$output->writeln("<info>Not enough data, try again later (<error>" . $ex->getMessage() . "</error>)</info>");
			return 1;
		} catch (ServiceException $ex) {
			$output->writeln("<error>Could not train a model: " . $ex->getMessage() . "</error>");
			return 1;
		}
		return 0;
	}

}
