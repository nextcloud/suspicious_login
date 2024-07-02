<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method void setUid(string $uid)
 * @method string getUid()
 * @method void setIp(string $ip)
 * @method string getIp()
 * @method void setCreatedAt(int $createdAt)
 * @method int getCreatedAt()
 * @method void setRequestId(string $requestId)
 * @method string getRequestId()
 * @method void setUrl(string $userAgent)
 * @method string getUrl()
 * @method void setNotificationState(int|null $state)
 * @method int|null getNotificationState()
 */
class SuspiciousLogin extends Entity {
	protected $uid;
	protected $ip;
	protected $createdAt;
	protected $requestId;
	protected $url;
	protected $notificationState;
}
