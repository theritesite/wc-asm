<?php
/**
 * Functions used by plugins
 */
if ( ! class_exists( 'WC_ASM_Dependencies' ) )
	require_once 'class-wc-asm-dependencies.php';

/**
 * WC Detection
 */
if ( ! function_exists( 'is_woocommerce_active' ) ) {
	function is_woocommerce_active() {
		return WC_ASM_Dependencies::woocommerce_active_check();
	}
}