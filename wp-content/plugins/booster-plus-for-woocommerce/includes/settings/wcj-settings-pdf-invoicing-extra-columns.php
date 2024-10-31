<?php
/**
 * Booster for WooCommerce - Settings - PDF Invoicing - Extra Columns
 *
 * @version 7.0.0
 * @author  Pluggabl LLC.
 * @package Booster_Plus_For_WooCommerce/settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$settings         = array(
	array(
		'type'              => 'module_head',
		'title'             => __( 'How to add extra columns(info) to pdf', 'woocommerce-jetpack' ),
		'desc'              => __( 'PDF Invoicing : How to add extra columns(info) to pdf' ),
		'icon'              => 'pr-sm-icn.png',
		'module_reset_link' => '<a style="width:auto;" onclick="return confirm(\'' . __( 'Are you sure? This will reset module to default settings.', 'woocommerce-jetpack' ) . '\')" class="wcj_manage_settting_btn wcj_tab_end_save_btn" href="' . add_query_arg(
			array(
				'wcj_reset_settings' => $this->id,
				'wcj_reset_settings-' . $this->id . '-nonce' => wp_create_nonce( 'wcj_reset_settings' ),
			)
		) . '">' . __( 'Reset settings', 'woocommerce-jetpack' ) . '</a>',
	),
);
$paths            = 'admin.php?page=wcj-plugins&wcj-cat=pdf_invoicing&section=pdf_invoicing_templates';
$templates_tb_url = admin_url( $paths );

$settings = array_merge(
	$settings,
	array(
		array(
			'title' => 'How to add extra columns(info) to pdf',
			'type'  => 'information',
			'desc'  => 'On <a href="' . $templates_tb_url . '"> Templates </a> tab you can customize HTML Template code and display extra column,information to pdf using shortcodes.',
			'id'    => 'wcj_invoicing_invoice_extra_columns',
		),
		array(
			'title' => 'Display product image on pdf',
			'type'  => 'information',
			'desc'  => 'Follow below steps to display product image on pdf. <br> 1. Find wcj_order_items_table shortcode on Template code <br> 2. Using "|" separator add "product_thumbnail" on "columns" argument. <br> 3. same add product image column title on "columns_titles" argument. <br> 4. same add product image column styles on "columns_styles" argument.',
			'id'    => 'wcj_invoicing_invoice_extra_columns_1',
		),
		array(
			'title' => 'Display product addons on pdf',
			'type'  => 'information',
			'desc'  => 'Follow below steps to display product addons on pdf. <br> 1. Find wcj_order_items_table shortcode on Template code <br> 2. Using "|" separator add "item_product_addons" on "columns" argument. <br> 3. same add product addon column title on "columns_titles" argument. <br> 4. same add product addon column styles on "columns_styles" argument.',
			'id'    => 'wcj_invoicing_invoice_extra_columns_2',
		),
		array(
			'title' => 'Display product input fields on pdf',
			'type'  => 'information',
			'desc'  => 'Follow below steps to display product input fields on pdf. <br> 1. Find wcj_order_items_table shortcode on Template code <br> 2. Using "|" separator add "item_product_input_fields_with_titles" on "columns" argument. <br> 3. same add product input field column title on "columns_titles" argument. <br> 4. same add product input field column styles on "columns_styles" argument.',
			'id'    => 'wcj_invoicing_invoice_extra_columns_3',
		),
		array(
			'title' => 'List of predefined table columns you need to display',
			'type'  => 'information',
			'desc'  => 'List of predefined table columns you need to display. please check document from <a target="_blank" href="https://booster.io/shortcodes/wcj_order_items_table/"> here </a>',
			'id'    => 'wcj_invoicing_invoice_extra_columns_4',
		),
		array(
			'title' => 'Display checkout custom fields on pdf',
			'type'  => 'information',
			'desc'  => 'Using [wcj_order_checkout_field] shortcode you can display checkout custom fields on pdf. <br> For example : [wcj_order_checkout_field field_id="billing_wcj_checkout_field_1"] please replace field _id with custom checkout field key.',
			'id'    => 'wcj_invoicing_invoice_extra_columns_5',
		),
		array(
			'title' => 'Display WooCommerce order’s meta on pdf',
			'type'  => 'information',
			'desc'  => 'Using [wcj_order_meta] shortcode you can display WooCommerce order’s meta on pdf. <br> For example : [wcj_order_meta meta_key="_your_key"]',
			'id'    => 'wcj_invoicing_invoice_extra_columns_6',
		),
		array(
			'title' => 'All order related shortcodes',
			'type'  => 'information',
			'desc'  => 'All order related shortcodes please check from <a target="_blank" href="https://booster.io/category/shortcodes/orders-shortcodes/"> here </a>',
			'id'    => 'wcj_invoicing_invoice_extra_columns_7',
		),
		array(
			'title' => 'Display WooCommerce subscription order details on pdf',
			'type'  => 'information',
			'desc'  => 'Using [wcj_order_subscription_get_date] shortcode you can display WooCommerce subscription order details. <br> For example : [wcj_order_subscription_get_date subscription_date_type="start"] <br> The type of subscription_date_type to get, can be "start", "trial_end", "next_payment", "last_payment" or "end"',
			'id'    => 'wcj_invoicing_invoice_extra_columns_8',
		),
		array(
			'type' => 'sectionend',
			'id'   => 'wcj_invoicing_invoice_extra_columns',
		),
	)
);

return $settings;
