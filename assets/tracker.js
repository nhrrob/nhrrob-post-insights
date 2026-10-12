/**
 * Reports one view of the post on this page, and how long the tab was in view.
 * Sets no cookie and stores nothing in the browser.
 */
( function () {
	var data = window.nhrrobPostInsights;

	// The longest stay counted for one page view, in seconds.
	var MAX = 1800;
	var counted = false;
	var since = 0;
	var sent = 0;

	if ( ! data || ! data.post || navigator.webdriver || ! navigator.sendBeacon ) {
		return;
	}

	function send( route, values ) {
		values.post = data.post;
		navigator.sendBeacon( data.root + route, new URLSearchParams( values ) );
	}

	function shown() {
		since = Date.now();

		// A tab opened in the background is not a view until it is looked at.
		if ( ! counted ) {
			counted = true;
			send( '/view', { referrer: document.referrer } );
		}
	}

	function hidden() {
		var seconds;

		if ( ! since ) {
			return;
		}

		seconds = Math.min( Math.round( ( Date.now() - since ) / 1000 ), MAX - sent );
		since = 0;

		if ( seconds > 0 ) {
			sent += seconds;
			send( '/time', { seconds: seconds } );
		}
	}

	document.addEventListener( 'visibilitychange', function () {
		if ( 'visible' === document.visibilityState ) {
			shown();
		} else {
			hidden();
		}
	} );

	// Safari does not always report a closed tab as hidden.
	window.addEventListener( 'pagehide', hidden );

	if ( 'visible' === document.visibilityState ) {
		shown();
	}
}() );
