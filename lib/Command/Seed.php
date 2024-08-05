<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Command;

use OCA\SuspiciousLogin\Db\LoginAddressAggregatedSeeder;
use OCA\SuspiciousLogin\Service\Ipv4Strategy;
use OCA\SuspiciousLogin\Service\IpV6Strategy;
use OCP\IConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Seed extends Command {
	use ModelStatistics;

	/** @var IConfig */
	private $config;

	/** @var LoginAddressAggregatedSeeder */
	private $seeder;

	public function __construct(IConfig $config,
		LoginAddressAggregatedSeeder $seeder) {
		parent::__construct("suspiciouslogin:seed");
		$this->seeder = $seeder;

		$this->addOption(
			'v6',
			null,
			InputOption::VALUE_NONE,
			"train with IPv6 data"
		);
		$this->setDescription("Fills the database with random IPs for development and testing purposes");
		$this->config = $config;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		if ($this->config->getSystemValueBool('debug', false) === false) {
			$output->writeln("<error>This command is meant for development purposes.</error> Enable debug mode and try again if you know what you are doing.");
			return 1;
		}

		$num = $this->seeder->seed(
			$input->getOption('v6') ? new IpV6Strategy() : new Ipv4Strategy()
		);
		$output->writeln("$num rows created");

		return 0;
	}
}
