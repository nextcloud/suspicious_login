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

namespace OCA\SuspiciousLogin\Service;

use OCA\SuspiciousLogin\Db\LoginAddressAggregatedMapper;
use OCA\SuspiciousLogin\Service\MLP\Config;
use function array_map;
use function explode;

class Ipv4Strategy extends AClassificationStrategy {
	public static function getTypeName(): string {
		return 'ipv4';
	}

	public function hasSufficientData(LoginAddressAggregatedMapper $loginAddressMapper, int $validationDays): bool {
		return $loginAddressMapper->hasSufficientIpV4Data($validationDays);
	}

	public function findHistoricAndRecent(LoginAddressAggregatedMapper $loginAddressMapper, int $validationThreshold, int $maxAge): array {
		return $loginAddressMapper->findHistoricAndRecentIpv4($validationThreshold, $maxAge);
	}

	public function generateRandomIpVector(): array {
		$ip = $this->generateRandomIp();
		$splitIp = explode('.', $ip);
		return $this->numStringsToBitArray($splitIp, 10, 8);
	}

	protected function ipToVec(string $ip): array {
		return $this->numStringsToBitArray(explode('.', $ip), 10, 8);
	}

	public function generateRandomIp(): string {
		return implode('.', array_map(function () {
			return random_int(0, 255);
		}, range(0, 3)));
	}

	public function getSize(): int {
		return 16 + 32;
	}

	public function getDefaultMlpConfig(): Config {
		return Config::default();
	}
}
