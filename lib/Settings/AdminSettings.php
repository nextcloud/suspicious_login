<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Settings;

use OCA\SuspiciousLogin\AppInfo\Application;
use OCA\SuspiciousLogin\Service\AdminService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IInitialStateService;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings {

	/** @var IInitialStateService */
	private $initialStateService;

	/** @var AdminService */
	private $adminService;

	public function __construct(IInitialStateService $initialStateService, AdminService $adminService) {
		$this->initialStateService = $initialStateService;
		$this->adminService = $adminService;
	}

	public function getForm() {
		$this->initialStateService->provideInitialState(
			Application::APP_ID,
			'stats',
			$this->adminService->getStatistics()
		);

		return new TemplateResponse(Application::APP_ID, 'settings-admin');
	}

	public function getSection() {
		return 'security';
	}

	public function getPriority() {
		return 90;
	}
}
