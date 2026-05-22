<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Listener;

use OCA\SuspiciousLogin\Service\LoginClassifier;
use OCA\SuspiciousLogin\Service\LoginDataCollector;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IRequest;
use OCP\User\Events\UserLoggedInEvent;

/**
 * @implements IEventListener<UserLoggedInEvent>
 */
class LoginListener implements IEventListener {
	/** @psalm-mutation-free */
	public function __construct(
		private readonly IRequest $request,
		private readonly ITimeFactory $timeFactory,
		private readonly LoginClassifier $loginClassifier,
		private readonly LoginDataCollector $loginDataCollector,
	) {
	}

	#[\Override]
	public function handle(Event $event): void {
		if (!$event instanceof UserLoggedInEvent) {
			return;
		}

		$this->handleClassification($event);
		$this->handleDataCollection($event);
	}

	private function handleClassification(UserLoggedInEvent $event): void {
		if ($event->isTokenLogin()) {
			// We don't care about those
			return;
		}

		$this->loginClassifier->process(
			$event->getUser()->getUID(),
			$this->request->getRemoteAddress()
		);
	}

	private function handleDataCollection(UserLoggedInEvent $event): void {
		$this->loginDataCollector->collectSuccessfulLogin(
			$event->getUser()->getUID(),
			$this->request->getRemoteAddress(),
			$this->timeFactory->getTime()
		);
	}
}
