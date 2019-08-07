<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.theritesites.com
 * @since      1.0.0
 *
 * @package    WC_ASM
 * @subpackage WC_ASM/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WC_ASM
 * @subpackage WC_ASM/admin
 * @author     TheRiteSites <contact@theritesites.com>
 */
class WC_ASM_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Attach hooks that require WooCommerce first
	 * 
	 * @since	 1.0.0
	 */
	public function init_wc_hooks() {
		include_once WC()->plugin_path() . '/includes/abstracts/abstract-wc-shipping-method.php';
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wc-asm-shipping-method.php';

		add_action( 'woocommerce_load_shipping_methods', array( $this, 'register_shipping_method_test' ) );
		add_action( 'woocommerce_shipping_methods', array( $this, 'register_shipping_method' ) );
		add_filter( 'woocommerce_form_field_toggler', array( $this, 'wc_asm_toggler_handler' ), 10, 4 );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		global $pagenow;
		
		if ( is_admin() && 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] && isset( $_GET['tab'] ) && 'shipping' === $_GET['tab'] ) {
			wp_enqueue_style( $this->plugin_name . '-timepicker', plugin_dir_url( __FILE__ ) . 'css/jquery.timepicker.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wc-asm-admin.css', array(), $this->version, 'all' );
		}


	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		global $pagenow;

		global $wp_query, $post;
		
		if ( is_admin() && 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] && isset( $_GET['tab'] ) && 'shipping' === $_GET['tab'] ) {
			wp_enqueue_script( $this->plugin_name . '-timepicker', plugin_dir_url( __FILE__ ) . 'js/jquery.timepicker.js', array( 'jquery' ), $this->version, false );

			wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wc-asm-admin.js', array( 'jquery'/*, 'wc-shipping-zone-methods'*/ ), $this->version );
			wp_localize_script( $this->plugin_name, 'shippingZoneMethods2LocalizeScript', array(
				'debug'
			));
			wp_enqueue_script( $this->plugin_name );

		}
		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wc-asm-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the Shipping Method for the plugin
	 * 
	 * @since	 1.0.0
	 */
	public function register_shipping_method( $shipping_methods = array() ) {
		if ( ! empty( $shipping_methods ) ) {
			$shipping_methods['wc_asm'] = 'WC_ASM_Shipping_Method';
		}
		// error_log('here and wc_adm registered');
		return $shipping_methods;
	}

	public function register_shipping_method_test( $package ) {
		// error_log('in test method with package');
		// error_log( print_r(array()) );
		// error_log( print_r($package) );
	}
}
