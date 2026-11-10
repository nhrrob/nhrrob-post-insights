/**
 * Posts: every published post with its views and average reading time in the
 * chosen days, sortable and paged by the route.
 */
import { useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import api from '../api';
import {
	Button,
	External,
	Loading,
	Note,
	Panel,
	ScreenHeader,
} from '../components/ui';
import { average, byKind, count } from '../format';

const P = 'nhrrob-post-insights-';

/**
 * A column header that sorts the table by its column.
 *
 * @param {Object}   props        Props.
 * @param {string}   props.id     Sort key.
 * @param {string}   props.label  Header text.
 * @param {Object}   props.sort   Current orderby and order.
 * @param {Function} props.onSort Called with the key.
 * @param {boolean}  props.number Whether the column holds numbers.
 */
function SortHeader( { id, label, sort, onSort, number } ) {
	const active = sort.orderby === id;
	let ariaSort = 'none';

	if ( active ) {
		ariaSort = 'asc' === sort.order ? 'ascending' : 'descending';
	}

	return (
		<th
			scope="col"
			className={ number ? 'is-number' : undefined }
			aria-sort={ ariaSort }
		>
			<button
				type="button"
				className={ P + 'sort' + ( active ? ' is-active' : '' ) }
				onClick={ () => onSort( id ) }
			>
				{ label }
				<span aria-hidden="true" className={ P + 'sort__arrow' }>
					{ active && 'asc' === sort.order ? '↑' : '↓' }
				</span>
			</button>
		</th>
	);
}

export default function Posts( {
	range,
	query,
	search,
	onSearch,
	extra,
	onOpen,
} ) {
	const [ sort, setSort ] = useState( { orderby: 'views', order: 'desc' } );
	const [ page, setPage ] = useState( 1 );
	const [ table, setTable ] = useState( null );
	const [ failed, setFailed ] = useState( '' );
	const [ words, setWords ] = useState( search );
	const columns = extra.columns || [];

	// The search is asked for once typing pauses, and only when it changed.
	useEffect( () => {
		if ( words.trim() === search ) {
			return;
		}
		const timer = setTimeout( () => onSearch( words.trim() ), 300 );
		return () => clearTimeout( timer );
	}, [ words, search, onSearch ] );

	// New filters or a new search start again from the first page.
	useEffect( () => setPage( 1 ), [ query, search ] );

	useEffect( () => {
		setFailed( '' );
		api(
			'/posts?' +
				query +
				'&search=' +
				encodeURIComponent( search ) +
				'&orderby=' +
				sort.orderby +
				'&order=' +
				sort.order +
				'&page=' +
				page
		)
			.then( setTable )
			.catch( ( e ) => setFailed( e.message || String( e ) ) );
	}, [ query, search, sort, page ] );

	// Text sorts from A and dates and numbers from the largest, the first time.
	const onSort = ( orderby ) => {
		let order = 'title' === orderby ? 'asc' : 'desc';

		if ( sort.orderby === orderby ) {
			order = 'asc' === sort.order ? 'desc' : 'asc';
		}

		setSort( { orderby, order } );
		setPage( 1 );
	};

	let body = <Loading />;

	if ( failed ) {
		body = <Note tone="warn">{ failed }</Note>;
	} else if ( table && ! table.posts.length ) {
		body = (
			<div className={ P + 'empty' }>
				<p>
					{ search
						? __(
								'No post has these words in its title.',
								'nhrrob-post-insights'
						  )
						: __(
								'No published posts yet.',
								'nhrrob-post-insights'
						  ) }
				</p>
			</div>
		);
	} else if ( table ) {
		body = (
			<div className={ P + 'scroll' }>
				<table className={ P + 'grid' }>
					<thead>
						<tr>
							<SortHeader
								id="title"
								label={ __( 'Post', 'nhrrob-post-insights' ) }
								sort={ sort }
								onSort={ onSort }
							/>
							<SortHeader
								id="date"
								label={ __( 'Date', 'nhrrob-post-insights' ) }
								sort={ sort }
								onSort={ onSort }
							/>
							<SortHeader
								id="views"
								label={ __( 'Views', 'nhrrob-post-insights' ) }
								sort={ sort }
								onSort={ onSort }
								number
							/>
							<SortHeader
								id="average"
								label={ __(
									'Average time',
									'nhrrob-post-insights'
								) }
								sort={ sort }
								onSort={ onSort }
								number
							/>
							{ columns.map( ( column ) => (
								<th
									key={ column.key }
									scope="col"
									className={
										'text' === column.kind
											? undefined
											: 'is-number'
									}
								>
									{ column.label }
								</th>
							) ) }
						</tr>
					</thead>
					<tbody>
						{ table.posts.map( ( post ) => (
							<tr key={ post.id }>
								<td className="is-wrap">
									<External href={ post.link }>
										{ post.title }
									</External>
									<button
										type="button"
										className={ P + 'edit' }
										onClick={ () => onOpen( post ) }
									>
										{ __(
											'Details',
											'nhrrob-post-insights'
										) }
									</button>
									{ post.edit && (
										<a
											className={ P + 'edit' }
											href={ post.edit }
										>
											{ __(
												'Edit',
												'nhrrob-post-insights'
											) }
										</a>
									) }
								</td>
								<td>{ post.date }</td>
								<td className="is-number">
									{ count( post.views ) }
								</td>
								<td className="is-number">
									{ average( post.seconds, post.views ) }
								</td>
								{ columns.map( ( column ) => (
									<td
										key={ column.key }
										className={
											'text' === column.kind
												? undefined
												: 'is-number'
										}
									>
										{ byKind(
											column.kind,
											( post.extra || {} )[ column.key ]
										) }
									</td>
								) ) }
							</tr>
						) ) }
					</tbody>
				</table>
			</div>
		);
	}

	return (
		<>
			<ScreenHeader
				title={ __( 'Posts', 'nhrrob-post-insights' ) }
				lede={ __(
					'Every published post, how often it was viewed, and how long a reader stayed on average. The time counts only while the tab was in view.',
					'nhrrob-post-insights'
				) }
			/>
			{ range }
			<Panel
				title={ __( 'All posts', 'nhrrob-post-insights' ) }
				icon="posts"
				meta={
					table
						? sprintf(
								/* translators: %s: number of posts. */
								__( '%s posts', 'nhrrob-post-insights' ),
								count( table.total )
						  )
						: undefined
				}
				actions={
					<input
						type="search"
						className={ P + 'input ' + P + 'input--inline' }
						placeholder={ __(
							'Search titles',
							'nhrrob-post-insights'
						) }
						aria-label={ __(
							'Search titles',
							'nhrrob-post-insights'
						) }
						value={ words }
						onChange={ ( e ) => setWords( e.target.value ) }
					/>
				}
				flush
			>
				{ body }
				{ table && table.pages > 1 && (
					<nav
						className={ P + 'pager' }
						aria-label={ __( 'Pages', 'nhrrob-post-insights' ) }
					>
						<Button
							small
							variant="ghost"
							disabled={ page <= 1 }
							onClick={ () => setPage( page - 1 ) }
						>
							{ __( 'Previous', 'nhrrob-post-insights' ) }
						</Button>
						<span>
							{ sprintf(
								/* translators: 1: page, 2: number of pages. */
								__(
									'Page %1$d of %2$d',
									'nhrrob-post-insights'
								),
								page,
								table.pages
							) }
						</span>
						<Button
							small
							variant="ghost"
							disabled={ page >= table.pages }
							onClick={ () => setPage( page + 1 ) }
						>
							{ __( 'Next', 'nhrrob-post-insights' ) }
						</Button>
					</nav>
				) }
			</Panel>
		</>
	);
}
