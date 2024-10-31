<?php

namespace Rbc_Payplan\Classes;

if (!defined('ABSPATH')) {
    exit;
}

class Rbc_Payplan_Options_Product {
    
    /**
     * @var $rbc_payplan_plugin
     */
    public $rbc_payplan_plugin = false;

    /**
     *
     * @var type 
     */
    public $rbc_payplan_utilities = false;
    
    /**
     * @var $rbc_payplan_gateway
     */
    public $rbc_payplan_gateway = false;

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

    public function __construct() {
        if (!$this->rbc_payplan_plugin) {
            $this->rbc_payplan_plugin = Rbc_Payplan_Plugin::instance();
        }
        if (!$this->rbc_payplan_utilities) {
            $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
        }
        
        if(!$this->rbc_payplan_gateway) {
            $this->rbc_payplan_gateway = $this->rbc_payplan_plugin->get_rbc_gateway();
        }
    }

    public function get_options($request) {
        $config = $request['config'];
        $quantity = null;
        if(isset($request['quantity'])) {
            $quantity = $request['quantity'];
        }
        $options = array(
            'buttonId' => $config['opts']['buttonId'],
            'allowCheckout' => false
        );
        
        $options['currency'] = get_woocommerce_currency();
        
        $productType = $config['productType'];
        $customTotal = 0;
        
        //Simple products
        if($productType === 'simple') {
            $product = wc_get_product($config['productId']);

            if (!is_object($product)) {
                return $options;
            }
            
            $item = $this->getItem($product, $options['currency'], $quantity);
            $options['items'][] = $item;
            
            $options['customTotal'] = $item['unitPrice']['value'] * $item['quantity'];
        }
        
        //Variable products
        if ($productType === 'variable') {
            $product = wc_get_product($config['productId']);

            if (!is_object($product)) {
                return $options;
            }

            $item = $this->getItem($product, $options['currency'], $quantity);

            if (isset($request['variation_id'])) {
                $variations = $product->get_available_variations();
                if (count($variations) >= 1) {
                    foreach ($variations as $variation) {
                        if ($request['variation_id'] == $variation['variation_id']) {
                            //Update to the variation price
                            $item['unitPrice']['value'] = $this->rbc_payplan_utilities->priceToCents($variation['display_price']);
                            $item['sku'] = $variation['sku'];
                        }
                    }
                }
            }
            $options['items'][] = $item;
            $options['customTotal'] = $item['unitPrice']['value'] * $item['quantity'];
        }
        
        //Grouped products
        if($productType === 'grouped') {
            $customTotal = 0;
            $product = wc_get_product($config['productId']);
            
            if (!is_object($product)) {
                return $options;
            }

            //When we first load, fetch minimum price
            if(is_null($quantity)) {
                $item = $this->getItem($product, $options['currency'], $quantity);
                $options['items'][] = $item;
                $customTotal = $item['unitPrice']['value'];
            } else {             
                $children = $product->get_children();
                foreach($children as $childProduct) {
                    foreach($quantity as $id => $value) {
                        if($id == $childProduct && $value >= 1) {
                            $product = wc_get_product($childProduct);
                            $item = $this->getItem($product, $options['currency'], $value);                           
                            $options['items'][] = $item;
                            $customTotal += $item['unitPrice']['value'] * $item['quantity'];
                        }
                    }
                }
            }
            $options['customTotal'] = $customTotal;
        }
        
        if($productType === 'composite') {
            $product = wc_get_product($config['productId']);
            $item = $this->getItem($product, $options['currency'], $quantity);
            $compositePrice = $product->get_composite_price() ?: 0;
            $item['unitPrice']['value'] = $this->rbc_payplan_utilities->priceToCents($compositePrice);    
            $options['items'][] = $item;
            $options['customTotal'] = $item['unitPrice']['value'];
        }
        
        return array_merge($options, $this->getBillingContact(), $this->getShippingContact());
    }
    
