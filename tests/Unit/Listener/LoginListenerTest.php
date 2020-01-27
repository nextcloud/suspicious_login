<?php declare(strict_types=1);

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
