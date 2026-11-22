/**
 * Views as bars, with a scale, gridlines and the exact number of a bar on
 * hover or focus. Up to 90 days a bar is a day; longer, a week, and past
 * two years a month, so the bars stay wide enough to read.
 */
import { useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';

import { count, dayLabel } from '../format';

const P = 'nhrrob-post-insights-';
const STEPS = [
	1, 2, 5, 10, 20, 25, 50, 100, 200, 250, 500, 1000, 2000, 2500, 5000,
];

/**
 * The days grouped into the bars of the chart.
 *
 * @param {Array} days Days, each with day (Y-m-d) and views.
 * @return {Object} unit (day, week or month) and bars, each with day (the first), label and views.
 */
export function buckets( days ) {
	let unit = 'day';

	if ( days.length > 730 ) {
		unit = 'month';
	} else if ( days.length > 90 ) {
		unit = 'week';
	}

	const bars = [];

	days.forEach( ( d, index ) => {
		const last = bars[ bars.length - 1 ];
		const start =
			'day' === unit ||
			( 'week' === unit && index % 7 === 0 ) ||
			( 'month' === unit &&
				( ! last || last.day.slice( 0, 7 ) !== d.day.slice( 0, 7 ) ) );

		if ( start ) {
			bars.push( { day: d.day, views: 0 } );
		}
		bars[ bars.length - 1 ].views += d.views;
	} );

	bars.forEach( ( bar ) => {
		if ( 'month' === unit ) {
			bar.label = dayLabel( 'F Y', bar.day );
		} else if ( 'week' === unit ) {
			bar.label = sprintf(
				/* translators: %s: the first day of a week. */
				__( 'Week of %s', 'nhrrob-post-insights' ),
				dayLabel( 'M j', bar.day )
			);
		} else {
			bar.label = dayLabel( 'D, M j', bar.day );
		}
	} );

	return { unit, bars };
}

/**
 * @param {Object} props       Props.
 * @param {Array}  props.bars  Bars from buckets().
 * @param {string} props.unit  day, week or month, from buckets().
 * @param {string} props.label What the bars count, for screen readers.
 * @return {Object} Chart.
 */
export default function DayChart( { bars, unit, label } ) {
	const [ active, setActive ] = useState( null );
	const max = Math.max( 1, ...bars.map( ( b ) => b.views ) );
	const step = STEPS.find( ( s ) => max / s <= 4 ) || 10000;
	const top = Math.ceil( max / step ) * step;
	const ticks = [];

	for ( let tick = 0; tick <= top; tick += step ) {
		ticks.push( tick );
	}

	const n = bars.length;
	const marks = [
		...new Set(
			n > 8
				? [ 0, 1, 2, 3, 4 ].map( ( k ) =>
						Math.round( ( k * ( n - 1 ) ) / 4 )
				  )
				: [ 0, Math.round( ( n - 1 ) / 2 ), n - 1 ]
		),
	];
	const shown = null === active ? null : bars[ active ];
	const centre = ( index ) => ( ( index + 0.5 ) / n ) * 100;
	let gap = 10;

	if ( n > 60 ) {
		gap = 1;
	} else if ( n > 20 ) {
		gap = 3;
	}

	return (
		<div className={ P + 'chart' }>
			<div className={ P + 'chart__y' } aria-hidden="true">
				{ ticks.map( ( tick ) => (
					<span
						key={ tick }
						style={ { bottom: ( tick / top ) * 100 + '%' } }
					>
						{ count( tick ) }
					</span>
				) ) }
			</div>
			<div className={ P + 'chart__area' }>
				<div className={ P + 'chart__plot' }>
					{ ticks.map( ( tick ) => (
						<span
							key={ tick }
							aria-hidden="true"
							className={
								P + 'chart__line' + ( tick ? '' : ' is-base' )
							}
							style={ { bottom: ( tick / top ) * 100 + '%' } }
						/>
					) ) }
					<ol
						className={ P + 'chart__bars' }
						style={ { gap } }
						aria-label={ label }
						onMouseLeave={ () => setActive( null ) }
					>
						{ bars.map( ( bar, index ) => (
							<li key={ bar.day } className={ P + 'chart__slot' }>
								<button
									type="button"
									className={
										P +
										'chart__hit' +
										( index === active ? ' is-active' : '' )
									}
									aria-label={ sprintf(
										/* translators: 1: a day, week or month, 2: number of views. */
										_n(
											'%1$s: %2$s view',
											'%1$s: %2$s views',
											bar.views,
											'nhrrob-post-insights'
										),
										bar.label,
										count( bar.views )
									) }
									onMouseEnter={ () => setActive( index ) }
									onFocus={ () => setActive( index ) }
									onBlur={ () => setActive( null ) }
								>
									<span
										className={ P + 'chart__bar' }
										style={ {
											height:
												( bar.views / top ) * 100 + '%',
										} }
									/>
								</button>
							</li>
						) ) }
					</ol>
					{ shown && (
						<div
							className={ P + 'chart__tip' }
							aria-hidden="true"
							style={ {
								left:
									Math.min(
										88,
										Math.max( 12, centre( active ) )
									) + '%',
							} }
						>
							<strong>
								{ sprintf(
									/* translators: %s: number of views. */
									_n(
										'%s view',
										'%s views',
										shown.views,
										'nhrrob-post-insights'
									),
									count( shown.views )
								) }
							</strong>
							<span>{ shown.label }</span>
						</div>
					) }
				</div>
				<div className={ P + 'chart__x' } aria-hidden="true">
					{ marks.map( ( index, k ) => {
						let edge = '';

						if ( 0 === k ) {
							edge = 'is-first';
						} else if ( marks.length - 1 === k ) {
							edge = 'is-last';
						}

						return (
							<span
								key={ index }
								className={ edge }
								style={ { left: centre( index ) + '%' } }
							>
								{ dayLabel(
									'month' === unit ? 'M Y' : 'M j',
									bars[ index ].day
								) }
							</span>
						);
					} ) }
				</div>
			</div>
		</div>
	);
}
