/**
 * How numbers and durations are written in the app.
 */
import { __, sprintf } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';

/**
 * @param {number} value A count.
 * @return {string} The count with thousands separators.
 */
export function count( value ) {
	return Number( value ).toLocaleString();
}

/**
 * A number of seconds as a duration.
 *
 * @param {number} seconds Seconds.
 * @return {string} Such as "45 s" or "2 min 05 s".
 */
export function duration( seconds ) {
	const each = Math.round( Number( seconds ) );

	if ( each < 60 ) {
		/* translators: %d: seconds. */
		return sprintf( __( '%d s', 'nhrrob-post-insights' ), each );
	}

	return sprintf(
		/* translators: 1: minutes, 2: seconds, two digits. */
		__( '%1$d min %2$s s', 'nhrrob-post-insights' ),
		Math.floor( each / 60 ),
		String( each % 60 ).padStart( 2, '0' )
	);
}

/**
 * Average time a reader stayed, from the seconds and views of the same rows.
 *
 * @param {number} seconds Seconds in total.
 * @param {number} views   Views in total.
 * @return {string} Such as "2 min 05 s", or a dash without views.
 */
export function average( seconds, views ) {
	return views ? duration( seconds / views ) : '–';
}

/**
 * A value an add-on gave, written by the kind it declared.
 *
 * @param {string} kind  number, time or text.
 * @param {*}      value The value; missing shows a dash.
 * @return {string} The value as text.
 */
export function byKind( kind, value ) {
	if ( value === undefined || value === null || value === '' ) {
		return '–';
	}

	if ( 'number' === kind ) {
		return count( value );
	}

	if ( 'time' === kind ) {
		return duration( value );
	}

	return String( value );
}

/**
 * A day as text. The day is a plain date from the server, so it is read and
 * written as UTC; any other time zone could move it to the day before.
 *
 * @param {string} format PHP date format.
 * @param {string} day    Date as YYYY-MM-DD.
 * @return {string} The formatted day.
 */
export function dayLabel( format, day ) {
	return dateI18n( format, day + 'T12:00:00Z', true );
}
