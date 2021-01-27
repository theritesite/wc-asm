<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cost_desc = __( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'asm-wc' ) . '<br/><br/>' . __( 'Use <code>[qty]</code> for the number of items, <br/><code>[cost]</code> for the total cost of items, and <code>[fee percent="10" min_fee="20" max_fee=""]</code> for percentage based fees.', 'asm-wc' );

$shipping_classes = WC()->shipping->get_shipping_classes();

$categories = wp_count_terms( 'product_cat' );

if ( ! function_exists( 'asm_wc_day_of_week' ) ) {
	function asm_wc_day_of_week() {
		$week = array(
			'sun'	=> __( 'Sunday', 'asm-wc' ),
			'mon'	=> __( 'Monday', 'asm-wc' ),
			'tue'	=> __( 'Tuesday', 'asm-wc' ),
			'wed'	=> __( 'Wednesday', 'asm-wc' ),
			'thu'	=> __( 'Thursday', 'asm-wc' ),
			'fri'	=> __( 'Friday', 'asm-wc' ),
			'sat'	=> __( 'Saturday', 'asm-wc' ),
		);

		return $week;
	}
}

if ( ! function_exists( 'asm_wc_shipping_classes_array' ) ) {
	function asm_wc_shipping_classes_array() {
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

if ( ! function_exists( 'asm_wc_get_timestamp' ) ) {
	function asm_wc_get_timestamp() {
		return current_time('D h:i:s A');
	}
}

if ( ! function_exists( 'asm_wc_product_categories' ) ) {
	function asm_wc_product_categories() {
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
		'title' 		=> __( 'Method title', 'asm-wc' ),
		'type' 			=> 'text',
		'description' 	=> __( 'This controls the title which the user sees during checkout.', 'asm-wc' ),
		'default'		=> __( 'Advanced flat rate', 'asm-wc' ),
		'desc_tip'		=> true,
	),
	'tax_status' => array(
		'title' 		=> __( 'Tax status', 'asm-wc' ),
		'type' 			=> 'select',
		'class'         => 'wc-enhanced-select',
		'default' 		=> 'taxable',
		'options'		=> array(
			'taxable' 	=> __( 'Taxable', 'asm-wc' ),
			'none' 		=> _x( 'None', 'Tax status', 'asm-wc' ),
		),
	),
	'cost' => array(
		'title' 		=> __( 'Cost', 'asm-wc' ),
		'type' 			=> 'text',
		'placeholder'	=> '',
		'description'	=> $cost_desc,
		'default'		=> '0',
		'desc_tip'		=> true,
	),
);

if ( ! empty( $categories ) ) {

	$cat_options = asm_wc_product_categories();

	$settings['categories'] = array(
		'title'			=> __( 'Categories', 'asm-wc' ),
		'type'			=> 'multiselect',
		'description'	=> __( 'controls which product categories should determine when this shipping method is available.', 'asm-wc' ),
		'class'			=> 'multiselect-asm categories',
		'options'		=> $cat_options,
	);

	foreach( $cat_options as $key => $value ) {
		$settings[ $key . '_qty_min' ] = array(
			'parent_id'		=> 'woocommerce_asm_wc_categories',
			'type'			=> 'text',
			'title'			=> $value . ' quantity minimum',
			'description'	=> '-1, 0 or blank for no minimum.',
		);
		$settings[ $key . '_qty_max' ] = array(
			'parent_id'		=> 'woocommerce_asm_wc_categories',
			'type'			=> 'text',
			'title'			=> $value . ' quantity maximum',
			'description'	=> '-1 or blank for unlimited.',
		);
	}
}

if ( ! empty( $shipping_classes ) ) {
	$settings['classes'] = array(
        'title'             => __( 'Shipping Class(es)', 'asm-wc' ),
        'type'              => 'multiselect',
        'description'       => __( 'Controls which shipping classes should determine when this shipping method is available. If no class is selected, any shipping class will be valid for any quantity.', 'asm-wc' ),
        'class'             => 'multiselect-asm classes',
		'options'			=> asm_wc_shipping_classes_array(),
	);

	foreach( $shipping_classes as $shipping_class ) {
		if ( ! isset( $shipping_class->term_id ) ) {
			continue;
		}
		$settings['sc_' . $shipping_class->term_id . '_qty'] = array(
			'parent_id'		=> 'woocommerce_asm_wc_classes',
			'type'			=> 'text',
			'title'			=> $shipping_class->name . ' quantity limit',
			'description'	=> '-1 for unlimited.',
		);
	}
}
else {
	$settings['classes'] = array(
        'title'             => __( 'Shipping Class(es)', 'asm-wc' ),
		'type'              => 'text',
		'disabled'			=> true,
		'placeholder'		=> __( 'Define shipping classes to use this feature!', 'asm-wc' ),
        'description'       => __( 'Controls which shipping classes should determine when this shipping method is available. If no class is selected, any shipping class will be valid for any quantity.', 'asm-wc' ),
	);
}

$settings = array_merge( $settings, array(
    'toggler' => array(
		'title'         => __( 'Enable time restrictions?', 'asm-wc' ),
		'label'			=> ' ',
        'type'          => 'checkbox',
		'class'         => 'time-enabled slider',
	),
	'time-display' => array(
		'title'			=> __( 'Current store time.', 'asm-wc' ),
		'type'			=> 'text',
		'disabled'		=> true,
		'class'			=> 'timelimited',
		'default'		=> asm_wc_get_timestamp(),
		'placeholder'	=> asm_wc_get_timestamp(),
	),
	'day-stop' => array(
		'title'			=> __( 'Day of week and time to stop shipping method', 'asm-wc' ),
		'type'			=> 'select',
		'class'			=> 'timelimited day-stop daypicker',
		'options'		=> asm_wc_day_of_week(),
	),
	'time-stop' => array(
		'type'			=> 'text',
		'class'			=> 'timelimited time-stop timepicker',
	),
	'day-begin' => array(
		'title'			=> __( 'Day of week and time to begin shipping method again', 'asm-wc' ),
		'type'			=> 'select',
		'class'			=> 'timelimited day-begin daypicker',
		'options'		=> asm_wc_day_of_week(),
	),
	'time-begin' => array(
		'type'			=> 'text',
		'class'			=> 'timelimited time-begin timepicker',
	),
));

if ( ! empty( $shipping_classes ) ) {
	$settings['class_costs'] = array(
		'title'			 => __( 'Shipping class costs', 'asm-wc' ),
		'type'			 => 'title',
		'default'        => '',
		'description'    => sprintf( __( 'These costs can optionally be added based on the <a href="%s">product shipping class</a>.', 'asm-wc' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ) ),
	);
	foreach ( $shipping_classes as $shipping_class ) {
		if ( ! isset( $shipping_class->term_id ) ) {
			continue;
		}
		$settings[ 'sc_' . $shipping_class->term_id . '_cost' ] = array(
			/* translators: %s: shipping class name */
			'title'       => sprintf( __( '"%s" shipping class cost', 'asm-wc' ), esc_html( $shipping_class->name ) ),
			'type'        => 'text',
			'placeholder' => __( 'N/A', 'asm-wc' ),
			'description' => $cost_desc,
			'default'     => $this->get_option( 'class_cost_' . $shipping_class->slug ), // Before 2.5.0, we used slug here which caused issues with long setting names
			'desc_tip'    => true,
			'class'		  => 'timelimited',
		);
	}
	$settings['no_class_cost'] = array(
		'title'       => __( 'No shipping class cost', 'asm-wc' ),
		'type'        => 'text',
		'placeholder' => __( 'N/A', 'asm-wc' ),
		'description' => $cost_desc,
		'default'     => '',
		'desc_tip'    => true,
	);
	$settings['type'] = array(
		'title' 		=> __( 'Calculation type', 'asm-wc' ),
		'type' 			=> 'select',
		'class'         => 'wc-enhanced-select',
		'default' 		=> 'class',
		'options' 		=> array(
			'class' 	=> __( 'Per class: Charge shipping for each shipping class individually', 'asm-wc' ),
			'order' 	=> __( 'Per order: Charge shipping for the most expensive shipping class', 'asm-wc' ),
		),
	);
}

/*
if ( ! empty( $categories ) ) {
	$settings['categories_costs'] = array(
		'title'			 => __( 'Shipping categories costs', 'asm-wc' ),
		'type'			 => 'title',
		'default'        => '',
		// 'description'    => sprintf( __( 'These costs can optionally be added based on the <a href="%s">product shipping class</a>.', 'asm-wc' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ) ),
	);

	foreach ( $cat_options as $key => $value ) {
		$settings[ $key . '_cost' ] = array(
			// translators: %s: product category name 
			'title'       => sprintf( __( '"%s" category cost', 'asm-wc' ), esc_html( $value ) ),
			'type'        => 'text',
			'placeholder' => __( 'N/A', 'asm-wc' ),
			'description' => $cost_desc,
			'default'     => $this->get_option( $key . '_cost' ), // Before 2.5.0, we used slug here which caused issues with long setting names
			'desc_tip'    => true,
			'class'		  => 'categorylimited',
		);
	}
	$settings['no_category_cost'] = array(
		'title'       => __( 'No category cost', 'asm-wc' ),
		'type'        => 'text',
		'placeholder' => __( 'N/A', 'asm-wc' ),
		'description' => $cost_desc,
		'default'     => '',
		'desc_tip'    => true,
	);
}
*/
return $settings;
