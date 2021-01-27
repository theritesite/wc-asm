(function( $ ) {

	$( document ).ready(function() {
		$( 'select#woocommerce_asm_wc_categories' ).trigger( 'checkCategories' );
	});

	$( document ).on( 'change', 'select#woocommerce_asm_wc_categories', function(e) { $( this ).trigger( 'checkCategories' ); } );

	var displayCatQtyFields = function( arr ) {
		if ( shippingZoneMethods2LocalizeScript.debug === true ) {
			console.log( 'in displayCatQtyFields' );
			console.log( arr );
		}
		Object.keys( arr ).forEach( function( opt ) {
			if ( shippingZoneMethods2LocalizeScript.debug === true ) {
				console.log( opt );
			}
			var $el  = $( '#woocommerce_asm_wc_' + opt + '_qty_min' ).closest( 'tr' );
			var $el2 = $( '#woocommerce_asm_wc_' + opt + '_qty_max' ).closest( 'tr' );
			var $el3 = $( '#woocommerce_asm_wc_' + opt + '_cost' ).closest( 'tr' );
			if ( arr[opt] === 1 ) {
				$el.removeClass( 'hidden' );
				$el2.removeClass( 'hidden' );
				$el3.removeClass( 'hidden' );
			}
			else {
				$el.addClass( 'hidden' );
				$el2.addClass( 'hidden' );
				$el3.addClass( 'hidden' );
			}
		});
	};

	$( document ).on( 'checkCategories', 'select#woocommerce_asm_wc_categories', function(e) {
		
		var options = this.selectedOptions;
		var selected = set_arr = [];
		var flag, values;

		Object.keys( options ).forEach( function( opt ) {
			selected.push( options[opt].value );
		});

		values = $.map( $( 'select#woocommerce_asm_wc_categories option' ), function(e){
			return e.value;
		});

		values.forEach( function(e) {
			flag = -1;
			if ( $.inArray( e, selected ) !== -1 ) {
				flag = 1;
			}
			set_arr[e] = flag;
		});

		if ( shippingZoneMethods2LocalizeScript.debug === true ) {
			console.log( 'All values:' );
			console.log( values );
			console.log( 'Selected values: ' );
			console.log( selected );
			console.log( 'set array:' );
			console.log( set_arr );
		}
		displayCatQtyFields( set_arr );
	});
	


})( jQuery, shippingZoneMethods2LocalizeScript );
