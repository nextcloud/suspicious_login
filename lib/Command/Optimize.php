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

use OCA\SuspiciousLogin\Service\Ipv4Strategy;
use OCA\SuspiciousLogin\Service\IpV6Strategy;
use OCA\SuspiciousLogin\Service\MLP\Optimizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Optimize extends Command {

	use ModelStatistics;

	/** @var Optimizer */
	private $optimizer;

	public function __construct(Optimizer $optimizer) {
		parent::__construct("suspiciouslogin:optimize");
		$this->optimizer = $optimizer;

		$this->addOption(
			'max-epochs',
			null,
			InputOption::VALUE_OPTIONAL,
			"maximum number of epochs of optimization",
			100
		);
		$this->addOption(
			'v6',
			null,
			InputOption::VALUE_NONE,
			"train with IPv6 data"
		);
		$this->registerStatsOption();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->optimizer->optimize(
			(int)$input->getOption('max-epochs'),
			$input->getOption('v6') ? new IpV6Strategy() : new Ipv4Strategy(),
			$output
		);
	}

}
