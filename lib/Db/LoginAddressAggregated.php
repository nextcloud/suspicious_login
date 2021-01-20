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

namespace OCA\SuspiciousLogin\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getUid()
 * @method void setUid(string $uid)
 * @method string getIp()
 * @method void setIp(string $ip)
 * @method int getSeen()
 * @method void setSeen(int $seen)
 * @method int getFirstSeen()
 * @method void setFirstSeen(int $firstSeen)
 * @method int getLastSeen()
 * @method void setLastSeen(int $lastSeen)
 */
class LoginAddressAggregated extends Entity {
	protected $uid;
	protected $ip;
	protected $seen;
	protected $firstSeen;
	protected $lastSeen;
}
