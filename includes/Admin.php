<?php
/**
 * The dashboard screen: Tools → Post Insights.
 *
 * @package NHRRob\PostInsights
 */

namespace NHRRob\PostInsights;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Class
 *
 * Prints the mount point of the app in admin/build.
 */
class Admin {

	const SLUG = 'nhrrob-post-insights';

	/**
	 * Hook suffix of the screen.
	 *
	 * @var string|false
	 */
	private $hook = false;

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', [ $this, 'menu' ] );
		add_action( 'admin_menu', [ $this, 'redirect_old_address' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( NHRROB_POST_INSIGHTS_FILE ), [ $this, 'action_links' ] );
	}

	/**
	 * Add the screen under Tools.
	 *
	 * @return void
	 */
	public function menu() {
		/** This filter is documented in includes/Rest.php */
		$capability = (string) apply_filters( 'nhrrob_post_insights_capability', 'manage_options' );
		$this->hook = add_management_page( __( 'Post Insights', 'nhrrob-post-insights' ), __( 'Post Insights', 'nhrrob-post-insights' ), $capability, self::SLUG, [ $this, 'render' ] );
	}

	/**
	 * Send the screen's old address under Dashboard to Tools, so a bookmark
	 * still works. Runs on admin_menu, before core refuses the unknown page.
	 *
	 * @return void
	 */
	public function redirect_old_address() {
		global $pagenow;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- only compared, never stored.
		if ( 'index.php' !== $pagenow || ! isset( $_GET['page'] ) || self::SLUG !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'tools.php?page=' . self::SLUG ) );
		exit;
	}

	/**
	 * A "View insights" link on the plugin's row of the Plugins screen, for
	 * whoever may see the numbers.
	 *
	 * @param array $links Action links.
	 * @return array
	 */
	public function action_links( $links ) {
		if ( ! Rest::can_view() ) {
			return $links;
		}

		array_unshift( $links, sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'tools.php?page=' . self::SLUG ) ), esc_html__( 'View insights', 'nhrrob-post-insights' ) ) );

		return $links;
	}

	/**
	 * Print the mount point.
	 *
	 * @return void
	 */
	public function render() {
		// The heading and wp-header-end marker make core move admin notices
		// inside .wrap; the app draws its own title bar.
		?>
		<div class="wrap nhrrob-post-insights-wrap">
			<h1 class="screen-reader-text"><?php esc_html_e( 'Post Insights', 'nhrrob-post-insights' ); ?></h1>
			<hr class="wp-header-end">
			<div id="nhrrob-post-insights-app"></div>
		</div>
		<?php
	}

	/**
	 * The post types the app can be narrowed to, with their names, for the
	 * type filter.
	 *
	 * @return array Rows with name and label.
	 */
	private static function types() {
		$types = [];

		foreach ( Filters::types() as $name ) {
			$object  = get_post_type_object( $name );
			$types[] = [
				'name'  => $name,
				'label' => $object ? (string) $object->labels->name : $name,
			];
		}

		return $types;
	}

	/**
	 * Enqueue the app on its own screen only.
	 *
	 * @param string $hook Hook suffix of the current screen.
	 * @return void
	 */
	public function enqueue( $hook ) {
		$dir = NHRROB_POST_INSIGHTS_PATH . '/admin/build';

		if ( ! $this->hook || $hook !== $this->hook || ! file_exists( $dir . '/index.asset.php' ) ) {
			return; // Another screen, or not built yet: run `npm run build`.
		}

		$asset  = require $dir . '/index.asset.php';
		$url    = plugins_url( 'admin/build', NHRROB_POST_INSIGHTS_FILE );
		$handle = 'nhrrob-post-insights-index';

		wp_enqueue_script( $handle, $url . '/index.js', $asset['dependencies'], $asset['version'], true );
		wp_set_script_translations( $handle, 'nhrrob-post-insights' );
		wp_localize_script(
			$handle,
			'nhrrobPostInsightsApp',
			[
				'restRoot'   => esc_url_raw( rest_url() ),
				'nonce'      => wp_create_nonce( 'wp_rest' ),
				'pluginName' => __( 'Post Insights', 'nhrrob-post-insights' ),
				'version'    => NHRROB_POST_INSIGHTS_VERSION,
				'extra'      => Columns::get(),
				'types'      => self::types(),
				'today'      => current_time( 'Y-m-d' ),
			]
		);

		if ( file_exists( $dir . '/style-index.css' ) ) {
			// Own version: the asset hash covers the script only.
			wp_enqueue_style( $handle, $url . '/style-index.css', [], (string) filemtime( $dir . '/style-index.css' ) );
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}
	}
}
