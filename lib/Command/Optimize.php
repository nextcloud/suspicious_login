<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Command;

use OCA\SuspiciousLogin\Service\Ipv4Strategy;
use OCA\SuspiciousLogin\Service\IpV6Strategy;
use OCA\SuspiciousLogin\Service\MLP\OptimizerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function extension_loaded;
use function time;

class Optimize extends Command {
	use ModelStatistics;

	/** @var OptimizerService */
	private $optimizerService;

	public function __construct(OptimizerService $optimizer) {
		parent::__construct("suspiciouslogin:optimize");
		$this->optimizerService = $optimizer;

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
		if (extension_loaded('xdebug')) {
			$output->writeln('<comment>XDebug is active. This will slow down the training processes.</comment>');
		}

		// Prevent getting killed by a timeout
		if (strpos(ini_get('disable_functions'), 'set_time_limit') === false) {
			set_time_limit(0);
		}

		$this->optimizerService->optimize(
			(int)$input->getOption('max-epochs'),
			$input->getOption('v6') ? new IpV6Strategy() : new Ipv4Strategy(),
			(int) $input->getOption('now'),
			$output
		);

		return 0;
	}
}
