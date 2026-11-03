<?php
namespace NHRRob\PostInsights\Tests;

use NHRRob\PostInsights\Columns;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * What an add-on declares reaches the app, and a malformed entry is dropped.
 */
class ColumnsTest extends TestCase {

	protected function setUp(): void {
		WP_Mock::setUp();
		WP_Mock::passthruFunction( 'sanitize_key' );
		WP_Mock::userFunction( 'wp_strip_all_tags' )->andReturnUsing( 'strip_tags' );
	}

	protected function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_nothing_declared_gives_nothing() {
		$this->assertSame(
			[
				'columns' => [],
				'tiles'   => [],
			],
			Columns::get()
		);
	}

	public function test_valid_entries_are_kept_and_malformed_ones_dropped() {
		WP_Mock::onFilter( 'nhrrob_post_insights_columns' )
			->with(
				[
					'columns' => [],
					'tiles'   => [],
				]
			)
			->reply(
				[
					'columns' => [
						[ 'key' => 'reactions', 'label' => 'Reactions', 'kind' => 'number' ],
						[ 'key' => 'read', 'label' => '<b>Reading time</b>', 'kind' => 'time' ],
						[ 'key' => 'reactions', 'label' => 'Twice', 'kind' => 'number' ],
						[ 'key' => 'bad', 'label' => 'Bad kind', 'kind' => 'html' ],
						[ 'label' => 'No key' ],
						[ 'key' => 'nolabel', 'label' => ' ' ],
						'not an entry',
					],
					'tiles'   => 'not a list',
				]
			);

		$this->assertSame(
			[
				'columns' => [
					[ 'key' => 'reactions', 'label' => 'Reactions', 'kind' => 'number' ],
					[ 'key' => 'read', 'label' => 'Reading time', 'kind' => 'time' ],
				],
				'tiles'   => [],
			],
			Columns::get()
		);
	}
}
