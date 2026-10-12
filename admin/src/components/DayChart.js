/**
 * Views per day as bars. One series, so it has no legend; the panel's title
 * names it. Each bar can be hovered or focused for its exact number.
 */
import { useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';

import { count, dayLabel } from '../format';

const P = 'nhrrob-post-insights-';

export default function DayChart( { days } ) {
	const [ active, setActive ] = useState( null );
	const max = Math.max( 1, ...days.map( ( d ) => d.views ) );
	const shown = active === null ? null : days[ active ];

	return (
		<div className={ P + 'chart' }>
			<div className={ P + 'chart__top' }>
				<span className={ P + 'chart__max' }>{ count( max ) }</span>
				<span className={ P + 'chart__tip' } aria-live="polite">
					{ shown &&
						sprintf(
							/* translators: 1: date, 2: number of views. */
							_n(
								'%1$s: %2$s view',
								'%1$s: %2$s views',
								shown.views,
								'nhrrob-post-insights'
							),
							dayLabel( 'D, M j', shown.day ),
							count( shown.views )
						) }
				</span>
			</div>
			<ol
				className={ P + 'chart__bars' }
				aria-label={ __( 'Views per day', 'nhrrob-post-insights' ) }
				onMouseLeave={ () => setActive( null ) }
			>
				{ days.map( ( d, index ) => (
					<li key={ d.day } className={ P + 'chart__day' }>
						<button
							type="button"
							className={
								P +
								'chart__hit' +
								( index === active ? ' is-active' : '' )
							}
							aria-label={ sprintf(
								/* translators: 1: date, 2: number of views. */
								__(
									'%1$s: %2$s views',
									'nhrrob-post-insights'
								),
								dayLabel( 'M j', d.day ),
								count( d.views )
							) }
							onMouseEnter={ () => setActive( index ) }
							onFocus={ () => setActive( index ) }
							onBlur={ () => setActive( null ) }
						>
							<span
								className={ P + 'chart__bar' }
								style={ {
									height: ( d.views / max ) * 100 + '%',
								} }
							/>
						</button>
					</li>
				) ) }
			</ol>
			<div className={ P + 'chart__axis' }>
				<span>{ dayLabel( 'M j', days[ 0 ].day ) }</span>
				<span>{ dayLabel( 'M j', days[ days.length - 1 ].day ) }</span>
			</div>
		</div>
	);
}
