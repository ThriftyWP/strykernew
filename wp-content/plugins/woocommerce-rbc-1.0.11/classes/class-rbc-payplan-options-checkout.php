<?php

namespace Rbc_Payplan\Classes;

if (!defined('ABSPATH')) {
    exit;
}

class Rbc_Payplan_Options_Checkout extends Rbc_Payplan_Options_Cart {
    
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
        parent::__construct();
    }
    
    public function get_options($config = null, $form = array()) {
        $source = $this->rbc_payplan_utilities->getParam('source');
        $options = array(
            'allowCheckout' => true,
            'buttonLocation' => $source,
        );

        //Get cart totals
        $cartTotal = $this->rbc_payplan_utilities->priceToCents(WC()->cart->get_total('float'));
        $cartSubtotal = $this->rbc_payplan_utilities->priceToCents(WC()->cart->get_subtotal('float'));

        //Get Discounts
        $discountResponse = $this->getDiscounts();
        $discountTotal = $this->rbc_payplan_utilities->getDiscountTotal($discountResponse["discounts"] ?: array());

        /*
         * Include shipping cost in tax calculations to ensure 
         * Avalara accounts for shipping tax amount 
         */
        $shippingCost = 0;
        $shippingResponse = $this->getShipping();
        if (isset($shippingResponse['shippingOptions'][0]['cost'])) {
            $shippingCost = $shippingResponse['shippingOptions'][0]['cost'];
        }
        $options['shippingCountry'] = WC()->customer->get_shipping_country();

        //Get tax
        $taxResponse = $this->getTax($shippingCost);
        $taxTotal = $taxResponse['tax'];

        //Get items
        $options['items'] = $this->getItems();

        /* Add all fees as line items because Bread SDK doesn't have fee or additional cost option */
        $fee_line_items = $this->getFeesAsLineItems();
        if ($fee_line_items) {
            $options['items'] = array_merge($options['items'], $fee_line_items);
            $cartSubtotal += array_sum(array_column($fee_line_items, 'price'));
        }

        //Totals
        $options['subTotal'] = $cartSubtotal;
        $options['customTotal'] = ($cartSubtotal + $shippingCost + $taxTotal) - $discountTotal;
        $options['cartTotal'] = $cartTotal;

        //Currency options
        $currency = get_woocommerce_currency();
        $options['currency'] = $currency;

        return array_merge($options, $this->getBillingContact(), $this->getShippingContact(), $discountResponse, $taxResponse, $shippingResponse);
    }
    
    /**
     * Get the total shipping for this order.
     *
     * @return array
     */
    public function getShipping() {

        if (!WC()->cart->needs_shipping()) {
            return array();
        }

        $chosenMethods = WC()->session->get('chosen_shipping_methods');

        /*
         * For single-package shipments we can use the chosen shipping method title, otherwise use a generic
         * title.
         */
        WC()->shipping()->calculate_shipping(WC()->cart->get_shipping_packages());
        if (count($chosenMethods) === 1) {
            $chosenMethod = WC()->shipping()->get_shipping_methods()[explode(':', $chosenMethods[0])[0]];
            $shipping[] = array(
                'typeId' => $chosenMethod->id,
                'cost' => $this->rbc_payplan_utilities->priceToCents(WC()->cart->shipping_total),
                'type' => $chosenMethod->method_title
            );
        } else {
            $shipping[] = array(
                'typeId' => 0,
                'cost' => $this->rbc_payplan_utilities->priceToCents(WC()->cart->shipping_total),
                'type' => esc_html__('Shipping', 'rbc_payplan')
            );
        }

        return array('shippingOptions' => $shipping);
    }

    public function getTax($shippingCost) {
        $taxHelperResponse = $this->rbc_payplan_utilities->getTaxHelper($shippingCost);
        return (wc_tax_enabled()) ? array('tax' => $taxHelperResponse['tax']) : array('tax' => 0);
    }
    
    /**
     * Get the contact data as submitted on the checkout form.
     *
     * @return array
     */
    public function getContact() {

        $checkout = WC()->checkout();
        $contact = array();

        $contact['billingContact'] = array(
            'firstName' => $checkout->get_value('billing_first_name'),
            'lastName' => $checkout->get_value('billing_last_name'),
            'address' => $checkout->get_value('billing_address_1'),
            'address2' => $checkout->get_value('billing_address_2'),
            'zip' => $checkout->get_value('billing_postcode'),
            'city' => $checkout->get_value('billing_city'),
            'state' => $checkout->get_value('billing_state'),
            'phone' => substr(preg_replace('/[^0-9]/', '', $checkout->get_value('billing_phone')), -10),
            'email' => $checkout->get_value('billing_email'),
        );

        if ($checkout->get_value('ship_to_different_address')) {
            $contact['shippingContact'] = array(
                'firstName' => $checkout->get_value('shipping_first_name'),
                'lastName' => $checkout->get_value('shipping_last_name'),
                'address' => $checkout->get_value('shipping_address_1'),
                'address2' => $checkout->get_value('shipping_address_2'),
                'zip' => $checkout->get_value('shipping_postcode'),
                'city' => $checkout->get_value('shipping_city'),
                'state' => $checkout->get_value('shipping_state'),
                'phone' => substr(preg_replace('/[^0-9]/', '', $checkout->get_value('billing_phone')), -10),
            );
        } else {
            $contact['shippingContact'] = $contact['billingContact'];
        }

        return $contact;
    }
    
    public function getFeesAsLineItems() {
        /*
         * Returns all fees as line item array. Fee price will be in cents
        */
        WC()->cart->calculate_fees();
        $fee_line_items = [];
        $fees = WC()->cart->get_fees();

        foreach ($fees as $fee) {
            $fee_amount = $this->rbc_payplan_utilities->priceToCents(floatval($fee->amount));
            $line_item = [
                "name" => $fee->name,
                "price" => $fee_amount,
                "quantity" => 1
            ];
            array_push($fee_line_items, $line_item);
        }

        return $fee_line_items;
    }


}