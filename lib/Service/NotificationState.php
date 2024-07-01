<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\SuspiciousLogin\Service;

class NotificationState {
	public const SENT = 1;
	public const NOT_SENT_DUPLICATE = 2;
	public const NOT_SENT_PEAK_TWO_DAYS = 3;
	public const NOT_SENT_PEAK_ONE_HOUR = 4;
}
