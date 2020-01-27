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
