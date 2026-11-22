/**
 * Referrers: every source of views in one ranked list, with its kind,
 * share and change, narrowed by kind from the cards above it.
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { BarList, Change, Panel, ScreenHeader } from '../components/ui';
import { change, count, share } from '../format';
import { KINDS, sources } from '../sources';

const P = 'nhrrob-post-insights-';

/**
 * A source's name: the host, or "No referrer".
 *
 * @param {Object} props     Props.
 * @param {Object} props.row A row of sources().
 * @return {Object} Name.
 */
function Name( { row } ) {
	return row.host ? (
		<span className={ P + 'host' }>{ row.host }</span>
	) : (
		<span className={ P + 'direct' }>
			{ __( 'No referrer', 'nhrrob-post-insights' ) }
		</span>
	);
}

/**
 * The top sources as a bar list, for Overview and one post.
 *
 * @param {Object} props       Props.
 * @param {Object} props.data  The numbers of /stats.
 * @param {number} props.limit How many to show.
 * @param {Object} props.foot  The panel's footer, or nothing.
 * @param {string} props.title The panel's title.
 * @return {Object} Panel.
 */
export function SourcesPanel( { data, limit, foot, title } ) {
	const rows = sources( data ).slice( 0, limit );

	return (
		<Panel
			title={
				title || __( 'Where readers came from', 'nhrrob-post-insights' )
			}
			meta={ __( 'Share of views', 'nhrrob-post-insights' ) }
			foot={ foot }
			flush
		>
			{ rows.length ? (
				<BarList
					rows={ rows.map( ( row ) => ( {
						key: row.key,
						amount: row.views,
						label: <Name row={ row } />,
						aside: (
							<span className={ P + 'tag' }>
								{ KINDS[ row.kind ] }
							</span>
						),
						value: share( row.views, data.views ),
					} ) ) }
				/>
			) : (
				<p className={ P + 'empty' }>
					{ __( 'No views in these days.', 'nhrrob-post-insights' ) }
				</p>
			) }
		</Panel>
	);
}

export default function Referrers( { data, range } ) {
	const [ only, setOnly ] = useState( '' );
	const all = sources( data );
	const rows = only ? all.filter( ( row ) => row.kind === only ) : all;
	const max = Math.max( 1, ...rows.map( ( row ) => row.views ) );
	const groups = [
		[ '', __( 'All sources', 'nhrrob-post-insights' ) ],
		[ 'search', __( 'Search', 'nhrrob-post-insights' ) ],
		[ 'social', __( 'Social', 'nhrrob-post-insights' ) ],
		[ 'site', __( 'Other sites', 'nhrrob-post-insights' ) ],
		[ 'direct', __( 'No referrer', 'nhrrob-post-insights' ) ],
	];

	return (
		<>
			<ScreenHeader
				title={ __( 'Referrers', 'nhrrob-post-insights' ) }
				lede={ __(
					'Only the referring site’s name is kept, never the full address.',
					'nhrrob-post-insights'
				) }
			/>
			{ range }
			<div
				className={ P + 'groups' }
				role="group"
				aria-label={ __( 'Kind of source', 'nhrrob-post-insights' ) }
			>
				{ groups.map( ( [ id, label ] ) => {
					const views = all
						.filter( ( row ) => ! id || row.kind === id )
						.reduce( ( sum, row ) => sum + row.views, 0 );

					return (
						<button
							key={ id }
							type="button"
							className={ P + 'group' }
							aria-pressed={ only === id }
							onClick={ () => setOnly( id ) }
						>
							<span className={ P + 'kpi__label' }>
								{ label }
							</span>
							<span className={ P + 'group__value' }>
								{ share( views, data.views ) }
							</span>
							<span className={ P + 'kpi__note' }>
								{ count( views ) }{ ' ' }
								{ __( 'views', 'nhrrob-post-insights' ) }
							</span>
						</button>
					);
				} ) }
			</div>
			<Panel
				title={ groups.find( ( [ id ] ) => id === only )[ 1 ] }
				meta={ __(
					'Social networks and other sites in one ranked list',
					'nhrrob-post-insights'
				) }
				flush
			>
				{ ! rows.length ? (
					<p className={ P + 'empty' }>
						{ __(
							'No views in these days.',
							'nhrrob-post-insights'
						) }
					</p>
				) : (
					<div className={ P + 'scroll' }>
						<table className={ P + 'grid' }>
							<thead>
								<tr>
									<th scope="col" className="is-rank">
										#
									</th>
									<th scope="col">
										{ __(
											'Source',
											'nhrrob-post-insights'
										) }
									</th>
									<th scope="col">
										{ __( 'Kind', 'nhrrob-post-insights' ) }
									</th>
									<th scope="col" className="is-wide">
										{ __(
											'Share of views',
											'nhrrob-post-insights'
										) }
									</th>
									<th scope="col" className="is-number">
										{ __(
											'Views',
											'nhrrob-post-insights'
										) }
									</th>
									<th scope="col" className="is-number">
										{ __(
											'Change',
											'nhrrob-post-insights'
										) }
									</th>
								</tr>
							</thead>
							<tbody>
								{ rows.map( ( row, index ) => (
									<tr key={ row.key }>
										<td className="is-rank">
											{ index + 1 }
										</td>
										<td>
											<Name row={ row } />
										</td>
										<td>
											<span className={ P + 'tag' }>
												{ KINDS[ row.kind ] }
											</span>
										</td>
										<td>
											<span className={ P + 'meter' }>
												<span
													className={
														P + 'meter__track'
													}
													aria-hidden="true"
												>
													<span
														style={ {
															width:
																( row.views /
																	max ) *
																	100 +
																'%',
														} }
													/>
												</span>
												<strong>
													{ share(
														row.views,
														data.views
													) }
												</strong>
											</span>
										</td>
										<td className="is-number">
											{ count( row.views ) }
										</td>
										<td className="is-number">
											<Change
												dash
												change={ change(
													row.views,
													row.before
												) }
											/>
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
