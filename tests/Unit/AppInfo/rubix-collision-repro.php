<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/*
 * Standalone reproduction of issue #1113, run in a fresh PHP subprocess so we
 * fully control Composer's file autoload order (the bug cannot be reproduced
 * in-process because PHPUnit's bootstrap already loaded the rubix functions).
 *
 * It simulates another app (e.g. recognize) having won the composer "files"
 * dedupe race by pre-marking rubix's file identifiers as loaded *before* our
 * autoloader runs, so our un-scoped functions.php/constants.php get skipped.
 * Then it loads our RubixBootstrap.php (the fix under test) and asserts the
 * sigmoid activation - which relies on the string callable 'Rubix\ML\sigmoid' -
 * works again.
 *
 * Exit codes: 0 = fix works, 2 = collision could not be reproduced (bug guard
 * is stale), anything else (incl. 255 on TypeError) = the fix did not help.
 */

$appRoot = dirname(__DIR__, 3);

// File identifiers Composer assigns to the rubix files we depend on; these are
// md5("<package>:<path>") and are therefore identical in every install,
// including the scoped copy shipped by other apps.
$GLOBALS['__composer_autoload_files']['0315e8fd3e479309d097647b8ef2920b'] = true; // rubix/ml src/functions.php
$GLOBALS['__composer_autoload_files']['702239352e6628be5dc71b6fd029e72e'] = true; // rubix/ml src/constants.php
$GLOBALS['__composer_autoload_files']['8f758069bf9eb3411d096c10be343745'] = true; // rubix/tensor src/constants.php

require $appRoot . '/vendor/autoload.php';

// Guard: with the flags pre-set, our files loop must have skipped functions.php.
// If the function is already defined here the identifiers drifted and this test
// no longer reproduces the bug, so fail loudly instead of passing silently.
if (function_exists('Rubix\ML\sigmoid')) {
	fwrite(STDERR, "collision not reproduced: Rubix\\ML\\sigmoid already defined\n");
	exit(2);
}

// Exercise the exact path from the crash report. Without the fix this throws
// the TypeError from #1113 because 'Rubix\ML\sigmoid' no longer resolves.
$result = (new Rubix\ML\NeuralNet\ActivationFunctions\Sigmoid())
	->activate(Tensor\Matrix::quick([[0.5, -1.0], [2.0, 0.0]]));

exit($result instanceof Tensor\Matrix ? 0 : 3);
