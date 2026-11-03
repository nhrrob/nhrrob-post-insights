<?php
/**
 * Columns on the Posts table and tiles on Overview that an add-on declares.
 *
 * @package NHRRob\PostInsights
 */

namespace NHRRob\PostInsights;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Columns Class
 *
 * An add-on declares them with the filter `nhrrob_post_insights_columns`, then
 * fills a column in each row's `extra` with `nhrrob_post_insights_posts` and a
 * tile in the numbers' `extra` with `nhrrob_post_insights_stats`. The app
 * draws them; the add-on needs no script of its own.
 */
class Columns {

	/**
	 * How a value can be written: a count, seconds as a duration, or plain text.
	 */
	const KINDS = [ 'number', 'time', 'text' ];

	/**
	 * The declared columns and tiles, each with a key, a label and a kind. A
	 * malformed entry is dropped.
	 *
	 * @return array Columns and tiles.
	 */
	public static function get() {
		/**
		 * Columns for the Posts table and tiles for Overview.
		 *
		 * @param array $declared [
		 *     @type array $columns List of [ 'key' => string, 'label' => string, 'kind' => 'number'|'time'|'text' ].
		 *     @type array $tiles   The same, for Overview.
		 * ]
		 */
		$declared = apply_filters(
			'nhrrob_post_insights_columns',
			[
				'columns' => [],
				'tiles'   => [],
			]
		);

		return [
			'columns' => self::clean( isset( $declared['columns'] ) ? $declared['columns'] : [] ),
			'tiles'   => self::clean( isset( $declared['tiles'] ) ? $declared['tiles'] : [] ),
		];
	}

	/**
	 * Keep the entries with a key, a label and a known kind, each key once.
	 *
	 * @param mixed $entries Entries.
	 * @return array
	 */
	private static function clean( $entries ) {
		$clean = [];

		foreach ( is_array( $entries ) ? $entries : [] as $entry ) {
			if ( ! is_array( $entry ) || ! isset( $entry['key'], $entry['label'] ) || ! is_string( $entry['label'] ) ) {
				continue;
			}

			$key  = sanitize_key( (string) $entry['key'] );
			$kind = isset( $entry['kind'] ) ? (string) $entry['kind'] : 'text';

			if ( '' === $key || '' === trim( $entry['label'] ) || ! in_array( $kind, self::KINDS, true ) || isset( $clean[ $key ] ) ) {
				continue;
			}

			$clean[ $key ] = [
				'key'   => $key,
				'label' => wp_strip_all_tags( $entry['label'] ),
				'kind'  => $kind,
			];
		}

		return array_values( $clean );
	}
}
