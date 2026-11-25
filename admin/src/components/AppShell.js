/**
 * App shell: the app bar with the theme switch, the left menu and the
 * content. Below 782px the menu becomes a row above the content.
 */
import { Fragment, useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { Icon } from './ui';

const THEME_KEY = 'nhrrob-post-insights-theme';

// A theme fixed for every NHR plugin by another plugin: 'light', 'dark' or
// 'system'. Anything else is ignored, and the saved choice applies.
const FORCED = [ 'light', 'dark', 'system' ].includes( window.nhrUi?.theme )
	? window.nhrUi.theme
	: '';
const P = 'nhrrob-post-insights-';

function remember( key, value ) {
	try {
		window.localStorage.setItem( key, value );
	} catch ( e ) {
		/* storage unavailable — the choice lasts for this page only */
	}
}

export default function AppShell( {
	modules,
	active,
	onNavigate,
	title,
	children,
} ) {
	const [ theme, setTheme ] = useState( '' ); // '' follows the system setting

	useEffect( () => {
		try {
			const saved = window.localStorage.getItem( THEME_KEY );
			if ( saved === 'dark' || saved === 'light' ) {
				setTheme( saved );
			}
		} catch ( e ) {
			/* storage unavailable */
		}
	}, [] );

	const prefersDark =
		window.matchMedia &&
		window.matchMedia( '(prefers-color-scheme: dark)' ).matches;
	let shown = theme;
	if ( FORCED ) {
		shown = FORCED === 'system' ? '' : FORCED;
	}
	const isDark = shown ? shown === 'dark' : prefersDark;

	return (
		<div className={ P + 'app nhr-app' } data-theme={ shown || undefined }>
			<header className={ P + 'bar' }>
				<Icon name="overview" size={ 20 } />
				<span className={ P + 'bar__title' }>{ title }</span>
				<span className={ P + 'bar__spacer' } />
				{ ! FORCED && (
					<button
						type="button"
						className={ P + 'bar__btn' }
						aria-label={
							isDark
								? __(
										'Switch to light theme',
										'nhrrob-post-insights'
								  )
								: __(
										'Switch to dark theme',
										'nhrrob-post-insights'
								  )
						}
						onClick={ () => {
							const next = isDark ? 'light' : 'dark';
							remember( THEME_KEY, next );
							setTheme( next );
						} }
					>
						<Icon name={ isDark ? 'sun' : 'moon' } />
					</button>
				) }
			</header>

			<div className={ P + 'body' }>
				<nav className={ P + 'nav' } aria-label={ title }>
					{ modules.map( ( m, i ) => (
						<Fragment key={ m.id }>
							{ m.group &&
								m.group !==
									( modules[ i - 1 ] || {} ).group && (
									<p className={ P + 'nav__group' }>
										{ m.group }
									</p>
								) }
							<button
								type="button"
								className={
									P +
									'nav__item' +
									( m.id === active ? ' is-active' : '' )
								}
								aria-current={
									m.id === active ? 'page' : undefined
								}
								onClick={ () => onNavigate( m.id ) }
							>
								{ m.icon || <Icon name={ m.id } /> }
								{ m.label }
							</button>
						</Fragment>
					) ) }
					<p className={ P + 'nav__note' }>
						{ __(
							"No cookies, no visitor's address. Nothing leaves this site.",
							'nhrrob-post-insights'
						) }
					</p>
				</nav>
				<main className={ P + 'content' }>{ children }</main>
			</div>
		</div>
	);
}
