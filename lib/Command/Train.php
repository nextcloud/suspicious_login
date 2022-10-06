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
use OCA\SuspiciousLogin\Service\DataLoader;
use OCA\SuspiciousLogin\Service\Ipv4Strategy;
use OCA\SuspiciousLogin\Service\IpV6Strategy;
use OCA\SuspiciousLogin\Service\MLP\Trainer;
use OCA\SuspiciousLogin\Service\ModelStore;
use OCA\SuspiciousLogin\Service\TrainingDataConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function extension_loaded;
use function time;

class Train extends Command {
	use ModelStatistics;

	/** @var DataLoader */
	private $loader;

	/** @var Trainer */
	private $trainer;

	/** @var ModelStore */
	private $store;

	public function __construct(DataLoader $loader,
								Trainer $optimizer,
								ModelStore $store) {
		parent::__construct("suspiciouslogin:train");
		$this->trainer = $optimizer;
		$this->loader = $loader;
		$this->store = $store;

		$this->addOption(
			'epochs',
			'e',
			InputOption::VALUE_OPTIONAL,
			"number of epochs to train"
		);
		$this->addOption(
			'layers',
			'l',
			InputOption::VALUE_OPTIONAL,
			"number of hidden layers"
		);
		$this->addOption(
			'shuffled',
			null,
			InputOption::VALUE_OPTIONAL,
			"ratio of shuffled negative samples"
		);
		$this->addOption(
			'random',
			null,
			InputOption::VALUE_OPTIONAL,
			"ratio of random negative samples"
		);
		$this->addOption(
			'learn-rate',
			null,
			InputOption::VALUE_OPTIONAL,
			"learning rate"
		);
		$this->addOption(
			'validation-threshold',
			null,
			InputOption::VALUE_OPTIONAL,
			"determines how much of the most recent data is used for validation. the default is one week"
		);
		$this->addOption(
			'max-age',
			null,
			InputOption::VALUE_OPTIONAL,
			"determines the maximum age of test data"
		);
		$this->addOption(
			'now',
			null,
			InputOption::VALUE_OPTIONAL,
			"overwrite the current time",
			time()
		);
		$this->addOption(
			'v6',
			null,
			InputOption::VALUE_NONE,
			"train with IPv6 data"
		);
		$this->addOption(
			'dry-run',
			null,
			InputOption::VALUE_NONE,
			"train but don't persist the model"
		);
		$this->addOption(
			'now',
			null,
			InputOption::VALUE_OPTIONAL,
			"the current time as timestamp",
			time()
		);
		$this->registerStatsOption();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$strategy = $input->getOption('v6') ? new IpV6Strategy() : new Ipv4Strategy();
		$config = $strategy->getDefaultMlpConfig();
		if ($input->getOption('epochs') !== null) {
			$config = $config->setEpochs((int)$input->getOption('epochs'));
		}
		if ($input->getOption('layers') !== null) {
			$config = $config->setLayers((int)$input->getOption('layers'));
		}
		if ($input->getOption('shuffled') !== null) {
			$config = $config->setShuffledNegativeRate((float)$input->getOption('shuffled'));
		}
		if ($input->getOption('random') !== null) {
			$config = $config->setRandomNegativeRate((float)$input->getOption('random'));
		}
		if ($input->getOption('learn-rate') !== null) {
			$config = $config->setLearningRate((float)$input->getOption('learn-rate'));
		}

		$trainingDataConfig = TrainingDataConfig::default((int) $input->getOption('now'));
		if ($input->getOption('validation-threshold') !== null) {
			$trainingDataConfig = $trainingDataConfig->setThreshold((int)$input->getOption('validation-threshold'));
		}
		if ($input->getOption('max-age') !== null) {
			$trainingDataConfig = $trainingDataConfig->setMaxAge((int)$input->getOption('max-age'));
		}
		if ($input->getOption('now') !== null) {
			$trainingDataConfig = $trainingDataConfig->setNow((int)$input->getOption('now'));
		}

		try {
			if (extension_loaded('xdebug')) {
				$output->writeln('<comment>XDebug is active. This will slow down the training process.</comment>');
			}
			$output->writeln('Using ' . $strategy::getTypeName() . ' strategy');

			$collectedData = $this->loader->loadTrainingAndValidationData(
				$trainingDataConfig,
				$strategy
			);
			$data = $this->loader->generateRandomShuffledData(
				$collectedData,
				$config,
				$strategy
			);
			$result = $this->trainer->train(
				$config,
				$data,
				$strategy
			);
			$this->printModelStatistics($result->getModel(), $input, $output);
			if (!$input->getOption('dry-run')) {
				$this->store->persist(
					$result->getClassifier(),
					$result->getModel()
				);
				$output->writeln("<info>Model and estimator persisted.</info>");
			}
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
