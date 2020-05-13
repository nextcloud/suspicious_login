<?php
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

namespace OCA\SuspiciousLogin\Service\Statistics;

use JsonSerializable;
use OCA\SuspiciousLogin\Db\Model;
use OCA\SuspiciousLogin\Service\TrainingDataConfig;

class AppStatistics implements JsonSerializable {

	/** @var bool */
	private $active;

	/** @var Model[] */
	private $recentModels;

	/** @var TrainingDataConfig */
	private $trainingDataConfig;

	/** @var TrainingDataStatistics */
	private $trainingDataStatistics;

	public function __construct(bool $active,
								array $recentModels,
								TrainingDataConfig $trainingDataConfig,
								TrainingDataStatistics $trainingDataStatistics) {
		$this->active = $active;
		$this->recentModels = $recentModels;
		$this->trainingDataConfig = $trainingDataConfig;
		$this->trainingDataStatistics = $trainingDataStatistics;
	}

	/**
	 * @return bool
	 */
	public function isActive(): bool {
		return $this->active;
	}

	/**
	 * @return Model[]
	 */
	public function getRecentModels(): array {
		return $this->recentModels;
	}

	/**
	 * @return TrainingDataConfig
	 */
	public function getTrainingDataConfig(): TrainingDataConfig {
		return $this->trainingDataConfig;
	}

	/**
	 * @return TrainingDataStatistics
	 */
	public function getTrainingDataStatistics(): TrainingDataStatistics {
		return $this->trainingDataStatistics;
	}

	public function jsonSerialize() {
		return [
			'active' => $this->isActive(),
			'recentModels' => $this->getRecentModels(),
			'trainingDataConfig' => $this->getTrainingDataConfig(),
			'trainingDataStats' => $this->getTrainingDataStatistics(),
		];
	}
}
