<?php

declare(strict_types=1);

/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\SuspiciousLogin\Service;

use function array_merge;
use function str_pad;
use function substr;

abstract class AUidIpVector {

	/** @var string */
	private $uid;

	/** @var string */
	protected $ip;

	/** @var string */
	private $label;

	public function __construct(string $uid, string $ip, string $label) {
		$this->uid = $uid;
		$this->ip = $ip;
		$this->label = $label;
	}

	private function numStringToBitArray(string $s, int $base, int $padding): array {
		$bin = base_convert($s, $base, 2);
		// make sure we get 00000000 to 11111111
		$padded = str_pad($bin, $padding, '0', STR_PAD_LEFT);
		return str_split($padded);
	}

	protected function numStringsToBitArray(array $strings, $base, $padding): array {
		$converted = array_reduce(array_map(function (string $s) use ($base, $padding) {
			return $this->numStringToBitArray($s, $base, $padding);
		}, $strings), 'array_merge', []);
		return array_map(function ($x) {
			return (float)$x;
		}, $converted);
	}

	private function uidAsFeatureVector(): array {
		// TODO: just convert to binary and do substr of that
		$splitHash = str_split(
			substr(
				md5($this->uid),
				0,
				4
			)
		);
		return $this->numStringsToBitArray($splitHash, 16, 4);
	}

	/**
	 * Convert the decimal ip notation w.x.y.z to a binary (32bit) vector
	 *
	 * @return array
	 */
	abstract protected function ipAsFeatureVector(): array;

	public function asFeatureVector(): array {
		$v = $this->ipAsFeatureVector();
		return array_merge(
			$this->uidAsFeatureVector(),
			$v
		);
	}

	public function getIp(): string {
		return $this->ip;
	}

	public function getUid(): string {
		return $this->uid;
	}

	public function getLabel(): string {
		return $this->label;
	}
}
