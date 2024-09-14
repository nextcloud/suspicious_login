<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Task;

use Amp\Parallel\Worker\Environment;
use Amp\Parallel\Worker\Task;
use OCA\SuspiciousLogin\Service\AClassificationStrategy;
use OCA\SuspiciousLogin\Service\CollectedData;
use OCA\SuspiciousLogin\Service\DataLoader;
use OCA\SuspiciousLogin\Service\MLP\Config;
use OCA\SuspiciousLogin\Service\MLP\Trainer;
use function ini_get;
use function set_time_limit;
use function strpos;

class TrainTask implements Task {

	/** @var Config */
	private $config;

	/** @var CollectedData */
	private $dataSet;

	/** @var AClassificationStrategy */
	private $strategy;

	public function __construct(Config $config,
		CollectedData $dataSet,
		AClassificationStrategy $strategy) {
		$this->config = $config;
		$this->dataSet = $dataSet;
		$this->strategy = $strategy;
	}

	public function run(Environment $environment) {
		// TODO: only works if the app is placed into a sub-sub directory of Nextcloud
		require_once __DIR__ . '/../../../../lib/base.php';

		// Prevent getting killed by a timeout
		if (strpos(ini_get('disable_functions'), 'set_time_limit') === false) {
			set_time_limit(0);
		}

		/** @var DataLoader $loader */
		$loader = \OCP\Server::get(DataLoader::class);
		$data = $loader->generateRandomShuffledData($this->dataSet, $this->config, $this->strategy);
		/** @var Trainer $trainer */
		$trainer = \OCP\Server::get(Trainer::class);

		return $trainer->train(
			$this->config,
			$data,
			$this->strategy
		);
	}
}
