<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Event;

use OCP\EventDispatcher\Event;

class PostLoginEvent extends Event {

	public function __construct(
		private readonly string $uid,
		private readonly bool $isTokenLogin,
	) {
		parent::__construct();
	}

	public function getUid(): string {
		return $this->uid;
	}

	public function isTokenLogin(): bool {
		return $this->isTokenLogin;
	}
}
