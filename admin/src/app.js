/**
 * Root component: app shell, section routing, the filters and the numbers
 * they select.
 */
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import api from './api';
import AppShell from './components/AppShell';
import Filters, { RANGES } from './components/Filters';
import { ToastProvider } from './components/ToastProvider';
import { Loading, Note } from './components/ui';
import Overview from './screens/Overview';
import Post from './screens/Post';
import Posts from './screens/Posts';
import Referrers from './screens/Referrers';

const SCREENS = { overview: Overview, posts: Posts, referrers: Referrers };
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
	const [ data, setData ] = useState( null );
	const [ failed, setFailed ] = useState( '' );

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
		setFailed( '' );
		setData( null );
		api( '/stats?' + query + ( post ? '&post=' + post.id : '' ) )
			.then( setData )
			.catch( ( e ) => setFailed( e.message || String( e ) ) );
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

	const modules = [
		{ id: 'overview', label: __( 'Overview', 'nhrrob-post-insights' ) },
		{ id: 'posts', label: __( 'Posts', 'nhrrob-post-insights' ) },
		{ id: 'referrers', label: __( 'Referrers', 'nhrrob-post-insights' ) },
	];
	const Screen = post ? Post : SCREENS[ active ];

	const range = (
		<Filters
			filters={ filters }
			types={ boot.types || [] }
			today={ boot.today }
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
				query={ query }
				search={ filters.search }
				onSearch={ ( search ) => change( { search } ) }
				extra={ boot.extra || {} }
				post={ post }
				onOpen={ setPost }
				onClose={ () => setPost( null ) }
			/>
		);
	}

	return (
		<ToastProvider>
			<AppShell
				modules={ modules }
				active={ active }
				onNavigate={ ( id ) => {
					setPost( null );
					setActive( id );
				} }
				title={
					boot.pluginName ||
					__( 'Post Insights', 'nhrrob-post-insights' )
				}
			>
				{ content }
			</AppShell>
		</ToastProvider>
	);
}
