<?php
/**
 * Fired when the plugin is deleted: removes everything it stored.
 *
 * @package NHRRob\PostInsights
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Remove this plugin's tables and option from the current site.
 *
 * @return void
 */
function nhrrob_post_insights_uninstall_site() {
	global $wpdb;

	foreach ( [ 'nhrrob_post_insights_daily', 'nhrrob_post_insights_referrers' ] as $nhrrob_post_insights_table ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange -- removing the plugin's own tables.
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $wpdb->prefix . $nhrrob_post_insights_table ) );
	}

	delete_option( 'nhrrob_post_insights_db_version' );
}

if ( is_multisite() ) {
	foreach ( get_sites(
		[
			'fields' => 'ids',
			'number' => 0,
		]
	) as $nhrrob_post_insights_site_id ) {
		switch_to_blog( $nhrrob_post_insights_site_id );
		nhrrob_post_insights_uninstall_site();
		restore_current_blog();
	}
} else {
	nhrrob_post_insights_uninstall_site();
}
