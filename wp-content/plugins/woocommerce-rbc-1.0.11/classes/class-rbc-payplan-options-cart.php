<?php

namespace Rbc_Payplan\Classes;

if (!defined('ABSPATH')) {
    exit;
}

class Rbc_Payplan_Options_Cart {
    
    /**
     * @var $rbc_payplan_plugin
     */
    public $rbc_payplan_plugin = false;
    
    /**
     *
     * @var type 
     */
    public $rbc_payplan_utilities = false;
    
    public function __construct() {
        if(!$this->rbc_payplan_plugin) {
            $this->rbc_payplan_plugin = Rbc_Payplan_Plugin::instance();
        }
        
        if(!$this->rbc_payplan_utilities) {
            $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
        }
    }
    
    /**
     * Get shopping cart items formatted for RBC `opts.items`
     *
     * NOTE: Cart items should always be discrete products.
     *
     * Grouped products for example, will appear in the cart as one line item per child product
     * selected. Similarly, composite products are added as multiple discrete products along with
     * the parent product. Variable & simple products are always added as a single Variation/Simple
     * product respectively.
     *
     * This is why here we can bypass the product-type button classes and treat every product  as
     * a simple product once it is in the cart.
     *
     * @return array
     */
    public function getItems() {

        /*
         * NOTE: In Variable products, the value in `$item['data']` is the selected variation of the main product.
         */
        $cart = WC()->cart->get_cart();

        $items = array();
        foreach ($cart as $id => $line) {
            $product = $line['data'];
            $item = $this->getItem($product);

            /*
             * Append extra item data to the item name (options, custom text, etc)
             */
            if (version_compare(WC()->version, '3.3.0', ">=")) {
                $item_data = wp_strip_all_tags(html_entity_decode(wc_get_formatted_cart_item_data($line, true)));
            } else {
                $item_data = wp_strip_all_tags(html_entity_decode(WC()->cart->get_item_data($line, true)));
            }

            if (strlen($item_data) > 0) {
                $item['name'] .= "\n" . $item_data;
            }

            /*
             * Using `line_subtotal` here since `line_total` is the discounted price. Discounts are applied
             * separately in the `discounts` element of the RBC options.
             */
            if (array_key_exists('composite_parent', $line)) {
                $composite_parent = $line['composite_parent'];
                $items[$composite_parent]['price'] += $this->rbc_payplan_utilities->priceToCents($line['line_subtotal'] / $items[$composite_parent]['quantity']);

                $item['price'] = 0;
            } else {
                $item['price'] = $this->rbc_payplan_utilities->priceToCents($line['line_subtotal'] / $line['quantity']);
            }

            $item['quantity'] = $line['quantity'];

            $item = array_merge($item, $this->getProductImageUrl($product));

            $items[$id] = $item;
        }

        return array_values($items);
    }

    public function getDiscounts() {

        /*
         * Borrowed from plugins/woocommerce/includes/wc-cart-functions.php->wc_cart_totals_coupon_html
         */
        $cart = WC()->cart;
        if (!$cart->has_discount()) {
            return array();
        }

        $discounts = array();

        /**
         * @var string $code
         * @var \WC_Coupon $coupon
         */
        foreach ($cart->get_coupons() as $code => $coupon) {
            if ($amount = $cart->get_coupon_discount_amount($code)) {
                $discounts[] = array(
                    'amount' => $this->rbc_payplan_utilities->priceToCents($amount),
                    'description' => $code
                );
            }
        }
        return array('discounts' => $discounts);
    }
    
    /**
     * Gets the RBC `item` properties for a product.
     *
     * Variable, grouped and other product types eventually resolve to a simple or variation product
     * which have a common set of properties we can use to build out the item array.
     *
     * @param $product  \WC_Product
     *
     * @return array
     */
    protected function getItem($product) {
        $item = array(
            'name' => wp_strip_all_tags($product->get_formatted_name()),
            'price' => $this->rbc_payplan_utilities->priceToCents($product->get_price()),
            'sku' => strval($product->get_id()),
            'detailUrl' => $product->get_permalink(),
            'quantity' => $product->get_min_purchase_quantity()
        );

        return array_merge($item, $this->getProductImageUrl($product));
    }

    /**
     * @param $product  \WC_Product
     *
     * @return array
     */
    protected function getProductImageUrl($product) {
        if ($imageId = $product->get_image_id()) {
            return array('imageUrl' => wp_get_attachment_image_src($imageId)[0]);
        } else {
            return array();
        }
    }
    
    /**
     * Get the billing contact for the current cart session.
     *
     * All pages except checkout. See \WooCommerceGateway\ButtonCart->getContact
     *
     * @return array
     */
    public function getContact() {
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
            ),
            'shippingContact' => array(
                    'firstName' => $customer->get_shipping_first_name(),
                    'lastName' => $customer->get_shipping_last_name(),
                    'address' => $customer->get_shipping_address_1(),
                    'address2' => $customer->get_shipping_address_2(),
                    'zip' => preg_replace('/[^0-9]/', '', $customer->get_shipping_postcode()),
                    'city' => $customer->get_shipping_city(),
                    'state' => $customer->get_shipping_state(),
                    'phone' => substr(preg_replace('/[^0-9]/', '', $customer->get_shipping_phone()), - 10)
                )
        );
    }
    
    /**
     * Get the billing contact for the current cart session.
     *
     * All pages except checkout. 
     *
     * @return array
     */
    public function getBillingContact() {
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

    /**
     * Get the shipping contact for the current cart session.
     *
     * All pages except checkout. getContact
     *
     * @return array
     */
    public function getShippingContact() {
        /*
         * User has not logged in or entered any checkout data.
         */
        if (WC()->customer->get_shipping_address() === '') {
            return array();
        }

        $required = array('first_name', 'last_name', 'address_1', 'postcode', 'city', 'state', 'phone');

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