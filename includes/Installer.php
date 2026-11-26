<?php
/**
 * The plugin's one table: created on activation, and again when its layout
 * changes. Up to 0.5 the counts were in two tables, which are moved into it
 * once and dropped.
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
	const DB_VERSION = '3';

	/**
	 * A row's kind: a post's views and seconds of one day; item is empty.
	 */
	const POST = 'post';

	/**
	 * A row's kind: a post's views from one referring site; item is its host.
	 */
	const HOST = 'host';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'maybe_install' ] );
	}

	/**
	 * Name of the table: one row per day, post, kind and item. Another plugin
	 * may keep counters of its own kind in it.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;

		return $wpdb->prefix . 'nhrrob_post_insights';
	}

	/**
	 * Create the table when it is missing or older than this version. A
	 * plugin installed by Composer or copied in is never "activated" again on
	 * an update, so this also runs on init.
	 *
	 * @return void
	 */
	public function maybe_install() {
		if ( self::DB_VERSION !== get_option( self::OPTION ) ) {
			self::install();
		}
	}

	/**
	 * Create or update the table, and move in the rows of the old tables.
	 *
	 * @return void
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::table();
		$charset = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$table} (
				day date NOT NULL,
				post_id bigint(20) unsigned NOT NULL,
				kind varchar(16) NOT NULL,
				item varchar(191) NOT NULL DEFAULT '',
				views int(10) unsigned NOT NULL DEFAULT 0,
				seconds int(10) unsigned NOT NULL DEFAULT 0,
				PRIMARY KEY  (day,post_id,kind,item),
				KEY post_kind (post_id,kind)
			) {$charset};"
		);

		// The version is kept only once the table is there, so a failed create is tried again.
		if ( ! self::exists( $table ) ) {
			return;
		}

		self::migrate( $wpdb->prefix . 'nhrrob_post_insights_daily', "SELECT day, post_id, %s, '', views, seconds FROM %i", self::POST );
		self::migrate( $wpdb->prefix . 'nhrrob_post_insights_referrers', 'SELECT day, post_id, %s, host, views, 0 FROM %i', self::HOST );

		// Autoloaded: it is read on every request.
		update_option( self::OPTION, self::DB_VERSION, true );
	}

	/**
	 * Whether a table exists.
	 *
	 * @param string $table Table.
	 * @return bool
	 */
	private static function exists( $table ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- a check of the plugin's own table, while installing.
		return $table === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
	}

	/**
	 * Copy the rows of an old table into the table as one kind, then drop it.
	 *
	 * @param string $old    Old table.
	 * @param string $select SELECT of day, post_id, kind (%s), item, views and seconds from the old table (%i).
	 * @param string $kind   The rows' kind.
	 * @return void
	 */
	private static function migrate( $old, $select, $kind ) {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- moving the plugin's own rows once; $select is one of two fixed strings above.
		if ( ! self::exists( $old ) ) {
			return;
		}

		$copied = $wpdb->query( $wpdb->prepare( 'INSERT IGNORE INTO %i (day, post_id, kind, item, views, seconds) ' . $select, self::table(), $kind, $old ) );

		if ( false !== $copied ) {
			$wpdb->query( $wpdb->prepare( 'DROP TABLE %i', $old ) );
		}
		// phpcs:enable
	}
}
