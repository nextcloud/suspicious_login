<?php

declare(strict_types=1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
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

use OCA\SuspiciousLogin\Event\PostLoginEvent;
use OCA\SuspiciousLogin\Service\LoginClassifier;
use OCA\SuspiciousLogin\Service\LoginDataCollector;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IRequest;

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
