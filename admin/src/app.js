/**
 * Root component: app shell, section routing, the filters and the numbers
 * they select.
 */
import { useState, useEffect, useMemo, useRef } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import { __, _n, sprintf } from '@wordpress/i18n';

import api from './api';
import AppShell from './components/AppShell';
import Filters, { RANGES, shift } from './components/Filters';
import { ToastProvider } from './components/ToastProvider';
import { Loading, Note } from './components/ui';
import { dayLabel } from './format';
import Overview from './screens/Overview';
import Post from './screens/Post';
import Posts from './screens/Posts';
import Referrers from './screens/Referrers';

const DAY = /^\d{4}-\d{2}-\d{2}$/;

/**
 * The filters kept in the address, so a filtered view can be bookmarked.
 * The route checks them again and ignores what it does not know.
 *
 * @return {Object} days, from, to, type and search.
 */
function fromAddress() {
	const query = new URLSearchParams( window.location.search );
	const days = parseInt( query.get( 'days' ), 10 );
	const from = query.get( 'from' ) || '';
	const to = query.get( 'to' ) || '';
	const custom = DAY.test( from ) && DAY.test( to ) && from <= to;

	return {
		days: RANGES.includes( days ) ? days : 30,
		from: custom ? from : '',
		to: custom ? to : '',
		type: query.get( 'type' ) || '',
		search: query.get( 'search' ) || '',
	};
}

/**
 * The filters as route arguments, and as the address of the screen.
 *
 * @param {Object} filters days, from, to, type and search.
 * @return {URLSearchParams} Arguments; empty ones and the default days left out.
 */
function toQuery( filters ) {
	const query = new URLSearchParams();

	if ( filters.from ) {
		query.set( 'from', filters.from );
		query.set( 'to', filters.to );
	} else if ( 30 !== filters.days ) {
		query.set( 'days', filters.days );
	}

	[ 'type', 'search' ].forEach(
		( key ) => filters[ key ] && query.set( key, filters[ key ] )
	);

	return query;
}

export default function App( { boot } ) {
	const [ active, setActive ] = useState( 'overview' );
	const [ filters, setFilters ] = useState( fromAddress );
	// The post opened from the Posts table, or null for the whole site.
	const [ post, setPost ] = useState( null );
	// The numbers on screen and the post they are for. They stay while newer
	// ones load, so the screen neither empties nor changes height.
	const [ shown, setShown ] = useState( null );
	const [ busy, setBusy ] = useState( true );
	const [ failed, setFailed ] = useState( '' );
	const top = useRef( null );
	const data = shown ? shown.data : null;

	const change = ( patch ) =>
		setFilters( ( current ) => ( { ...current, ...patch } ) );
	// What the numbers of every screen are narrowed by, as route arguments;
	// the search is the Posts table's own.
	const args = toQuery( { ...filters, search: '' } );
	if ( ! filters.from ) {
		args.set( 'days', filters.days );
	}
	const query = args.toString();

	useEffect( () => {
		// An answer that comes after a newer request was made is dropped.
		let current = true;

		setFailed( '' );
		setBusy( true );
		api( '/stats?' + query + ( post ? '&post=' + post.id : '' ) )
			.then(
				( numbers ) => current && setShown( { data: numbers, post } )
			)
			.catch( ( e ) => current && setFailed( e.message || String( e ) ) )
			.finally( () => current && setBusy( false ) );

		return () => {
			current = false;
		};
	}, [ query, post ] );

	useEffect( () => {
		const url = new URL( window.location.href );
		const kept = toQuery( filters );

		[ 'days', 'from', 'to', 'type', 'search' ].forEach( ( key ) =>
			kept.has( key )
				? url.searchParams.set( key, kept.get( key ) )
				: url.searchParams.delete( key )
		);
		window.history.replaceState( null, '', url );
	}, [ filters ] );

	// The screens of the left menu. An add-on adds its own through this
	// filter: { id, label, component }, and optionally `group` (a heading in
	// the menu) and `icon` (an element). An entry without them is dropped.
	const modules = useMemo(
		() =>
			applyFilters( 'nhrrobPostInsights.screens', [
				{
					id: 'overview',
					label: __( 'Overview', 'nhrrob-post-insights' ),
					component: Overview,
				},
				{
					id: 'posts',
					label: __( 'Posts', 'nhrrob-post-insights' ),
					component: Posts,
				},
				{
					id: 'referrers',
					label: __( 'Referrers', 'nhrrob-post-insights' ),
					component: Referrers,
				},
			] ).filter(
				( m, i, all ) =>
					m &&
					m.id &&
					m.label &&
					'function' === typeof m.component &&
					all.findIndex( ( o ) => o && o.id === m.id ) === i
			),
		[]
	);
	const Screen =
		shown && shown.post
			? Post
			: ( modules.find( ( m ) => m.id === active ) || modules[ 0 ] )
					.component;
	// A new screen starts at the top of the app, when that is out of view.
	const toTop = () => {
		if ( top.current && top.current.getBoundingClientRect().top < 0 ) {
			window.scrollTo( 0, 0 );
		}
	};
	const open = ( next ) => {
		setPost( next );
		toTop();
	};
	const navigate = ( id ) => {
		open( null );
		setActive( id );
	};

	// What the numbers are compared with: the same number of days before,
	// or nothing for all time.
	let note = '';
	let against = '';
	if ( data && data.days.length ) {
		const first = data.days[ 0 ].day;
		const n = data.days.length;

		if ( data.previous ) {
			note = sprintf(
				/* translators: 1: first day, 2: last day. */
				__( 'Compared with %1$s – %2$s', 'nhrrob-post-insights' ),
				dayLabel( 'M j', shift( first, -n ) ),
				dayLabel( 'M j', shift( first, -1 ) )
			);
			against =
				1 === n
					? __( 'vs the day before', 'nhrrob-post-insights' )
					: sprintf(
							/* translators: %d: number of days, more than one. */
							_n(
								'vs the %d day before',
								'vs the %d days before',
								n,
								'nhrrob-post-insights'
							),
							n
					  );
		} else {
			note = sprintf(
				/* translators: %s: a day. */
				__(
					'Since %s, the first day with a view',
					'nhrrob-post-insights'
				),
				dayLabel( 'M j, Y', first )
			);
			against = sprintf(
				/* translators: %s: a day. */
				__( 'since %s', 'nhrrob-post-insights' ),
				dayLabel( 'M j', first )
			);
		}
	}

	const range = (
		<Filters
			filters={ filters }
			types={ boot.types || [] }
			today={ boot.today }
			note={ note }
			onChange={ change }
		/>
	);

	let content = <Loading />;
	if ( failed ) {
		content = <Note tone="warn">{ failed }</Note>;
	} else if ( data ) {
		content = (
			<Screen
				data={ data }
				range={ range }
				against={ against }
				onNavigate={ navigate }
				query={ query }
				search={ filters.search }
				onSearch={ ( search ) => change( { search } ) }
				extra={ boot.extra || {} }
				post={ shown.post }
				onOpen={ open }
				onClose={ () => open( null ) }
			/>
		);
	}

	return (
		<ToastProvider>
			<AppShell
				modules={ modules }
				active={ active }
				onNavigate={ navigate }
				title={
					boot.pluginName ||
					__( 'Post Insights', 'nhrrob-post-insights' )
				}
			>
				<div
					ref={ top }
					className="nhrrob-post-insights-view"
					aria-busy={ busy || undefined }
				>
					{ content }
				</div>
			</AppShell>
		</ToastProvider>
	);
}
