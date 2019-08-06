<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * The Shipping Method class
 *
 * @link       https://www.theritesites.com
 * @since      1.0.0
 * @package    WC_ASM
 * @subpackage WC_ASM/admin
 * @author     TheRiteSites <contact@theritesites.com>
 */
class WC_ASM_Shipping_Method extends WC_Shipping_Method {

    /**
	 * Constructor.
	 *
	 * @param int $instance_id
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                    = 'wc_asm';
		$this->instance_id 			 = absint( $instance_id );
		$this->method_title          = __( 'Advanced Flat Rate', 'wc-asm' );
		$this->method_description    = __( 'Lets you charge rates according to cart contents with time limitations.', 'wc-asm' );
		$this->supports              = array(
			'shipping-zones',
			'instance-settings',
			// 'instance-settings-modal',
		);
		$this->init();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }
    
    /**
	 * init user set variables.
	 */
	public function init() {
		$this->instance_form_fields = include( 'settings-wc-asm.php' );
		$this->title                = $this->get_option( 'title' );
		$this->tax_status           = $this->get_option( 'tax_status' );
		$this->cost                 = $this->get_option( 'cost' );
		$this->type                 = $this->get_option( 'type', 'class' );
	}
	
    /**
	 * Evaluate a cost from a sum/string.
	 * @param  string $sum
	 * @param  array  $args
	 * @return string
	 */
	protected function evaluate_cost( $sum, $args = array() ) {
		include_once( WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php' );

		// Allow 3rd parties to process shipping cost arguments
		$args           = apply_filters( 'woocommerce_evaluate_shipping_cost_args', $args, $sum, $this );
		$locale         = localeconv();
		$decimals       = array( wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'], ',' );
		$this->fee_cost = $args['cost'];

		// Expand shortcodes
		add_shortcode( 'fee', array( $this, 'fee' ) );

		$sum = do_shortcode( str_replace(
			array(
				'[qty]',
				'[cost]',
			),
			array(
				$args['qty'],
				$args['cost'],
			),
			$sum
		) );

		remove_shortcode( 'fee', array( $this, 'fee' ) );

		// Remove whitespace from string
		$sum = preg_replace( '/\s+/', '', $sum );

		// Remove locale from string
		$sum = str_replace( $decimals, '.', $sum );

		// Trim invalid start/end characters
		$sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );

		// Do the math
		return $sum ? WC_Eval_Math::evaluate( $sum ) : 0;
	}

	/**
	 * Work out fee (shortcode).
	 * @param  array $atts
	 * @return string
	 */
	public function fee( $atts ) {
		$atts = shortcode_atts( array(
			'percent' => '',
			'min_fee' => '',
			'max_fee' => '',
		), $atts, 'fee' );

		$calculated_fee = 0;

		if ( $atts['percent'] ) {
			$calculated_fee = $this->fee_cost * ( floatval( $atts['percent'] ) / 100 );
		}

		if ( $atts['min_fee'] && $calculated_fee < $atts['min_fee'] ) {
			$calculated_fee = $atts['min_fee'];
		}

		if ( $atts['max_fee'] && $calculated_fee > $atts['max_fee'] ) {
			$calculated_fee = $atts['max_fee'];
		}

		return $calculated_fee;
	}

	/**
	 * calculate_shipping function.
	 *
	 * @param array $package (default: array())
	 */
	public function calculate_shipping( $package = array() ) {
        $rate = array(
            'id'      => $this->get_rate_id(),
            'label'   => $this->title,
            'cost'    => 0,
            'package' => $package,
        );

            // Calculate the costs
            $has_costs = false; // True when a cost is set. False if all costs are blank strings.
            $cost      = $this->get_option( 'cost' );

			$shippable_qty = $this->get_package_item_qty( $package );
            if ( '' !== $cost ) {
                $has_costs    = true;
                $rate['cost'] = $this->evaluate_cost( $cost, array(
                    'qty'  => $shippable_qty,
                    'cost' => $package['contents_cost'],
                ) );
            }

            // Add shipping class costs.
            $shipping_classes = WC()->shipping->get_shipping_classes();
			
			// if ( false ) {

            if ( ! empty( $shipping_classes ) ) {
                $found_shipping_classes = $this->find_shipping_classes( $package );
				$highest_class_cost     = 0;
/*				$limited_by_class		= $this->get_option( 'classes' );
				$limited_by_time		= $this->get_option( 'toggler' );
				$class_quantities		= array();

				if ( ! empty( $limited_by_class ) ) {
					foreach ( $limited_by_class as $shipping_class ) {
						$class_quantities[$shipping_class] = $this->get_option( $shipping_class . '_qty' );
					}
				}

				if ( 'yes' === $limited_by_time ) {
					$stop_ship = $this->get_option( 'day-stop' );
					$stop_time = $this->get_option( 'time-stop' );

					$begin_ship = $this->get_option( 'day-begin' );
					$begin_time = $this->get_option( 'time-begin' );

					$stop_datetime  = new WC_DateTime( strtotime( $stop_ship . ' ' . $stop_time ) );
					$begin_datetime = new WC_DateTime( strtotime( $begin_ship . ' ' . $begin_time ) );
					
					$stop_timestamp  = $stop_datetime->getOffsetTimestamp();
					$begin_timestamp = $begin_datetime->getOffsetTimestamp();

					if ( $begin_timestamp > $stop_timestamp ) {
						// return;
					}

					// $wc_date = new WC_DateTime();
					// $store_timestamp = $wc_date->getOffsetTimestamp();
					// $day = $store_timestamp->format('D');
					// $hour = $store_timestamp->format('h');
					// $minute = $store_timestamp->format('i');
					// $seconds = $store_timestamp->format('s');
				}
*/
                foreach ( $found_shipping_classes as $shipping_class => $products ) {
                    // Also handles BW compatibility when slugs were used instead of ids
                    $shipping_class_term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
                    $class_cost_string   = $shipping_class_term && $shipping_class_term->term_id ? $this->get_option( 'class_cost_' . $shipping_class_term->term_id, $this->get_option( 'class_cost_' . $shipping_class, '' ) ) : $this->get_option( 'no_class_cost', '' );
					$class_qty			 = array_sum( wp_list_pluck( $products, 'quantity' ) );
					$class_cost			 = array_sum( wp_list_pluck( $products, 'line_total' ) );

					/*if ( ! empty( $limited_by_class ) && isset( $class_quantities['sc_' . $shipping_class_term->term_id] ) && ( -1 !== (int)$class_quantities['sc_' . $shipping_class_term->term_id] && (int)$class_qty >= (int)$class_quantities['sc_' . $shipping_class_term->term_id] ) ) {
						$arr = array( 'limited' => $limited_by_class, 'class_qts[]' => $class_quantities['sc_' . $shipping_class_term->term_id], 'class_qty' => $class_qty, 'class' => 'sc_' . $shipping_class_term->term_id );
						// wp_die(print_r($arr));
						error_log( $arr );
						error_log( 'advanced shipping not available for cart');
						return;
					}
*/
                    if ( '' === $class_cost_string ) {
                        continue;
                    }

                    $has_costs  = true;
                    $class_cost = $this->evaluate_cost( $class_cost_string, array(
                        'qty'  => $class_qty,
                        'cost' => $class_cost,
                    ) );

                    if ( 'class' === $this->type ) {
                        $rate['cost'] += $class_cost;
                    } else {
                        $highest_class_cost = $class_cost > $highest_class_cost ? $class_cost : $highest_class_cost;
                    }
                }

                if ( 'order' === $this->type && $highest_class_cost ) {
                    $rate['cost'] += $highest_class_cost;
                }
            }
        // }
            // Add the rate
            if ( $has_costs ) {
                $this->add_rate( $rate );
            }

            /**
             * Developers can add additional flat rates based on this one via this action since @version 2.4.
             *
             * Previously there were (overly complex) options to add additional rates however this was not user.
             * friendly and goes against what Flat Rate Shipping was originally intended for.
             *
             * This example shows how you can add an extra rate based on this flat rate via custom function:
             *
             * 		add_action( 'woocommerce_flat_rate_shipping_add_rate', 'add_another_custom_flat_rate', 10, 2 );
             *
             * 		function add_another_custom_flat_rate( $method, $rate ) {
             * 			$new_rate          = $rate;
             * 			$new_rate['id']    .= ':' . 'custom_rate_name'; // Append a custom ID.
             * 			$new_rate['label'] = 'Rushed Shipping'; // Rename to 'Rushed Shipping'.
             * 			$new_rate['cost']  += 2; // Add $2 to the cost.
             *
             * 			// Add it to WC.
             * 			$method->add_rate( $new_rate );
             * 		}.
             */
            do_action( 'woocommerce_' . $this->id . '_shipping_add_rate', $this, $rate );
        // }
    }

	/**
	 * Get items in package.
	 * @param  array $package
	 * @return int
	 */
	public function get_package_item_qty( $package ) {
		$total_quantity = 0;
		foreach ( $package['contents'] as $item_id => $values ) {
			if ( $values['quantity'] > 0 && $values['data']->needs_shipping() ) {
				$total_quantity += $values['quantity'];
			}
		}
		return $total_quantity;
	}

	/**
	 * Finds and returns shipping classes and the products with said class.
	 * @param mixed $package
	 * @return array
	 */
	public function find_shipping_classes( $package ) {
		$found_shipping_classes = array();

		foreach ( $package['contents'] as $item_id => $values ) {
			if ( $values['data']->needs_shipping() ) {
				$found_class = $values['data']->get_shipping_class();

				if ( ! isset( $found_shipping_classes[ $found_class ] ) ) {
					$found_shipping_classes[ $found_class ] = array();
				}

				$found_shipping_classes[ $found_class ][ $item_id ] = $values;
			}
		}

		return $found_shipping_classes;
	}
}