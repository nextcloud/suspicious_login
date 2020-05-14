<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2019, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
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
