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
	 * The first day of a range. 0 days is all time: it starts on the first day
	 * with a view, or today when there is none.
	 *
	 * @param int $days Number of days, today included, at most 365, or 0.
	 * @return string Date as Y-m-d.
	 */
	public static function from( $days ) {
		global $wpdb;

		$days  = (int) $days;
		$today = current_time( 'Y-m-d' );

		if ( 0 === $days ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- the plugin's own table, indexed by day.
			$first = (string) $wpdb->get_var( $wpdb->prepare( 'SELECT MIN(day) FROM %i', Installer::daily_table() ) );

			return '' !== $first && $first < $today ? $first : $today;
		}

		$days = max( 1, min( 365, $days ) );

		return gmdate( 'Y-m-d', strtotime( $today . ' -' . ( $days - 1 ) . ' days' ) );
	}

	/**
	 * Everything the dashboard shows for the last days, today included.
	 *
	 * @param int $days Number of days, or 0 for all time.
	 * @return array
	 */
	public static function stats( $days ) {
		global $wpdb;

		$asked = (int) $days;
		$from  = self::from( $days );
		$days  = (int) round( ( strtotime( current_time( 'Y-m-d' ) ) - strtotime( $from ) ) / DAY_IN_SECONDS ) + 1;

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
		 * @param int   $days  Number of days asked for, 0 for all time.
		 */
		return (array) apply_filters( 'nhrrob_post_insights_stats', $stats, $asked );
	}

	/**
	 * Columns of the posts table it can be sorted by, and how.
	 */
	const ORDERBY = [
		'title'   => 'p.post_title',
		'date'    => 'p.post_date',
		'views'   => 'views',
		// The sums, not the aliases: inside an expression MySQL reads `views` and
		// `seconds` as the columns of one row of the daily table.
		'average' => 'CASE WHEN SUM(d.views) > 0 THEN SUM(d.seconds) / SUM(d.views) ELSE 0 END',
	];

	const PER_PAGE = 20;

	/**
	 * One page of the table of every published, public post, with its views
	 * and seconds in the range. A post without a view is there with 0.
	 *
	 * @param int    $days    Number of days, or 0 for all time.
	 * @param string $orderby A key of ORDERBY.
	 * @param string $order   asc or desc.
	 * @param int    $page    Page, from 1.
	 * @return array Rows, total and pages.
	 */
	public static function posts( $days, $orderby = 'views', $order = 'desc', $page = 1 ) {
		global $wpdb;

		$types = array_values( array_filter( get_post_types(), 'is_post_type_viewable' ) );
		$sort  = isset( self::ORDERBY[ $orderby ] ) ? self::ORDERBY[ $orderby ] : self::ORDERBY['views'];
		$dir   = 'asc' === $order ? 'ASC' : 'DESC';
		$page  = max( 1, (int) $page );
		$in    = implode( ', ', array_fill( 0, count( $types ), '%s' ) );
		$where = $wpdb->prepare( "p.post_status = 'publish' AND p.post_password = '' AND p.post_type IN ($in)", $types ); // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- one %s per post type.

		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $where is prepared above; $sort and $dir come from fixed lists.
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} p WHERE $where" );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID AS id, p.post_date AS date, COALESCE(SUM(d.views), 0) AS views, COALESCE(SUM(d.seconds), 0) AS seconds
				FROM {$wpdb->posts} p LEFT JOIN %i d ON d.post_id = p.ID AND d.day >= %s
				WHERE $where GROUP BY p.ID ORDER BY $sort $dir, p.ID DESC LIMIT %d OFFSET %d",
				Installer::daily_table(),
				self::from( $days ),
				self::PER_PAGE,
				( $page - 1 ) * self::PER_PAGE
			)
		);
		// phpcs:enable

		$posts = [];

		foreach ( $rows as $row ) {
			$id    = (int) $row->id;
			$title = get_the_title( $id );

			$posts[] = [
				'id'      => $id,
				'title'   => '' !== $title ? html_entity_decode( $title, ENT_QUOTES, 'UTF-8' ) : sprintf( '#%d', $id ),
				'link'    => (string) get_permalink( $id ),
				'edit'    => current_user_can( 'edit_post', $id ) ? (string) get_edit_post_link( $id, 'raw' ) : '',
				'date'    => (string) get_the_date( 'Y-m-d', $id ),
				'views'   => (int) $row->views,
				'seconds' => (int) $row->seconds,
				'extra'   => [],
			];
		}

		/**
		 * The rows of one page of the posts table. An add-on fills a declared
		 * column in each row's `extra`, by the column's key.
		 *
		 * @param array $posts Rows: id, title, link, edit, date, views, seconds, extra.
		 * @param int   $days  Number of days, or 0 for all time.
		 */
		$posts = (array) apply_filters( 'nhrrob_post_insights_posts', $posts, (int) $days );

		return [
			'posts' => array_values( $posts ),
			'total' => $total,
			'pages' => max( 1, (int) ceil( $total / self::PER_PAGE ) ),
		];
	}
}
