<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Service;

use OCA\SuspiciousLogin\Db\SuspiciousLogin;
use OCA\SuspiciousLogin\Db\SuspiciousLoginMapper;
use OCA\SuspiciousLogin\Event\SuspiciousLoginEvent;
use OCA\SuspiciousLogin\Exception\ModelNotFoundException;
use OCA\SuspiciousLogin\Exception\ServiceException;
use OCA\SuspiciousLogin\Util\AddressClassifier;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IRequest;
use Psr\Log\LoggerInterface;
use Throwable;
use function base64_decode;
use function explode;
use function preg_match;
use function strlen;
use function substr;

class LoginClassifier {

	public function __construct(
		private EstimatorService $estimator,
		private IRequest $request,
		private LoggerInterface $logger,
		private SuspiciousLoginMapper $mapper,
		private ITimeFactory $timeFactory,
		private IEventDispatcher $dispatcher,
	) {
	}

	/**
	 * @todo find a more reliable way of checking this
	 */
	private function isAuthenticatedWithAppPassword(IRequest $request): bool {
		$authHeader = $request->getHeader('Authorization');
		if (empty($authHeader)) {
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
		} catch (ModelNotFoundException $ex) {
			$this->logger->debug('Could not predict suspiciousness: ' . $ex->getMessage());
			// This most likely means there is no trained model yet, so we return early here
			return;
		} catch (ServiceException $ex) {
			$this->logger->warning("Could not predict suspiciousness: " . $ex->getMessage());
			// There was an error loading the model, so we return early here
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
	private function persistSuspiciousLogin(string $uid, string $ip): ?SuspiciousLogin {
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
			$this->logger->critical('could not save the details of a suspicious login', ['exception' => $ex]);
			return null;
		}
	}

	/**
	 * @param string $uid
	 * @param string $ip
	 * @param ?SuspiciousLogin $login If null the user will be notified regardless of training thresholds
	 */
	private function notifyUser(string $uid, string $ip, ?SuspiciousLogin $login): void {
		if ($login === null) {
			// There was an error persisting the login attempt, so we can not look for related events in the past.
			// But we should still warn the user and not silently accept that attempt.
			$event = new SuspiciousLoginEvent($uid, $ip);
			$this->dispatcher->dispatchTyped($event);
			return;
		}

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
		$this->dispatcher->dispatchTyped($event);
		$login->setNotificationState(NotificationState::SENT);

		$this->mapper->update($login);
	}
}
