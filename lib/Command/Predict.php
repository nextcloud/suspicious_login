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

use OCA\SuspiciousLogin\Service\EstimatorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Predict extends Command {

	/** @var EstimatorService */
	private $estimatorService;

	public function __construct(EstimatorService $estimatorService) {
		parent::__construct("suspiciouslogin:predict");

		$this->estimatorService = $estimatorService;

		$this->addArgument(
			'uid',
			InputArgument::REQUIRED,
			"the UID of the user to run a prediction for"
		);
		$this->addArgument(
			'ip',
			InputArgument::REQUIRED,
			"the IP to predict suspiciousness"
		);
		$this->addArgument(
			'model',
			InputArgument::OPTIONAL,
			"persisted model id (latest if omited)"
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$uid = $input->getArgument('uid');
		$ip = $input->getArgument('ip');
		$modelId = $input->getArgument('model');
		if ($this->estimatorService->predict($uid, $ip, (int) $modelId)) {
			$output->writeln("OK:   IP $ip is not suspicious");
		} else {
			$output->writeln("WARN: IP $ip is suspicious");
		}
	}

}
