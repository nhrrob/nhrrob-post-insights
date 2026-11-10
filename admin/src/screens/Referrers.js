/**
 * Referrers: the sites readers came from.
 */
import { __ } from '@wordpress/i18n';

import { Panel, ScreenHeader } from '../components/ui';
import { count } from '../format';

const P = 'nhrrob-post-insights-';

/**
 * The sites readers came from, and the views with no referrer, as one panel.
 *
 * @param {Object} props           Props.
 * @param {Array}  props.referrers Rows with host and views.
 * @param {number} props.direct    Views with no referrer.
 * @return {Object} Panel element.
 */
export function ReferrerPanel( { referrers, direct } ) {
	return (
		<Panel
			title={ __( 'Sites', 'nhrrob-post-insights' ) }
			icon="referrers"
			flush
		>
			{ ! referrers.length && ! direct ? (
				<div className={ P + 'empty' }>
					<p>
						{ __(
							'No views in these days.',
							'nhrrob-post-insights'
						) }
					</p>
				</div>
			) : (
				<div className={ P + 'scroll' }>
					<table className={ P + 'grid' }>
						<thead>
							<tr>
								<th scope="col">
									{ __( 'Site', 'nhrrob-post-insights' ) }
								</th>
								<th scope="col" className="is-number">
									{ __( 'Views', 'nhrrob-post-insights' ) }
								</th>
							</tr>
						</thead>
						<tbody>
							{ referrers.map( ( row ) => (
								<tr key={ row.host }>
									<td className="is-mono">{ row.host }</td>
									<td className="is-number">
										{ count( row.views ) }
									</td>
								</tr>
							) ) }
							{ direct > 0 && (
								<tr>
									<td>
										{ __(
											'No referrer',
											'nhrrob-post-insights'
										) }
									</td>
									<td className="is-number">
										{ count( direct ) }
									</td>
								</tr>
							) }
						</tbody>
					</table>
				</div>
			) }
		</Panel>
	);
}

export default function Referrers( { data, range } ) {
	return (
		<>
			<ScreenHeader
				title={ __( 'Referrers', 'nhrrob-post-insights' ) }
				lede={ __(
					'The sites a reader followed a link from. Only the name of the site is kept, never the page. "No referrer" is a typed address, a bookmark or a link in an app.',
					'nhrrob-post-insights'
				) }
			/>
			{ range }
			<ReferrerPanel
				referrers={ data.referrers }
				direct={ data.direct || 0 }
			/>
		</>
	);
}
