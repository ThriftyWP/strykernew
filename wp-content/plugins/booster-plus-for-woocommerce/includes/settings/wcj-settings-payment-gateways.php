<?php
/**
 * Booster for WooCommerce - Settings - Custom Gateways
 *
 * @version 7.0.0
 * @since  1.0.0
 * @author  Pluggabl LLC.
 * @package Booster_Plus_For_WooCommerce/settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wocommerce_checkout_settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout' );
$wocommerce_checkout_settings_url = '<a href="' . $wocommerce_checkout_settings_url . '">' . __( 'WooCommerce > Settings > Checkout', 'woocommerce-jetpack' ) . '</a>';
$settings                         = array(
	array(
		'id'   => 'wcj_payment_gateways_options',
		'type' => 'sectionend',
	),
	array(
		'id'      => 'wcj_payment_gateways_options',
		'type'    => 'tab_ids',
		'tab_ids' => array(
			'wcj_payment_gateways_gatways_options_tab'  => __( 'Gateways Options', 'woocommerce-jetpack' ),
			'wcj_payment_gateways_advanced_options_tab' => __( 'Advanced Options', 'woocommerce-jetpack' ),
		),
	),
	array(
		'id'   => 'wcj_payment_gateways_gatways_options_tab',
		'type' => 'tab_start',
	),
	array(
		'title' => __( 'Custom Payment Gateways Options', 'woocommerce-jetpack' ),
		'type'  => 'title',
		'id'    => 'wcj_custom_payment_gateways_options',
		'desc'  => __( 'This section lets you set number of custom payment gateways to add.', 'woocommerce-jetpack' )
				/* translators: %s: translators Added */
			. ' ' . sprintf( __( 'After setting the number, visit %s to set each gateway options.', 'woocommerce-jetpack' ), $wocommerce_checkout_settings_url ),
	),
	array(
		'title'             => __( 'Number of Gateways', 'woocommerce-jetpack' ),
		'desc'              => apply_filters( 'booster_message', '', 'desc' ),
		'desc_tip'          => __( 'Number of custom payments gateways to be added. All settings for each new gateway are in WooCommerce > Settings > Checkout.', 'woocommerce-jetpack' ),
		'id'                => 'wcj_custom_payment_gateways_number',
		'default'           => 1,
		'type'              => 'number',
		'custom_attributes' => is_array( apply_filters( 'booster_message', '', 'readonly' ) ) ?
			apply_filters( 'booster_message', '', 'readonly' ) : array(
				'step' => '1',
				'min'  => '1',
			),
	),
);
$payment_gateways_num             = apply_filters( 'booster_option', 1, wcj_get_option( 'wcj_custom_payment_gateways_number', 1 ) );
for ( $i = 1; $i <= $payment_gateways_num;  $i++ ) {
	$settings = array_merge(
		$settings,
		array(
			array(
				'title'   => __( 'Admin Title Custom Gateway', 'woocommerce-jetpack' ) . ' #' . $i,
				'id'      => 'wcj_custom_payment_gateways_admin_title_' . $i,
				'default' => __( 'Custom Gateway', 'woocommerce-jetpack' ) . ' #' . $i,
				'type'    => 'text',
			),
		)
	);
}
$settings = array_merge(
	$settings,
	array(
		array(
			'id'   => 'wcj_custom_payment_gateways_options',
			'type' => 'sectionend',
		),
		array(
			'id'   => 'wcj_payment_gateways_gatways_options_tab',
			'type' => 'tab_end',
		),
		array(
			'id'   => 'wcj_payment_gateways_advanced_options_tab',
			'type' => 'tab_start',
		),
		array(
			'title' => __( 'Advanced Options', 'woocommerce-jetpack' ),
			'type'  => 'title',
			'id'    => 'wcj_custom_payment_gateways_advanced_options',
		),
		array(
			'title'    => __( 'Gateways Input Fields', 'woocommerce-jetpack' ),
			'desc'     => __( 'Add "Delete" button', 'woocommerce-jetpack' ),
			'desc_tip' => __( 'This will add "Delete" button to custom payment gateways input fields admin order meta box.', 'woocommerce-jetpack' ),
			'id'       => 'wcj_custom_payment_gateways_input_fields_delete_button',
			'default'  => 'no',
			'type'     => 'checkbox',
		),
		array(
			'id'   => 'wcj_custom_payment_gateways_advanced_options',
			'type' => 'sectionend',
		),
		array(
			'id'   => 'wcj_payment_gateways_advanced_options_tab',
			'type' => 'tab_end',
		),
	)
);
return $settings;
