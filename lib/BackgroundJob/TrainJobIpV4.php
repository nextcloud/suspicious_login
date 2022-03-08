<?php

declare(strict_types=1);

/**
 * @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\SuspiciousLogin\BackgroundJob;

use OCA\SuspiciousLogin\Exception\InsufficientDataException;
use OCA\SuspiciousLogin\Service\Ipv4Strategy;
use OCA\SuspiciousLogin\Service\TrainingDataConfig;
use OCA\SuspiciousLogin\Service\TrainService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCP\ILogger;
use Throwable;

class TrainJobIpV4 extends TimedJob {

	/** @var TrainService */
	private $trainService;

	/** @var ILogger */
	private $logger;

	public function __construct(TrainService $trainService,
								ILogger $logger,
								ITimeFactory $time) {
		parent::__construct($time);

		$this->setInterval(24 * 60 * 60);
		/**
		 * @todo remove checks with 24+
		 */
		if (defined('\OCP\BackgroundJob\IJob::TIME_INSENSITIVE') && method_exists($this, 'setTimeSensitivity')) {
			$this->setTimeSensitivity(self::TIME_INSENSITIVE);
		}
		$this->trainService = $trainService;
		$this->logger = $logger;
	}

	/**
	 * @param $argument
	 *
	 * @return mixed
	 */
	protected function run($argument) {
		try {
			$strategy = new Ipv4Strategy();
			$this->trainService->train(
				$strategy->getDefaultMlpConfig(),
				TrainingDataConfig::default(),
				$strategy
			);
		} catch (InsufficientDataException $ex) {
			$this->logger->logException($ex, [
				'level' => ILogger::INFO,
				'message' => 'No suspicious login model for IPv4 trained because of insufficient data',
			]);
		} catch (Throwable $ex) {
			$this->logger->logException($ex, [
				'level' => ILogger::ERROR,
				'message' => 'Caught unknown error during IPv4 background training',
			]);
		}
	}
}
