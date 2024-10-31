<?php 

namespace Rbc_Payplan\Classes;

if (!defined('ABSPATH')) {
    exit;
}

class Rbc_Payplan_Button {
    
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
     * Plugin manager instance
     */
    public $rbc_payplan_plugin = false;
    
    /**
     * Utility Helper
     */
    public $rbc_payplan_utilities = false;
    
    
    public function __construct() {
        if(!$this->rbc_payplan_plugin) {
            $this->rbc_payplan_plugin = Rbc_Payplan_Plugin::instance();
        }
        
        if(!$this->rbc_payplan_utilities) {
            $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
        }
        add_action('wp',array($this, 'add_template_hooks'));
    }
    
    /**
     * Add template hooks for the rbc button
     */
    public function add_template_hooks() {
        $use_custom_size = false;
        // $wcAjax = defined( 'WC_DOING_AJAX' ) ? filter_var($_GET['wc-ajax'], FILTER_SANITIZE_STRING) : false;
        // jaroncito fix
        $wcAjax = defined( 'WC_DOING_AJAX' ) ? filter_var($_GET['wc-ajax'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : false;
        
        //Category page Hooks
        $button_location_category = $this->rbc_payplan_plugin->get_rbc_gateway()->get_configuration_setting('button_location_category') ? : false;
        if($this->rbc_payplan_utilities->getPageType()=== 'category' && $button_location_category) {
            $category_hook = explode( ':', $button_location_category);
            add_action('woocommerce_' . $category_hook[0], function () use ($use_custom_size) {
                print $this->conditionally_render_rbc_button($use_custom_size);
            }, ( $category_hook[1] === 'before' ) ? 9 : 11 );
        }
        
        // Product Page Hooks
        $button_location_product = $this->rbc_payplan_plugin->get_rbc_gateway()->get_configuration_setting('button_location_product') ? : false;
        if ($this->rbc_payplan_utilities->getPageType() === 'product' && $button_location_product) {
            /**
             * Allow the merchant to display the RBC button under the product price
             * Woocommerce does not have a hook for placing items directly under price, so will 
             * hook onto filter_price and append the RBC button under
             */
            if ($button_location_product == 'get_price_html') {
                add_filter('woocommerce_get_price_html', function($price) use ($useCustomSize) {
                    return $price . '<br />' .
                            $this->conditionally_render_rbc_button($use_custom_size);
                });
            } else {
                add_action('woocommerce_' . $button_location_product, function () use ($use_custom_size) {
                    print $this->conditionally_render_rbc_button($use_custom_size);
                });
            }
        }

        // Add splitpay price underneath product price
        add_action('woocommerce_single_product_summary', function() {
            print '<div class="splitpay-clickable-price" style="margin:0;"></div>';
        });

        $gateway = $this->rbc_payplan_plugin->get_rbc_gateway();
        //Cart summary page hooks
        if ($this->rbc_payplan_utilities->getPageType() === 'cart_summary' || $wcAjax === 'update_shipping_method') {
            global $woocommerce;


            $items = $woocommerce->cart->get_cart();
            foreach ($items as $item) {
                $product_id = $item["product_id"];
                $products_to_exclude = explode(",", $gateway->get_products_to_exclude());
                if (in_array($product_id, $products_to_exclude)) {
                    return;
                }
            }

            $woocommerce->cart->calculate_totals();
            $cart_total = $woocommerce->cart->get_cart_contents_total();

            $price_threshold_enabled = $gateway->is_price_threshold_enabled();
            $rbc_threshold_amount = $gateway->get_price_threshold();

            // The method get_cart_contents_total() returns a string value. Use floatval to check if cart total is under price threshold 
            if ($price_threshold_enabled && floatval($cart_total) < floatval($rbc_threshold_amount)) {
                return;
            }
            
            $button_location_cart = $gateway->get_configuration_setting('button_location_cart');
            if ($button_location_cart) {
                add_action('woocommerce_' . $button_location_cart, function () use ($use_custom_size) {
                    print $this->render_rbc_button(array('buttonId' => 'rbc_checkout_button', 'buttonLocation' => $this->rbc_payplan_utilities->getPageType()), array(), $use_custom_size);
                });
            }
        }

        //Checkout page
        if($this->rbc_payplan_utilities->getPageType() === 'checkout') {
            add_action( 'woocommerce_after_checkout_form', function() {
                print('<div id="rbc_checkout_placeholder"></div>');
            }, 10 );
        }
    }

    
    /**
     * 
     * @global type $product
     * @param type $use_custom_size
     * @return type
     */
    public function conditionally_render_rbc_button($use_custom_size) {
        global $product;
        $is_composite = $product->get_type() === 'composite';
        
        //Check if the product exists
        if(!wc_get_product($product->get_id())) {
            return;
        }
        
        //is the product type supported
        if (!$this->rbc_payplan_plugin->supports_product($product)) {
            return;
        }

        //Check if product should be excluded by product ID
        $products_to_exclude = explode(",", $this->rbc_payplan_plugin->get_rbc_gateway()->get_products_to_exclude());
        if (in_array($product->get_id(), $products_to_exclude)) {
            return;
        }
        
        //Check if product should be excluded by price threshold
        $price_threshold_enabled = $this->rbc_payplan_plugin->get_rbc_gateway()->is_price_threshold_enabled();
        $rbc_threshold_amount = $this->rbc_payplan_plugin->get_rbc_gateway()->get_price_threshold();
        $show_button_for_composite = $this->rbc_payplan_plugin->get_rbc_gateway()->show_button_for_composite();
    
        if(!$price_threshold_enabled || ($is_composite && $show_button_for_composite) || ($product->get_price() >= $rbc_threshold_amount)) {
            //We are rendering the RBC Button here
            return $this->show_rbc_button($product->get_id(), $product->get_type(), $use_custom_size);
        }
        return;
    }
    
    /**
     * 
     * @param type $product_id
     * @param type $product_type
     * @param type $use_custom_size
     * @return string
     */
    public function show_rbc_button($product_id, $product_type,  $use_custom_size = false) {
        if(!$this->rbc_payplan_plugin->get_rbc_gateway()->enabled) {
            return;
        }
        
        $button_id = 'rbc_checkout_button_' . $product_id;
        
        $meta = array(
            'productId' => $product_id,
            'productType' => $product_type
        );

        $opts = array(
                    'buttonId' => $button_id,
                    'buttonLocation' => $this->rbc_payplan_utilities->getPageType(),
                );
        
        //Render RBC Button
        return $this->render_rbc_button($opts, $meta, $use_custom_size);
    }
    
    public function render_rbc_button($opts, $meta = array(), $customSize = false) {
        $data_bind_rbc = $meta;
	$data_bind_rbc['opts'] = $opts;
        
        $button_placeholder = '
<div id="rbc-placeholder" class="rbc-placeholder">
    <div class="rbc-placeholder-inner">
        <div class="rbc-placeholder-center">
            <div id="rbc-placeholder-center-inner" class="rbc-placeholder-center-inner">
                <span class="rbc-placeholder-text">' . $this->rbc_payplan_plugin->get_rbc_gateway()->get_configuration_setting('title') . '</span>
            </div>
        </div>
    </div>
    <div id="rbc-placeholder-icon" class="rbc-placeholder-icon"></div>
</div>';

        $placeholder_content = is_product() ? ($this->rbc_payplan_plugin->get_rbc_gateway()->get_configuration_setting('button_placeholder') ?: $button_placeholder) : '';
        $buttonPreventContent = is_product() ? '<div class="button-prevent" id="button-prevent" style="display:block;"> <span class="buy_error_tip override_tip" data-content="Please complete product configuration">&nbsp;</span></div>' : '';
        return sprintf('<div id="rbc-btn-cntnr">
<div id="%s" data-view-model="woocommerce-gateway-rbcpayplan" class="rbc-checkout-button" data-rbc-default-size="%s" %s>' . $placeholder_content . '</div>' . 
                $buttonPreventContent . '</div>',
                $opts['buttonId'],
                $customSize ? 'false' : 'true',
                "data-bind='rbc: " . json_encode($data_bind_rbc) . "'"
        );
    }

}

Rbc_Payplan_Button::instance();