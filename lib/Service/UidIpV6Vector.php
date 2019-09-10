<?php

declare(strict_types=1);


namespace OCA\SuspiciousLogin\Service;


use function array_map;
use function base_convert;
use function bin2hex;
use Darsyn\IP\Version\IPv6;
use function str_pad;
use function str_split;
use function substr;

class UidIpV6Vector extends AUidIpVector {

	/**
	 * Convert the decimal ip notation w.x.y.z to a binary (32bit) vector
	 *
	 * @return array
	 */
	protected function ipAsFeatureVector(): array {
		$addr = IPv6::factory($this->ip);

		$hex = bin2hex($addr->getBinary());
		$padded = str_pad($hex, 32, '0', STR_PAD_LEFT);
		$binString = implode('', array_map(function(string $h) {
			return str_pad(base_convert($h, 16, 2), 4, '0', STR_PAD_LEFT);
		}, str_split($padded)));
		$mostSign = substr($binString, 0, 64);

		return array_map(
			function (string $bit) {
				return (float)$bit;
			},
			str_split($mostSign)
		);
	}
}
