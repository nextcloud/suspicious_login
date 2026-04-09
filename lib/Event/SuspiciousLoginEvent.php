<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */


namespace OCA\SuspiciousLogin\Event;

use OCP\EventDispatcher\Event;

class SuspiciousLoginEvent extends Event {
	public function __construct(
		private readonly string $uid,
		private readonly string $ip,
	) {
		parent::__construct();
	}

	public function getUid(): string {
		return $this->uid;
	}

	public function getIp(): string {
		return $this->ip;
	}
}
