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

use function array_key_exists;
use function array_map;
use function array_merge;
use ArrayAccess;
use Countable;
use OCA\SuspiciousLogin\Db\LoginAddressAggregated;
use OCA\SuspiciousLogin\Service\MLP\Trainer;
use function shuffle;

class DataSet implements ArrayAccess, Countable {

	/** @var UidIPVector[] */
	private $data;

	public function __construct(array $data) {
		$this->data = array_map(function (array $item) {
			return new UidIPVector($item['uid'], $item['ip'], $item['label']);
		}, $data);
	}

	/**
	 * @param LoginAddressAggregated[] $loginAddresses
	 */
	public static function fromLoginAddresses(array $loginAddresses): DataSet {
		return new DataSet(array_map(function (LoginAddressAggregated $addr) {
			return [
				'uid' => $addr->getUid(),
				'ip' => $addr->getIp(),
				'label' => Trainer::LABEL_POSITIVE,
			];
		}, $loginAddresses));
	}

	public function asTrainingData(): array {
		return array_map(function (UidIPVector $vec) {
			return $vec->asFeatureVector();
		}, $this->data);
	}

	/**
	 * Whether a offset exists
	 */
	public function offsetExists($offset) {
		return array_key_exists($offset, $this->data);
	}

	/**
	 * Offset to retrieve
	 */
	public function offsetGet($offset) {
		return $this->data[$offset];
	}

	/**
	 * Offset to set
	 */
	public function offsetSet($offset, $value) {
		$this->data[$offset] = $value;
	}

	/**
	 * Offset to unset
	 */
	public function offsetUnset($offset) {
		unset($this->data[$offset]);
	}

	public function count() {
		return count($this->data);
	}

	/**
	 * @return string[]
	 */
	public function getLabels(): array {
		return array_map(function (UidIPVector $vec) {
			return $vec->getLabel();
		}, $this->data);
	}

	public function merge(DataSet $other): DataSet {
		$merged = array_merge($this->data, $other->data);
		$new = new DataSet([]);
		$new->data = $merged;
		return $new;
	}

	public function shuffle() {
		shuffle($this->data);
	}

}
