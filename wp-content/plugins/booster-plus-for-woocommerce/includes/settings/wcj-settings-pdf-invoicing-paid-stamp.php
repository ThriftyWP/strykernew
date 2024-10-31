<?php
/**
 * Booster for WooCommerce - Settings - PDF Invoicing - Paid Stamp
 *
 * @version 7.0.0
 * @author  Pluggabl LLC.
 * @package Booster_Plus_For_WooCommerce/settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$available_gateways = WC()->payment_gateways->payment_gateways();
foreach ( $available_gateways as $key => $gateway ) {
	$available_gateways_options_array[ $key ] = $gateway->title;
}
$settings = array(
	array(
		'type'              => 'module_head',
		'title'             => __( 'Paid Stamp', 'woocommerce-jetpack' ),
		'desc'              => __( 'PDF Invoicing: Paid Stamp Settings' ),
		'icon'              => 'pr-sm-icn.png',
		'module_reset_link' => '<a style="width:auto;" onclick="return confirm(\'' . __( 'Are you sure? This will reset module to default settings.', 'woocommerce-jetpack' ) . '\')" class="wcj_manage_settting_btn wcj_tab_end_save_btn" href="' . add_query_arg(
			array(
				'wcj_reset_settings' => $this->id,
				'wcj_reset_settings-' . $this->id . '-nonce' => wp_create_nonce( 'wcj_reset_settings' ),
			)
		) . '">' . __( 'Reset settings', 'woocommerce-jetpack' ) . '</a>',
	),
);

$settings = array_merge(
	$settings,
	array(
		array(
			'id'   => 'pdf_invoicing_paid_stamp_options',
			'type' => 'sectionend',
		),
		array(
			'id'      => 'pdf_invoicing_paid_stamp_options',
			'type'    => 'tab_ids',
			'tab_ids' => array(
				'pdf_invoicing_paid_stamp_invoice_tab' => __( 'Invoice', 'woocommerce-jetpack' ),
			),
		),
		array(
			'id'   => 'pdf_invoicing_paid_stamp_invoice_tab',
			'type' => 'tab_start',
		),
		array(
			'title' => 'Invoice',
			'type'  => 'title',
			'desc'  => '',
			'id'    => 'wcj_invoicing_invoice_paid_stamp',
		),
		array(
			'title'   => __( 'Paid Stamp', 'woocommerce-jetpack' ),
			'desc'    => '<strong>' . __( 'Enable', 'woocommerce-jetpack' ) . '</strong>',
			'id'      => 'wcj_invoicing_invoice_paid_stamp_enabled',
			'default' => 'no',
			'type'    => 'checkbox',
		),
		array(
			'title'    => __( 'Custom Paid Stamp', 'woocommerce-jetpack' ),
			'id'       => 'wcj_invoicing_invoice_custom_paid_stamp',
			'default'  => '',
			'type'     => 'text',
			'desc'     => sprintf(
				/* translators: %s: search term */
				__( 'Enter a local URL to an image you want to show in the invoice\'s paid stamp. Upload your image using the <a href="%s">media uploader</a>.', 'woocommerce-jetpack' ),
				admin_url( 'media-new.php' )
			) .
			wcj_get_invoicing_current_image_path_desc( 'wcj_invoicing_invoice_custom_paid_stamp' ),
			'desc_tip' => __( 'Leave blank to use the default', 'woocommerce-jetpack' ),
			'class'    => 'widefat',
		),
		array(
			'title'             => __( 'Payment gateways to include', 'woocommerce' ),
			'id'                => 'wcj_invoicing_invoice_paid_stamp_payment_gateways',
			'type'              => 'multiselect',
			'class'             => 'chosen_select',
			'css'               => 'width: 450px;',
			'default'           => '',
			'options'           => $available_gateways_options_array,
			'custom_attributes' => array( 'data-placeholder' => __( 'Select some gateways. Leave blank to include all.', 'woocommerce-jetpack' ) ),
		),
		array(
			'id'   => 'wcj_invoicing_invoice_paid_stamp',
			'type' => 'sectionend',
		),
		array(
			'id'   => 'pdf_invoicing_paid_stamp_invoice_tab',
			'type' => 'tab_end',
		),
	)
);

return $settings;
