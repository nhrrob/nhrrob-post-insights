/**
 * One post: its headline numbers with their change, its views over time and
 * where its readers came from, for the chosen days.
 */
import { __, sprintf } from '@wordpress/i18n';

import { External } from '../components/ui';
import { dayLabel } from '../format';
import { Kpis, ViewsPanel } from './Overview';
import { SourcesPanel } from './Referrers';

const P = 'nhrrob-post-insights-';

export default function Post( { data, range, against, post, onClose } ) {
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
