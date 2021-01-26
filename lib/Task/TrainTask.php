<?php

declare(strict_types=1);

/*
 * @copyright 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
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
 */

namespace OCA\SuspiciousLogin\Task;

use Amp\Parallel\Worker\Environment;
use Amp\Parallel\Worker\Task;
use OC;
use OCA\SuspiciousLogin\Service\AClassificationStrategy;
use OCA\SuspiciousLogin\Service\MLP\Config;
use OCA\SuspiciousLogin\Service\MLP\Trainer;
use OCA\SuspiciousLogin\Service\TrainingDataSet;

class TrainTask implements Task {

	/** @var Config */
	private $config;

	/** @var TrainingDataSet */
	private $dataSet;

	/** @var AClassificationStrategy */
	private $strategy;

	public function __construct(Config $config,
								TrainingDataSet $dataSet,
								AClassificationStrategy $strategy) {
		$this->config = $config;
		$this->dataSet = $dataSet;
		$this->strategy = $strategy;
	}

	public function run(Environment $environment) {
		// TODO: only works if the app is placed into a sub-sub directory of Nextcloud
		require_once __DIR__ . '/../../../../lib/base.php';

		/** @var Trainer $trainer */
		$trainer = OC::$server->get(Trainer::class);

		$result = $trainer->train(
			$this->config,
			$this->dataSet,
			$this->strategy
		);

		return $result->getModel();
	}
}
