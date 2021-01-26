<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cost_desc = __( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'wc-asm' ) . '<br/><br/>' . __( 'Use <code>[qty]</code> for the number of items, <br/><code>[cost]</code> for the total cost of items, and <code>[fee percent="10" min_fee="20" max_fee=""]</code> for percentage based fees.', 'wc-asm' );

$shipping_classes = WC()->shipping->get_shipping_classes();

$categories = wp_count_terms( 'product_cat' );

if ( ! function_exists( 'wc_asm_day_of_week' ) ) {
	function wc_asm_day_of_week() {
		$week = array(
			'sun'	=> __( 'Sunday', 'wc-asm' ),
			'mon'	=> __( 'Monday', 'wc-asm' ),
			'tue'	=> __( 'Tuesday', 'wc-asm' ),
			'wed'	=> __( 'Wednesday', 'wc-asm' ),
			'thu'	=> __( 'Thursday', 'wc-asm' ),
			'fri'	=> __( 'Friday', 'wc-asm' ),
			'sat'	=> __( 'Saturday', 'wc-asm' ),
		);

		return $week;
	}
}

if ( ! function_exists( 'wc_asm_shipping_classes_array' ) ) {
	function wc_asm_shipping_classes_array() {
		$classes = array();
		$shipping_classes = WC()->shipping->get_shipping_classes();
		foreach ( $shipping_classes as $shipping_class ) {
			if ( ! isset( $shipping_class->term_id ) ) {
				continue;
			}
			$classes[ 'sc_' . $shipping_class->term_id ] = $shipping_class->name;
		}
		return $classes;
	}
}

if ( ! function_exists( 'wc_asm_get_timestamp' ) ) {
	function wc_asm_get_timestamp() {
		return current_time('D h:i:s A');
	}
}

if ( ! function_exists( 'wc_asm_product_categories' ) ) {
	function wc_asm_product_categories() {
		$args = array(
			'taxonomy'		=> 'product_cat',
			'hide_empty'	=> false,
			'order'			=> 'ASC',
		);
		$product_cats = get_terms( $args );

		$categories = array();

		foreach( $product_cats as $cats ) {
			if ( 0 !== $cats->parent ) {
				$categories[ 'pc_' . $cats->term_id ] = get_term_by('id', $cats->parent, 'product_cat')->name . ' -> ' . $cats->name;
			}
			else {
				$categories[ 'pc_' . $cats->term_id ] = $cats->name;
			}
		}

		return $categories;
	}
}

/**
 * Settings for flat rate shipping.
 */
$settings = array(
	'title' => array(
		'title' 		=> __( 'Method title', 'wc-asm' ),
		'type' 			=> 'text',
		'description' 	=> __( 'This controls the title which the user sees during checkout.', 'wc-asm' ),
		'default'		=> __( 'Advanced flat rate', 'wc-asm' ),
		'desc_tip'		=> true,
	),
	'tax_status' => array(
		'title' 		=> __( 'Tax status', 'wc-asm' ),
		'type' 			=> 'select',
		'class'         => 'wc-enhanced-select',
		'default' 		=> 'taxable',
		'options'		=> array(
			'taxable' 	=> __( 'Taxable', 'wc-asm' ),
			'none' 		=> _x( 'None', 'Tax status', 'wc-asm' ),
		),
	),
	'cost' => array(
		'title' 		=> __( 'Cost', 'wc-asm' ),
		'type' 			=> 'text',
		'placeholder'	=> '',
		'description'	=> $cost_desc,
		'default'		=> '0',
		'desc_tip'		=> true,
	),
);

if ( ! empty( $categories ) ) {

	$cat_options = wc_asm_product_categories();

	$settings['categories'] = array(
		'title'			=> __( 'Categories', 'wc-asm' ),
		'type'			=> 'multiselect',
		'description'	=> __( 'controls which product categories should determine when this shipping method is available.', 'wc-asm' ),
		'class'			=> 'multiselect-asm categories',
		'options'		=> $cat_options,
	);

	foreach( $cat_options as $key => $value ) {
		$settings[ $key . '_qty_min' ] = array(
			'parent_id'		=> 'woocommerce_wc_asm_categories',
			'type'			=> 'text',
			'title'			=> $value . ' quantity minimum',
			'description'	=> '-1, 0 or blank for no minimum.',
		);
		$settings[ $key . '_qty_max' ] = array(
			'parent_id'		=> 'woocommerce_wc_asm_categories',
			'type'			=> 'text',
			'title'			=> $value . ' quantity maximum',
			'description'	=> '-1 or blank for unlimited.',
		);
	}
}

