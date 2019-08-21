(function( $ ) {

	$( document ).ready(function() {
		$( 'select#woocommerce_wc_asm_classes' ).trigger( 'checkClasses' );
	});

	$( document ).on( 'change', 'select#woocommerce_wc_asm_classes', function(e) { $( this ).trigger( 'checkClasses' );} );

	var displayClassQtyFields = function( arr ) {
		if ( shippingZoneMethods2LocalizeScript.debug === true ) {
			console.log( 'in displayClassQtyFields' );
			console.log( arr );
		}
		Object.keys( arr ).forEach( function( opt ) {
			if ( shippingZoneMethods2LocalizeScript.debug === true ) {
				console.log(opt);
			}
			var $el  = $( '#woocommerce_wc_asm_' + opt + '_qty' ).closest( 'tr' );
			var $el2 = $( '#woocommerce_wc_asm_' + opt + '_cost' ).closest( 'tr' );
			if ( arr[opt] === 1 ) {
				$el.removeClass( 'hidden' );
				$el2.removeClass( 'hidden' );
			}
			else {
				$el.addClass( 'hidden' );
				$el2.addClass( 'hidden' );
			}
		});
	};

	$( document ).on( 'checkClasses', 'select#woocommerce_wc_asm_classes', function(e) {
		
		var options = this.selectedOptions;
		var selected = set_arr = [];
		var flag, values;

		Object.keys( options ).forEach( function( opt ) {
			selected.push( options[opt].value );
		});

		values = $.map( $( 'select#woocommerce_wc_asm_classes option' ), function(e){
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
		displayClassQtyFields( set_arr );
	});


})( jQuery, shippingZoneMethods2LocalizeScript );
