<?php

declare(strict_types=1);

/**
 * @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
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
 */

namespace OCA\SuspiciousLogin\Service;

use OCA\SuspiciousLogin\AppInfo\Application;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Notification\IManager;

class LoginNotifier {

	/** @var IManager */
	private $notificationManager;

	/** @var ITimeFactory */
	private $timeFactory;

	public function __construct(IManager $notificationManager,
								ITimeFactory $timeFactory) {
		$this->notificationManager = $notificationManager;
		$this->timeFactory = $timeFactory;
	}

	public function notify(string $uid, string $ip): void {
		$notification = $this->notificationManager->createNotification();
		$notification->setApp(Application::APP_ID)
			->setUser($uid)
			->setDateTime($this->timeFactory->getDateTime())
			->setObject('ip', $ip)
			->setSubject('suspicious_login_detected', [
				'ip' => $ip,
			]);
		$this->notificationManager->notify($notification);
	}

}
