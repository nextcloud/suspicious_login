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

use OCA\SuspiciousLogin\Service\MLPTrainer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TrainMLP extends Train {

	use ModelStatistics;

	/** @var MLPTrainer */
	private $trainer;

	public function __construct(MLPTrainer $trainer) {
		parent::__construct("suspiciouslogin:train:mlp");
		$this->trainer = $trainer;

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
			'epochs',
			'e',
			InputOption::VALUE_OPTIONAL,
			"number of epochs to train",
			5000
		);
		$this->addOption(
			'layers',
			'l',
			InputOption::VALUE_OPTIONAL,
			"number of hidden layers",
			10
		);
		$this->addOption(
			'learn-rate',
			null,
			InputOption::VALUE_OPTIONAL,
			"learning rate",
			0.05
		);
		$this->addOption(
			'validation-rate',
			null,
			InputOption::VALUE_OPTIONAL,
			"relative size of the validation data set",
			0.15
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$model = $this->trainer->train(
			$output,
			(float)$input->getOption('shuffled'),
			(float)$input->getOption('random'),
			(int)$input->getOption('epochs'),
			(int)$input->getOption('layers'),
			(float)$input->getOption('learn-rate'),
			(float)$input->getOption('validation-rate')
		);

		if ($input->hasOption(parent::OPTION_STATS) && $input->getOption(parent::OPTION_STATS)) {
			$this->printModelStatistics($model, $output);
		}
	}

}
