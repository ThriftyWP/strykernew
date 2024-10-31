<?php
/**
 * Booster for WooCommerce - Settings - One page Checkout
 *
 * @version 7.0.0
 * @author  Pluggabl LLC.
 * @package Booster_Plus_For_WooCommerce/settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$settings = array(
	array(
		'id'   => 'wcj_one_page_checkout_options',
		'type' => 'sectionend',
	),
	array(
		'id'      => 'wcj_one_page_checkout_options',
		'type'    => 'tab_ids',
		'tab_ids' => array(
			'wcj_one_page_checkout_general_options_tab' => __( 'General Options', 'woocommerce-jetpack' ),
		),
	),
	array(
		'id'   => 'wcj_one_page_checkout_general_options_tab',
		'type' => 'tab_start',
	),
	array(
		'title' => __( 'Options', 'woocommerce-jetpack' ),
		'type'  => 'title',
		'id'    => 'wcj_opc_options',
	),
	array(
		'title'   => __( 'Products', 'woocommerce-jetpack' ),
		'type'    => 'multiselect',
		'id'      => 'wcj_opc_global_ids',
		'default' => '',
		'class'   => 'chosen_select',
		'options' => wcj_get_products(),
		'desc'    => __( 'If you did not set product_ids on the shortcode [wcj_one_page_checkout], then the selected products will be used.', 'woocommerce-jetpack' ),
	),
	array(
		'title'    => __( 'Position Priority', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'Please select the possition priority for One page checkout. Set to zero to use default priority.', 'woocommerce-jetpack' ),
		'id'       => 'wcj_one_page_checkout_hooks_priority',
		'default'  => 50,
		'type'     => 'number',
	),
	array(
		'id'   => 'wcj_opc_global_ids',
		'type' => 'sectionend',
	),
	array(
		'id'   => 'wcj_one_page_checkout_general_options_tab',
		'type' => 'tab_end',
	),
);


return $settings;
