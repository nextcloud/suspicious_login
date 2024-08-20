<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getUid()
 * @method void setUid(string $uid)
 * @method string getIp()
 * @method void setIp(string $ip)
 * @method int getCreatedAt()
 * @method void setCreatedAt(int $createdAt)
 */
class LoginAddress extends Entity {
	protected $uid;
	protected $ip;
	protected $createdAt;
}
