(function () {
	'use strict';

	function triggerHeroAnimations() {
		var heroes = document.querySelectorAll( '.sbca-hero' );
		if ( ! heroes.length ) {
			return;
		}

		if ( 'IntersectionObserver' in window ) {
			var observer = new IntersectionObserver(
				function ( entries ) {
					entries.forEach( function ( entry ) {
						if ( entry.isIntersecting ) {
							entry.target.classList.add( 'sbca-in-view' );
							observer.unobserve( entry.target );
						}
					} );
				},
				{ threshold: 0.2 }
			);

			heroes.forEach( function ( hero ) {
				observer.observe( hero );
			} );
		} else {
			// Fallback for very old browsers.
			heroes.forEach( function ( hero ) {
				hero.classList.add( 'sbca-in-view' );
			} );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', triggerHeroAnimations );
	} else {
		triggerHeroAnimations();
	}
})();
