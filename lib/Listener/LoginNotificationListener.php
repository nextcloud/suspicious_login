<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Listener;

use OCA\SuspiciousLogin\AppInfo\Application;
use OCA\SuspiciousLogin\Event\SuspiciousLoginEvent;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Notification\IManager;
use Psr\Log\LoggerInterface;

/**
 * @implements IEventListener<SuspiciousLoginEvent>
 */
class LoginNotificationListener implements IEventListener {

	public function __construct(
		private IManager $notificationManager,
		private ITimeFactory $timeFactory,
		private LoggerInterface $logger,
	) {
	}

	public function handle(Event $event): void {
		if (!($event instanceof SuspiciousLoginEvent)) {
			return;
		}

		try {
			$notification = $this->notificationManager->createNotification();
			$notification->setApp(Application::APP_ID)
				->setUser($event->getUid())
				->setDateTime($this->timeFactory->getDateTime())
				->setObject('ip', $event->getIp())
				->setSubject('suspicious_login_detected', [
					'ip' => $event->getIp(),
				]);
			$this->notificationManager->notify($notification);
		} catch (\Throwable $ex) {
			$this->logger->critical('Could not send notification about a suspicious login', ['exception' => $ex]);
		}
	}
}
