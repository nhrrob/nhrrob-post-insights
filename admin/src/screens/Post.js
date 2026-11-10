/**
 * One post: its totals, views per day and the sites its readers came from,
 * for the chosen days.
 */
import { __ } from '@wordpress/i18n';

import DayChart from '../components/DayChart';
import { Button, External, Panel, ScreenHeader } from '../components/ui';
import { average, count } from '../format';
import { Tile } from './Overview';
import { ReferrerPanel } from './Referrers';

const P = 'nhrrob-post-insights-';

export default function Post( { data, range, post, onClose } ) {
	return (
		<>
			<Button small variant="ghost" onClick={ onClose }>
				{ __( '← All posts', 'nhrrob-post-insights' ) }
			</Button>
			<ScreenHeader
				title={ post.title }
				lede={ <External href={ post.link }>{ post.link }</External> }
			/>
			{ range }
			<div className={ P + 'tiles' }>
				<Tile
					label={ __( 'Views', 'nhrrob-post-insights' ) }
					value={ count( data.views ) }
				/>
				<Tile
					label={ __( 'Average time', 'nhrrob-post-insights' ) }
					value={ average( data.seconds, data.views ) }
				/>
			</div>
			<Panel
				title={ __( 'Views per day', 'nhrrob-post-insights' ) }
				icon="overview"
			>
				<DayChart days={ data.days } />
			</Panel>
			<ReferrerPanel
				referrers={ data.referrers }
				direct={ data.direct || 0 }
			/>
		</>
	);
}
