<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Command;

use OCA\SuspiciousLogin\Exception\ModelNotFoundException;
use OCA\SuspiciousLogin\Exception\ServiceException;
use OCA\SuspiciousLogin\Service\EstimatorService;
use OCA\SuspiciousLogin\Service\Ipv4Strategy;
use OCA\SuspiciousLogin\Service\IpV6Strategy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
		$this->addOption(
			'v6',
			null,
			InputOption::VALUE_NONE,
			"train with IPv6 data"
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$uid = $input->getArgument('uid');
		$ip = $input->getArgument('ip');
		$modelId = $input->getArgument('model');

		try {
			if (!$this->estimatorService->predict(
				$uid,
				$ip,
				$input->getOption('v6') ? new IpV6Strategy() : new Ipv4Strategy(),
				$modelId ? (int)$modelId : null)) {
				$output->writeln("WARN: IP $ip is suspicious");
				return 1;
			}
		} catch (ModelNotFoundException $ex) {
			$output->writeln('<error>Could not predict suspiciousness: ' . $ex->getMessage() . '</error>');
			return 2;
		} catch (ServiceException $ex) {
			$output->writeln('<error>Could not predict suspiciousness: ' . $ex->getMessage() . '</error>');
			return 3;
		}

		$output->writeln("OK:   IP $ip is not suspicious");
		return 0;
	}
}
