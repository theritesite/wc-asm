(function( $ ) {

	$( document ).ready(function() {
		$( 'input.timepicker.input-text.regular-input' ).timepicker({ dropdown: true, scrollbar: true });
		$( 'input#woocommerce_wc_asm_toggler' ).trigger( 'checkTimeLimited' );
	});

	$( document ).on( 'change', 'input#woocommerce_wc_asm_toggler', function(e){ $( this ).trigger( 'checkTimeLimited' )});

	var displayTimeFields = function( arg ) {
		if ( shippingZoneMethods2LocalizeScript.debug === true ) {
			console.log( 'in displayTimeFields' );
		}

		var $el = $( '.timelimited' ).closest( 'tr' );
		if ( arg === 1 ) {
			$el.removeClass( 'hidden' );
		}
		else {
			$el.addClass( 'hidden' );
		}
	}

	$( document ).on( 'checkTimeLimited', 'input#woocommerce_wc_asm_toggler', function(e) {
		console.log( $( this ).prop( 'checked' ) );
		displayTimeFields( $( this ).prop( 'checked' ) === true ? 1 : 0 );
	});

})( jQuery, shippingZoneMethods2LocalizeScript );
