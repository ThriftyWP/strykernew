<?php

namespace Rbc_Payplan\Classes;

if (!defined('ABSPATH')) {
    exit;
}

Class Rbc_Payplan_Form_Fields {

    /**
     * Returns the fields that we need filled
     */
    public static function fields() {
        $general = array(
            'enabled' => array(
                'title' => esc_html__('Enable / Disable', 'rbc_payplan'),
                'label' => esc_html__('Enable this gateway', 'rbc_payplan'),
                'type' => 'checkbox',
                'default' => 'no',
            ),
            'title' => array(
                'title' => esc_html__('Title', 'rbc_payplan'),
                'type' => 'text',
                'desc_tip' => esc_html__('Payment method title that the customer will see during checkout.', 'rbc_payplan'),
                'default' => esc_html__('Pay Over Time With RBC Payplan', 'rbc_payplan'),
            ),
            'description' => array(
                'title' => esc_html__('Description', 'rbc_payplan'),
                'type' => 'textarea',
                'desc_tip' => esc_html__('Payment method description that the customer will see during checkout.', 'rbc_payplan'),
                'default' => esc_html__('RBC Payplan lets you pay over time for the things you need.', 'rbc_payplan'),
            ),
            'display_icon' => array(
                'title' => esc_html__('Display RBC Icon', 'rbc_payplan'),
                'label' => esc_html__('Display the RBC icon next to the payment method title during checkout.', 'rbc_payplan'),
                'type' => 'checkbox',
                'default' => 'no'
            ),
            'pre_populate' => array(
                'title' => esc_html__('Auto-Populate Forms', 'rbc_payplan'),
                'label' => esc_html__('Auto-populate form fields for logged-in WooCommerce users.', 'rbc_payplan'),
                'type' => 'checkbox',
                'default' => 'yes',
            ),
            'debug' => array(
                'title' => esc_html__('Enable Debugging', 'rbc_payplan'),
                'label' => esc_html__('Log errors to the Javascript console.', 'rbc_payplan'),
                'type' => 'checkbox',
                'default' => 'no',
            ),
            'sentry_enabled' => array(
                'title' => esc_html__('Send Error Information to RBC', 'rbc_payplan'),
                'label' => esc_html__('Proactively send information about any RBC related issues.', 'rbc_payplan'),
                'type' => 'checkbox',
                'default' => 'yes',
            ),
        );
        $environment = array(
            'api_settings' => array(
                'title' => esc_html__('API Settings', 'rbc_payplan'),
                'type' => 'title'
            ),
            'environment' => array(
                'title' => esc_html__('Environment', 'rbc_payplan'),
                'type' => 'select',
                'desc_tip' => esc_html__('Select the gateway environment to use for transactions', 'rbc_payplan'),
                'default' => 'sandbox',
                'options' => array(
                    'sandbox' => 'Sandbox',
                    'production' => 'Production'
                )
            ),
            'sandbox_api_key' => array(
                'title' => esc_html__('Sandbox API Key', 'rbc_payplan'),
                'type' => 'text',
                'desc_tip' => esc_html__('Your RBC Sandbox API Key')
            ),
            
            'sandbox_api_secret_key' => array(
                'title' => esc_html__('Sandbox API Secret Key', 'rbc_payplan'),
                'type' => 'text',
                'desc_tip' => esc_html__('Your RBC Sandbox API Secret Key')
            ),
            'sandbox_integration_key' => array(
                'title' => esc_html__('Sandbox Integration Key', 'rbc_payplan'),
                'type' => 'text',
                'desc_tip' => esc_html__('Your RBC Sandbox integration key. This will be provided by your customer success manager')
            ),
            'production_api_key' => array(
                'title' => esc_html__('Production API Key', 'rbc_payplan'),
                'type' => 'text',
                'desc_tip' => esc_html__('Your RBC Production API Key')
            ),
            'production_api_secret_key' => array(
                'title' => esc_html__('Production API Secret Key', 'rbc_payplan'),
                'type' => 'text',
                'desc_tip' => esc_html__('Your RBC Production API Secret Key')
            ),
            'production_integration_key' => array(
                'title' => esc_html__('Production Integration Key', 'rbc_payplan'),
                'type' => 'text',
                'desc_tip' => esc_html__('Your RBC production integration key. This will be provided by your customer success manager')
            )
        );

        $button_appearance = array(
            'button_options_category' => array(
                'title' => esc_html__('Category Page Options', 'rbc_payplan'),
                'type' => 'title',
            ),
            'button_location_category' => array(
                'title' => esc_html__('Button Placement', 'rbc_payplan'),
                'type' => 'select',
                'description' => esc_html__('Location on the category pages where RBC button should appear', 'rbc_payplan'),
                'options' => array(
                    'after_shop_loop_item:before' => esc_html__('Before Add to Cart Button', 'rbc_payplan'),
                    'after_shop_loop_item:after' => esc_html__('After Add to Cart Button', 'rbc_payplan'),
                    '' => esc_html__("Don't Display Button on Category Pages", 'rbc_payplan')
                ),
                'default' => 'woocommerce_after_shop_loop_item:after'
            ),
            'button_options_product' => array(
                'title' => esc_html__('Product Page Options', 'rbc_payplan'),
                'type' => 'title',
            ),
            'button_location_product' => array(
                'title' => esc_html__('Button Placement', 'rbc_payplan'),
                'type' => 'select',
                'description' => esc_html__('Location on the product pages where the RBC button should appear', 'rbc_payplan'),
                'options' => array(
                    'before_single_product_summary' => esc_html__('Before Product Summary', 'rbc_payplan'),
                    'before_add_to_cart_form' => esc_html__('Before Add to Cart Button', 'rbc_payplan'),
                    'after_add_to_cart_form' => esc_html__('After Add to Cart Button', 'rbc_payplan'),
                    'after_single_product_summary' => esc_html__('After Product Summary', 'rbc_payplan'),
                    'get_price_html' => esc_html__('After Product Price', 'rbc_payplan'),
                    '' => esc_html__("Don't Display Button on Product Pages", 'rbc_payplan')
                ),
                'default' => 'after_add_to_cart_form'
            ),
            'button_options_cart' => array(
                'title' => esc_html__('Cart Summary Page Options', 'rbc_payplan'),
                'type' => 'title',
            ),
            'button_location_cart' => array(
                'title' => esc_html__('Button Placement', 'rbc_payplan'),
                'type' => 'select',
                'description' => esc_html__('Location on the cart summary page where the RBC button should appear', 'rbc_payplan'),
                'options' => array(
                    'after_cart_totals' => esc_html__('After Cart Totals', 'rbc_payplan'),
                    '' => esc_html__("Don't Display Button on Cart Summary Page", 'rbc_payplan')
                ),
                'default' => 'after_cart_totals'
            ),
            'button_options_checkout' => array(
                'title' => esc_html__('Checkout Page Options', 'rbc_payplan'),
                'type' => 'title',
            ),
            'button_checkout_checkout' => array(
                'title' => esc_html__('Show RBC Payplan at Checkout', 'rbc_payplan'),
                'type' => 'checkbox',
                'label' => esc_html('Enable RBC Payplan as a payment option on the checkout page.', 'rbc_payplan'),
                'default' => 'yes'
            ),
             'button_placeholder' => array(
                'title' => esc_html__('Button Placeholder', 'rbc_payplan'),
                'type' => 'textarea',
                'description' => esc_html__('Custom HTML to show as a placeholder for bread buttons that have not yet been rendered.', 'rbc_payplan'),
            ),
        );
        
        $advanced = array(
                'advanced_settings_title' => array(
                    'title' => esc_html__('Advanced Settings (requires authorization from your Payplan by RBC representative)', 'rbc_payplan'),
                    'type' => 'title',
                ),
                'advanced_settings' => array(
                    'type' => 'advanced_settings',
                ),
            );

        $settings = array_merge($general, $environment, $button_appearance, $advanced);

        return apply_filters('rbc_payplan_wc_gateway_settings', $settings);
    }

}
