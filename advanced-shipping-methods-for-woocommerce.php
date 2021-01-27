<?php

/**
 * @link              https://www.theritesites.com
 * @since             1.0.0
 * @package           ASM_WC
 *
 * @wordpress-plugin
 * Plugin Name:       Advanced Shipping Methods for WooCommerce
 * Plugin URI:        https://www.theritesites.com/plugins/
 * Description:       When your shipping methods dont quite fit your work routines! Restrict by time, shipping class, category, or quantity.
 * Version:           1.0.0
 * Author:            TheRiteSites
 * Author URI:        https://www.theritesites.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       asm-wc
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'ASM_WC_VERSION', '1.0.0' );

if ( ! function_exists( 'is_woocommerce_active' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-asm-wc.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_asm_wc() {

	$plugin = new ASM_WC();
	$plugin->run();

}
run_asm_wc();
