<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service;

use OCA\SuspiciousLogin\Db\LoginAddress;
use OCA\SuspiciousLogin\Db\LoginAddressMapper;

class LoginDataCollector {

	/** @var LoginAddressMapper */
	private $addressMapper;

	public function __construct(LoginAddressMapper $addressMapper) {
		$this->addressMapper = $addressMapper;
	}

	public function collectSuccessfulLogin(string $uid, string $ip, int $timestamp): void {
		$addr = new LoginAddress();
		$addr->setUid($uid);
		$addr->setIp($ip);
		$addr->setCreatedAt($timestamp);

		$this->addressMapper->insert($addr);
	}
}
