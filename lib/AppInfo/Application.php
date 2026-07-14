<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\AppInfo;

use OCA\SuspiciousLogin\Event\SuspiciousLoginEvent;
use OCA\SuspiciousLogin\Listener\LoginListener;
use OCA\SuspiciousLogin\Listener\LoginMailListener;
use OCA\SuspiciousLogin\Listener\LoginNotificationListener;
use OCA\SuspiciousLogin\Notifications\Notifier;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\User\Events\UserLoggedInEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'suspicious_login';

	/** @psalm-mutation-free */
	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	#[\Override]
	public function register(IRegistrationContext $context): void {
		include_once __DIR__ . '/../../vendor/autoload.php';

		$context->registerEventListener(SuspiciousLoginEvent::class, LoginNotificationListener::class);
		$context->registerEventListener(SuspiciousLoginEvent::class, LoginMailListener::class);
		$context->registerEventListener(UserLoggedInEvent::class, LoginListener::class);

		$context->registerNotifierService(Notifier::class);
	}

	#[\Override]
	public function boot(IBootContext $context): void {
	}
}
