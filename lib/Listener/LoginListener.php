<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Listener;

use OCA\SuspiciousLogin\Event\PostLoginEvent;
use OCA\SuspiciousLogin\Service\LoginClassifier;
use OCA\SuspiciousLogin\Service\LoginDataCollector;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IRequest;

/**
 * @implements IEventListener<PostLoginEvent>
 */
class LoginListener implements IEventListener {

	/** @var IRequest */
	private $request;

	/** @var ITimeFactory */
	private $timeFactory;

	/** @var LoginClassifier */
	private $loginClassifier;

	/** @var LoginDataCollector */
	private $loginDataCollector;

	public function __construct(IRequest $request,
		ITimeFactory $timeFactory,
		LoginClassifier $loginClassifier,
		LoginDataCollector $loginDataCollector) {
		$this->request = $request;
		$this->timeFactory = $timeFactory;
		$this->loginDataCollector = $loginDataCollector;
		$this->loginClassifier = $loginClassifier;
	}

	public function handle(Event $event): void {
		if (!($event instanceof PostLoginEvent)) {
			// Unrelated
			return;
		}

		$this->handleClassification($event);
		$this->handleDataCollection($event);
	}

	private function handleClassification(PostLoginEvent $event): void {
		if ($event->isTokenLogin()) {
			// We don't care about those
			return;
		}

		$this->loginClassifier->process(
			$event->getUid(),
			$this->request->getRemoteAddress()
		);
	}

	private function handleDataCollection(PostLoginEvent $event): void {
		$this->loginDataCollector->collectSuccessfulLogin(
			$event->getUid(),
			$this->request->getRemoteAddress(),
			$this->timeFactory->getTime()
		);
	}
}
