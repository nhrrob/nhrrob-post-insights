/**
 * Overview: the headline numbers of the chosen days with their change, the
 * views over time, the most read posts and where readers came from, then
 * the numbers other plugins add.
 */
import { __, sprintf } from '@wordpress/i18n';

import DayChart, { buckets } from '../components/DayChart';
import { BarList, Kpi, Panel, ScreenHeader } from '../components/ui';
import { average, byKind, change, count } from '../format';
import { SourcesPanel } from './Referrers';

const P = 'nhrrob-post-insights-';

/**
 * The headline numbers of the whole site or of one post.
 *
 * @param {Object}  props         Props.
 * @param {Object}  props.data    The numbers of /stats.
 * @param {string}  props.against What they are compared with.
 * @param {boolean} props.site    Whether the numbers are the whole site's.
 * @return {Object} Cards.
 */
export function Kpis( { data, against, site } ) {
	const before = data.previous;
	const referred = data.views - data.direct;

	return (
		<div className={ P + 'kpis' }>
			<Kpi
				label={ __( 'Views', 'nhrrob-post-insights' ) }
				value={ count( data.views ) }
				change={ change( data.views, before && before.views ) }
				note={ against }
			/>
			<Kpi
				label={ __( 'Average time on a post', 'nhrrob-post-insights' ) }
				value={ average( data.seconds, data.views ) }
				change={
					before && before.views && data.views
						? change(
								data.seconds / data.views,
								before.seconds / before.views
						  )
						: null
				}
				note={ against }
			/>
			{ site && (
				<Kpi
					label={ __( 'Posts read', 'nhrrob-post-insights' ) }
					value={ count( data.read ) }
					change={ change( data.read, before && before.read ) }
					note={ against }
				/>
			) }
			<Kpi
				label={ __( 'From other sites', 'nhrrob-post-insights' ) }
				value={ count( referred ) }
				change={ change(
					referred,
					before && before.views - before.direct
				) }
				note={ against }
			/>
		</div>
	);
}

/**
 * The views over time, in a panel named by its bars.
 *
 * @param {Object} props      Props.
 * @param {Array}  props.days Days, each with day and views.
 * @return {Object} Panel.
 */
export function ViewsPanel( { days } ) {
	const { unit, bars } = buckets( days );
	const titles = {
		day: __( 'Views per day', 'nhrrob-post-insights' ),
		week: __( 'Views per week', 'nhrrob-post-insights' ),
		month: __( 'Views per month', 'nhrrob-post-insights' ),
	};
	const best = bars.reduce(
		( top, bar ) => ( bar.views > top.views ? bar : top ),
		bars[ 0 ]
	);

	return (
		<Panel
			title={ titles[ unit ] }
			meta={
				best && best.views
					? sprintf(
							/* translators: 1: a day, week or month, 2: number of views. */
							__(
								'Busiest: %1$s, %2$s views',
								'nhrrob-post-insights'
							),
							best.label,
							count( best.views )
					  )
					: undefined
			}
		>
			<DayChart bars={ bars } unit={ unit } label={ titles[ unit ] } />
		</Panel>
	);
}

export default function Overview( {
	data,
	range,
	against,
	extra,
	onOpen,
	onNavigate,
} ) {
	const tiles = extra.tiles || [];
	const link = ( id, text ) => (
		<button
			type="button"
			className={ P + 'link' }
			onClick={ () => onNavigate( id ) }
		>
			{ text }
		</button>
	);

	return (
		<>
			<ScreenHeader
				title={ __( 'Overview', 'nhrrob-post-insights' ) }
				lede={ __(
					'Visitors’ views only. Your own views while logged in, and crawlers, are not counted.',
					'nhrrob-post-insights'
				) }
			/>
			{ range }
			<Kpis data={ data } against={ against } site />
			<ViewsPanel days={ data.days } />
			<div className={ P + 'two' }>
				<Panel
					title={ __( 'Most read posts', 'nhrrob-post-insights' ) }
					meta={ __( 'Views', 'nhrrob-post-insights' ) }
					foot={ link(
						'posts',
						__( 'All posts →', 'nhrrob-post-insights' )
					) }
					flush
				>
					{ data.posts.length ? (
						<BarList
							rows={ data.posts.slice( 0, 6 ).map( ( row ) => ( {
								key: row.id,
								amount: row.views,
								value: count( row.views ),
								label: (
									<button
										type="button"
										className={ P + 'bars__open' }
										onClick={ () => onOpen( row ) }
									>
										{ row.title }
									</button>
								),
							} ) ) }
						/>
					) : (
						<p className={ P + 'empty' }>
							{ __(
								'No views in these days.',
								'nhrrob-post-insights'
							) }
						</p>
					) }
				</Panel>
				<SourcesPanel
					data={ data }
					limit={ 7 }
					foot={ link(
						'referrers',
						__( 'All referrers →', 'nhrrob-post-insights' )
					) }
				/>
			</div>
			{ tiles.length > 0 && (
				<Panel
					title={ __(
						'From your other plugins',
						'nhrrob-post-insights'
					) }
				>
					<dl className={ P + 'facts' }>
						{ tiles.map( ( tile ) => (
							<div
								key={ tile.key }
								className={ P + 'facts__row' }
							>
								<dt>{ tile.label }</dt>
								<dd>
									{ byKind(
										tile.kind,
										( data.extra || {} )[ tile.key ]
									) }
								</dd>
							</div>
						) ) }
					</dl>
				</Panel>
			) }
		</>
	);
}
