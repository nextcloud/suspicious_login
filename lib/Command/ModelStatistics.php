<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Command;

use OCA\SuspiciousLogin\Db\Model;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

trait ModelStatistics {
	private function registerStatsOption() {
		$this->addOption(
			'stats',
			null,
			InputOption::VALUE_NONE,
			'show model statics'
		);
	}

	private function printModelStatistics(Model $model, InputInterface $input, OutputInterface $output) {
		if (!$input->hasOption('stats') && !$input->getOption('stats')) {
			return;
		}
		$output->writeln("Prescision(y): " . $model->getPrecisionY());
		$output->writeln("Prescision(n): " . $model->getPrecisionN());
		$output->writeln("Recall(y): " . $model->getRecallY());
		$output->writeln("Recall(n): " . $model->getRecallN());
	}
}
