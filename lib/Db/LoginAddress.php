<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Db;

use OCP\AppFramework\Db\Entity;

class LoginAddress extends Entity {
	protected $uid;
	protected $ip;
	protected $createdAt;
}
