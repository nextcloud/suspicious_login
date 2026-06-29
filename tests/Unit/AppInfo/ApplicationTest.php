<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Tests\Unit\AppInfo;

use ChristophWurst\Nextcloud\Testing\TestCase;

class ApplicationTest extends TestCase {

	/**
	 * Regression test for #1113: when another app wins Composer's "files"
	 * autoload dedupe race, our un-scoped rubix functions get skipped and
	 * inference crashes with "Tensor\Matrix::map(): ... must be of type
	 * callable, string given". RubixBootstrap.php must load them explicitly.
	 *
	 * The scenario only manifests across a fresh autoload order, so it is
	 * reproduced in a subprocess.
	 */
	public function testRubixFunctionsSurviveAutoloadDedupeCollision(): void {
		$script = __DIR__ . '/rubix-collision-repro.php';

		$output = [];
		$exitCode = -1;
		exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script) . ' 2>&1', $output, $exitCode);

		$message = implode("\n", $output);
		self::assertNotSame(2, $exitCode, "Collision could not be reproduced, the test is stale:\n$message");
		self::assertSame(0, $exitCode, "Sigmoid activation failed despite RubixBootstrap.php:\n$message");
	}
}
