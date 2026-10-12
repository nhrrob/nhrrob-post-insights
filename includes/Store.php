<?php
/**
 * Writes views, referrers and reading time to the tables, and reads the totals the dashboard shows.
 *
 * @package NHRRob\PostInsights
 */

namespace NHRRob\PostInsights;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store Class
 */
class Store {

	/**
	 * Count one view of a post today, and the site it came from.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $host    Referring host, or empty.
	 * @return void
	 */
	public static function add_view( $post_id, $host ) {
		global $wpdb;

		$day = current_time( 'Y-m-d' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- a counter in the plugin's own table.
		$wpdb->query( $wpdb->prepare( 'INSERT INTO %i (day, post_id, views, seconds) VALUES (%s, %d, 1, 0) ON DUPLICATE KEY UPDATE views = views + 1', Installer::daily_table(), $day, $post_id ) );

		if ( '' !== $host ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- a counter in the plugin's own table.
			$wpdb->query( $wpdb->prepare( 'INSERT INTO %i (day, post_id, host, views) VALUES (%s, %d, %s, 1) ON DUPLICATE KEY UPDATE views = views + 1', Installer::referrers_table(), $day, $post_id, $host ) );
		}

		/**
		 * Fires after a view of a post was counted.
		 *
		 * @param int    $post_id Post ID.
		 * @param string $host    Referring host, or empty.
		 */
		do_action( 'nhrrob_post_insights_view', (int) $post_id, $host );
	}

	/**
	 * Add reading time to a post's row of today.
	 *
	 * @param int $post_id Post ID.
	 * @param int $seconds Seconds.
	 * @return void
	 */
	public static function add_seconds( $post_id, $seconds ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- a counter in the plugin's own table.
		$wpdb->query( $wpdb->prepare( 'INSERT INTO %i (day, post_id, views, seconds) VALUES (%s, %d, 0, %d) ON DUPLICATE KEY UPDATE seconds = seconds + %d', Installer::daily_table(), current_time( 'Y-m-d' ), $post_id, $seconds, $seconds ) );
	}

	/**
	 * Everything the dashboard shows for the last days, today included.
	 *
	 * @param int $days Number of days.
	 * @return array
	 */
	public static function stats( $days ) {
		global $wpdb;

		$days  = max( 1, min( 365, (int) $days ) );
		$today = current_time( 'Y-m-d' );
		$from  = gmdate( 'Y-m-d', strtotime( $today . ' -' . ( $days - 1 ) . ' days' ) );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery -- read from the plugin's own tables for one dashboard request.
		$by_day    = $wpdb->get_results( $wpdb->prepare( 'SELECT day, SUM(views) AS views, SUM(seconds) AS seconds FROM %i WHERE day >= %s GROUP BY day', Installer::daily_table(), $from ), OBJECT_K );
		$posts     = $wpdb->get_results( $wpdb->prepare( 'SELECT post_id, SUM(views) AS views, SUM(seconds) AS seconds FROM %i WHERE day >= %s GROUP BY post_id ORDER BY views DESC, post_id DESC LIMIT 50', Installer::daily_table(), $from ) );
		$referrers = $wpdb->get_results( $wpdb->prepare( 'SELECT host, SUM(views) AS views FROM %i WHERE day >= %s GROUP BY host ORDER BY views DESC, host ASC LIMIT 50', Installer::referrers_table(), $from ) );
		// phpcs:enable

		$stats = [
			'days'      => [],
			'views'     => 0,
			'seconds'   => 0,
			'posts'     => [],
			'referrers' => [],
		];

		// A day without a view has no row, and still belongs in the chart.
		for ( $offset = 0; $offset < $days; $offset++ ) {
			$day     = gmdate( 'Y-m-d', strtotime( $from . ' +' . $offset . ' days' ) );
			$views   = isset( $by_day[ $day ] ) ? (int) $by_day[ $day ]->views : 0;
			$seconds = isset( $by_day[ $day ] ) ? (int) $by_day[ $day ]->seconds : 0;

			$stats['days'][]   = [
				'day'     => $day,
				'views'   => $views,
				'seconds' => $seconds,
			];
			$stats['views']   += $views;
			$stats['seconds'] += $seconds;
		}

		foreach ( $posts as $row ) {
			$title = get_the_title( (int) $row->post_id );

			$stats['posts'][] = [
				'id'      => (int) $row->post_id,
				'title'   => '' !== $title ? html_entity_decode( $title, ENT_QUOTES, 'UTF-8' ) : sprintf( '#%d', $row->post_id ),
				'link'    => (string) get_permalink( (int) $row->post_id ),
				'views'   => (int) $row->views,
				'seconds' => (int) $row->seconds,
			];
		}

		foreach ( $referrers as $row ) {
			$stats['referrers'][] = [
				'host'  => (string) $row->host,
				'views' => (int) $row->views,
			];
		}

		/**
		 * The numbers the dashboard shows.
		 *
		 * @param array $stats Days, totals, posts and referrers.
		 * @param int   $days  Number of days asked for.
		 */
		return (array) apply_filters( 'nhrrob_post_insights_stats', $stats, $days );
	}
}
