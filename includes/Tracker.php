<?php
/**
 * Loads the small script that reports a view of a post and how long the reader stayed.
 *
 * @package NHRRob\PostInsights
 */

namespace NHRRob\PostInsights;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tracker Class
 */
class Tracker {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	/**
	 * Enqueue the script on a single post or page, for visitors only.
	 *
	 * @return void
	 */
	public function enqueue() {
		// Whoever can write posts is the author looking at their own work.
		if ( ! is_singular() || is_preview() || current_user_can( 'edit_posts' ) || ! Visit::counts( get_queried_object_id() ) ) {
			return;
		}

		wp_enqueue_script(
			'nhrrob-post-insights',
			plugins_url( 'assets/tracker.js', NHRROB_POST_INSIGHTS_FILE ),
			[],
			NHRROB_POST_INSIGHTS_VERSION,
			[
				'strategy'  => 'defer',
				'in_footer' => true,
			]
		);
		wp_localize_script(
			'nhrrob-post-insights',
			'nhrrobPostInsights',
			[
				'root' => esc_url_raw( rest_url( Rest::REST_NAMESPACE ) ),
				'post' => get_queried_object_id(),
			]
		);
	}
}
