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

namespace OCA\SuspiciousLogin\Event;

use OCP\EventDispatcher\Event;

class PostLoginEvent extends Event {

	/** @var string */
	private $uid;

	/** @var bool */
	private $isTokenLogin;

	public function __construct(string $uid, bool $isTokenLogin) {
		parent::__construct();
		$this->uid = $uid;
		$this->isTokenLogin = $isTokenLogin;
	}

	public function getUid(): string {
		return $this->uid;
	}

	public function isTokenLogin(): bool {
		return $this->isTokenLogin;
	}
}
