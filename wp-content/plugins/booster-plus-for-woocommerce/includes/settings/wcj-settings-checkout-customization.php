<?php
/**
 * Booster for WooCommerce - Settings - Checkout Customization
 *
 * @version 7.0.0
 * @since  1.0.0
 * @author  Pluggabl LLC.
 * @package Booster_Plus_For_WooCommerce/settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

return array(
	array(
		'id'   => 'wcj_checkout_customization_options',
		'type' => 'sectionend',
	),
	array(
		'id'      => 'wcj_checkout_customization_options',
		'type'    => 'tab_ids',
		'tab_ids' => array(
			'wcj_checkout_customization_restrict_countries_tab'   => __( 'Restrict Countries', 'woocommerce-jetpack' ),
			'wcj_checkout_customization_checkbox_options_tab'   => __( 'Checkbox Options', 'woocommerce-jetpack' ),
			'wcj_checkout_customization_button_options_tab'   => __( 'Button Options', 'woocommerce-jetpack' ),
			'wcj_checkout_customization_disable_fields_tab'   => __( 'Disable Fields on Checkout for Logged Users', 'woocommerce-jetpack' ),
			'wcj_checkout_customization_order_message_options_tab'   => __( '"Order received" Message Options', 'woocommerce-jetpack' ),
			'wcj_checkout_customization_returning_message_options_tab'   => __( '"Returning customer?" Message Options', 'woocommerce-jetpack' ),
			'wcj_checkout_customization_recalculate_checkout_tab'   => __( 'Recalculate Checkout', 'woocommerce-jetpack' ),
			'wcj_checkout_customization_force_checkout_tab'   => __( 'Force Checkout Update', 'woocommerce-jetpack' ),
		),
	),
	array(
		'id'   => 'wcj_checkout_customization_restrict_countries_tab',
		'type' => 'tab_start',
	),
	array(
		'title' => __( 'Restrict Countries by Customer\'s IP', 'woocommerce-jetpack' ),
		'type'  => 'title',
		'id'    => 'wcj_checkout_restrict_countries_options',
	),
	array(
		'title'   => __( 'Restrict Billing Countries by Customer\'s IP', 'woocommerce-jetpack' ),
		'desc'    => __( 'Enable', 'woocommerce-jetpack' ),
		'id'      => 'wcj_checkout_restrict_countries_by_customer_ip_billing',
		'default' => 'no',
		'type'    => 'checkbox',
	),
	array(
		'title'             => __( 'Restrict Shipping Countries by Customer\'s IP', 'woocommerce-jetpack' ),
		'desc'              => __( 'Enable', 'woocommerce-jetpack' ),
		'desc_tip'          => sprintf(
						/* translators: %s: translators Added */
			__( 'To restrict shipping countries, "Shipping location(s)" option in %s must be set to "Ship to specific countries only" (and you can leave "Ship to specific countries" option empty there).', 'woocommerce-jetpack' ),
			'<a target="_blank" href="' . admin_url( 'admin.php?page=wc-settings&tab=general' ) . '">' .
			__( 'WooCommerce > Settings > General', 'woocommerce-jetpack' ) . '</a>'
		) . '<br>' . apply_filters( 'booster_message', '', 'desc' ),
		'id'                => 'wcj_checkout_restrict_countries_by_customer_ip_shipping',
		'default'           => 'no',
		'type'              => 'checkbox',
		'custom_attributes' => apply_filters( 'booster_message', '', 'disabled' ),
	),
	array(
		'title'             => __( 'Ignore on Admin', 'woocommerce-jetpack' ),
		'desc'              => __( 'Enable', 'woocommerce-jetpack' ),
		'desc_tip'          => __( 'Ignores restriction on admin', 'woocommerce-jetpack' ),
		'id'                => 'wcj_checkout_restrict_countries_by_customer_ip_ignore_admin',
		'default'           => 'no',
		'type'              => 'checkbox',
		'custom_attributes' => apply_filters( 'booster_message', '', 'disabled' ),
	),
	array(
		'title'             => __( 'Restrict By Customer\'s Billing Country', 'woocommerce-jetpack' ),
		'desc'              => __( 'Enable', 'woocommerce-jetpack' ),
		'desc_tip'          => __( 'Restricts based on Customer\'s Billing Country, ignoring other restrictions', 'woocommerce-jetpack' ),
		'id'                => 'wcj_checkout_restrict_countries_by_user_billing_country',
		'default'           => 'no',
		'type'              => 'checkbox',
		'custom_attributes' => apply_filters( 'booster_message', '', 'disabled' ),
	),
	array(
		'title'             => __( 'Restrict based on a YITH manual order', 'woocommerce-jetpack' ),
		'desc'              => __( 'Enable', 'woocommerce-jetpack' ),
		'desc_tip'          => __( 'Enable if you are creating a manual order using "YITH WooCommerce Request a Quote" plugin and selecting the billing country manually', 'woocommerce-jetpack' ),
		'id'                => 'wcj_checkout_restrict_countries_based_on_yith_raq',
		'default'           => 'no',
		'type'              => 'checkbox',
		'custom_attributes' => apply_filters( 'booster_message', '', 'disabled' ),
	),
	array(
		'title'    => __( 'Conditions', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'The restriction will work only if some condition is true.', 'woocommerce-jetpack' ) . '<br /> ' . __( 'Leave it empty if you want to restrict countries everywhere.', 'woocommerce-jetpack' ),
		'id'       => 'wcj_checkout_restrict_countries_by_customer_ip_conditions',
		'default'  => 'no',
		'type'     => 'multiselect',
		'class'    => 'chosen_select',
		'options'  => array(
			'is_cart'     => __( 'Is Cart', 'popup-notices-for-woocommerce' ),
			'is_checkout' => __( 'Is Checkout', 'popup-notices-for-woocommerce' ),
		),
	),
	array(
		'id'   => 'wcj_checkout_restrict_countries_options',
		'type' => 'sectionend',
	),
	array(
		'id'   => 'wcj_checkout_customization_restrict_countries_tab',
		'type' => 'tab_end',
	),
	array(
		'id'   => 'wcj_checkout_customization_checkbox_options_tab',
		'type' => 'tab_start',
	),
	array(
		'title' => __( '"Create an account?" Checkbox Options', 'woocommerce-jetpack' ),
		'type'  => 'title',
		'id'    => 'wcj_checkout_create_account_checkbox_options',
	),
	array(
		'title'    => __( '"Create an account?" Checkbox', 'woocommerce-jetpack' ),
		'desc_tip' => __( '"Create an account?" checkbox default value', 'woocommerce-jetpack' ),
		'id'       => 'wcj_checkout_create_account_default_checked',
		'default'  => 'default',
		'type'     => 'select',
		'options'  => array(
			'default'     => __( 'WooCommerce default', 'woocommerce-jetpack' ),
			'checked'     => __( 'Checked', 'woocommerce-jetpack' ),
			'not_checked' => __( 'Not checked', 'woocommerce-jetpack' ),
		),
	),
	array(
		'id'   => 'wcj_checkout_create_account_checkbox_options',
		'type' => 'sectionend',
	),
	array(
		'id'   => 'wcj_checkout_customization_checkbox_options_tab',
		'type' => 'tab_end',
	),
	array(
		'id'   => 'wcj_checkout_customization_button_options_tab',
		'type' => 'tab_start',
	),
	array(
		'title' => __( '"Order Again" Button Options', 'woocommerce-jetpack' ),
		'type'  => 'title',
		'id'    => 'wcj_checkout_order_again_button_options',
	),
	array(
		'title'   => __( 'Hide "Order Again" Button on "View Order" Page', 'woocommerce-jetpack' ),
		'desc'    => __( 'Hide', 'woocommerce-jetpack' ),
		'id'      => 'wcj_checkout_hide_order_again',
		'default' => 'no',
		'type'    => 'checkbox',
	),
	array(
		'id'   => 'wcj_checkout_order_again_button_options',
		'type' => 'sectionend',
	),
	array(
		'id'   => 'wcj_checkout_customization_button_options_tab',
		'type' => 'tab_end',
	),
	array(
		'id'   => 'wcj_checkout_customization_disable_fields_tab',
		'type' => 'tab_start',
	),
	array(
		'title' => __( 'Disable Fields on Checkout for Logged Users', 'woocommerce-jetpack' ),
		'type'  => 'title',
		'id'    => 'wcj_checkout_customization_disable_fields_for_logged_options',
	),
	array(
		'title'   => __( 'Fields to Disable', 'woocommerce-jetpack' ),
		'id'      => 'wcj_checkout_customization_disable_fields_for_logged',
		'default' => array(),
		'type'    => 'multiselect',
		'class'   => 'chosen_select',
		'options' => array(
			'billing_country'     => __( 'Billing country', 'woocommerce-jetpack' ),
			'billing_first_name'  => __( 'Billing first name', 'woocommerce-jetpack' ),
			'billing_last_name'   => __( 'Billing last name', 'woocommerce-jetpack' ),
			'billing_company'     => __( 'Billing company', 'woocommerce-jetpack' ),
			'billing_address_1'   => __( 'Billing address 1', 'woocommerce-jetpack' ),
			'billing_address_2'   => __( 'Billing address 2', 'woocommerce-jetpack' ),
			'billing_city'        => __( 'Billing city', 'woocommerce-jetpack' ),
			'billing_state'       => __( 'Billing state', 'woocommerce-jetpack' ),
			'billing_postcode'    => __( 'Billing postcode', 'woocommerce-jetpack' ),
			'billing_email'       => __( 'Billing email', 'woocommerce-jetpack' ),
			'billing_phone'       => __( 'Billing phone', 'woocommerce-jetpack' ),
			'shipping_country'    => __( 'Shipping country', 'woocommerce-jetpack' ),
			'shipping_first_name' => __( 'Shipping first name', 'woocommerce-jetpack' ),
			'shipping_last_name'  => __( 'Shipping last name', 'woocommerce-jetpack' ),
			'shipping_company'    => __( 'Shipping company', 'woocommerce-jetpack' ),
			'shipping_address_1'  => __( 'Shipping address 1', 'woocommerce-jetpack' ),
			'shipping_address_2'  => __( 'Shipping address 2', 'woocommerce-jetpack' ),
			'shipping_city'       => __( 'Shipping city', 'woocommerce-jetpack' ),
			'shipping_state'      => __( 'Shipping state', 'woocommerce-jetpack' ),
			'shipping_postcode'   => __( 'Shipping postcode', 'woocommerce-jetpack' ),
			'order_comments'      => __( 'Order comments', 'woocommerce-jetpack' ),
		),
	),
	array(
		'title'    => __( 'Message for Logged Users', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'You can use HTML here.', 'woocommerce-jetpack' ),
		'id'       => 'wcj_checkout_customization_disable_fields_for_logged_message',
		'default'  => '<em>' . __( 'This field can not be changed', 'woocommerce-jetpack' ) . '</em>',
		'type'     => 'textarea',
		'css'      => 'width:100%;',
	),
	array(
		'title'             => __( 'Advanced: Custom Fields (Readonly)', 'woocommerce-jetpack' ),
		'desc'              => apply_filters( 'booster_message', '', 'desc' ),
		/* translators: %s: translators Added */
		'desc_tip'          => sprintf( __( 'Comma separated list of fields ids, e.g.: %s.', 'woocommerce-jetpack' ), '<em>billing_wcj_checkout_field_1, billing_wcj_checkout_field_2</em>' ),
		'id'                => 'wcj_checkout_customization_disable_fields_for_logged_custom_r',
		'default'           => '',
		'type'              => 'text',
		'css'               => 'width:100%;',
		'custom_attributes' => apply_filters( 'booster_message', '', 'readonly' ),
	),
	array(
		'title'             => __( 'Advanced: Custom Fields (Disabled)', 'woocommerce-jetpack' ),
		'desc'              => apply_filters( 'booster_message', '', 'desc' ),
		/* translators: %s: translators Added */
		'desc_tip'          => sprintf( __( 'Comma separated list of fields ids, e.g.: %s.', 'woocommerce-jetpack' ), '<em>billing_wcj_checkout_field_1, billing_wcj_checkout_field_2</em>' ),
		'id'                => 'wcj_checkout_customization_disable_fields_for_logged_custom_d',
		'default'           => '',
		'type'              => 'text',
		'css'               => 'width:100%;',
		'custom_attributes' => apply_filters( 'booster_message', '', 'readonly' ),
	),
	array(
		'id'   => 'wcj_checkout_customization_disable_fields_for_logged_options',
		'type' => 'sectionend',
	),
	array(
		'id'   => 'wcj_checkout_customization_disable_fields_tab',
		'type' => 'tab_end',
	),
	array(
		'id'   => 'wcj_checkout_customization_order_message_options_tab',
		'type' => 'tab_start',
	),
	array(
		'title' => __( '"Order received" Message Options', 'woocommerce-jetpack' ),
		'type'  => 'title',
		'id'    => 'wcj_checkout_customization_order_received_message_options',
	),
	array(
		'title'   => __( 'Customize Message', 'woocommerce-jetpack' ),
		'desc'    => __( 'Enable', 'woocommerce-jetpack' ),
		'id'      => 'wcj_checkout_customization_order_received_message_enabled',
		'default' => 'no',
		'type'    => 'checkbox',
	),
	array(
		'desc_tip' => __( 'You can use HTML and/or shortcodes here.', 'woocommerce-jetpack' ),
		'id'       => 'wcj_checkout_customization_order_received_message',
		'default'  => __( 'Thank you. Your order has been received.', 'woocommerce' ),
		'type'     => 'textarea',
		'css'      => 'width:100%;',
	),
	array(
		'id'   => 'wcj_checkout_customization_order_received_message_options',
		'type' => 'sectionend',
	),
	array(
		'id'   => 'wcj_checkout_customization_order_message_options_tab',
		'type' => 'tab_end',
	),
	array(
		'id'   => 'wcj_checkout_customization_returning_message_options_tab',
		'type' => 'tab_start',
	),
	array(
		'title' => __( '"Returning customer?" Message Options', 'woocommerce-jetpack' ),
		'type'  => 'title',
		'id'    => 'wcj_checkout_customization_checkout_login_message_options',
	),
	array(
		'title'   => __( 'Customize Message', 'woocommerce-jetpack' ),
		'desc'    => __( 'Enable', 'woocommerce-jetpack' ),
		'id'      => 'wcj_checkout_customization_checkout_login_message_enabled',
		'default' => 'no',
		'type'    => 'checkbox',
	),
	array(
		'id'      => 'wcj_checkout_customization_checkout_login_message',
		'default' => __( 'Returning customer?', 'woocommerce' ),
		'type'    => 'textarea',
		'css'     => 'width:100%;',
	),
	array(
		'id'   => 'wcj_checkout_customization_checkout_login_message_options',
		'type' => 'sectionend',
	),
	array(
		'id'   => 'wcj_checkout_customization_returning_message_options_tab',
		'type' => 'tab_end',
	),
	array(
		'id'   => 'wcj_checkout_customization_recalculate_checkout_tab',
		'type' => 'tab_start',
	),
	array(
		'title' => __( 'Recalculate Checkout', 'woocommerce-jetpack' ),
		'desc'  => __( 'Recalculate checkout right after the default calculation has been requested.', 'woocommerce-jetpack' ),
		'type'  => 'title',
		'id'    => 'wcj_checkout_recalculate_checkout_update_options',
	),
	array(
		'title'   => __( 'Recalculate Checkout', 'woocommerce-jetpack' ),
		'desc'    => __( 'Enable', 'woocommerce-jetpack' ),
		'id'      => 'wcj_checkout_recalculate_checkout_update_enable',
		'default' => 'no',
		'type'    => 'checkbox',
	),
	array(
		'title'    => __( 'Fields', 'woocommerce-jetpack' ),
		'desc'     => __( 'Required fields that need to be changed in order to recalculate checkout.', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'Use CSS selector syntax.', 'woocommerce-jetpack' ),
		'id'       => 'wcj_checkout_recalculate_checkout_update_fields',
		'default'  => '#billing_country, #shipping_country',
		'type'     => 'text',
	),
	array(
		'id'   => 'wcj_checkout_recalculate_checkout_update_options',
		'type' => 'sectionend',
	),
	array(
		'id'   => 'wcj_checkout_customization_recalculate_checkout_tab',
		'type' => 'tab_end',
	),
	array(
		'id'   => 'wcj_checkout_customization_force_checkout_tab',
		'type' => 'tab_start',
	),
	array(
		'title' => __( 'Force Checkout Update', 'woocommerce-jetpack' ),
		'desc'  => __( 'Update checkout when some field have its value changed.', 'woocommerce-jetpack' ),
		'type'  => 'title',
		'id'    => 'wcj_checkout_force_checkout_update_options',
	),
	array(
		'title'   => __( 'Force Checkout Update', 'woocommerce-jetpack' ),
		'desc'    => __( 'Enable', 'woocommerce-jetpack' ),
		'id'      => 'wcj_checkout_force_checkout_update_enable',
		'default' => 'no',
		'type'    => 'checkbox',
	),
	array(
		'title'    => __( 'Fields', 'woocommerce-jetpack' ),
		'desc'     => __( 'Fields that need to be changed in order to update checkout.', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'Use CSS selector syntax.', 'woocommerce-jetpack' ),
		'id'       => 'wcj_checkout_force_checkout_update_fields',
		'default'  => '',
		'type'     => 'text',
	),
	array(
		'id'   => 'wcj_checkout_force_checkout_update_options',
		'type' => 'sectionend',
	),
	array(
		'id'   => 'wcj_checkout_customization_force_checkout_tab',
		'type' => 'tab_end',
	),
);
