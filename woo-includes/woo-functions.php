<?php
/**
 * Functions used by plugins
 */
if ( ! class_exists( 'ASM_WC_Dependencies' ) )
	require_once 'class-asm-wc-dependencies.php';

/**
 * WC Detection
 */
if ( ! function_exists( 'is_woocommerce_active' ) ) {
	function is_woocommerce_active() {
		return ASM_WC_Dependencies::woocommerce_active_check();
	}
}