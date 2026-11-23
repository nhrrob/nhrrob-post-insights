<?php
/**
 * Reads for other plugins and themes. They call these instead of querying
 * the plugin's table, so its layout can change in a release without
 * breaking them; each keeps its arguments and its answer from version to
 * version.
 *
 * @package NHRRob\PostInsights
 */

namespace NHRRob\PostInsights;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read Class
 */
class Read {

	/**
	 * A post's views and seconds, all time.
	 *
	 * @param int $post_id Post ID.
	 * @return int[] views and seconds.
	 */
	public static function totals( $post_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- one sum from the plugin's own table, indexed by post.
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT SUM(views) AS views, SUM(seconds) AS seconds FROM %i WHERE post_id = %d AND kind = %s', Installer::table(), $post_id, Installer::POST ) );

		return [
			'views'   => $row ? (int) $row->views : 0,
			'seconds' => $row ? (int) $row->seconds : 0,
		];
	}

	/**
	 * The most viewed posts of all time with at least some views, most first.
	 *
	 * @param int $min   Fewest views a post needs.
	 * @param int $limit Most posts.
	 * @return int[] Post IDs.
	 */
	public static function popular( $min, $limit ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- one sum from the plugin's own table; the caller caches it.
		$ids = $wpdb->get_col( $wpdb->prepare( 'SELECT post_id FROM %i WHERE kind = %s GROUP BY post_id HAVING SUM(views) >= %d ORDER BY SUM(views) DESC, post_id DESC LIMIT %d', Installer::table(), Installer::POST, $min, $limit ) );

		return array_map( 'intval', $ids );
	}

	/**
	 * Views from each referring site between two days, of every post.
	 *
	 * @param string $from First day, Y-m-d.
	 * @param string $to   Last day, Y-m-d.
	 * @return int[] Host => views.
	 */
	public static function hosts( $from, $to ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- one read from the plugin's own table, indexed by day.
		$rows  = $wpdb->get_results( $wpdb->prepare( 'SELECT item, SUM(views) AS views FROM %i WHERE kind = %s AND day BETWEEN %s AND %s GROUP BY item', Installer::table(), Installer::HOST, $from, $to ) );
		$hosts = [];

		foreach ( (array) $rows as $row ) {
			$hosts[ (string) $row->item ] = (int) $row->views;
		}

		return $hosts;
	}

	/**
	 * The views from referring sites of some posts, one row per post, day and
	 * site, all time.
	 *
	 * @param int[] $post_ids Post IDs.
	 * @return array[] Each with post_id, day (Y-m-d), host and views.
	 */
	public static function referrals( $post_ids ) {
		global $wpdb;

		$post_ids = array_values( array_filter( array_map( 'intval', (array) $post_ids ) ) );

		if ( ! $post_ids ) {
			return [];
		}

		$in = implode( ', ', array_fill( 0, count( $post_ids ), '%d' ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- the plugin's own table; $in holds one %d per post.
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, day, item, views FROM %i WHERE kind = %s AND post_id IN ($in)", array_merge( [ Installer::table(), Installer::HOST ], $post_ids ) ) );
		$out  = [];

		foreach ( (array) $rows as $row ) {
			$out[] = [
				'post_id' => (int) $row->post_id,
				'day'     => (string) $row->day,
				'host'    => (string) $row->item,
				'views'   => (int) $row->views,
			];
		}

		return $out;
	}
}
