<?php
/**
 * The plugin's two tables: created on activation, and again when their layout changes.
 *
 * @package NHRRob\PostInsights
 */

namespace NHRRob\PostInsights;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Installer Class
 */
class Installer {

	const OPTION     = 'nhrrob_post_insights_db_version';
	const DB_VERSION = '2';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'maybe_install' ] );
	}

	/**
	 * Name of the table with one row per post per day.
	 *
	 * @return string
	 */
	public static function daily_table() {
		global $wpdb;

		return $wpdb->prefix . 'nhrrob_post_insights_daily';
	}

	/**
	 * Name of the table with one row per post, day and referring site.
	 *
	 * @return string
	 */
	public static function referrers_table() {
		global $wpdb;

		return $wpdb->prefix . 'nhrrob_post_insights_referrers';
	}

	/**
	 * Create the tables when they are missing or older than this version.
	 * A plugin installed by Composer or copied in is never "activated" again
	 * on an update, so this also runs on init.
	 *
	 * @return void
	 */
	public function maybe_install() {
		if ( self::DB_VERSION !== get_option( self::OPTION ) ) {
			self::install();
		}
	}

	/**
	 * Create or update the tables.
	 *
	 * @return void
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset   = $wpdb->get_charset_collate();
		$daily     = self::daily_table();
		$referrers = self::referrers_table();

		dbDelta(
			"CREATE TABLE {$daily} (
				day date NOT NULL,
				post_id bigint(20) unsigned NOT NULL,
				views int(10) unsigned NOT NULL DEFAULT 0,
				seconds int(10) unsigned NOT NULL DEFAULT 0,
				PRIMARY KEY  (day,post_id),
				KEY post_id (post_id)
			) {$charset};"
		);

		dbDelta(
			"CREATE TABLE {$referrers} (
				day date NOT NULL,
				post_id bigint(20) unsigned NOT NULL,
				host varchar(191) NOT NULL,
				views int(10) unsigned NOT NULL DEFAULT 0,
				PRIMARY KEY  (day,post_id,host)
			) {$charset};"
		);

		// Autoloaded: it is read on every request.
		update_option( self::OPTION, self::DB_VERSION, true );
	}
}
