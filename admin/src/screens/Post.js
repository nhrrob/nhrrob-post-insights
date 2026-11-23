/**
 * One post: its headline numbers with their change, its views over time and
 * where its readers came from, for the chosen days.
 */
import { useState } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import { __, sprintf } from '@wordpress/i18n';

import { External } from '../components/ui';
import { dayLabel } from '../format';
import { Kpis, ViewsPanel } from './Overview';
import { SourcesPanel } from './Referrers';

const P = 'nhrrob-post-insights-';

/**
 * The post's own numbers: the first tab.
 *
 * @param {Object} props         Props.
 * @param {Object} props.data    The numbers of /stats for the post.
 * @param {string} props.against What they are compared with.
 * @return {Object} Numbers, chart and sources.
 */
function Numbers( { data, against } ) {
	return (
		<>
			<Kpis data={ data } against={ against } />
			<div className={ P + 'two' }>
				<ViewsPanel days={ data.days } />
				<SourcesPanel
					data={ data }
					limit={ 10 }
					title={ __(
						'Where its readers came from',
						'nhrrob-post-insights'
					) }
				/>
			</div>
		</>
	);
}

export default function Post( props ) {
	const { range, post, onClose } = props;
	// An add-on adds a tab through this filter: { id, label, component },
	// given the same props as this screen. One tab shows no tab bar.
	const [ tabs ] = useState( () =>
		applyFilters(
			'nhrrobPostInsights.postTabs',
			[
				{
					id: 'numbers',
					label: __( 'Numbers', 'nhrrob-post-insights' ),
					component: Numbers,
				},
			],
			post
		).filter(
			( tab ) =>
				tab &&
				tab.id &&
				tab.label &&
				'function' === typeof tab.component
		)
	);
	const [ open, setOpen ] = useState( tabs[ 0 ].id );
	const Tab = ( tabs.find( ( tab ) => tab.id === open ) || tabs[ 0 ] )
		.component;

	return (
		<>
			<button type="button" className={ P + 'back' } onClick={ onClose }>
				{ __( '← All posts', 'nhrrob-post-insights' ) }
			</button>
			<div className={ P + 'screen__head is-post' }>
				<h1 className={ P + 'screen__title' }>{ post.title }</h1>
				<p className={ P + 'screen__lede' }>
					{ post.date &&
						sprintf(
							/* translators: %s: the day a post was published. */
							__( 'Published %s', 'nhrrob-post-insights' ),
							dayLabel( 'M j, Y', post.date )
						) + ' · ' }
					<External href={ post.link }>
						{ __( 'View post', 'nhrrob-post-insights' ) }
					</External>
					{ post.edit && (
						<>
							{ ' · ' }
							<a href={ post.edit }>
								{ __( 'Edit', 'nhrrob-post-insights' ) }
							</a>
						</>
					) }
				</p>
			</div>
			{ range }
			{ tabs.length > 1 && (
				<div
					className={ P + 'segment ' + P + 'tabs' }
					role="group"
					aria-label={ __( 'Show', 'nhrrob-post-insights' ) }
				>
					{ tabs.map( ( tab ) => (
						<button
							key={ tab.id }
							type="button"
							aria-pressed={ tab.id === open }
							onClick={ () => setOpen( tab.id ) }
						>
							{ tab.label }
						</button>
					) ) }
				</div>
			) }
			<Tab { ...props } />
		</>
	);
}
