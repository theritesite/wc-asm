(function( $, data, wp, ajaxurl ) {

	$(document).ready(function() {
		$('input.timepicker.input-text.regular-input').timepicker({ dropdown: true, scrollbar: true });
	});

	$( document.body ).on( 'click', 'input.timepicker.input-text.regular-input', function(e) {
		console.log("really made it.");
		console.log(this);
	});

	$( document.body ).on( 'change', 'select#woocommerce_wc_asm_classes', function(e) {
		console.log( "in the select change method" );
		var options = this.selectedOptions;
		Object.keys(options).forEach( function(opt) {
			console.log(opt);
		});
		// console.log( this.selectedOptions );
	});

})( jQuery, shippingZoneMethods2LocalizeScript );
