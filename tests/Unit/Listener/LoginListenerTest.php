<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Tests\Listener;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\SuspiciousLogin\Event\PostLoginEvent;
use OCA\SuspiciousLogin\Listener\LoginListener;
use OCA\SuspiciousLogin\Service\LoginClassifier;
use OCA\SuspiciousLogin\Service\LoginDataCollector;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\EventDispatcher\Event;
use OCP\IRequest;
use PHPUnit\Framework\MockObject\MockObject;

class LoginListenerTest extends TestCase {

	/** @var IRequest|MockObject */
	private $request;

	/** @var ITimeFactory|MockObject */
	private $timeFactory;

	/** @var LoginClassifier|MockObject */
	private $loginClassifier;

	/** @var LoginDataCollector|MockObject */
	private $loginDataCollector;

	/** @var LoginListener */
	private $listener;

	protected function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->loginClassifier = $this->createMock(LoginClassifier::class);
		$this->loginDataCollector = $this->createMock(LoginDataCollector::class);

		$this->listener = new LoginListener(
			$this->request,
			$this->timeFactory,
			$this->loginClassifier,
			$this->loginDataCollector
		);
	}

	public function testHandleUnrelated(): void {
		$event = $this->createMock(Event::class);
		$this->loginClassifier->expects($this->never())
			->method('process');

		$this->listener->handle($event);
	}

	public function testHandleTokenLogin(): void {
		$this->loginClassifier->expects($this->never())
			->method('process');
		$this->loginDataCollector->expects($this->once())
			->method('collectSuccessfulLogin')
			->with(
				'user',
				$this->anything(),
				$this->anything()
			);
		$event = new PostLoginEvent('user', true);

		$this->listener->handle($event);
	}

	public function testHandlePasswordLogin(): void {
		$this->loginClassifier->expects($this->once())
			->method('process');
		$this->loginDataCollector->expects($this->once())
			->method('collectSuccessfulLogin')
			->with(
				'user',
				$this->anything(),
				$this->anything()
			);
		$event = new PostLoginEvent('user', false);

		$this->listener->handle($event);
	}
}
