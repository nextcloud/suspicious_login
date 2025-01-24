<?php

declare(strict_types=1);

/*
 * @copyright 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\SuspiciousLogin\tests\Unit\Service;

use ChristophWurst\Nextcloud\Testing\ServiceMockObject;
use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\SuspiciousLogin\Service\AClassificationStrategy;
use OCA\SuspiciousLogin\Service\MLP\Trainer;
use OCA\SuspiciousLogin\Service\NegativeSampleGenerator;
use Rubix\ML\Datasets\Unlabeled;
use function array_fill;
use function array_merge;
use function array_slice;
use function decbin;
use function str_pad;
use function str_split;

class NegativeSampleGeneratorTest extends TestCase {

	/** @var ServiceMockObject */
	private $serviceMock;

	/** @var NegativeSampleGenerator */
	private $generator;

	protected function setUp(): void {
		parent::setUp();

		$this->serviceMock = $this->createServiceMock(NegativeSampleGenerator::class);
		$this->generator = $this->serviceMock->getService();
	}

	public function testGenerateRandomFromSinglePositive(): void {
		$uidVec = array_fill(0, 16, 0);
		$positiveVec = array_fill(0, 32, 1);
		$randVec = array_fill(0, 32, 2);
		$positives = new Unlabeled([array_merge($uidVec, $positiveVec)]);
		$strategy = $this->createMock(AClassificationStrategy::class);
		$strategy->method('generateRandomIpVector')->willReturn($randVec);

		$result = $this->generator->generateRandomFromPositiveSamples($positives, 1, $strategy);

		self::assertCount(1, $result);
		$first = $result[0];
		self::assertEquals(
			array_merge($uidVec, $randVec, [Trainer::LABEL_NEGATIVE]),
			$first
		);
	}

	public function testGenerateShuffledFromSinglePositive(): void {
		$uidVec = array_fill(0, 16, 0);
		$positiveVec = array_fill(0, 32, 1);
		$positives = new Unlabeled([array_merge($uidVec, $positiveVec)]);

		$result = $this->generator->generateShuffledFromPositiveSamples($positives, 1);

		self::assertCount(1, $result);
		$first = $result[0];
		self::assertEquals(
			array_merge($uidVec, $positiveVec, [Trainer::LABEL_NEGATIVE]),
			$first
		);
	}

	public function testGenerateShuffledFromSingleUnique(): void {
		$positives = new Unlabeled([
			array_merge(self::decToBitArray(1, 16), self::decToBitArray(1, 32)),
			array_merge(self::decToBitArray(1, 16), self::decToBitArray(2, 32)),
			array_merge(self::decToBitArray(1, 16), self::decToBitArray(3, 32)),

			array_merge(self::decToBitArray(2, 16), self::decToBitArray(1, 32)), // dup
			array_merge(self::decToBitArray(2, 16), self::decToBitArray(2, 32)), // dup
			array_merge(self::decToBitArray(2, 16), self::decToBitArray(3, 32)), // dup
			array_merge(self::decToBitArray(2, 16), self::decToBitArray(4, 32)), // uniq
		]);

		$result = $this->generator->generateShuffledFromPositiveSamples($positives, 1);

		self::assertCount(1, $result);
		$first = $result[0];
		self::assertEquals(
			self::decToBitArray(4, 32),
			array_slice($first, 16, 32)
		);
	}

	public function testGenerateMultipleShuffledFromLimitedUnique(): void {
		$positives = new Unlabeled([
			array_merge(self::decToBitArray(1, 16), self::decToBitArray(1, 32)),
			array_merge(self::decToBitArray(1, 16), self::decToBitArray(2, 32)),
			array_merge(self::decToBitArray(1, 16), self::decToBitArray(3, 32)), // uniq

			array_merge(self::decToBitArray(2, 16), self::decToBitArray(1, 32)), // dup
			array_merge(self::decToBitArray(2, 16), self::decToBitArray(2, 32)), // dup
			array_merge(self::decToBitArray(2, 16), self::decToBitArray(4, 32)), // uniq
		]);

		$result = $this->generator->generateShuffledFromPositiveSamples($positives, 5);

		self::assertCount(5, $result);
		foreach ($result as $sample) {
			$ipVec = array_slice($sample, 16, 32);

			self::assertTrue(
				$ipVec == self::decToBitArray(3, 32) ||
				$ipVec === self::decToBitArray(4, 32),
				'sample has a unique IP'
			);
		}

		$positives = new Unlabeled([
			array_merge(self::decToBitArray(1, 16), self::decToBitArray(1, 32)),
			array_merge(self::decToBitArray(2, 16), self::decToBitArray(1, 32)),
			array_merge(self::decToBitArray(3, 16), self::decToBitArray(1, 32)),
			array_merge(self::decToBitArray(4, 16), self::decToBitArray(1, 32)),
		]);

		$result = $this->generator->generateShuffledFromPositiveSamples($positives, 5);

		self::assertCount(5, $result);
	}

	/**
	 * DataSet can consist of multiple unique entries only. If not handled correctly,
	 * this will result in an array without any IP. This tests the
	 * correct handling. See GitHub issue #860 for more.
	 * @return void
	 */
	public function testGenerateMultipleShuffledFromUniquesOnly(): void {
		$positives = new Unlabeled([
			array_merge(self::decToBitArray(1, 16), self::decToBitArray(1, 32)),
			array_merge(self::decToBitArray(1, 16), self::decToBitArray(1, 32)),
			array_merge(self::decToBitArray(1, 16), self::decToBitArray(1, 32)),

			array_merge(self::decToBitArray(2, 16), self::decToBitArray(2, 32)),
			array_merge(self::decToBitArray(2, 16), self::decToBitArray(2, 32)),
			array_merge(self::decToBitArray(2, 16), self::decToBitArray(2, 32)),
		]);

		$result = $this->generator->generateShuffledFromPositiveSamples($positives, 2);

		self::assertCount(2, $result);
		foreach ($result as $sample) {
			$ipVec = array_slice($sample, 16, 32);

			self::assertTrue(
				$ipVec === self::decToBitArray(1, 32) ||
				$ipVec === self::decToBitArray(2, 32),
				'Sample has an unique IP'
			);
		}
	}

	/**
	 * @return int[]
	 */
	private static function decToBitArray(int $dec, int $length): array {
		return array_map(
			function (string $d): int {
				return (int) $d;
			},
			str_split(str_pad(decbin($dec), $length, "0", STR_PAD_LEFT))
		);
	}
}
