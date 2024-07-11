<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
use OCP\Notification\UnknownNotificationException;

class Notifier implements INotifier {
	
	/** @var IFactory */
	private $factory;

	/** @var IRequest */
	private $request;

	/** @var IConfig */
	protected $config;
	
	/** @var IURLGenerator */
	protected $url;

	public function __construct(
		IFactory $factory,
		IRequest $request,
		IConfig $config,
		IURLGenerator $urlGenerator
	) {
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
			throw new UnknownNotificationException('Unknown app id');
		}

		// Read the language from the notification
		$l = $this->factory->get(Application::APP_ID, $languageCode);

		$subjectParameters = $notification->getSubjectParameters();

		switch ($notification->getSubject()) {
			case 'suspicious_login_detected':
				$notification = $this->parseSuspiciousLoginDetected($notification, $subjectParameters, $l);
			default:
				// Unknown subject => Unknown notification => throw
				throw new UnknownNotificationException('Unknown subject');
		}
		return $notification;
	}
	
	protected function parseSuspiciousLoginDetected(INotification $notification, array $subjectParameters, IL10N $l): INotification {
		string $additionalText = '';
		string $suspiciousIp = $subjectParameters['ip'] ?? '';

		if ($suspiciousIp === $this->request->getRemoteAddress()) {
			// It is the potential attacking user so don't render the notification for them
			throw new InvalidArgumentException();
		}

		if ($this->config->getAppValue('suspicious_login', 'show_more_info_button', '1') === "1") {
			// If enabled (the default), add button to retrieve more information about the IP address
			$label = $l->t('More information ↗');
			$link = 'https://iplookup.flagfox.net/?ip=' . $suspiciousIp;
			$additionalText = 
				' ' 
				. $l->t('You can get more info by pressing the button which will open %s and show info about the suspicious IP-address.',
					'https://iplookup.flagfox.net');
			
			$action = $notification->createAction();
			$action->setLabel($label)
				->setParsedLabel($label)
				->setLink($link, IAction::TYPE_WEB)
				->setPrimary(true);
			$notification->addParsedAction($action);
		}

		$notification->setParsedSubject($l->t('New login detected'))
			->setParsedMessage(
				$l->t('A new login into your account was detected. The IP address %s was classified as suspicious. '
    					. 'If this was you, you can ignore this message. Otherwise you should change your password.', $suspiciousIp)
					. $additionalText);

		$notification->setIcon($this->url->getAbsoluteURL($this->url->imagePath('suspicious_login', 'app.svg')));
		
		return $notification;
	}
}
