<?php
namespace NHRRob\PostInsights\Tests;

use NHRRob\PostInsights\Installer;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * The layout version is kept only when the table was created.
 */
class InstallerTest extends TestCase {

	/**
	 * The arguments of each update_option() call.
	 *
	 * @var array
	 */
	private $saved = [];

	protected function setUp(): void {
		WP_Mock::setUp();
		$GLOBALS['wpdb'] = new class() {
			public $prefix = 'wp_';
			public $tables = [];
			public function get_charset_collate() {
				return '';
			}
			public function esc_like( $text ) {
				return $text;
			}
			public function prepare( $query, ...$args ) {
				return $args[0];
			}
			public function get_var( $table ) {
				return in_array( $table, $this->tables, true ) ? $table : null;
			}
		};
		WP_Mock::userFunction( 'dbDelta' );
		WP_Mock::userFunction(
			'update_option',
			[
				'return' => function ( ...$args ) {
					$this->saved[] = $args;
					return true;
				},
			]
		);
	}

	protected function tearDown(): void {
		unset( $GLOBALS['wpdb'] );
		WP_Mock::tearDown();
	}

	public function test_a_failed_create_keeps_no_version() {
		Installer::install();

		$this->assertSame( [], $this->saved );
	}

	public function test_a_created_table_keeps_the_version() {
		$GLOBALS['wpdb']->tables = [ 'wp_nhrrob_post_insights' ];

		Installer::install();

		$this->assertSame( [ [ Installer::OPTION, Installer::DB_VERSION, true ] ], $this->saved );
	}
}
