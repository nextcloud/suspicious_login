<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Tests\Unit\Settings;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\SuspiciousLogin\Service\AdminService;
use OCA\SuspiciousLogin\Service\Statistics\AppStatistics;
use OCA\SuspiciousLogin\Settings\AdminSettings;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IInitialStateService;
use PHPUnit\Framework\MockObject\MockObject;

class AdminSettingsTest extends TestCase {

	/** @var IInitialStateService|MockObject */
	private $initialState;

	/** @var AdminService|MockObject */
	private $adminService;

	/** @var AdminSettings */
	private $settings;

	protected function setUp(): void {
		parent::setUp();

		$this->initialState = $this->createMock(IInitialStateService::class);
		$this->adminService = $this->createMock(AdminService::class);

		$this->settings = new AdminSettings(
			$this->initialState,
			$this->adminService
		);
	}


	public function testGetSection() {
		$section = $this->settings->getSection();

		$this->assertEquals('security', $section);
	}

	public function testGetForm() {
		$stats = $this->createMock(AppStatistics::class);
		$this->adminService->expects($this->once())
			->method('getStatistics')
			->willReturn($stats);
		$this->initialState->expects($this->once())
			->method('provideInitialState');
		$expected = new TemplateResponse('suspicious_login', 'settings-admin');

		$template = $this->settings->getForm();

		$this->assertEquals($expected, $template);
	}

	public function testGetPriority() {
		$priority = $this->settings->getPriority();

		$this->assertEquals(90, $priority);
	}
}
