<?php
/**
 * Booster for WooCommerce - Settings - Cart Custom Info
 *
 * @version 7.0.0
 * @since  1.0.0
 * @author  Pluggabl LLC.
 * @todo    (maybe) 'Hide "Note: Shipping and taxes are estimated..." message on Cart page' - `wcj_cart_hide_shipping_and_taxes_estimated_message`
 * @package Booster_Plus_For_WooCommerce/settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$settings = array(
	array(
		'id'   => 'wcj_cart_info_options',
		'type' => 'sectionend',
	),
	array(
		'id'      => 'wcj_cart_info_options',
		'type'    => 'tab_ids',
		'tab_ids' => array(
			'wcj_cart_info_info_blocks_tab'      => __( 'Info Blocks', 'woocommerce-jetpack' ),
			'wcj_cart_info_cart_items_table_tab' => __( 'Cart Items Table', 'woocommerce-jetpack' ),
		),
	),
	array(
		'id'   => 'wcj_cart_info_info_blocks_tab',
		'type' => 'tab_start',
	),
	// Cart Custom Info Options.
	array(
		'title' => __( 'Cart Custom Info Blocks', 'woocommerce-jetpack' ),
		'type'  => 'title',
		'id'    => 'wcj_cart_custom_info_options',
		'desc'  => __( 'This feature allows you to add a final checkpoint for your customers before they proceed to payment.', 'woocommerce-jetpack' ) . '<br>' .
			__( 'Show custom information at on the cart page using Booster\'s various shortcodes and give your customers a seamless cart experience.', 'woocommerce-jetpack' ) . '<br>' .
			__( 'For example, show them the total weight of their items, any additional fees or taxes, or a confirmation of the address their products are being sent to.', 'woocommerce-jetpack' ),
	),
	array(
		'title'             => __( 'Total Blocks', 'woocommerce-jetpack' ),
		'id'                => 'wcj_cart_custom_info_total_number',
		'default'           => 1,
		'type'              => 'custom_number',
		'desc'              => apply_filters( 'booster_message', '', 'desc' ),
		'custom_attributes' => apply_filters( 'booster_message', '', 'readonly' ),
	),
	array(
		'id'   => 'wcj_cart_custom_info_options',
		'type' => 'sectionend',
	),
);
$custom_info_num = apply_filters( 'booster_option', 1, wcj_get_option( 'wcj_cart_custom_info_total_number', 1 ) );
for ( $i = 1; $i <= $custom_info_num;  $i++ ) {
		$settings = array_merge(
			$settings,
			array(
				array(
					'title' => __( 'Info Block', 'woocommerce-jetpack' ) . ' #' . $i,
					'type'  => 'title',
					'id'    => 'wcj_cart_custom_info_options_' . $i,
				),
				array(
					'title'   => __( 'Content', 'woocommerce-jetpack' ),
					'id'      => 'wcj_cart_custom_info_content_' . $i,
					'default' => '[wcj_cart_items_total_weight before="Total weight: " after=" kg"]',
					'type'    => 'textarea',
					'css'     => 'width:100%;height:200px;',
				),
				array(
					'title'   => __( 'Position', 'woocommerce-jetpack' ),
					'id'      => 'wcj_cart_custom_info_hook_' . $i,
					'default' => 'woocommerce_after_cart_totals',
					'type'    => 'select',
					'options' => wcj_get_cart_filters(),
				),
				array(
					'title'   => __( 'Position Order (i.e. Priority)', 'woocommerce-jetpack' ),
					'id'      => 'wcj_cart_custom_info_priority_' . $i,
					'default' => 10,
					'type'    => 'number',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wcj_cart_custom_info_options_' . $i,
				),
			)
		);
}
$settings = array_merge(
	$settings,
	array(
		array(
			'id'   => 'wcj_cart_info_info_blocks_tab',
			'type' => 'tab_end',
		),
		array(
			'id'   => 'wcj_cart_info_cart_items_table_tab',
			'type' => 'tab_start',
		),
		// Cart Items Table Custom Info Options.
		array(
			'title' => __( 'Cart Items Table Custom Info', 'woocommerce-jetpack' ),
			'type'  => 'title',
			'id'    => 'wcj_cart_custom_info_item_options',
			'desc'  => '',
		),
		array(
			'title'    => __( 'Add to Each Item Name', 'woocommerce-jetpack' ),
			'desc_tip' => __( 'You can use shortcodes here. E.g.: [wcj_product_sku]. Leave blank to disable.', 'woocommerce-jetpack' ),
			'id'       => 'wcj_cart_custom_info_item',
			'default'  => '',
			'type'     => 'textarea',
			'css'      => 'width:100%;height:100px;',
		),
		array(
			'id'   => 'wcj_cart_custom_info_item_options',
			'type' => 'sectionend',
		),
		array(
			'id'   => 'wcj_cart_abandonment_email_options_tab',
			'type' => 'tab_end',
		),
	)
);
return $settings;
