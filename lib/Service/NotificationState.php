<?php

declare(strict_types=1);

namespace OCA\SuspiciousLogin\Service;

class NotificationState {
	public const SENT = 1;
	public const NOT_SENT_DUPLICATE = 2;
	public const NOT_SENT_PEAK_TWO_DAYS = 3;
	public const NOT_SENT_PEAK_ONE_HOUR = 4;
}
