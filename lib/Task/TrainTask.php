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
		$loader = OC::$server->get(DataLoader::class);
		$data = $loader->generateRandomShuffledData($this->dataSet, $this->config, $this->strategy);
		/** @var Trainer $trainer */
		$trainer = OC::$server->get(Trainer::class);

		return $trainer->train(
			$this->config,
			$data,
			$this->strategy
		);
	}
}
