<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/*
 * Companion to rubix-collision-repro.php for the opposite hazard: another app
 * (e.g. mail) ships rubix *un-scoped* and has already defined the Rubix\ML\* /
 * Tensor\* symbols - from a different path - before we boot. RubixBootstrap.php
 * must notice the symbols already exist and NOT reload its own copy, otherwise
 * PHP fatals with "Cannot redeclare function Rubix\ML\argmin()" (or warns about
 * a redeclared constant).
 *
 * The pre-existing symbols are synthesised here instead of relying on another
 * app being installed, so this runs on a clean CI runner. With a correct guard
 * RubixBootstrap.php is a no-op; without it the require_once below redeclares
 * the synthesised symbols.
 *
 * Exit codes: 0 = no reload, 4 = a redeclare warning fired, 255 = redeclare
 * fatal.
 */

$appRoot = dirname(__DIR__, 3);

// Make a "Constant ... already defined" warning fail the run as well.
set_error_handler(static function (int $no, string $msg): bool {
	fwrite(STDERR, "unexpected error: $msg\n");
	exit(4);
});

// Pretend another un-scoped rubix copy already defined the symbols we guard on.
eval('namespace Rubix\ML { function sigmoid(float $v): float { return $v; } const HALF_PI = 1.0; }');
eval('namespace Tensor { const EPSILON = 1e-8; }');

require $appRoot . '/lib/RubixBootstrap.php';

echo "ok\n";
exit(0);
