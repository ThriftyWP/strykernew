<?php
	/**
	 * Plugin Name: Variation Swatches for WooCommerce - Pro
	 * Plugin URI: https://wordpress.org/plugins/woo-variation-swatches/
	 * Description: Advance features of Variation Swatches for WooCommerce. Requires WooCommerce 5.6+
	 * Author: Emran Ahmed
	 * Version: 2.1.4
	 * Requires PHP: 7.4
	 * Requires at least: 5.9
	 * Tested up to: 6.6
	 * WC requires at least: 7.5
	 * WC tested up to: 9.2
	 * Text Domain: woo-variation-swatches-pro
	 * Domain Path: /languages
	 * Author URI: https://getwooplugins.com/
	 * Requires Plugins: woocommerce, woo-variation-swatches
	 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WOO_VARIATION_SWATCHES_PRO_PLUGIN_VERSION' ) ) {
	define( 'WOO_VARIATION_SWATCHES_PRO_PLUGIN_VERSION', '2.1.4' );
}

if ( ! defined( 'WOO_VARIATION_SWATCHES_PRO_PLUGIN_FILE' ) ) {
	define( 'WOO_VARIATION_SWATCHES_PRO_PLUGIN_FILE', __FILE__ );
}

	/**
	 * Show Required WooCommerce Notice
	 *
	 * @return void
	 */
function woo_variation_swatches_pro_wc_requirement_notice() {

	if ( ! class_exists( 'WooCommerce' ) ) {

		$args = array(
			'tab' => 'plugin-information',
			'plugin' => 'woocommerce',
			'TB_iframe' => 'true',
			'width' => '640',
			'height' => '500',
		);

		printf(
			'<div class="%1$s"><p>%2$s <a class="thickbox open-plugin-details-modal" href="%3$s"><strong>%4$s</strong></a></p></div>',
			'notice notice-error',
			wp_kses( __( '<strong>Variation Swatches for WooCommerce - Pro</strong> is an add-on of ', 'woo-variation-swatches-pro' ), array( 'strong' => array() ) ),
			esc_url( add_query_arg( $args, admin_url( 'plugin-install.php' ) ) ),
			esc_html__( 'WooCommerce', 'woo-variation-swatches-pro' )
		);
	}

	if ( ! class_exists( 'Woo_Variation_Swatches' ) ) {

		$args = array(
			'tab' => 'plugin-information',
			'plugin' => 'woo-variation-swatches',
			'TB_iframe' => 'true',
			'width' => '640',
			'height' => '500',
		);

		printf(
			'<div class="%1$s"><p>%2$s <a class="thickbox open-plugin-details-modal" href="%3$s"><strong>%4$s</strong></a></p></div>',
			'notice notice-error',
			wp_kses( __( '<strong>Variation Swatches for WooCommerce - Pro</strong> is an add-on of ', 'woo-variation-swatches-pro' ), array( 'strong' => array() ) ),
			esc_url( add_query_arg( $args, admin_url( 'plugin-install.php' ) ) ),
			esc_html__( 'Variation Swatches for WooCommerce', 'woo-variation-swatches-pro' )
		);
	}
}

	/**
	 * Make High-Performance order storage compatible
	 *
	 * @return void
	 */
function woo_variation_swatches_pro_hpos_compatibility() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}

	add_action( 'before_woocommerce_init', 'woo_variation_swatches_pro_hpos_compatibility' );

	add_action( 'admin_notices', 'woo_variation_swatches_pro_wc_requirement_notice' );

	/**
	 * Woo_Variation_Swatches_Pro instance.
	 *
	 * @return Woo_Variation_Swatches_Pro
	 */
function woo_variation_swatches_pro() {

	// Include the main class.
	if ( ! class_exists( 'Woo_Variation_Swatches_Pro', false ) ) {
		require_once dirname( __FILE__ ) . '/includes/class-woo-variation-swatches-pro.php';
	}

	return Woo_Variation_Swatches_Pro::instance();
}
