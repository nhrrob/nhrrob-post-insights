<?php
namespace NHRRob\PostInsights\Tests;

use NHRRob\PostInsights\Store;
use PHPUnit\Framework\TestCase;

/**
 * The days of a chart and the views with no referrer.
 */
class StatsTest extends TestCase {

	public function test_one_posts_days_include_a_day_with_no_row_as_zero() {
		$by_day = [
			'2026-11-01' => (object) [
				'views'   => 3,
				'seconds' => 90,
			],
			'2026-11-03' => (object) [
				'views'   => 1,
				'seconds' => 20,
			],
		];

		$this->assertSame(
			[
				[
					'day'     => '2026-11-01',
					'views'   => 3,
					'seconds' => 90,
				],
				[
					'day'     => '2026-11-02',
					'views'   => 0,
					'seconds' => 0,
				],
				[
					'day'     => '2026-11-03',
					'views'   => 1,
					'seconds' => 20,
				],
			],
			Store::days( '2026-11-01', 3, $by_day )
		);
	}

	public function test_no_referrer_is_the_views_less_the_referred_never_below_zero() {
		$this->assertSame( 4, Store::direct( 10, 6 ) );
		$this->assertSame( 0, Store::direct( 6, 6 ) );
		$this->assertSame( 0, Store::direct( 5, 6 ) );
	}

	public function test_the_days_before_a_range_are_as_many_and_end_the_day_before() {
		$this->assertSame( [ '2026-10-02', '2026-10-31' ], Store::before( '2026-11-01', '2026-11-30' ) );
		$this->assertSame( [ '2026-10-31', '2026-10-31' ], Store::before( '2026-11-01', '2026-11-01' ) );
		$this->assertSame( [ '2024-02-23', '2024-02-29' ], Store::before( '2024-03-01', '2024-03-07' ) );
	}
}
