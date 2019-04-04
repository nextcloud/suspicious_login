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

namespace OCA\SuspiciousLogin\AppInfo;


use function call_user_func_array;
use function func_get_args;
use OCA\SuspiciousLogin\Listener\LoginListener;
use OCA\SuspiciousLogin\Notifications\Notifier;
use OCP\AppFramework\IAppContainer;
use OCP\IL10N;
use OCP\Notification\IManager;
use OCP\Util;

class BootstrapSingleton {

	/** @var BootstrapSingleton */
	private static $instance = null;

	private $booted = false;

	private function __construct() {
	}

	public static function getInstance(): BootstrapSingleton {
		if (self::$instance === null) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	public function boot(IAppContainer $container): void {
		if ($this->booted) {
			return;
		}

		$this->registerEvents($container);
		$this->registerNotification($container);

		$this->booted = true;
	}

	private function registerEvents(IAppContainer $container): void {
		$lazyListener = new class($container) {
			/** @var IAppContainer */
			private $container;

			public function __construct(IAppContainer $container) {
				$this->container = $container;
			}

			public function handle() {
				/** @var LoginListener $loginListener */
				$loginListener = $this->container->query(LoginListener::class);
				call_user_func_array([$loginListener, 'handle'], func_get_args());
			}
		};

		Util::connectHook(
			'OC_User',
			'post_login',
			$lazyListener,
			'handle'
		);
	}

	private function registerNotification(IAppContainer $container) {
		/** @var IManager $manager */
		$manager = $container->query(IManager::class);
		$manager->registerNotifier(
			function () use ($container) {
				return $container->query(Notifier::class);
			},
			function () use ($container) {
				$l = $container->query(IL10N::class);
				return ['id' => self::APP_ID, 'name' => $l->t('Suspicious Login')];
			}
		);
	}

}
