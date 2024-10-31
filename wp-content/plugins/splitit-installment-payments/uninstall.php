<?php
/**
 * File - uninstall.php
 * Remove DB tables and clear options
 *
 * @package     Splitit_WooCommerce_Plugin
 */

// @if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$option_names = [
	'woocommerce_splitit_settings',
	'splitit_environment',
	'splitit_sandbox_new_login',
	'splitit_sandbox_merchant_id',
	'splitit_sandbox_client_id',
	'splitit_sandbox_client_secret',
	'splitit_sandbox_api_key',
	'splitit_sandbox_terminal_id',
	'splitit_production_new_login',
	'splitit_production_merchant_id',
	'splitit_production_client_id',
	'splitit_production_client_secret',
	'splitit_production_api_key',
	'splitit_production_terminal_id',
	'splitit_last_deactivation_time',
	'splitit_last_activation_time',
	'splitit_logged_user_data',
	'api_key',
	'merchant_name',
	'terminal_name',
	'merchant_settings'
];

$sites = is_multisite() ? get_sites() : [];

foreach ($option_names as $option_name) {
	delete_option($option_name);

	foreach ($sites as $site) {
		delete_blog_option($site->blog_id, $option_name);
	}
}

// @drop a custom database table
global $wpdb;

if ( is_multisite() ) {

	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}splitit_log" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}splitit_order_data_with_ipn" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}splitit_transactions_log" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}splitit_async_refund_log" );
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'splitit_refund_data%'" );

	foreach ( $sites as $site ) {
		$blog_id = $site->blog_id;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}{$blog_id}_splitit_log" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}{$blog_id}_splitit_order_data_with_ipn" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}{$blog_id}_splitit_transactions_log" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}{$blog_id}_splitit_async_refund_log" );

		$table_name = $wpdb->base_prefix . $blog_id . '_options';
		$wpdb->query( "DELETE FROM $table_name WHERE option_name LIKE 'splitit_refund_data%'" );
	}
} else {
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}splitit_log" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}splitit_order_data_with_ipn" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}splitit_transactions_log" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}splitit_async_refund_log" );
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'splitit_refund_data%'" );
}