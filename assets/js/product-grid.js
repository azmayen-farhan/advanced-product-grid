/**
 * Advanced Product Grid — front-end behaviour.
 *
 * The hover image swap is handled entirely in CSS (see product-grid.css).
 * This file only progressively enhances the sorting dropdown so it submits
 * on change, the same way the default WooCommerce ordering dropdown does.
 */
( function () {
	'use strict';

	function initSortingForms() {
		var selects = document.querySelectorAll( '.apg-orderby' );

		selects.forEach( function ( select ) {
			select.addEventListener( 'change', function () {
				if ( this.form ) {
					this.form.submit();
				}
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initSortingForms );
	} else {
		initSortingForms();
	}
} )();
