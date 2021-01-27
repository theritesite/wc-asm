(function( $ ) {

	$( document ).ready(function() {
		$( 'input.timepicker.input-text.regular-input' ).timepicker({ dropdown: true, scrollbar: true });
		$( 'input#woocommerce_asm_wc_toggler' ).trigger( 'checkTimeLimited' );
		$( 'select#woocommerce_asm_wc_classes' ).trigger( 'checkClasses' );
		$( 'select#woocommerce_asm_wc_categories' ).trigger( 'checkCategories' );
	});

	$( document ).on( 'change', 'select#woocommerce_asm_wc_classes', function(e) {$( this ).trigger( 'checkClasses' );})
					  .on( 'change', 'input#woocommerce_asm_wc_toggler', function(e){$( this ).trigger( 'checkTimeLimited' )})
					  .on( 'change', 'select#woocommerce_asm_wc_categories', function(e) {$( this ).trigger( 'checkCategories' );});

	var displayCatQtyFields = function( arr ) {
		if ( shippingZoneMethods2LocalizeScript.debug === true ) {
			console.log( 'in displayCatQtyFields' );
			console.log( arr );
		}
		Object.keys( arr ).forEach( function(opt) {
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

	$( document ).on( 'checkTimeLimited', 'input#woocommerce_asm_wc_toggler', function(e) {
		console.log( $( this ).prop( 'checked' ) );
		displayTimeFields( $( this ).prop( 'checked' ) === true ? 1 : 0 );
	});

	var displayClassQtyFields = function( arr ) {
		if ( shippingZoneMethods2LocalizeScript.debug === true ) {
			console.log( 'in displayClassQtyFields' );
			console.log( arr );
		}
		Object.keys( arr ).forEach( function( opt ) {
			if ( shippingZoneMethods2LocalizeScript.debug === true ) {
				console.log(opt);
			}
			var $el  = $( '#woocommerce_asm_wc_' + opt + '_qty' ).closest( 'tr' );
			var $el2 = $( '#woocommerce_asm_wc_' + opt + '_cost' ).closest( 'tr' );
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

	$( document ).on( 'checkClasses', 'select#woocommerce_asm_wc_classes', function(e) {
		
		var options = this.selectedOptions;
		var selected = set_arr = [];
		var flag, values;

		Object.keys( options ).forEach( function( opt ) {
			selected.push( options[opt].value );
		});

		values = $.map( $( 'select#woocommerce_asm_wc_classes option' ), function(e){
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
