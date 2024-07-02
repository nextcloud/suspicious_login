<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
