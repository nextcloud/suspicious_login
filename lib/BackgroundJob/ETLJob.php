<?php

declare(strict_types=1);

/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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

use OCA\SuspiciousLogin\Service\ETLService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;

class ETLJob extends TimedJob {

	/** @var ETLService */
	private $etlService;

	public function __construct(ETLService $etlService,
								ITimeFactory $time) {
		parent::__construct($time);

		$this->setInterval(60 * 60);
		$this->etlService = $etlService;
	}

	/**
	 * @param $argument
	 *
	 * @return mixed
	 */
	protected function run($argument) {
		$this->etlService->extractAndTransform();
	}
}
