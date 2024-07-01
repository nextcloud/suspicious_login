<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\AppInfo;

use OCA\SuspiciousLogin\Event\PostLoginEvent;
use OCA\SuspiciousLogin\Event\SuspiciousLoginEvent;
use OCA\SuspiciousLogin\Listener\LoginListener;
use OCA\SuspiciousLogin\Listener\LoginMailListener;
use OCA\SuspiciousLogin\Listener\LoginNotificationListener;
use OCA\SuspiciousLogin\Notifications\Notifier;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Util;

class Application extends App implements IBootstrap {
	public const APP_ID = 'suspicious_login';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		include_once __DIR__ . '/../../vendor/autoload.php';

		$context->registerEventListener(SuspiciousLoginEvent::class, LoginNotificationListener::class);
		$context->registerEventListener(SuspiciousLoginEvent::class, LoginMailListener::class);
		$context->registerEventListener(PostLoginEvent::class, LoginListener::class);

		$context->registerNotifierService(Notifier::class);
	}

	public function boot(IBootContext $context): void {
		$context->injectFn(function (IEventDispatcher $dispatcher) {
			$loginHookAdapter = new class($dispatcher) {
				/** @var IEventDispatcher */
				private $dispatcher;

				public function __construct(IEventDispatcher $dispatcher) {
					$this->dispatcher = $dispatcher;
				}

				public function handle(array $data) {
					if (!isset($data['uid'], $data['isTokenLogin'])) {
						// Ignore invalid data
						return;
					}

					$this->dispatcher->dispatch(
						PostLoginEvent::class,
						new PostLoginEvent($data['uid'], $data['isTokenLogin'])
					);
				}
			};

			Util::connectHook(
				'OC_User',
				'post_login',
				$loginHookAdapter,
				'handle'
			);
		});
	}
}
