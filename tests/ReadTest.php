<?php
namespace NHRRob\PostInsights\Tests;

use NHRRob\PostInsights\Read;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * The reads answer in their documented shape, whatever the table holds.
 */
class ReadTest extends TestCase {

	protected function setUp(): void {
		WP_Mock::setUp();
		$GLOBALS['wpdb'] = new class() {
			public $prefix = 'wp_';
			public $rows   = [];
			public function prepare( $query, ...$args ) {
				return $query;
			}
			public function get_results() {
				return $this->rows;
			}
			public function get_row() {
				return null;
			}
		};
	}

	protected function tearDown(): void {
		unset( $GLOBALS['wpdb'] );
		WP_Mock::tearDown();
	}

	public function test_hosts_are_keyed_by_host() {
		$GLOBALS['wpdb']->rows = [ (object) [ 'item' => 'linkedin.com', 'views' => '3' ] ];

		$this->assertSame( [ 'linkedin.com' => 3 ], Read::hosts( '2026-11-01', '2026-11-24' ) );
	}

	public function test_referrals_of_no_posts_read_nothing() {
		$this->assertSame( [], Read::referrals( [ 0, 'x' ] ) );
	}

	public function test_referrals_name_the_host() {
		$GLOBALS['wpdb']->rows = [ (object) [ 'post_id' => '5', 'day' => '2026-11-24', 'item' => 'x.com', 'views' => '2' ] ];

		$this->assertSame( [ [ 'post_id' => 5, 'day' => '2026-11-24', 'host' => 'x.com', 'views' => 2 ] ], Read::referrals( [ 5 ] ) );
	}

	public function test_totals_of_a_post_without_rows_are_zero() {
		$this->assertSame( [ 'views' => 0, 'seconds' => 0 ], Read::totals( 5 ) );
	}
}
