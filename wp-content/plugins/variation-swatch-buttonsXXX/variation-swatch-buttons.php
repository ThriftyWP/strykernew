<?php
/*
Plugin Name: Variation Swatch Buttons
Description: Displays WooCommerce product variations as color swatches without triggering add-to-cart.
Version: 1.0
Author: Your Name
*/

// Enqueue styles and scripts
function vsb_enqueue_scripts() {
    wp_enqueue_style('vsb-styles', plugin_dir_url(__FILE__) . 'assets/styles.css');
    wp_enqueue_script('vsb-scripts', plugin_dir_url(__FILE__) . 'assets/scripts.js', ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'vsb_enqueue_scripts');

// Add hex color field to each term in color attribute
function vsb_add_color_hex_field_to_terms() {
    ?>
    <div class="form-field term-color-hex-wrap">
        <label for="color_hex"><?php esc_html_e('Color Hex', 'vsb'); ?></label>
        <input name="color_hex" id="color_hex" type="text" value="" placeholder="#ffffff" />
        <p class="description"><?php esc_html_e('Enter a hex color for this term (e.g., #ff0000).', 'vsb'); ?></p>
    </div>
    <?php
}
add_action('pa_color_add_form_fields', 'vsb_add_color_hex_field_to_terms');

function vsb_edit_color_hex_field_in_terms($term) {
    $color_hex = get_term_meta($term->term_id, 'color_hex', true);
    ?>
    <tr class="form-field term-color-hex-wrap">
        <th scope="row"><label for="color_hex"><?php esc_html_e('Color Hex', 'vsb'); ?></label></th>
        <td>
            <input name="color_hex" id="color_hex" type="text" value="<?php echo esc_attr($color_hex); ?>" placeholder="#ffffff" />
            <p class="description"><?php esc_html_e('Enter a hex color for this term (e.g., #ff0000).', 'vsb'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('pa_color_edit_form_fields', 'vsb_edit_color_hex_field_in_terms');

// Save hex color field value for each term
function vsb_save_color_hex_for_terms($term_id) {
    if (isset($_POST['color_hex']) && !empty($_POST['color_hex'])) {
        update_term_meta($term_id, 'color_hex', sanitize_hex_color($_POST['color_hex']));
    }
}
add_action('created_pa_color', 'vsb_save_color_hex_for_terms', 10, 2);
add_action('edited_pa_color', 'vsb_save_color_hex_for_terms', 10, 2);

// Replace dropdown with color swatches on the single product page
function vsb_display_color_swatches_in_single_product($html, $args) {
    if ($args['attribute'] === 'pa_color') {
        global $product;

        // Get the terms for the color attribute
        $terms = wc_get_product_terms($product->get_id(), $args['attribute'], ['fields' => 'all']);
        if (empty($terms)) {
            return $html;
        }

        $swatch_html = '<div class="vsb-color-swatches">';
        foreach ($terms as $term) {
            // Retrieve the hex color associated with each term
            $color_hex = get_term_meta($term->term_id, 'color_hex', true);
            $swatch_html .= sprintf(
                '<div class="vsb-swatch" data-value="%s" style="background-color:%s;" title="%s"></div>',
                esc_attr($term->slug),
                esc_attr($color_hex),
                esc_attr($term->name)
            );
        }
        $swatch_html .= '</div>';
        return $swatch_html;
    }

    return $html;
}
add_filter('woocommerce_dropdown_variation_attribute_options_html', 'vsb_display_color_swatches_in_single_product', 10, 2);

// Log selected product attributes in the cart
function vsb_log_selected_attributes($cart_item_data, $product_id, $variation_id) {
    if ($variation_id) {
        $product = wc_get_product($variation_id);

        // Check if attributes are set and retrieve them
        $attributes = $product->get_attributes();
        $selected_attributes = $cart_item_data['variation'] ?? [];

        // Set up the logger
        if (class_exists('WC_Logger')) {
            $logger = wc_get_logger();
            $context = ['source' => 'variation-swatch-buttons'];

            // Log the selected attributes
            $logger->info('Selected Attributes: ' . print_r($selected_attributes, true), $context);
        }
    }

    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'vsb_log_selected_attributes', 10, 3);
