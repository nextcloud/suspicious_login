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

use OCA\SuspiciousLogin\Service\ETLService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ETL extends Command {

	/** @var ETLService */
	private $etlService;

	public function __construct(ETLService $etlService) {
		parent::__construct("suspiciouslogin:etl");

		$this->etlService = $etlService;

		$this->addOption(
			'max',
			null,
			InputOption::VALUE_OPTIONAL,
			'the maximum number of rows to transform',
			25000
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$max = $input->getOption('max');
		$this->etlService->extractAndTransform(
			(int)$max,
			$output
		);
		return 0;
	}
}
