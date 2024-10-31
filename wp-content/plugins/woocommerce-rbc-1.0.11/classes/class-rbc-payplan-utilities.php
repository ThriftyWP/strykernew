<?php

namespace Rbc_Payplan\Classes;

if(!defined('ABSPATH')) {
    exit;    
}

/**
 * Utility manager helper class
 * 
 */
class Rbc_Payplan_Utilities {
    
    /**
     * Our custom boolean values
     */
    private $boolvals = array( 'yes', 'on', 'true', 'checked' );

    
    /**
     * Reference singleton instance of this class
     * 
     * @var $instance
     */
    private static $instance;
    
    /**
     * 
     * Return singleton instance of this class
     * 
     * @return object self::$instance
     */
    public static function instance() {
        if (null == self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
      
    /**
     * Capitalize the first letter and lower-case all remaining letters of a string.
     *
     * @param $string
     *
     * @return string
     */
    public function properCase($string) {
        return mb_strtoupper(substr($string, 0, 1)) . mb_strtolower(substr($string, 1));
    }

    /**
     * Checks the parameter, usually a string form value, for truthiness (i.e. yes, on, checked).
     * If the parameter is not a string value, use the native type-coercion function.
     *
     * @param $value mixed        The value to check.
     *
     * @return bool
     */
    public function toBool($value) {
        return is_string($value) ? in_array(strtolower($value), $this->boolvals) : boolval($value);
    }

    /**
     * Convert a price value in dollars to cents.
     *
     * @param $price
     *
     * @return int
     */
    public function priceToCents($price) {
        /**
         * Convert price to float
         * 
         * @since 1.1.0
         */
        $floatPrice = floatval($price);
        $split_price = explode(wc_get_price_decimal_separator(), number_format($floatPrice, 2, '.', ''));

        $dollars = intval($split_price[0]) * 100;
        $cents = ( count($split_price) > 1 ) ? intval(str_pad($split_price[1], 2, '0')) : 0;

        return $dollars + $cents;
    }

    /**
     * Convert a price value in cents to dollars.
     *
     * @param $price
     * @param $quantity
     *
     * @return float
     */
    public function priceToDollars($price, $quantity = 1) {
        return round($price / 100 * $quantity, 2);
    }

    /**
     * Get the current WooCommerce page type. If no page type can be determined, as can be the case when using
     * shortcode, default to 'Product'.
     *
     * NOTE: The return values of this function correspond with the RBC `buttonLocation` option allowed values.
     *
     * @return string
     */
    public function getPageType() {

        if (is_post_type_archive('product') || is_product_category() || is_shop()) {
            return 'category';
        }

        if (is_product()) {
            return 'product';
        }

        if (is_cart()) {
            return 'cart_summary';
        }

        if (is_checkout()) {
            return 'checkout';
        }

        return 'other';
    }

    public function getProductType() {

        if (is_product()) {
            global $product, $post;

            if (is_string($product)) {
                if (!isset($post)) {
                    $post = get_page_by_path($product, OBJECT, 'product');
                }
                $currentProduct = wc_get_product($post->ID);
            } elseif (is_null($product)) {
                $currentProduct = wc_get_product($post->ID);
            } else {
                $currentProduct = $product;
            }

            if (is_object($currentProduct)) {
                return $currentProduct->get_type();
            }
        }

        return '';
    }

    public function validateCalculatedTotals($order, $transaction) {
        $rbc_payplan_plugin = Rbc_Payplan_Plugin::instance();
        /* Validate calculated totals */
        if (abs($this->priceToCents($order->get_total()) - $transaction['totalAmount']['value']) > 2) {
            $message = esc_html__("Transaction amount does not equal order total.", 'rbc_payplan');
            $order->update_status("failed", $message);
            wp_send_json_error(__("RBC transaction total does not match order total.", 'rbc_payplan'));
        }

        if (floatval($order->get_total_tax()) !== floatval($transaction['taxAmount']['value'] / 100)) {
            $order->add_order_note("RBC tax total does not match order tax total.");
        }

        if (floatval($order->get_shipping_total()) !== floatval($transaction['shippingAmount']['value'] / 100)) {
            $order->add_order_note("RBC shipping total does not match order shipping total.");
        }
    }

    /**
     * Check if Avalara tax plugin exists and is enabled
     *
     * @return bool
     */
    public function isAvataxEnabled() {
        return function_exists('wc_avatax') && wc_avatax()->get_tax_handler()->is_enabled();
    }

    public function getTaxHelper($shippingCost) {
        $tax;
        $cart = WC()->cart;

        /* For merchants using AvaTax, use Avalara method to calculate tax on virtual cart */
        if ($this->isAvataxEnabled()) {
            $cart->set_shipping_total($shippingCost); // At checkout, Avalara needs shipping cost to calculate shipping tax properly
            $avaResponse = wc_avatax()->get_api()->calculate_cart_tax($cart);
            $tax = $this->priceToCents($avaResponse->response_data->totalTax);
        } else {
            $tax = $this->priceToCents($cart->get_taxes_total());
        }

        return array('tax' => $tax);
    }

    public function getDiscountTotal($discounts) {
        return array_reduce($discounts, function($sum, $current) {
            return $sum += $current["amount"] ?: 0;
        }, 0);
    }
    
    /**
     * 
     * @param type $name
     * @param type $defaultValue
     * @return string
     */
    public function getParam($name, $defaultValue = null) {
        return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $defaultValue);
    }

}
