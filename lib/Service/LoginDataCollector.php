<?php

declare(strict_types=1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
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
 *
 */

namespace OCA\SuspiciousLogin\Service;

use OCA\SuspiciousLogin\Service\Collection\IAddressCollectionStrategy;
use OCA\SuspiciousLogin\Service\Collection\IpV4Collector;
use OCA\SuspiciousLogin\Service\Collection\IpV6Collector;
use OCA\SuspiciousLogin\Util\AddressClassifier;
use OCP\ILogger;

class LoginDataCollector {

	/** @var IpV4Collector */
	private $ipV4Collector;

	/** @var IpV6Collector */
	private $ipV6Collector;

	/** @var ILogger */
	private $logger;

	public function __construct(IpV4Collector $ipV4Collector, IpV6Collector $ipV6Collector, ILogger $logger) {
		$this->ipV4Collector = $ipV4Collector;
		$this->ipV6Collector = $ipV6Collector;
		$this->logger = $logger;
	}

	private function getCollectionStrategy(string $ip): ?IAddressCollectionStrategy {
		if (AddressClassifier::isIpV4($ip)) {
			return $this->ipV4Collector;
		} else if (AddressClassifier::isIpV6($ip)) {
			return $this->ipV6Collector;
		} else {
			return null;
		}
	}

	public function collectSuccessfulLogin(string $uid, string $ip, int $timestamp): void {
		$strategy = $this->getCollectionStrategy($ip);
		if ($strategy === null) {
			$this->logger->error("Got invalid address <$ip>");
			return;
		}

		$strategy->collect($uid, $ip, $timestamp);
	}

}
