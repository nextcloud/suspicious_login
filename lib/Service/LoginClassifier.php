<?php

declare(strict_types=1);

/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\SuspiciousLogin\Service;

use function base64_decode;
use function explode;
use OCA\SuspiciousLogin\Event\SuspiciousLoginEvent;
use OCA\SuspiciousLogin\Exception\ServiceException;
use OCA\SuspiciousLogin\Util\AddressClassifier;
use OCP\EventDispatcher\IEventDispatcher;
use function preg_match;
use function strlen;
use function substr;
use OCA\SuspiciousLogin\Db\SuspiciousLogin;
use OCA\SuspiciousLogin\Db\SuspiciousLoginMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\ILogger;
use OCP\IRequest;
use Throwable;

class LoginClassifier {

	/** @var EstimatorService */
	private $estimator;

	/** @var IRequest */
	private $request;

	/** @var ILogger */
	private $logger;

	/** @var SuspiciousLoginMapper */
	private $mapper;

	/** @var ITimeFactory */
	private $timeFactory;

	/** @var IEventDispatcher */
	private $dispatcher;

	public function __construct(EstimatorService $estimator,
								IRequest $request,
								ILogger $logger,
								SuspiciousLoginMapper $mapper,
								ITimeFactory $timeFactory,
								IEventDispatcher $dispatcher) {
		$this->estimator = $estimator;
		$this->request = $request;
		$this->logger = $logger;
		$this->mapper = $mapper;
		$this->timeFactory = $timeFactory;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * @todo find a more reliable way of checking this
	 */
	private function isAuthenticatedWithAppPassword(IRequest $request): bool {
		$authHeader = $request->getHeader('Authorization');
		if (is_null($authHeader)) {
			return false;
		}
		if (substr($authHeader, 0, strlen('Basic ')) !== 'Basic ') {
			return false;
		}
		$pwd = explode(
			':',
			base64_decode(substr($authHeader, strlen('Basic ')))
		);
		if (!isset($pwd[1])) {
			return false;
		}

		return preg_match(
				"/^([0-9A-Za-z]{5})-([0-9A-Za-z]{5})-([0-9A-Za-z]{5})-([0-9A-Za-z]{5})-([0-9A-Za-z]{5})$/",
				$pwd[1]
			) === 1;
	}

	public function process(string $uid, string $ip) {
		if ($this->isAuthenticatedWithAppPassword($this->request)) {
			// We don't care about those logins
			$this->logger->debug("App password detected. No address classification is performed");
			return;
		}
		try {
			$strategy = AddressClassifier::isIpV4($ip) ? new Ipv4Strategy() : new IpV6Strategy();
			if ($this->estimator->predict($uid, $ip, $strategy)) {
				$this->logger->debug("Ip $ip for user $uid is not suspicious");
				// All good, carry on!
				return;
			}
		} catch (ServiceException $ex) {
			$this->logger->debug("Could not predict suspiciousness: " . $ex->getMessage());
			// This most likely means there is no trained model yet, so we return early here
			return;
		}

		$this->logger->info("Detected a login from a suspicious login. user=$uid ip=$ip strategy=" . $strategy::getTypeName());

		$login = $this->persistSuspiciousLogin($uid, $ip);
		$this->notifyUser($uid, $ip, $login);
	}

	/**
	 * @param string $uid
	 * @param string $ip
	 */
	private function persistSuspiciousLogin(string $uid, string $ip): SuspiciousLogin {
		try {
			$entity = new SuspiciousLogin();
			$entity->setUid($uid);
			$entity->setIp($ip);
			$entity->setRequestId($this->request->getId());
			$entity->setUrl($this->request->getRequestUri());
			$entity->setCreatedAt($this->timeFactory->getTime());

			$this->mapper->insert($entity);

			return $entity;
		} catch (Throwable $ex) {
			$this->logger->critical("could not save the details of a suspicious login");
			$this->logger->logException($ex);
		}
	}

	/**
	 * @param string $uid
	 * @param string $ip
	 * @param SuspiciousLogin $login
	 */
	private function notifyUser(string $uid, string $ip, SuspiciousLogin $login): void {
		$now = $this->timeFactory->getTime();

		// Assuming that a suspicious IP is most likely one that hasn't been seen before
		// (for this user), we'll not send another notification until the data is used
		// for the model training
		$secondsSinceLastTraining = TrainingDataConfig::default()->getThreshold() * 60 * 60 * 24;
		if (count($this->mapper->findRelated($uid, $ip, $login, $now - $secondsSinceLastTraining)) > 0) {
			$this->logger->debug("Notification for $uid:$ip already sent, waiting until the next training period");
			$login->setNotificationState(NotificationState::NOT_SENT_DUPLICATE);
			$this->mapper->update($login);
			return;
		}

		$lastTwoDays = count($this->mapper->findRecentByUid($uid, $now - 60 * 60 * 24 * 2));
		if ($lastTwoDays > 10) {
			$this->logger->info("Suspicious login peak detected: $uid received $lastTwoDays alerts in the last two days");
			$login->setNotificationState(NotificationState::NOT_SENT_PEAK_TWO_DAYS);
			$this->mapper->update($login);
			return;
		}

		$lastHour = count($this->mapper->findRecentByUid($uid, $now - 60 * 60));
		if ($lastHour > 3) {
			$this->logger->info("Suspicious login peak detected: $uid received $lastHour alerts in the last hour");
			$login->setNotificationState(NotificationState::NOT_SENT_PEAK_ONE_HOUR);
			$this->mapper->update($login);
			return;
		}

		$event = new SuspiciousLoginEvent($uid, $ip);
		$this->dispatcher->dispatch(SuspiciousLoginEvent::class, $event);
		$login->setNotificationState(NotificationState::SENT);

		$this->mapper->update($login);
	}
}
