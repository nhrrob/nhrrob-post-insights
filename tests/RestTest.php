<?php
namespace NHRRob\PostInsights\Tests;

use NHRRob\PostInsights\Rest;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * The posts table's route: what it accepts and who may call it.
 */
class RestTest extends TestCase {

	protected function setUp(): void {
		WP_Mock::setUp();
	}

	protected function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_posts_route_takes_days_sort_order_and_page() {
		$args = Rest::posts_args();

		$this->assertSame( [ 'days', 'orderby', 'order', 'page', 'type', 'from', 'to', 'search' ], array_keys( $args ) );
		$this->assertSame( 'absint', $args['days']['sanitize_callback'] );
		$this->assertSame( [ 'title', 'date', 'views', 'average' ], $args['orderby']['enum'] );
		$this->assertSame( 'views', $args['orderby']['default'] );
		$this->assertSame( [ 'asc', 'desc' ], $args['order']['enum'] );
		$this->assertSame( 1, $args['page']['default'] );
		$this->assertSame( 1, $args['page']['minimum'] );
	}

	public function test_posts_route_is_behind_the_capability() {
		$routes = [];

		WP_Mock::userFunction( 'register_rest_route' )->andReturnUsing(
			function ( $space, $route, $args ) use ( &$routes ) {
				$routes[ $route ] = $args;
				return true;
			}
		);

		( new Rest() )->register_routes();

		$this->assertSame( [ Rest::class, 'can_view' ], $routes['/posts']['permission_callback'] );
		$this->assertSame( 'GET', $routes['/posts']['methods'] );

		WP_Mock::onFilter( 'nhrrob_post_insights_capability' )->with( 'manage_options' )->reply( 'manage_options' );
		WP_Mock::userFunction( 'current_user_can' )->with( 'manage_options' )->andReturn( false, true );

		$this->assertFalse( Rest::can_view() );
		$this->assertTrue( Rest::can_view() );
	}
}
