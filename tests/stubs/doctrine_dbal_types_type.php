<?php

namespace Doctrine\DBAL\Types;

/**
 * SPDX-FileCopyrightText: 2006-2018 Doctrine Project
 * SPDX-FileCopyrightText: Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: MIT
 */

abstract class Type {
	public static function getType($name) {
		return new static();
	}
}
