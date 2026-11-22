/**
 * Where views came from: the referring sites and the views with no
 * referrer, as one ranked list, each with a kind told from its host name.
 */
import { __ } from '@wordpress/i18n';

const SEARCH = [
	'google.',
	'bing.com',
	'duckduckgo.com',
	'yahoo.',
	'yandex.',
	'baidu.com',
	'ecosia.org',
	'search.brave.com',
	'startpage.com',
	'qwant.com',
	'kagi.com',
];

const SOCIAL = [
	'linkedin.com',
	'lnkd.in',
	't.co',
	'x.com',
	'twitter.com',
	'facebook.com',
	'fb.me',
	'instagram.com',
	'threads.net',
	'reddit.com',
	'news.ycombinator.com',
	'bsky.app',
	'mastodon.social',
	'youtube.com',
	'pinterest.com',
	'quora.com',
	'medium.com',
	'dev.to',
	't.me',
];

export const KINDS = {
	search: __( 'Search', 'nhrrob-post-insights' ),
	social: __( 'Social', 'nhrrob-post-insights' ),
	site: __( 'Site', 'nhrrob-post-insights' ),
	direct: __( 'Direct', 'nhrrob-post-insights' ),
};

/**
 * Whether a host is a domain or one of its subdomains. A name ending in a
 * dot, such as "google.", stands for every top-level domain.
 *
 * @param {string} host Host.
 * @param {string} name Domain.
 * @return {boolean} Whether it matches.
 */
function under( host, name ) {
	if ( name.endsWith( '.' ) ) {
		return host.startsWith( name ) || host.includes( '.' + name );
	}

	return host === name || host.endsWith( '.' + name );
}

/**
 * @param {string} host Referring host.
 * @return {string} search, social or site.
 */
export function kind( host ) {
	if ( SEARCH.some( ( name ) => under( host, name ) ) ) {
		return 'search';
	}

	return SOCIAL.some( ( name ) => under( host, name ) ) ? 'social' : 'site';
}

/**
 * Every source of the numbers, the views with no referrer among them,
 * ranked by views.
 *
 * @param {Object} data The numbers of /stats.
 * @return {Array} Rows with key, host, kind, views and before (null for all time).
 */
export function sources( data ) {
	const previous = data.previous;
	const rows = data.referrers.map( ( row ) => ( {
		key: row.host,
		host: row.host,
		kind: kind( row.host ),
		views: row.views,
		before: previous ? ( previous.referrers || {} )[ row.host ] || 0 : null,
	} ) );

	if ( data.direct > 0 ) {
		rows.push( {
			key: '',
			host: '',
			kind: 'direct',
			views: data.direct,
			before: previous ? previous.direct : null,
		} );
	}

	return rows.sort( ( a, b ) => b.views - a.views );
}
