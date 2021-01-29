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

namespace OCA\SuspiciousLogin\Service;

use OCA\SuspiciousLogin\Db\Model;
use Rubix\ML\Estimator;
use Rubix\ML\Report;

class TrainingResult {

	/** @var Estimator */
	private $classifier;

	/** @var Model */
	private $model;

	/** @var Report */
	private $report;

	public function __construct(Estimator $classifier,
								Model $model,
								Report $report) {
		$this->classifier = $classifier;
		$this->model = $model;
		$this->report = $report;
	}

	public function getClassifier(): Estimator {
		return $this->classifier;
	}

	public function getModel(): Model {
		return $this->model;
	}

	public function getReport(): Report {
		return $this->report;
	}
}