if ( ! empty( $shipping_classes ) ) {
	$settings['classes'] = array(
        'title'             => __( 'Shipping Class(es)', 'wc-asm' ),
        'type'              => 'multiselect',
        'description'       => __( 'Controls which shipping classes should determine when this shipping method is available. If no class is selected, any shipping class will be valid for any quantity.', 'wc-asm' ),
        'class'             => 'multiselect-asm classes',
		'options'			=> wc_asm_shipping_classes_array(),
	);

	foreach( $shipping_classes as $shipping_class ) {
		if ( ! isset( $shipping_class->term_id ) ) {
			continue;
		}
		$settings['sc_' . $shipping_class->term_id . '_qty'] = array(
			'parent_id'		=> 'woocommerce_wc_asm_classes',
			'type'			=> 'text',
			'title'			=> $shipping_class->name . ' quantity limit',
			'description'	=> '-1 for unlimited.',
		);
	}
}
else {
	$settings['classes'] = array(
        'title'             => __( 'Shipping Class(es)', 'wc-asm' ),
		'type'              => 'text',
		'disabled'			=> true,
		'placeholder'		=> __( 'Define shipping classes to use this feature!', 'wc-asm' ),
        'description'       => __( 'Controls which shipping classes should determine when this shipping method is available. If no class is selected, any shipping class will be valid for any quantity.', 'wc-asm' ),
	);
}

$settings = array_merge( $settings, array(
    'toggler' => array(
		'title'         => __( 'Enable time restrictions?', 'wc-asm' ),
		'label'			=> ' ',
        'type'          => 'checkbox',
		'class'         => 'time-enabled slider',
	),
	'time-display' => array(
		'title'			=> __( 'Current store time.', 'wc-asm' ),
		'type'			=> 'text',
		'disabled'		=> true,
		'class'			=> 'timelimited',
		'default'		=> wc_asm_get_timestamp(),
		'placeholder'	=> wc_asm_get_timestamp(),
	),
	'day-stop' => array(
		'title'			=> __( 'Day of week and time to stop shipping method', 'wc-asm' ),
		'type'			=> 'select',
		'class'			=> 'timelimited day-stop daypicker',
		'options'		=> wc_asm_day_of_week(),
	),
	'time-stop' => array(
		'type'			=> 'text',
		'class'			=> 'timelimited time-stop timepicker',
	),
	'day-begin' => array(
		'title'			=> __( 'Day of week and time to begin shipping method again', 'wc-asm' ),
		'type'			=> 'select',
		'class'			=> 'timelimited day-begin daypicker',
		'options'		=> wc_asm_day_of_week(),
	),
	'time-begin' => array(
		'type'			=> 'text',
		'class'			=> 'timelimited time-begin timepicker',
	),
));

if ( ! empty( $shipping_classes ) ) {
	$settings['class_costs'] = array(
		'title'			 => __( 'Shipping class costs', 'wc-asm' ),
		'type'			 => 'title',
		'default'        => '',
		'description'    => sprintf( __( 'These costs can optionally be added based on the <a href="%s">product shipping class</a>.', 'wc-asm' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ) ),
	);
	foreach ( $shipping_classes as $shipping_class ) {
		if ( ! isset( $shipping_class->term_id ) ) {
			continue;
		}
		$settings[ 'sc_' . $shipping_class->term_id . '_cost' ] = array(
			/* translators: %s: shipping class name */
			'title'       => sprintf( __( '"%s" shipping class cost', 'wc-asm' ), esc_html( $shipping_class->name ) ),
			'type'        => 'text',
			'placeholder' => __( 'N/A', 'wc-asm' ),
			'description' => $cost_desc,
			'default'     => $this->get_option( 'class_cost_' . $shipping_class->slug ), // Before 2.5.0, we used slug here which caused issues with long setting names
			'desc_tip'    => true,
			'class'		  => 'timelimited',
		);
	}
	$settings['no_class_cost'] = array(
		'title'       => __( 'No shipping class cost', 'wc-asm' ),
		'type'        => 'text',
		'placeholder' => __( 'N/A', 'wc-asm' ),
		'description' => $cost_desc,
		'default'     => '',
		'desc_tip'    => true,
	);
	$settings['type'] = array(
		'title' 		=> __( 'Calculation type', 'wc-asm' ),
		'type' 			=> 'select',
		'class'         => 'wc-enhanced-select',
		'default' 		=> 'class',
		'options' 		=> array(
			'class' 	=> __( 'Per class: Charge shipping for each shipping class individually', 'wc-asm' ),
			'order' 	=> __( 'Per order: Charge shipping for the most expensive shipping class', 'wc-asm' ),
		),
	);
}

/*
if ( ! empty( $categories ) ) {
	$settings['categories_costs'] = array(
		'title'			 => __( 'Shipping categories costs', 'wc-asm' ),
		'type'			 => 'title',
		'default'        => '',
		// 'description'    => sprintf( __( 'These costs can optionally be added based on the <a href="%s">product shipping class</a>.', 'wc-asm' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ) ),
	);

	foreach ( $cat_options as $key => $value ) {
		$settings[ $key . '_cost' ] = array(
			// translators: %s: product category name 
			'title'       => sprintf( __( '"%s" category cost', 'wc-asm' ), esc_html( $value ) ),
			'type'        => 'text',
			'placeholder' => __( 'N/A', 'wc-asm' ),
			'description' => $cost_desc,
			'default'     => $this->get_option( $key . '_cost' ), // Before 2.5.0, we used slug here which caused issues with long setting names
			'desc_tip'    => true,
			'class'		  => 'categorylimited',
		);
	}
	$settings['no_category_cost'] = array(
		'title'       => __( 'No category cost', 'wc-asm' ),
		'type'        => 'text',
		'placeholder' => __( 'N/A', 'wc-asm' ),
		'description' => $cost_desc,
		'default'     => '',
		'desc_tip'    => true,
	);
}
*/
return $settings;
