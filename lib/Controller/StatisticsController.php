<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Controller;

use OCA\SuspiciousLogin\Service\StatisticsService;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

class StatisticsController extends OCSController {
	public function __construct(
		string $appName,
		IRequest $request,
		private readonly StatisticsService $statisticsService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Export stats about suspicious logins and training data.
	 *
	 * 200: The stats were collected successfully.
	 */
	#[ApiRoute(verb: 'GET', url: '/api/stats')]
	#[NoCSRFRequired]
	public function stats(): DataResponse {
		return new DataResponse($this->statisticsService->getStatistics());
	}
}
