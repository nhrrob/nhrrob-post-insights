<?php
/**
 * Plugin Name: NHR Post Insights
 * Plugin URI: https://github.com/nhrrob/nhrrob-post-insights
 * Description: How many readers each post gets, where they come from and how long they stay. No cookies, and the numbers stay on your site.
 * Author: Nazmul Hasan Robin
 * Author URI: https://profiles.wordpress.org/nhrrob/
 * Version: 0.1.0
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Text Domain: nhrrob-post-insights
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package NHRRob\PostInsights
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * The main plugin class
 */
final class NHRRob_Post_Insights {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	const VERSION = '0.1.0';

	/**
	 * Class constructor
	 */
	private function __construct() {
		define( 'NHRROB_POST_INSIGHTS_VERSION', self::VERSION );
		define( 'NHRROB_POST_INSIGHTS_FILE', __FILE__ );
		define( 'NHRROB_POST_INSIGHTS_PATH', __DIR__ );

		register_activation_hook( __FILE__, [ \NHRRob\PostInsights\Installer::class, 'install' ] );
		add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
	}

	/**
	 * Initialize a singleton instance
	 *
	 * @return self
	 */
	public static function init(): self {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	public function init_plugin(): void {
		( new \NHRRob\PostInsights\Installer() )->init();
		( new \NHRRob\PostInsights\Rest() )->init();

		if ( is_admin() ) {
			( new \NHRRob\PostInsights\Admin() )->init();
		} else {
			( new \NHRRob\PostInsights\Tracker() )->init();
		}
	}
}

/**
 * Initializes the main plugin
 *
 * @return NHRRob_Post_Insights
 */
function nhrrob_post_insights(): NHRRob_Post_Insights {
	return NHRRob_Post_Insights::init();
}

// Call the plugin.
nhrrob_post_insights();
