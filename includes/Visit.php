<?php
/**
 * What a visit may add to the numbers: which visitors count, which site they came from and how long they stayed.
 *
 * @package NHRRob\PostInsights
 */

namespace NHRRob\PostInsights;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visit Class
 *
 * Nothing here identifies a visitor. No cookie is set, and neither the IP
 * address nor the full address of the referring page is kept.
 */
class Visit {

	/**
	 * The longest stay counted for one page view, in seconds.
	 */
	const MAX_SECONDS = 1800;

	/**
	 * Whether a user agent is a crawler, a preview fetcher or a script.
	 *
	 * @param string $agent User agent.
	 * @return bool
	 */
	public static function is_bot( $agent ) {
		$agent = (string) $agent;

		return '' === $agent || 1 === preg_match( '/bot\b|crawl|spider|slurp|preview|fetch|monitor|lighthouse|headless|curl|wget|python|http-client|facebookexternalhit|whatsapp/i', $agent );
	}

	/**
	 * The site a visitor came from, reduced to its host name.
	 *
	 * @param string $referrer Address of the referring page.
	 * @param string $own      This site's host name.
	 * @return string Empty for no referrer, this site itself or an address that is not a web page.
	 */
	public static function host( $referrer, $own ) {
		$parts = wp_parse_url( trim( (string) $referrer ) );

		if ( ! is_array( $parts ) || empty( $parts['host'] ) || ! isset( $parts['scheme'] ) || ! in_array( strtolower( $parts['scheme'] ), [ 'http', 'https' ], true ) ) {
			return '';
		}

		$host = (string) preg_replace( '/^www\./', '', strtolower( $parts['host'] ) );

		if ( preg_replace( '/^www\./', '', strtolower( (string) $own ) ) === $host || ! preg_match( '/^[a-z0-9.-]{1,191}$/', $host ) ) {
			return '';
		}

		return $host;
	}

	/**
	 * Seconds of a stay as they may be added: whole, not negative and capped.
	 *
	 * @param mixed $seconds Seconds reported by the browser.
	 * @return int
	 */
	public static function seconds( $seconds ) {
		return max( 0, min( self::MAX_SECONDS, (int) $seconds ) );
	}

	/**
	 * Whether views of a post are counted: a published, public post without a password.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function counts( $post_id ) {
		$post = get_post( $post_id );

		$counts = $post && 'publish' === $post->post_status && '' === $post->post_password && is_post_type_viewable( $post->post_type );

		/**
		 * Whether views of a post are counted.
		 *
		 * @param bool $counts  Whether they are.
		 * @param int  $post_id Post ID.
		 */
		return (bool) apply_filters( 'nhrrob_post_insights_counts', $counts, (int) $post_id );
	}
}
