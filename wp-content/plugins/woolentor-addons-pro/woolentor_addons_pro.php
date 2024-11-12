<?php
/**
 * Plugin Name: ShopLentor Pro – WooCommerce Builder for Elementor & Gutenberg
 * Description: An all-in-one WooCommerce solution to create a beautiful WooCommerce store.
 * Plugin URI: 	https://woolentor.com/
 * Version: 	2.5.2
 * Author: 		HasThemes
 * Author URI: 	https://hasthemes.com/plugins/woolentor-pro-woocommerce-page-builder/
 * License:  	GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: woolentor-pro
 * Domain Path: /languages
 * WC tested up to: 9.3.3
 * Elementor tested up to: 3.24.7
 * Elementor Pro tested up to: 3.24.4
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'WOOLENTOR_VERSION_PRO', '2.5.2' );
define( 'WOOLENTOR_ADDONS_PL_ROOT_PRO', __FILE__ );
define( 'WOOLENTOR_ADDONS_PL_URL_PRO', plugins_url( '/', WOOLENTOR_ADDONS_PL_ROOT_PRO ) );
define( 'WOOLENTOR_ADDONS_PL_PATH_PRO', plugin_dir_path( WOOLENTOR_ADDONS_PL_ROOT_PRO ) );
define( 'WOOLENTOR_ADDONS_DIR_URL_PRO', plugin_dir_url( WOOLENTOR_ADDONS_PL_ROOT_PRO ) );
define( 'WOOLENTOR_TEMPLATE_PRO', trailingslashit( WOOLENTOR_ADDONS_PL_PATH_PRO . 'includes/templates' ) );

// Required File
require_once ( WOOLENTOR_ADDONS_PL_PATH_PRO.'includes/base.php' );
\WooLentorPro\woolentor_pro();

// Compatible With WooCommerce Custom Order Tables
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );