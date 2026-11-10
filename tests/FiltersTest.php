<?php
namespace NHRRob\PostInsights\Tests;

use NHRRob\PostInsights\Filters;
use NHRRob\PostInsights\Store;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * The filters: each argument's sanitizing and the days they resolve to.
 */
class FiltersTest extends TestCase {

	protected function setUp(): void {
		WP_Mock::setUp();
	}

	protected function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_a_date_is_kept_only_when_it_is_a_real_day() {
		$this->assertSame( '2026-02-28', Filters::date( '2026-02-28' ) );
		$this->assertSame( '', Filters::date( '2026-02-30' ) );
		$this->assertSame( '', Filters::date( '2026-2-28' ) );
		$this->assertSame( '', Filters::date( '2026-02-28 00:00' ) );
		$this->assertSame( '', Filters::date( [ '2026-02-28' ] ) );
		$this->assertSame( '', Filters::date( '' ) );
	}

	public function test_a_type_is_kept_only_when_it_is_counted() {
		WP_Mock::userFunction( 'sanitize_key' )->andReturnUsing(
			function ( $value ) {
				return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', $value ) );
			}
		);
		WP_Mock::userFunction( 'get_post_types' )->andReturn(
			[
				'post'       => 'post',
				'page'       => 'page',
				'attachment' => 'attachment',
				'book'       => 'book',
			]
		);
		WP_Mock::userFunction( 'is_post_type_viewable' )->andReturnUsing(
			function ( $type ) {
				return 'book' !== $type;
			}
		);

		$this->assertSame( [ 'post', 'page' ], Filters::types() );
		$this->assertSame( 'post', Filters::type( 'Post' ) );
		$this->assertSame( '', Filters::type( 'attachment' ) );
		$this->assertSame( '', Filters::type( 'book' ) );
		$this->assertSame( '', Filters::type( "post' OR 1=1" ) );
		$this->assertSame( '', Filters::type( [ 'post' ] ) );
	}

	public function test_a_search_is_plain_text_of_at_most_100_characters() {
		WP_Mock::userFunction( 'sanitize_text_field' )->andReturnUsing(
			function ( $value ) {
				return trim( strip_tags( $value ) );
			}
		);

		$this->assertSame( 'hello', Filters::search( '  <b>hello</b> ' ) );
		$this->assertSame( 100, mb_strlen( Filters::search( str_repeat( 'é', 150 ) ) ) );
		$this->assertSame( '', Filters::search( [ 'hello' ] ) );
	}

	public function test_args_add_the_search_to_the_posts_route_only() {
		$this->assertSame( [ 'type', 'from', 'to' ], array_keys( Filters::args() ) );
		$this->assertSame( [ 'type', 'from', 'to', 'search' ], array_keys( Filters::args( true ) ) );
		$this->assertSame( [ Filters::class, 'date' ], Filters::args()['from']['sanitize_callback'] );
	}

	public function test_a_custom_range_ends_today_at_the_latest_and_spans_at_most_max_days() {
		$today = '2026-11-10';

		$this->assertSame( [ '2026-10-01', '2026-10-31' ], Filters::range( 30, '2026-10-01', '2026-10-31', $today ) );
		$this->assertSame( [ '2026-11-01', $today ], Filters::range( 30, '2026-11-01', '2027-01-01', $today ) );
		$this->assertSame( [ '2021-11-07', $today ], Filters::range( 30, '2000-01-01', $today, $today ) );
	}

	public function test_a_custom_range_out_of_order_or_half_given_falls_back_to_the_days() {
		WP_Mock::userFunction( 'current_time' )->with( 'Y-m-d' )->andReturn( '2026-11-10' );
		$last_30 = [ '2026-10-12', '2026-11-10' ];

		$this->assertSame( $last_30, Filters::range( 30, '2026-10-31', '2026-10-01', '2026-11-10' ) );
		$this->assertSame( $last_30, Filters::range( 30, '2026-10-01', '', '2026-11-10' ) );
		$this->assertSame( $last_30, Filters::range( 30, 'yesterday', '2026-11-10', '2026-11-10' ) );
		$this->assertSame( $last_30, Filters::range( 30, '2026-12-01', '2026-12-31', '2026-11-10' ) );
	}

	public function test_a_filter_that_matches_nothing_still_lists_every_day_as_zero() {
		$this->assertSame(
			[
				[
					'day'     => '2026-10-30',
					'views'   => 0,
					'seconds' => 0,
				],
				[
					'day'     => '2026-10-31',
					'views'   => 0,
					'seconds' => 0,
				],
			],
			Store::days( '2026-10-30', 2, [] )
		);
	}
}

