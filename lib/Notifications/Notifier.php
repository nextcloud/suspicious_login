<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Notifications;

use OCA\SuspiciousLogin\AppInfo\Application;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;
use OCP\Notification\IAction;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;
use OCP\Notification\UnknownNotificationException;

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
			throw new UnknownNotificationException();
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
					throw new UnknownNotificationException();
				}

				$additionalText = '';
				// Add button for more information about the IP-address
				if ($this->config->getAppValue('suspicious_login', 'show_more_info_button', '1') === "1") {
					$action = $notification->createAction();
					$label = $l->t('Open %s ↗', ['iplookup.flagfox.net']);
					$link = 'https://iplookup.flagfox.net/?ip=' . $suspiciousIp;
					$action->setLabel($label)
						->setParsedLabel($label)
						->setLink($link, IAction::TYPE_WEB)
						->setPrimary(true);
					$notification->addParsedAction($action);
					// TODO: deduplicate with \OCA\SuspiciousLogin\Listener\LoginMailListener::getMail
					$additionalText = ' ' . $l->t('More info about the suspicious IP address available on %s', 'https://iplookup.flagfox.net');
				}

				$notification->setParsedSubject(
					$l->t('New login detected')
				)->setParsedMessage(
					$l->t('A new login into your account was detected. The IP address %s was classified as suspicious. If this was you, you can ignore this message. Otherwise you should change your password.', $suspiciousIp) . $additionalText
				);

				$notification->setIcon($this->url->getAbsoluteURL($this->url->imagePath('suspicious_login', 'app-dark.svg')));

				return $notification;
			default:
				// Unknown subject => Unknown notification => throw
				throw new UnknownNotificationException();
		}
	}
}
