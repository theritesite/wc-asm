(function( $ ) {

	$( document ).ready(function() {
		$('input.timepicker.input-text.regular-input').timepicker({ dropdown: true, scrollbar: true });
		$('input#woocommerce_wc_asm_toggler').trigger('checkTimeLimited');
		$('select#woocommerce_wc_asm_classes').trigger('checkClasses');
	});

	$( document ).on( 'change', 'select#woocommerce_wc_asm_classes', function(e) {$(this).trigger('checkClasses');})
					  .on( 'change', 'input#woocommerce_wc_asm_toggler', function(e){$(this).trigger('checkTimeLimited')});

	var displayTimeFields = function( arg ) {
		if ( shippingZoneMethods2LocalizeScript.debug === true ) {
			console.log( 'in displayTimeFields' );
		}

		var $el = $('.timelimited').closest('tr');
		if ( arg === 1 ) {
			$el.removeClass('hidden');
		}
		else {
			$el.addClass('hidden');
		}
		// $el.css("display", arg === 1 ? '' : 'none' );
	}

	$( document ).on( 'checkTimeLimited', 'input#woocommerce_wc_asm_toggler', function(e) {
		console.log($(this).prop('checked'));
		displayTimeFields( $(this).prop('checked') === true ? 1 : 0 );
	});

	var displayQtyFields = function( arr ) {
		if ( shippingZoneMethods2LocalizeScript.debug === true ) {
			console.log( 'in displayQtyFields' );
			console.log( arr );
		}
		Object.keys(arr).forEach( function(opt) {
			if ( shippingZoneMethods2LocalizeScript.debug === true ) {
				console.log(opt);
			}
			var $el = $('#woocommerce_wc_asm_' + opt + '_qty').closest('tr');
			if ( arr[opt] === 1 ) {
				$el.removeClass('hidden');
			}
			else {
				$el.addClass('hidden');
			}
			// $el.css("display", arr[opt] === 1 ? '' : 'none' );
		});
	};

	$( document ).on( 'checkClasses', 'select#woocommerce_wc_asm_classes', function(e) {
		
		var options = this.selectedOptions;
		var selected = set_arr = [];
		var flag, values;

		Object.keys( options ).forEach( function(opt) {
			selected.push( options[opt].value );
		});

		values = $.map( $( 'select#woocommerce_wc_asm_classes option' ), function(e){
			return e.value;
		});

		values.forEach( function(e) {
			flag = -1;
			if ( $.inArray(e, selected) !== -1 ) {
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
		displayQtyFields( set_arr );
	});


})( jQuery, shippingZoneMethods2LocalizeScript );
