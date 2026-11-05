<?php
namespace NHRRob\PostInsights\Tests;

use NHRRob\PostInsights\Store;
use PHPUnit\Framework\TestCase;

/**
 * How the posts table is sorted.
 */
class StoreTest extends TestCase {

	public function test_average_is_sorted_by_the_sums_of_the_range() {
		$this->assertSame( 'CASE WHEN SUM(d.views) > 0 THEN SUM(d.seconds) / SUM(d.views) ELSE 0 END', Store::ORDERBY['average'] );
	}
}
