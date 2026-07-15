<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/*
 * rubix/ml and rubix/tensor expose free functions and constants through
 * Composer's "files" autoloader rather than class autoloading (PHP does not
 * autoload functions or constants).
 *
 * That loader dedupes by a content-blind identifier, md5("<package>:<path>"),
 * stored process-wide in $GLOBALS['__composer_autoload_files']. Several apps
 * bundle rubix and register the very same identifier, so only the first app to
 * boot actually loads its copy; every other app's files loop is skipped. The
 * catch is that those copies are not interchangeable:
 *
 *   - an app that php-scopes rubix (e.g. recognize) only defines prefixed
 *     symbols such as OCA\Recognize\Vendor\Rubix\ML\sigmoid, so when it wins the
 *     race our un-scoped Rubix\ML\sigmoid is never defined and inference crashes
 *     with "Tensor\Matrix::map(): Argument #1 ($callback) must be of type
 *     callable, string given" (issue #1113);
 *   - an app that ships rubix un-scoped (e.g. mail) defines the very same
 *     Rubix\ML\* symbols we do, from a different path.
 *
 * So we must make sure our symbols exist, but only load our copy when they are
 * actually missing: blindly requiring the files would fatal with "Cannot
 * redeclare function Rubix\ML\argmin()" whenever another un-scoped copy already
 * loaded them. require_once is not enough here - it dedupes by realpath, not by
 * symbol, so it does not protect against another app's copy. Guard on a
 * representative symbol from each file instead.
 */
if (!function_exists('Rubix\ML\sigmoid')) {
	require_once __DIR__ . '/../vendor/rubix/ml/src/functions.php';
}
if (!defined('Rubix\ML\HALF_PI')) {
	require_once __DIR__ . '/../vendor/rubix/ml/src/constants.php';
}
if (!defined('Tensor\EPSILON')) {
	require_once __DIR__ . '/../vendor/rubix/tensor/src/constants.php';
}
