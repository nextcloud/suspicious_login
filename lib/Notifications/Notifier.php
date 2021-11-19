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

namespace OCA\SuspiciousLogin\Notifications;

use OCP\IConfig;
use InvalidArgumentException;
use OCP\IURLGenerator;
use OCA\SuspiciousLogin\AppInfo\Application;
use OCP\IRequest;
use OCP\L10N\IFactory;
use OCP\Notification\IAction;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class Notifier implements INotifier {

	/** @var IConfig */
	protected $config;

	/** @var IFactory */
	private $factory;

	/** @var IRequest */
	private $request;

	/** @var IURLGenerator */
	protected $url;

	public function __construct(IFactory $factory, IRequest $request, IConfig $config, IURLGenerator $urlGenerator) {
		$this->config = $config;
		$this->factory = $factory;
		$this->request = $request;
		$this->url = $urlGenerator;
	}

	public function getID(): string {
		return Application::APP_ID;
	}

	public function getName(): string {
		return $this->factory->get(Application::APP_ID)->t('Suspicious Login');
	}

	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() !== Application::APP_ID) {
			// Not my app => throw
			throw new InvalidArgumentException();
		}

		// Read the language from the notification
		$l = $this->factory->get(Application::APP_ID, $languageCode);

		/** @var string $suspiciousIp */
		$params = $notification->getSubjectParameters();
		$suspiciousIp = $params['ip'] ?? '';

		switch ($notification->getSubject()) {
			case 'suspicious_login_detected':
				if ($suspiciousIp === $this->request->getRemoteAddress()) {
					// It is the potential attacking user so don't render the notification for them
					throw new InvalidArgumentException();
				}

				$additionalText = '';
				// Add button for more information about the IP-address
				if ($this->config->getAppValue('suspicious_login', 'show_more_info_button', '1') === "1") {
					$action = $notification->createAction();
					$label = $l->t('More information ↗');
					$link = 'https://iplookup.flagfox.net/?ip=' . $suspiciousIp;
					$action->setLabel($label)
						->setParsedLabel($label)
						->setLink($link, IAction::TYPE_WEB)
						->setPrimary(true);
					$notification->addParsedAction($action);
					$additionalText = ' ' . $l->t('You can get more info by pressing the button which will open %s and show info about the suspicious IP-address.', 'https://iplookup.flagfox.net');
				}

				$notification->setParsedSubject(
					$l->t('New login detected')
				)->setParsedMessage(
					$l->t('A new login into your account was detected. The IP address %s was classified as suspicious. If this was you, you can ignore this message. Otherwise you should change your password.', $suspiciousIp) . $additionalText
				);

				$notification->setIcon($this->url->getAbsoluteURL($this->url->imagePath('suspicious_login', 'app.svg')));

				return $notification;
			default:
				// Unknown subject => Unknown notification => throw
				throw new InvalidArgumentException();
		}
	}
}
