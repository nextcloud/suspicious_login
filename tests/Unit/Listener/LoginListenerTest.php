<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Tests\Listener;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\SuspiciousLogin\Listener\LoginListener;
use OCA\SuspiciousLogin\Service\LoginClassifier;
use OCA\SuspiciousLogin\Service\LoginDataCollector;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\EventDispatcher\Event;
use OCP\IRequest;
use OCP\IUser;
use OCP\User\Events\UserLoggedInEvent;
use Override;
use PHPUnit\Framework\MockObject\MockObject;

class LoginListenerTest extends TestCase {
	private IRequest&MockObject $request;
	private ITimeFactory&MockObject $timeFactory;
	private LoginClassifier&MockObject $loginClassifier;

	private LoginDataCollector&MockObject $loginDataCollector;
	private LoginListener $listener;

	#[Override]
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
		$event = $this->createStub(Event::class);
		$this->loginClassifier->expects($this->never())
			->method('process');

		/** @psalm-suppress InvalidArgument */
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
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('user');
		$event = new UserLoggedInEvent($user, 'user', null, true);

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
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('user');
		$event = new UserLoggedInEvent($user, 'user', null, false);

		$this->listener->handle($event);
	}
}
