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

		// use unset($this->instance_form_fields['classes'] etc to remove inputs.

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

	protected function wc_asm_check_categories( $l_b_c = array(), $package = array() ) {
		$categories_quantities	= array();
		foreach ( $l_b_c as $categories ) {
			$cat_qtys = array(
				'min'	=> $this->get_option( $categories . '_qty_min' ),
				'max'	=> $this->get_option( $categories . '_qty_max' ),
			);
			$categories_quantities[$categories] = $cat_qtys;
			error_log( 'category: ' . $categories );
			error_log( '  min: ' . $cat_qtys['min'] );
			error_log( '  max: ' . $cat_qtys['max'] );
			
		}
		$cat_ship_qty = array();
		error_log(' from aqui' );
		// wp_die(print_r($package));
		foreach ( $package['contents'] as $item_id => $values ) {
			if ( $values['quantity'] > 0 && $values['data']->needs_shipping() ) {
				$flag = false;
				if ( ! empty( $values['data']->get_category_ids() ) ) {
					$prod_qty = $values['quantity'];
					foreach( $values['data']->get_category_ids() as $cat_id ) {
						if ( isset( $categories_quantities['pc_' . $cat_id ] ) ) { // . '_qty_min'] ) ) {
							if ( ! isset( $cat_ship_qty[ 'pc_' . $cat_id ] ) ) {
								$cat_ship_qty[ 'pc_' . $cat_id ] = 0;
							}
							$cat_ship_qty[ 'pc_' . $cat_id ] += $prod_qty;
							$flag = true;
							if ( defined('WP_DEBUG') && WP_DEBUG ) {
								error_log( 'Category found id: ' . $cat_id . '   quantity added: ' . $prod_qty );
								error_log( 'cat_ship_qty[pc_' . $cat_id . ']');
								error_log( 'Total quantity for category: ' . $cat_ship_qty['pc_' . $cat_id] );
							}
						}
					}
					
					if ( false === $flag ) {
						if ( defined('WP_DEBUG') && WP_DEBUG ) {
							error_log( 'Ship method is invalid, category is not in ship method.' );
						}
						return $flag;
					}
				}
				elseif ( 0 !== ( $parent = $values['data']->get_parent_id() ) ) {
					$prod_qty = $values['quantity'];
					$parent = wc_get_product( $values['data']->get_parent_id() );
					foreach( $parent->get_category_ids() as $cat_id ) {
						if ( isset( $categories_quantities['pc_' . $cat_id ] ) ) { // . '_qty_min'] ) ) {
							if ( ! isset( $cat_ship_qty[ 'pc_' . $cat_id ] ) ) {
								$cat_ship_qty[ 'pc_' . $cat_id ] = 0;
							}
							$cat_ship_qty[ 'pc_' . $cat_id ] += $prod_qty;
							$flag = true;
							if ( defined('WP_DEBUG') && WP_DEBUG ) {
								error_log( 'Checking parent product since variations have category issues' );
								error_log( 'Category found id: ' . $cat_id . '   quantity added: ' . $prod_qty );
								error_log( 'cat_ship_qty[pc_' . $cat_id . ']');
								error_log( 'Total quantity for category: ' . $cat_ship_qty['pc_' . $cat_id] );
							}
						}
					}
				}
				else {
					if ( defined('WP_DEBUG') && WP_DEBUG ) {
						error_log($values['data']->get_category_ids());
						error_log( 'Ship method is invalid, no category found but ship method has category limitation.' );
					}
					return $flag;
				}
			}
		}
		// $category will be array( 'pc_##' => array( 'min' => ##, 'max' => ##) ) ;
		foreach( $categories_quantities as $key => $arr ) {
			if ( ! isset( $cat_ship_qty[$key] ) && ( ! empty( $arr['min'] ) && 0 < (int)$arr['min'] ) ) {
				if ( defined('WP_DEBUG') && WP_DEBUG ) {
					error_log( 'Ship method is invalid, category minimum req not met. There is no products of this category in here.' );
				}
			return false;
			}
			elseif ( isset( $cat_ship_qty[$key] ) ) {
				if ( isset( $arr['min'] ) && ! empty( $arr['min'] ) && (int)$cat_ship_qty[$key] < (int)$arr['min'] ) {
					if ( defined('WP_DEBUG') && WP_DEBUG ) {
						error_log( 'Ship method is invalid, category minimum req not met.' );
					}
					return false;
				}
				elseif ( isset( $arr['max'] ) ){
					if ( 0 === $arr['max'] || '0' === $arr['max'] ) {
						if ( defined('WP_DEBUG') && WP_DEBUG ) {
							error_log( 'Ship method is invalid, category max is 0, effectively making this category invalid.' );
						}
						return false;
					}
					elseif( ! empty( $arr['max'] ) && (int)$cat_ship_qty[$key] > (int)$arr['max'] ) {
						if ( -1 === (int)$arr['max'] ) {
							if ( defined('WP_DEBUG') && WP_DEBUG ) {
								error_log( 'Ship method is valid, max is unlimited.' );
							}
							return true;
						}
						if ( defined('WP_DEBUG') && WP_DEBUG ) {
							error_log( 'Ship method is invalid, category max req not met.' );
						}
						return false;
					}
				}
			}
		}
	}

	protected function wc_asm_first_is_earlier( $first = array(), $second = array() ) {
		if ( $first['day'] === $second['day'] ) {
			if ( $first['hour'] < $second['hour'] ) {
				return true;
			}
			elseif ( $first['hour'] === $second['hour'] ) {
				if ( $first['minute'] < $second['minute'] ) {
					return true;
				}
				elseif ( $first['minute'] === $second['minute'] ) {
					if ( $first['seconds'] <= $second['seconds'] ) {
						return true;
					}
					else {
						return false;
					}
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}
		elseif ( $first['day'] < $second['day'] ) {
			return true;
		}
		else {
			return false;
		}
	}

	protected function wc_asm_check_time() {
		$stop_ship  = $this->get_option( 'day-stop' );
		$stop_time  = $this->get_option( 'time-stop' );
		
		$begin_ship = $this->get_option( 'day-begin' );
		$begin_time = $this->get_option( 'time-begin' );

		$stop_string  = $stop_ship . ' ' . $stop_time;
		$begin_string = $begin_ship . ' ' . $begin_time;

		$stop_datetime  = new WC_DateTime( $stop_string );
		$begin_datetime = new WC_DateTime( $begin_string );
		
		$stop_timestamp  = $stop_datetime->getOffsetTimestamp();
		$begin_timestamp = $begin_datetime->getOffsetTimestamp();

		$current_day     = current_time('N');
		$current_hour    = current_time('H');
		$current_minute  = current_time('i');
		$current_seconds = current_time('s');

		$stop_day      = $stop_datetime->format('N');
		$stop_hour     = $stop_datetime->format('H');
		$stop_minute   = $stop_datetime->format('i');
		$stop_seconds  = $stop_datetime->format('s');

		$begin_day     = $begin_datetime->format('N');
		$begin_hour    = $begin_datetime->format('H');
		$begin_minute  = $begin_datetime->format('i');
		$begin_seconds = $begin_datetime->format('s');

		$current_array = array( 'day' => $current_day, 'hour' => $current_hour, 'minute' => $current_minute, 'seconds' => $current_seconds );
		$stop_array    = array( 'day' => $stop_day, 'hour' => $stop_hour, 'minute' => $stop_minute, 'seconds' => $stop_seconds );
		$begin_array   = array( 'day' => $begin_day, 'hour' => $begin_hour, 'minute' => $begin_minute, 'seconds' => $begin_seconds );

		if ( defined('WP_DEBUG') && WP_DEBUG ) {

			error_log( 'stop shipping: ' . $stop_string );
			error_log( 'begin shipping: ' . $begin_string );
			
			error_log( 'current: ' . $current_day );
			error_log( 'stop:     ' . $stop_day );
			error_log( 'begin:   ' . $begin_day );

			error_log( 'current h: ' . $current_hour );
			error_log( 'stop h:     ' . $stop_hour );
			error_log( 'begin h:   ' . $begin_hour );

			error_log( 'current m: ' . $current_minute );
			error_log( 'stop m:     ' . $stop_minute );
			error_log( 'begin m:   ' . $begin_minute );

			error_log( 'current s: ' . $current_seconds );
			error_log( 'stop s:     ' . $stop_seconds );
			error_log( 'begin s:   ' . $begin_seconds );
		}

		if ( $this->wc_asm_first_is_earlier( $stop_array, $current_array ) && $this->wc_asm_first_is_earlier( $current_array, $begin_array ) ) {
			if ( defined('WP_DEBUG') && WP_DEBUG ) {
				error_log( 'stop is before current.' );
				error_log( 'current is before begin. Invalid.' ); // Reached here.
			}
			return false;
		}
		elseif ( $this->wc_asm_first_is_earlier( $stop_array, $begin_array ) ) {
			if ( defined('WP_DEBUG') && WP_DEBUG ) {
				error_log( 'stop is before begin.' );
			}
			if ( $this->wc_asm_first_is_earlier( $current_array, $begin_array ) ) {
				if ( defined('WP_DEBUG') && WP_DEBUG ) {
					error_log( 'current is before begin.' ); // Reached here.
				}
				if ( $this->wc_asm_first_is_earlier( $current_array, $stop_array ) ) {
					if ( defined('WP_DEBUG') && WP_DEBUG ) {
						error_log( 'current is before stop. Valid. ' );
					}
					return true;
				}
				else {
					if ( defined('WP_DEBUG') && WP_DEBUG ) {
						error_log( 'current is after stop. Invalid.' );
					}
					return false;
				}
			}
			if ( $this->wc_asm_first_is_earlier( $begin_array, $current_array ) ) {
				if ( defined('WP_DEBUG') && WP_DEBUG ) {
					error_log( 'begin is before current. Valid!' ); // Reached here.
				}
				return true;
			}
		}
		elseif ( $this->wc_asm_first_is_earlier( $begin_array, $current_array ) ) {
			if ( defined('WP_DEBUG') && WP_DEBUG ) {
				error_log( 'begin is earlier than current' );
			}
			if ( $this->wc_asm_first_is_earlier( $stop_array, $current_array ) ) {
				if ( defined('WP_DEBUG') && WP_DEBUG ) {
					error_log( 'stop is earlier than current' );
					error_log( 'both days are before the current time. Are we outside or inside the range?' );
				}
				if ( $this->wc_asm_first_is_earlier( $begin_array, $stop_array ) ) {
					if ( defined('WP_DEBUG') && WP_DEBUG ) {
						error_log( 'begin is earlier than stop.' );
						error_log( 'this means HUGE range, we are invalid.' ); // Reached here.
					}
					return false;
				}
				elseif ( $this->wc_asm_first_is_earlier( $stop_array, $begin_array ) ) {
					if ( defined('WP_DEBUG') && WP_DEBUG ) {
						error_log( 'stop is earlier than begin' );
						error_log( 'small range, we are okay. VALID.' ); // Reached here.
					}
					return true;
				}
			}
			if ( $this->wc_asm_first_is_earlier( $current_array, $stop_array ) ) {
				if ( defined('WP_DEBUG') && WP_DEBUG ) {
					error_log( 'current is earlier than stop. Valid.' );
				}
				return true;
			}
		}
		
		elseif ( $this->wc_asm_first_is_earlier( $current_array, $stop_array ) && ( $this->wc_asm_first_is_earlier( $begin_array, $current_array ) || $this->wc_asm_first_is_earlier( $stop_array, $begin_array ) ) ) {
			error_log( 'current is before stop.' );
			if ( $this->wc_asm_first_is_earlier( $begin_array, $current_array ) ) {
				error_log( 'begin is before current. Valid.' );
				return true;
			}
			if ( $this->wc_asm_first_is_earlier( $stop_array, $begin_array ) ) {
				error_log( 'stop is before begin, but all after current. Valid.' ); // Reached here.
				return true;
			}
		}
		// elseif ( $this->wc_asm_first_is_earlier( $current_array, $stop_array ) ) {
		// 	error_log( 'current is before stop. BUT begin is NOT before current AND stop is NOT before begin.' );
		// } // This is probably never reached from the section above catching everything else.
		else {
			error_log( 'fell through.' );
		}
		

		return true;
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
		
		$limited_by_categories	= $this->get_option( 'categories' );
		if ( ! empty( $limited_by_categories ) ) {
			$result = $this->wc_asm_check_categories( $limited_by_categories, $package );
			if ( false === $result ) {

				return; 
			}
		}

		// Add shipping class costs.
		$shipping_classes = WC()->shipping->get_shipping_classes();
		

		if ( ! empty( $shipping_classes ) ) {
			$found_shipping_classes = $this->find_shipping_classes( $package );
			$highest_class_cost     = 0;
			$limited_by_class		= $this->get_option( 'classes' );
			$limited_by_time		= $this->get_option( 'toggler' );
			$class_quantities		= array();

			if ( 'yes' === $limited_by_time ) {
				$result = $this->wc_asm_check_time();
				if ( $result === false ) {
					return;
				}
			}

			if ( ! empty( $limited_by_class ) ) {
				foreach ( $limited_by_class as $shipping_class ) {
					$class_quantities[$shipping_class] = $this->get_option( $shipping_class . '_qty' );
				}
			}

			foreach ( $found_shipping_classes as $shipping_class => $products ) {
				// Also handles BW compatibility when slugs were used instead of ids
				$shipping_class_term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
				$class_cost_string   = $shipping_class_term && $shipping_class_term->term_id ? $this->get_option( 'class_cost_' . $shipping_class_term->term_id, $this->get_option( 'class_cost_' . $shipping_class, '' ) ) : $this->get_option( 'no_class_cost', '' );
				$class_qty			 = array_sum( wp_list_pluck( $products, 'quantity' ) );
				$class_cost			 = array_sum( wp_list_pluck( $products, 'line_total' ) );


				if ( ! empty( $limited_by_class ) && ( -1 !== (int)$class_quantities['sc_' . $shipping_class_term->term_id] && (int)$class_qty > (int)$class_quantities['sc_' . $shipping_class_term->term_id] ) ) {
					if ( defined('WP_DEBUG') && WP_DEBUG ) {
						$arr = array( 'limited' => $limited_by_class, 'class_qts[]' => $class_quantities['sc_' . $shipping_class_term->term_id], 'class_qty' => $class_qty, 'class' => 'sc_' . $shipping_class_term->term_id );
						error_log( $arr );
						error_log( 'advanced shipping not available for cart due to quantity limits.');
					}
					return;
				}
				else {
					if ( empty( $limited_by_class) ) {
						if ( defined('WP_DEBUG') && WP_DEBUG ) {
							error_log( 'limited by class is empty, moving on.' );
						}
					}
					else {
						if ( defined('WP_DEBUG') && WP_DEBUG ) {
							error_log( 'limited by class passed its check.' );
						}
					}
				}

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

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log( 'has cost: ' . $has_costs );
			error_log( 'rate[cost]: ' . $rate['cost'] );
		}
		
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