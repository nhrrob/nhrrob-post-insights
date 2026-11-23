<?php
/**
 * The filters of the dashboard: post type, a custom date range and a title
 * search. Each is sanitized here, and an unknown value is ignored, not an error.
 *
 * @package NHRRob\PostInsights
 */

namespace NHRRob\PostInsights;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filters Class
 */
class Filters {

	/**
	 * The longest custom range, in days: about five years.
	 */
	const MAX_DAYS = 1830;

	/**
	 * The REST arguments of the filters. `search` is for the posts table only.
	 *
	 * @param bool $search Whether to include the title search.
	 * @return array
	 */
	public static function args( $search = false ) {
		$args = [
			'type' => [
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => [ self::class, 'type' ],
			],
			'from' => [
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => [ self::class, 'date' ],
			],
			'to'   => [
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => [ self::class, 'date' ],
			],
		];

		if ( $search ) {
			$args['search'] = [
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => [ self::class, 'search' ],
			];
		}

		return $args;
	}

	/**
	 * A post type that is counted, or empty for every type.
	 *
	 * @param mixed $value Asked type.
	 * @return string
	 */
	public static function type( $value ) {
		$value = is_string( $value ) ? sanitize_key( $value ) : '';

		return '' !== $value && in_array( $value, self::types(), true ) ? $value : '';
	}

	/**
	 * The post types the dashboard can be narrowed to: the public types a
	 * visitor can open, without attachments.
	 *
	 * @return string[]
	 */
	public static function types() {
		return array_values( array_diff( array_filter( get_post_types( [ 'public' => true ] ), 'is_post_type_viewable' ), [ 'attachment' ] ) );
	}

	/**
	 * A real date as Y-m-d, or empty.
	 *
	 * @param mixed $value Asked date.
	 * @return string
	 */
	public static function date( $value ) {
		if ( ! is_string( $value ) || ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $parts ) ) {
			return '';
		}

		return checkdate( (int) $parts[2], (int) $parts[3], (int) $parts[1] ) ? $value : '';
	}

	/**
	 * Words to look for in the titles, at most 100 characters.
	 *
	 * @param mixed $value Asked words.
	 * @return string
	 */
	public static function search( $value ) {
		return is_string( $value ) ? mb_substr( trim( sanitize_text_field( $value ) ), 0, 100 ) : '';
	}

	/**
	 * The first and last day shown. A custom range wins when both its days are
	 * given and in order; it ends today at the latest and spans at most
	 * MAX_DAYS. Otherwise the last days up to today, as `Store::from()`.
	 *
	 * @param int    $days  Number of days, or 0 for all time.
	 * @param string $from  First day of a custom range, Y-m-d, or empty.
	 * @param string $to    Last day of a custom range, Y-m-d, or empty.
	 * @param string $today Today, Y-m-d.
	 * @return string[] First and last day, Y-m-d.
	 */
	public static function range( $days, $from, $to, $today ) {
		$from = self::date( $from );
		$to   = self::date( $to );

		if ( ! self::is_custom( $from, $to, $today ) ) {
			return [ Store::from( $days ), $today ];
		}

		$to    = min( $to, $today );
		$first = gmdate( 'Y-m-d', strtotime( $to . ' -' . ( self::MAX_DAYS - 1 ) . ' days' ) );

		return [ max( $from, $first ), $to ];
	}

	/**
	 * Whether two days make a custom range: both real dates, in order, the
	 * first not after today.
	 *
	 * @param string $from  First day, Y-m-d, or empty.
	 * @param string $to    Last day, Y-m-d, or empty.
	 * @param string $today Today, Y-m-d.
	 * @return bool
	 */
	public static function is_custom( $from, $to, $today ) {
		$from = self::date( $from );
		$to   = self::date( $to );

		return '' !== $from && '' !== $to && $from <= $to && $from <= $today;
	}

	/**
	 * The filters of a request, sanitized, with the days they resolve to.
	 *
	 * @param int   $days    Number of days, or 0 for all time.
	 * @param array $filters Any of type, from, to and search.
	 * @return array from, to, type, search, and custom: whether the days are a custom range.
	 */
	public static function resolve( $days, $filters ) {
		$filters = (array) $filters;
		$from    = isset( $filters['from'] ) ? $filters['from'] : '';
		$to      = isset( $filters['to'] ) ? $filters['to'] : '';
		$today   = current_time( 'Y-m-d' );
		$range   = self::range( $days, $from, $to, $today );

		return [
			'from'   => $range[0],
			'to'     => $range[1],
			'custom' => self::is_custom( $from, $to, $today ),
			'type'   => self::type( isset( $filters['type'] ) ? $filters['type'] : '' ),
			'search' => self::search( isset( $filters['search'] ) ? $filters['search'] : '' ),
		];
	}

	/**
	 * The prepared WHERE of a read from the plugin's table: the days and the post.
	 *
	 * @param array $filters Resolved filters: from, to and type.
	 * @param int   $post    Post ID, or 0 for every post.
	 * @return string
	 */
	public static function where( $filters, $post = 0 ) {
		global $wpdb;

		$where = $wpdb->prepare( 'day BETWEEN %s AND %s', $filters['from'], $filters['to'] );

		if ( $post ) {
			$where .= $wpdb->prepare( ' AND post_id = %d', $post );
		}

		if ( '' !== $filters['type'] ) {
			$where .= $wpdb->prepare( " AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = %s)", $filters['type'] ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- core's table name.
		}

		return $where;
	}
}
