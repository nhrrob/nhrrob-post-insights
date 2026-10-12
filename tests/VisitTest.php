<?php
namespace NHRRob\PostInsights\Tests;

use NHRRob\PostInsights\Visit;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * Which visitors count, where they came from and how long they stayed.
 */
class VisitTest extends TestCase {

	protected function setUp(): void {
		WP_Mock::setUp();
		WP_Mock::userFunction( 'wp_parse_url' )->andReturnUsing( 'parse_url' );
	}

	protected function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_a_browser_counts_and_a_crawler_does_not() {
		$this->assertFalse( Visit::is_bot( 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15' ) );
		$this->assertTrue( Visit::is_bot( 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)' ) );
		$this->assertTrue( Visit::is_bot( 'LinkedInBot/1.0 (compatible; Mozilla/5.0; Apache-HttpClient +http://www.linkedin.com)' ) );
		$this->assertTrue( Visit::is_bot( 'curl/8.4.0' ) );
		$this->assertTrue( Visit::is_bot( '' ) );
	}

	public function test_a_referrer_is_reduced_to_its_host() {
		$this->assertSame( 'linkedin.com', Visit::host( 'https://www.linkedin.com/feed/update/urn:li:share:1?x=1', 'example.com' ) );
		$this->assertSame( 'news.ycombinator.com', Visit::host( 'https://News.Ycombinator.com/item?id=1', 'example.com' ) );
	}

	public function test_the_site_itself_and_other_schemes_are_no_referrer() {
		$this->assertSame( '', Visit::host( 'https://www.example.com/blog/', 'example.com' ) );
		$this->assertSame( '', Visit::host( 'https://example.com/blog/', 'www.example.com' ) );
		$this->assertSame( '', Visit::host( 'android-app://com.linkedin.android/', 'example.com' ) );
		$this->assertSame( '', Visit::host( 'not an address', 'example.com' ) );
		$this->assertSame( '', Visit::host( '', 'example.com' ) );
	}

	public function test_seconds_are_whole_not_negative_and_capped() {
		$this->assertSame( 42, Visit::seconds( '42' ) );
		$this->assertSame( 0, Visit::seconds( -5 ) );
		$this->assertSame( 0, Visit::seconds( 'abc' ) );
		$this->assertSame( Visit::MAX_SECONDS, Visit::seconds( 999999 ) );
	}
}
