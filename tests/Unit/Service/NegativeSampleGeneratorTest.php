<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
				'Sample must have an unique IP'
			);
		}
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
				'Sample must have an unique IP'
			);
		}
	}

	/**
	 * Generating shuffled samples isn't possible when no user has an unique IP.
	 * In that case, we have to return an empty Labeled() object as merging will
	 * fail otherwise. See GitHub issue #860 for more.
	 * @return void
	 */
	public function testGenerateShuffledFromDuplicatesOnly(): void {
		$positives = new Unlabeled([
			array_merge(self::decToBitArray(1, 16), self::decToBitArray(1, 32)),
			array_merge(self::decToBitArray(2, 16), self::decToBitArray(1, 32)),
			array_merge(self::decToBitArray(3, 16), self::decToBitArray(1, 32)),
			array_merge(self::decToBitArray(4, 16), self::decToBitArray(1, 32)),
		]);

		$result = $this->generator->generateShuffledFromPositiveSamples($positives, 4);

		self::assertCount(0, $result, 'Returned sample must be empty');
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
