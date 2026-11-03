/**
 * Overview: the totals of the chosen days and the views of each day.
 */
import { __ } from '@wordpress/i18n';

import DayChart from '../components/DayChart';
import { Panel, ScreenHeader } from '../components/ui';
import { average, byKind, count } from '../format';

const P = 'nhrrob-post-insights-';

function Tile( { label, value } ) {
	return (
		<div className={ P + 'tile' }>
			<span className={ P + 'tile__label' }>{ label }</span>
			<span className={ P + 'tile__value' }>{ value }</span>
		</div>
	);
}

export default function Overview( { data, range, extra } ) {
	return (
		<>
			<ScreenHeader
				title={ __( 'Overview', 'nhrrob-post-insights' ) }
				lede={ __(
					'Views of your posts and pages by visitors. Your own views while logged in, and crawlers, are not counted.',
					'nhrrob-post-insights'
				) }
			/>
			{ range }
			<div className={ P + 'tiles' }>
				<Tile
					label={ __( 'Views', 'nhrrob-post-insights' ) }
					value={ count( data.views ) }
				/>
				<Tile
					label={ __(
						'Average time on a post',
						'nhrrob-post-insights'
					) }
					value={ average( data.seconds, data.views ) }
				/>
				<Tile
					label={ __( 'Posts read', 'nhrrob-post-insights' ) }
					value={ count( data.posts.length ) }
				/>
				{ ( extra.tiles || [] ).map( ( tile ) => (
					<Tile
						key={ tile.key }
						label={ tile.label }
						value={ byKind(
							tile.kind,
							( data.extra || {} )[ tile.key ]
						) }
					/>
				) ) }
			</div>
			<Panel
				title={ __( 'Views per day', 'nhrrob-post-insights' ) }
				icon="overview"
			>
				<DayChart days={ data.days } />
			</Panel>
		</>
	);
}
