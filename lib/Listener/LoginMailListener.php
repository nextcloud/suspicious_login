<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Listener;

use Exception;
use OCA\SuspiciousLogin\Event\SuspiciousLoginEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Mail\IMailer;
use OCP\Mail\IMessage;
use Psr\Log\LoggerInterface;

/**
 * @implements IEventListener<SuspiciousLoginEvent>
 */
class LoginMailListener implements IEventListener {

	public function __construct(
		private LoggerInterface $logger,
		private IMailer $mailer,
		private IUserManager $userManager,
		private IL10N $l,
		private IConfig $config,
	) {
	}

	public function handle(Event $event): void {
		if (!($event instanceof SuspiciousLoginEvent)) {
			return;
		}

		$uid = $event->getUid();
		$user = $this->userManager->get($uid);
		if ($user === null) {
			$this->logger->warning("not sending suspicious login email because user <$uid> does not exist (anymore)");
			return;
		}
		if ($user->getEMailAddress() === null) {
			$this->logger->info("not sending a suspicous login email because user <$uid> has no email set");
			return;
		}

		try {
			$this->mailer->send(
				$this->getMail($event, $user)
			);
		} catch (Exception $e) {
			$this->logger->error("Could not send suspicious login email to <$uid>", ['exception' => $e]);
		}
	}

	private function getMail(SuspiciousLoginEvent $event, IUser $user): IMessage {
		$suspiciousIp = $event->getIp();
		$addButton = $this->config->getAppValue('suspicious_login', 'show_more_info_button', '1') === "1";

		$message = $this->mailer->createMessage();
		$emailTemplate = $this->mailer->createEMailTemplate('suspiciousLogin.suspiciousLoginDetected');

		$emailTemplate->setSubject($this->l->t('New login location detected'));
		$emailTemplate->addHeader();
		$emailTemplate->addHeading(
			$this->l->t('New login location detected')
		);
		
		$additionalText = '';
		// Add explanation for more information about the IP-address (if enabled)
		if ($addButton) {
			// TODO: deduplicate with \OCA\SuspiciousLogin\Notifications\Notifier::prepare
			$additionalText = ' ' . $this->l->t('More info about the suspicious IP address available on %s', 'https://iplookup.flagfox.net');
		}
		$emailTemplate->addBodyText(
			$this->l->t('A new login into your account was detected. The IP address %s was classified as suspicious. If this was you, you can ignore this message. Otherwise you should change your password.', $suspiciousIp) . $additionalText
		);
		// Add button for more information about the IP-address (if enabled)
		if ($addButton) {
			$link = 'https://iplookup.flagfox.net/?ip=' . $suspiciousIp;
			$emailTemplate->addBodyButton(
				htmlspecialchars($this->l->t('Open %s ↗', ['iplookup.flagfox.net'])),
				$link,
				false
			);
		}
		$emailTemplate->addFooter();
		$message->setTo([$user->getEMailAddress()]);
		$message->useTemplate($emailTemplate);

		return $message;
	}
}
