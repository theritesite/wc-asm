<?php

/**
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.theritesites.com
 * @since             1.0.1
 * @package           WC_ASM
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Advanced Shipping Methods
 * Plugin URI:        https://www.theritesites.com/plugins/woocommerce-advanced-shipping-methods
 * Description:       When your shipping methods dont quite fit your work routines!
 * Version:           1.0.0
 * Author:            TheRiteSites
 * Author URI:        https://www.theritesites.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-asm
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'WC_ASM_VERSION', '1.0.1' );

define( 'WC_ASM_UPDATER_URL', 'https://www.theritesites.com' );

define( 'WC_ASM_ITEM_ID', 0000 );

define( 'WC_ASM_LICENSE_PAGE', 'the_rite_plugins_settings' );

define( 'WC_ASM_ITEM_NAME', 'WooCommerce Advanced Shipping Methods' );

define( 'WC_ASM_LICENSE_KEY', 'wc_asm_license_key' );

define( 'WC_ASM_LICENSE_STATUS', 'wc_asm_license_status' );

if ( file_exists( __DIR__ . '/cmb2/init.php' ) ) {
	require_once __DIR__ . '/cmb2/init.php';
}

if ( ! function_exists( 'is_woocommerce_active' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wc-asm-activator.php
 */
// function activate_wc_asm() {
// 	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-asm-activator.php';
// 	WC_ASM_Activator::activate();
// }

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wc-asm-deactivator.php
 */
// function deactivate_wc_asm() {
// 	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-asm-deactivator.php';
// 	WC_ASM_Deactivator::deactivate();
// }

// register_activation_hook( __FILE__, 'activate_wc_asm' );
// register_deactivation_hook( __FILE__, 'deactivate_wc_asm' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wc-asm.php';


/**
 * Inits updater class to talk to https://www.theritesites.com for updates
 * 
 * @since 1.0.0
 */
function trs_wc_asm_update_check() {

	if( !class_exists( 'WC_ASM_Settings' ) ) {
		// load our custom updater
		include( plugin_dir_path( __FILE__ ) . '/includes/class-wc-asm-settings.php' );
	}

	if( !class_exists( 'WC_ASM_Plugin_Updater' ) ) {
		// load our custom updater
		include( plugin_dir_path( __FILE__ ) . '/includes/class-wc-asm-plugin-updater.php' );
	}

	if( class_exists( 'WC_ASM_Settings' ) ) {
		$license_key = WC_ASM_Settings::get_value(WC_ASM_LICENSE_KEY);
	}
	
	else {
		$opts = trim(get_option('the_rite_plugins_settings', false));

		$key = WC_ASM_LICENSE_KEY;
		if ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$license_key = $opts[ $key ];
		}
	}
	
	if( !class_exists( 'WC_ASM_Plugin_Updater' ) ) {
		return;
	}

	$plugin_updater = new WC_ASM_Plugin_Updater( WC_ASM_UPDATER_URL, __FILE__, array(
						'version'	=> WC_ASM_VERSION,
						'license'	=> $license_key,
						'item_id'	=> WC_ASM_ITEM_ID,
						'author'	=> 'TheRiteSites',
						'url'		=> home_url()
			)
	);

}
add_action( 'plugins_loaded', 'trs_wc_asm_update_check');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wc_asm() {

	$plugin = new WC_ASM();
	$plugin->run();

}
run_wc_asm();
