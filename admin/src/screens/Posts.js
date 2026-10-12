/**
 * Posts: views and average reading time of each post, most viewed first.
 */
import { __ } from '@wordpress/i18n';

import { External, Panel, ScreenHeader } from '../components/ui';
import { average, count } from '../format';

const P = 'nhrrob-post-insights-';

export default function Posts( { data, range } ) {
	return (
		<>
			<ScreenHeader
				title={ __( 'Posts', 'nhrrob-post-insights' ) }
				lede={ __(
					'How often each post was viewed, and how long a reader stayed on average. The time counts only while the tab was in view.',
					'nhrrob-post-insights'
				) }
			/>
			{ range }
			<Panel
				title={ __( 'Most viewed', 'nhrrob-post-insights' ) }
				icon="posts"
				flush
			>
				{ ! data.posts.length ? (
					<div className={ P + 'empty' }>
						<p>
							{ __(
								'No views in these days yet.',
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
										{ __( 'Post', 'nhrrob-post-insights' ) }
									</th>
									<th scope="col" className="is-number">
										{ __(
											'Views',
											'nhrrob-post-insights'
										) }
									</th>
									<th scope="col" className="is-number">
										{ __(
											'Average time',
											'nhrrob-post-insights'
										) }
									</th>
								</tr>
							</thead>
							<tbody>
								{ data.posts.map( ( post ) => (
									<tr key={ post.id }>
										<td className="is-wrap">
											<External href={ post.link }>
												{ post.title }
											</External>
										</td>
										<td className="is-number">
											{ count( post.views ) }
										</td>
										<td className="is-number">
											{ average(
												post.seconds,
												post.views
											) }
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
