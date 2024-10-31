<?php
/*
Plugin Name: Pro Woo Quick View
Plugin URI: 
Description: Woo Quick View plugin allows customers to have a brief overview of each product in a lightbox.
Author: Jaroncito
Author URI: 
Text Domain: pro-woo-quick-view
Version: 1.1.3
*/

// Activation hook
register_activation_hook(__FILE__, 'wcqv_quick_view_activate');
function wcqv_quick_view_activate() {
    $data = array(
        'enable_quick_view'    => '1',
        'enable_mobile'        => '0',
        'image_click_popup'    => '0',
        'disable_links'        => '0',
        'button_icon'          => '0',
        'button_lable'         => 'Quick View',
        'navigation_same_cat'  => '0'
    );
    add_option('wcqv_options', $data, '', 'yes');

    $data = array(
        'modal_bg'             => '#fff',
        'close_btn'            => '#95979c',
        'close_btn_bg'         => '#4C6298',
        'navigation_bg'        => 'rgba(255, 255, 255, 0.2)',
        'navigation_txt'       => '#fff'
    );
    add_option('wcqv_style', $data, '', 'yes');

    $data = array(
        'show_product_sale_flash'   => '1',
        'show_product_title'        => '1',
        'show_product_images'       => '1',
        'show_product_rating'       => '1',
        'show_product_price'        => '1',
        'show_product_excerpt'      => '1',
        'show_product_add_to_cart'  => '1',
        'show_product_meta'         => '1'
    );
    add_option('wcqv_display', $data);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'wcqv_quick_view_deactivate');
function wcqv_quick_view_deactivate() {
    delete_option('wcqv_style');
    delete_option('wcqv_options');
    delete_option('wcqv_display');
}

// Load WooCommerce-specific files and textdomain
add_action('plugins_loaded', 'wqv_load_class_files');
// Modify the existing wqv_load_class_files() function instead of creating a new one
function wqv_load_class_files() {
    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        require_once 'classes/class.frontend.php';
        require_once 'classes/class.backend.php';

        load_plugin_textdomain('woocommerce-quick-view', false, plugin_basename(dirname(__FILE__)) . '/lang');

        $wcqv_plugin_dir_url = plugin_dir_url(__FILE__);
        $data = get_option('wcqv_options');
        $load_backend = new wcqv_backend($wcqv_plugin_dir_url);
        $enable_mobile = ($data['enable_mobile'] === '1') ? true : false;

        // Store frontend instance globally
        global $wcqv_frontend_instance;

        if ($load_backend->mobile_detect()) {
            if ($enable_mobile && ($data['enable_quick_view'] === '1')) {
                $wcqv_frontend_instance = new wcqv_frontend($wcqv_plugin_dir_url);
            }
        } else {
            if ($data['enable_quick_view'] === '1') {
                $wcqv_frontend_instance = new wcqv_frontend($wcqv_plugin_dir_url);
            }
        }

        // Remove the original filter that adds the button automatically
        remove_filter('woocommerce_loop_add_to_cart_link', 'wqv_add_quick_view_button_loop', 10);
    }
}

// Add settings link on the plugin page
function wcqv_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=woocommerce-quick-view">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'wcqv_settings_link');

// Include the class.frontend.php file to make the function available
require_once 'classes/class.frontend.php';

// Add a shortcode for the quick view button
// Modify the shortcode function
function wcqv_quick_view_shortcode($atts) {
    // Get product ID from shortcode attributes or current post
    $atts = shortcode_atts(array(
        'product_id' => get_the_ID()
    ), $atts);
    
    $product_id = intval($atts['product_id']);
    
    if (!$product_id) {
        return '';
    }
    
    // Get frontend instance
    global $wcqv_frontend_instance;
    if (!isset($wcqv_frontend_instance)) {
        $wcqv_plugin_dir_url = plugin_dir_url(__FILE__);
        $wcqv_frontend_instance = new wcqv_frontend($wcqv_plugin_dir_url);
    }
    
    return $wcqv_frontend_instance->get_quick_view_button($product_id);
}
add_shortcode('quick_view', 'wcqv_quick_view_shortcode');
