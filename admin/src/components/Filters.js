/**
 * The one row of filters above the numbers, the same on every screen: the
 * days, a custom range and the post type.
 */
import { __, sprintf } from '@wordpress/i18n';

import { Button } from './ui';

const P = 'nhrrob-post-insights-';

// 0 is all time.
export const RANGES = [ 7, 30, 90, 0 ];

/**
 * A day some days before or after another, both as Y-m-d.
 *
 * @param {string} day    Y-m-d.
 * @param {number} offset Days to add, negative to go back.
 * @return {string} Y-m-d.
 */
function shift( day, offset ) {
	const [ y, m, d ] = day.split( '-' ).map( Number );

	return new Date( Date.UTC( y, m - 1, d + offset ) )
		.toISOString()
		.slice( 0, 10 );
}

/**
 * @param {Object}   props          Props.
 * @param {Object}   props.filters  days, from, to and type.
 * @param {Array}    props.types    Post types, each with name and label.
 * @param {string}   props.today    The site's today, Y-m-d.
 * @param {Function} props.onChange Called with the filters that change.
 * @return {Object} Filter row.
 */
export default function Filters( { filters, types, today, onChange } ) {
	const { days, from, to, type } = filters;
	const custom = '' !== from;

	// A first day after the last moves the last day with it, and back.
	const setFrom = ( value ) =>
		value && onChange( { from: value, to: value > to ? value : to } );
	const setTo = ( value ) =>
		value && onChange( { to: value, from: value < from ? value : from } );

	return (
		<div
			className={ P + 'range' }
			role="group"
			aria-label={ __( 'Filters', 'nhrrob-post-insights' ) }
		>
			{ RANGES.map( ( option ) => (
				<Button
					key={ option }
					small
					variant={ ! custom && option === days ? 'soft' : 'ghost' }
					aria-pressed={ ! custom && option === days }
					onClick={ () =>
						onChange( { days: option, from: '', to: '' } )
					}
				>
					{ option
						? sprintf(
								/* translators: %d: number of days. */
								__( 'Last %d days', 'nhrrob-post-insights' ),
								option
						  )
						: __( 'All time', 'nhrrob-post-insights' ) }
				</Button>
			) ) }
			<Button
				small
				variant={ custom ? 'soft' : 'ghost' }
				aria-pressed={ custom }
				onClick={ () =>
					custom ||
					onChange( { from: shift( today, -29 ), to: today } )
				}
			>
				{ __( 'Custom range', 'nhrrob-post-insights' ) }
			</Button>
			{ custom && (
				<span className={ P + 'range__dates' }>
					<input
						type="date"
						className={ P + 'input ' + P + 'input--inline' }
						aria-label={ __( 'First day', 'nhrrob-post-insights' ) }
						value={ from }
						max={ today }
						onChange={ ( e ) => setFrom( e.target.value ) }
					/>
					<span aria-hidden="true">–</span>
					<input
						type="date"
						className={ P + 'input ' + P + 'input--inline' }
						aria-label={ __( 'Last day', 'nhrrob-post-insights' ) }
						value={ to }
						max={ today }
						onChange={ ( e ) => setTo( e.target.value ) }
					/>
				</span>
			) }
			{ types.length > 1 && (
				<select
					className={ P + 'input ' + P + 'input--inline' }
					aria-label={ __( 'Post type', 'nhrrob-post-insights' ) }
					value={ type }
					onChange={ ( e ) => onChange( { type: e.target.value } ) }
				>
					<option value="">
						{ __( 'All types', 'nhrrob-post-insights' ) }
					</option>
					{ types.map( ( option ) => (
						<option key={ option.name } value={ option.name }>
							{ option.label }
						</option>
					) ) }
				</select>
			) }
		</div>
	);
}
