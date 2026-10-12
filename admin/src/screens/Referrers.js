/**
 * Referrers: the sites readers came from.
 */
import { __ } from '@wordpress/i18n';

import { Panel, ScreenHeader } from '../components/ui';
import { count } from '../format';

const P = 'nhrrob-post-insights-';

export default function Referrers( { data, range } ) {
	return (
		<>
			<ScreenHeader
				title={ __( 'Referrers', 'nhrrob-post-insights' ) }
				lede={ __(
					'The sites a reader followed a link from. Only the name of the site is kept, never the page. A view with no referrer, such as a typed address or a link in an app, is not listed.',
					'nhrrob-post-insights'
				) }
			/>
			{ range }
			<Panel
				title={ __( 'Sites', 'nhrrob-post-insights' ) }
				icon="referrers"
				flush
			>
				{ ! data.referrers.length ? (
					<div className={ P + 'empty' }>
						<p>
							{ __(
								'No reader came from another site in these days.',
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
										{ __(
											'Views',
											'nhrrob-post-insights'
										) }
									</th>
								</tr>
							</thead>
							<tbody>
								{ data.referrers.map( ( row ) => (
									<tr key={ row.host }>
										<td className="is-mono">
											{ row.host }
										</td>
										<td className="is-number">
											{ count( row.views ) }
										</td>
									</tr>
								) ) }
							</tbody>
						</table>
					</div>
				) }
			</Panel>
		</>
	);
}