    /**
     * Gets the RBC `item` properties for a product on 2.0
     *
     * Variable, grouped and other product types eventually resolve to a simple or variation product
     * which have a common set of properties we can use to build out the item array.
     *
     * @param $product  \WC_Product
     *
     * @return array
     */
    protected function getItem($product, $currency, $quantity = null) {
        $item = array(
            'name' => wp_strip_all_tags($product->get_formatted_name()),
            'quantity' => is_null($quantity) ? $product->get_min_purchase_quantity() : (int) $quantity,
            'shippingCost' => [
                'value' => 0,
                'currency' => $currency
            ],
            'shippingDescription' => '',
            'unitTax' => [
                'value' => 0,
                'currency' => $currency
            ],
            'unitPrice' => [
                'currency' => $currency,
                'value' => $this->rbc_payplan_utilities->priceToCents($product->get_price())
            ],
            'sku' => strval($product->get_id()),
        );

        return array_merge($item, $this->getProductImageUrl($product));
    }
    
    /**
     * @param $product  \WC_Product
     *
     * @return array
     */
    protected function getProductImageUrl($product) {
        $imageId = $product->get_image_id();
        if ($imageId) {
            return array('imageUrl' => wp_get_attachment_image_src($imageId)[0]);
        } else {
            return array();
        }
    }
    
    public function getBillingContact() {
        
        if ($this->rbc_payplan_gateway->get_configuration_setting('pre_populate') === 'no') {
            return array();
        }
        
        /*
         * User has already pre-qualified. Do not send new contact information from these pages.
         */
        $qualstate = WC()->session->get('rbc_qualstate') ?: 'NONE';
        if (in_array($qualstate, ['PREQUALIFIED', 'PARTIALLY_PREQUALIFIED'])) {
            return array($qualstate);
        }
        
        /*
         * User has not logged in or entered any checkout data.
         */
        if (WC()->customer->get_billing_address() === '') {
            return array();
        }

        $required = array('first_name', 'last_name', 'address_1', 'postcode', 'city', 'state', 'phone', 'email');

        
        $customer = WC()->customer;
        foreach ($required as $field) {
            if ("" === call_user_func(array($customer, 'get_billing_' . $field))) {
                return array();
            }
        }
        return array(
            'billingContact' => array(
                'firstName' => $customer->get_billing_first_name(),
                'lastName' => $customer->get_billing_last_name(),
                'address' => $customer->get_billing_address_1(),
                'address2' => $customer->get_billing_address_2(),
                'zip' => preg_replace('/[^0-9]/', '', $customer->get_billing_postcode()),
                'city' => $customer->get_billing_city(),
                'state' => $customer->get_billing_state(),
                'phone' => substr(preg_replace('/[^0-9]/', '', $customer->get_billing_phone()), - 10),
                'email' => $customer->get_billing_email()
            )
        );
    }
    
    public function getShippingContact() {
        
        if ($this->rbc_payplan_gateway->get_configuration_setting('pre_populate') === 'no') {
            return array();
        }
        
        /*
         * User has already pre-qualified. Do not send new contact information from these pages.
         */
        $qualstate = WC()->session->get('rbc_qualstate') ?: 'NONE';
        if (in_array($qualstate, ['PREQUALIFIED', 'PARTIALLY_PREQUALIFIED'])) {
            return array($qualstate);
        }
        
        /*
         * User has not logged in or entered any checkout data.
         */
        if (WC()->customer->get_shipping_address() === '') {
            return array();
        }

        $required = array('first_name', 'last_name', 'address_1', 'address_2', 'zip', 'city', 'state', 'phone');

        
        $customer = WC()->customer;
        foreach ($required as $field) {
            if ("" === call_user_func(array($customer, 'get_shipping_' . $field))) {
                return array();
            }
        }
        return array(
            'shippingContact' => array(
                'firstName' => $customer->get_shipping_first_name(),
                'lastName' => $customer->get_shipping_last_name(),
                'address' => $customer->get_shipping_address_1(),
                'address2' => $customer->get_shipping_address_2(),
                'zip' => preg_replace('/[^0-9]/', '', $customer->get_shipping_postcode()),
                'city' => $customer->get_shipping_city(),
                'state' => $customer->get_shipping_state(),
                'phone' => substr(preg_replace('/[^0-9]/', '', $customer->get_shipping_phone()), - 10),
            )
        );
    }

}
