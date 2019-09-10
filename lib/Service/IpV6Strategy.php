<?php declare(strict_types=1);

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

class IpV6Strategy implements IClassificationStrategy {

	public static function getTypeName(): string {
		return 'ipv6';
	}

	public function hasSufficientData(LoginAddressAggregatedMapper $loginAddressMapper, int $validationDays): bool {
		return $loginAddressMapper->hasSufficientIpV6Data($validationDays);
	}

	public function findHistoricAndRecent(LoginAddressAggregatedMapper $loginAddressMapper, int $testingDays, int $validationDays): array {
		return $loginAddressMapper->findHistoricAndRecentIpv6($testingDays, $validationDays);
	}

	public function newVector($uid, $ip, $label): AUidIpVector {
		return new UidIpV6Vector($uid, $ip, $label);
	}

	public function generateRandomIp(): string {
		return implode(':', array_map(function () {
			return base_convert(random_int(0, 2 ** 16 - 1), 10, 16);
		}, range(0, 7)));
	}

	public function getSize(): int {
		return 16 + 64;
	}

	public function getDefaultMlpConfig(): Config {
		return Config::default()->setEpochs(20);
	}

}
