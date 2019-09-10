<?php

declare(strict_types=1);


namespace OCA\SuspiciousLogin\Service;


class UidIpV4Vector extends AUidIpVector {

	/**
	 * Convert the decimal ip notation w.x.y.z to a binary (32bit) vector
	 *
	 * @return array
	 */
	protected function ipAsFeatureVector(): array {
		$splitIp = explode('.', $this->ip);
		return $this->numStringsToBitArray($splitIp, 10, 8);
	}
}
