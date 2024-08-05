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
use OCP\ILogger;
use OCP\Notification\IManager;

class LoginNotificationListener implements IEventListener {

	/** @var IManager */
	private $notificationManager;
	/** @var ITimeFactory */
	private $timeFactory;
	/** @var ILogger */
	private $logger;

	public function __construct(IManager $notificationManager,
		ITimeFactory $timeFactory,
		ILogger $logger) {
		$this->notificationManager = $notificationManager;
		$this->timeFactory = $timeFactory;
		$this->logger = $logger;
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
			$this->logger->critical("could not send notification about a suspicious login");
			$this->logger->logException($ex);
		}
	}
}
