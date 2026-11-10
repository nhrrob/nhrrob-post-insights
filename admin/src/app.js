/**
 * Root component: app shell, section routing and the numbers of the chosen days.
 */
import { useState, useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import api from './api';
import AppShell from './components/AppShell';
import { ToastProvider } from './components/ToastProvider';
import { Button, Loading, Note } from './components/ui';
import Overview from './screens/Overview';
import Post from './screens/Post';
import Posts from './screens/Posts';
import Referrers from './screens/Referrers';

const SCREENS = { overview: Overview, posts: Posts, referrers: Referrers };
// 0 is all time.
const RANGES = [ 7, 30, 90, 0 ];

export default function App( { boot } ) {
	const [ active, setActive ] = useState( 'overview' );
	const [ days, setDays ] = useState( 30 );
	// The post opened from the Posts table, or null for the whole site.
	const [ post, setPost ] = useState( null );
	const [ data, setData ] = useState( null );
	const [ failed, setFailed ] = useState( '' );

	useEffect( () => {
		setFailed( '' );
		setData( null );
		api( '/stats?days=' + days + ( post ? '&post=' + post.id : '' ) )
			.then( setData )
			.catch( ( e ) => setFailed( e.message || String( e ) ) );
	}, [ days, post ] );

	const modules = [
		{ id: 'overview', label: __( 'Overview', 'nhrrob-post-insights' ) },
		{ id: 'posts', label: __( 'Posts', 'nhrrob-post-insights' ) },
		{ id: 'referrers', label: __( 'Referrers', 'nhrrob-post-insights' ) },
	];
	const Screen = post ? Post : SCREENS[ active ];

	// One row of filters above the numbers, the same on every screen.
	const range = (
		<div
			className="nhrrob-post-insights-range"
			role="group"
			aria-label={ __( 'Days shown', 'nhrrob-post-insights' ) }
		>
			{ RANGES.map( ( option ) => (
				<Button
					key={ option }
					small
					variant={ option === days ? 'soft' : 'ghost' }
					aria-pressed={ option === days }
					onClick={ () => setDays( option ) }
				>
					{ option
						? sprintf(
								/* translators: %d: number of days. */
								__( 'Last %d days', 'nhrrob-post-insights' ),
								option
						  )
						: __( 'All time', 'nhrrob-post-insights' ) }
				</Button>
			) ) }
		</div>
	);

	let content = <Loading />;
	if ( failed ) {
		content = <Note tone="warn">{ failed }</Note>;
	} else if ( data ) {
		content = (
			<Screen
				data={ data }
				range={ range }
				days={ days }
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
