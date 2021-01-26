<?php

/**
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.theritesites.com
 * @since             1.0.0
 * @package           WC_ASM
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Advanced Shipping Methods
 * Plugin URI:        https://www.theritesites.com/plugins/woocommerce-advanced-shipping-methods
 * Description:       When your shipping methods dont quite fit your work routines! Restrict by time, shipping class, category, or quantity.
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
define( 'WC_ASM_VERSION', '1.0.0' );

if ( ! function_exists( 'is_woocommerce_active' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wc-asm.php';

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
