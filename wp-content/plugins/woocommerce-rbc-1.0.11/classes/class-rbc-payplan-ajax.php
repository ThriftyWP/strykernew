<?php

namespace Rbc_Payplan\Classes;

if (!defined('ABSPATH')) {
    exit;
}

class Rbc_Payplan_Ajax extends \WC_AJAX {

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
     *
     * @var type 
     */
    public $rbc_payplan_api;

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

    //Initialize our class
    public function __construct() {
        if (!$this->rbc_payplan_plugin) {
            $this->rbc_payplan_plugin = Rbc_Payplan_Plugin::instance();
        }
        self::add_ajax_events();
    }

    /**
     * Hook in methods - uses WordPress ajax handlers (admin-ajax).
     */
    public static function add_ajax_events() {
        $ajax_events = array(
            'rbc_get_order_pay_opts' => true,
            'rbc_get_options' => true,
            'rbc_calculate_shipping' => true,
            'rbc_calculate_tax' => true,
            'rbc_set_qualstate' => true,
            'rbc_complete_checkout' => true
        );

        foreach ($ajax_events as $ajax_event => $nopriv) {
            add_action('wp_ajax_' . $ajax_event, array(__CLASS__, $ajax_event));
            if ($nopriv) {
                add_action('wp_ajax_nopriv_' . $ajax_event, array(__CLASS__, $ajax_event));
                // WC AJAX can be used for frontend ajax requests.
                add_action('wp_ajax_' . $ajax_event, array(__CLASS__, $ajax_event));
            }
        }
    }

    /**
     * Get RBC Checkout opts
     */
    public static function rbc_get_order_pay_opts() {
        $nonce = isset($_POST['nonce']) ? sanitize_key($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'get_rbc_payplan_opts')) {
            wp_send_json_error('bad_nonce');
            exit;
        }

        if (!$this->rbc_payplan_plugin->get_rbc_gateway()->enabled) {
            return;
        }

        try {
            // url: merchant_url.com/wordpress/checkout/order-pay/{order_id}/?pay_for_order=true&key=wc_order_{hash}
            $url = $_SERVER["HTTP_REFERER"];
            $start = strpos($url, 'order-pay/') + strlen('order-pay/');
            $end = strpos($url, '/?pay_for_order');
            $order_id = substr($url, $start, $end - $start);
            $order = wc_get_order($order_id);
            $opts = $this->rbc_payplan_plugin->get_rbc_gateway()->create_cart_opts($order);
            wp_send_json_success($opts);
        } catch (\Exception $e) {
            Rbc_Payplan_Logger::log( "Error: " . $e->getMessage() );
            wp_send_json_error(__("Error getting RBC options.", 'rbc_payplan'));
        }
    }

    /**
     * 
     * @return type
     */
    public static function rbc_get_options() {
        $rbc_payplan_plugin = Rbc_Payplan_Plugin::instance();

        if (!$rbc_payplan_plugin->get_rbc_gateway()->enabled) {
            return;
        }

        try {
            $button_helper = Rbc_Payplan_Button_Helper::instance();
            $options = $button_helper->get_rbc_options();
            wp_send_json_success($options);
        } catch (\Exception $e) {
            Rbc_Payplan_Logger::log( "Error: " . $e->getMessage() );
            wp_send_json_error(__("Error getting RBC options.", 'rbc_payplan'));
        }
    }

    /**
     * Calculate shipping costs
     */
    public static function rbc_calculate_shipping() {
        if(!$this->rbc_payplan_utilities) {
            $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
        }  
        $button_helper = Rbc_Payplan_Button_Helper::instance(); 
        $this->rbc_payplan_plugin = Rbc_Payplan_Plugin::instance();
        $source = filter_var($this->rbc_payplan_utilities->getParam('source'), FILTER_SANITIZE_STRING);
        try {
            $shipping_contact = $this->rbc_payplan_utilities->getParam('shipping_contact');
            if ($source === 'cart_summary') {
                $button_helper->update_cart_contact($shipping_contact);
            } else {
                $opts = $this->rbc_payplan_utilities->getParam('button_opts');
                $button_helper->create_rbc_cart($opts, $shipping_contact);
            }

            $shippingOptions = $button_helper->getShipping();
            wp_send_json($shippingOptions);
        } catch (\Exception $e) {
            Rbc_Payplan_Logger::log( "Error: " . $e->getMessage() );
            wp_send_json_error(__("Error calculating shipping.", 'rbc_payplan'));
        }
    }

    public static function rbc_calculate_tax() {
        if(!$this->rbc_payplan_utilities) {
            $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
        }  
        $button_helper = Rbc_Payplan_Button_Helper::instance();
        $this->rbc_payplan_plugin = Rbc_Payplan_Plugin::instance();
        $source = filter_var($this->rbc_payplan_utilities->getParam('source'), FILTER_SANITIZE_STRING);
        try {
            $shipping_contact = $this->rbc_payplan_utilities->getParam('shipping_contact');
            $billing_contact = $this->rbc_payplan_utilities->getParam('billing_contact');

            if ($source === 'cart_summary') {
                $button_helper->update_cart_contact($shipping_contact, $billing_contact);
            } else {
                $opts = $this->rbc_payplan_utilities->getParam('button_opts');
                $button_helper->create_rbc_cart($opts, $shipping_contact, $billing_contact);
            }

            $tax = $button_helper->getTax();
            wp_send_json($tax);
        } catch (\Exception $e) {
            Rbc_Payplan_Logger::log( "Error: " . $e->getMessage() );
            wp_send_json_error(__("Error calculating sales tax.", 'rbc_payplan'));
        }
    }

    /**
     * Set customer qualification state when window closed
     * 
     * @return type
     */
    public static function rbc_set_qualstate() {
        $this->rbc_payplan_plugin = Rbc_Payplan_Plugin::instance();
        if (!$this->rbc_payplan_plugin->get_rbc_gateway()->enabled) {
            return;
        }

        if ($this->rbc_payplan_plugin->get_rbc_gateway()->get_configuration_setting('default_payment') === 'no') {
            return;
        }
        
        $customer_data = $this->rbc_payplan_utilities->getParam('customer_data');
        $customer_data_state = filter_var($customer_data, FILTER_SANITIZE_STRING);
        switch ($customer_data_state) {
            case 'PREQUALIFIED':
            case 'PARTIALLY_PREQUALIFIED':
                WC()->session->set('chosen_payment_method', $this->rbc_payplan_plugin->get_rbc_gateway()::WC_RBC_PAYPLAN_ID);
                break;
            default:
                WC()->session->set('chosen_payment_method', '');
        }

        WC()->session->set('rbc_qualstate', $customer_data_state);

        wp_send_json_success();
    }
    
    
    public static function rbc_complete_checkout() {
        $rbc_payplan_plugin = Rbc_Payplan_Plugin::instance();
        $rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
        $rbc_payplan_api = Rbc_Payplan_Api::instance();
        
        if (!$rbc_payplan_plugin->get_rbc_gateway()->enabled) {
            return;
        }
        $tx_id = $rbc_payplan_utilities->getParam('tx_id');
        if (!$tx_id) {
            wp_send_json(array(
                'success' => false,
                'message' => __("Invalid RBC Transaction ID", 'rbc_payplan')
            ));
        }

        $transaction = $rbc_payplan_api->getTransaction($tx_id);
        if (is_wp_error($transaction)) {
            wp_send_json(array(
                'success' => false,
                'message' => 'Error fetching RBC transaction',
                'url' => $rbc_payplan_plugin->get_rbc_gateway()->get_api_base_url()
            ));
        }

        if (!isset($transaction['error']) || !$transaction['error']) {
            if (isset($transaction['externalID']) && $transaction['externalID'] === "") {
                
                //Get the list of cart items
                $checkout_options = Rbc_Payplan_Options_Checkout::instance()->get_options();
                
                $user_email = $transaction['billingContact']['email'];
                $order_user = get_user_by('email', $user_email); 

                if ($order_user === false) {
                    $user_password = wp_generate_password();
                    $user_id = wp_create_user($user_email, $user_password, $user_email);
                    if (is_wp_error($user_id)) {
                        wp_send_json(array('success' => false, 'message' => $user_id->get_error_message()));
                    }
                    $order_user = get_user_by('id', $user_id);
                }

                $billing_last_name = $transaction['billingContact']['name']['familyName'];
                $billing_first_name = $transaction['billingContact']['name']['givenName'];


                $shipping_last_name = $transaction['shippingContact']['name']['familyName'];
                $shipping_first_name = $transaction['shippingContact']['name']['givenName'];

                $order = wc_create_order(array('customer_id' => $order_user->ID));

                /* Set the payment method details */
                $order->set_payment_method($rbc_payplan_plugin->get_rbc_gateway()->id);
                $order->set_payment_method_title($rbc_payplan_plugin->get_rbc_gateway()->method_title);
                $order->set_transaction_id($tx_id);
                $order->add_meta_data('rbc_tx_id', $tx_id); 
                /* Set billing address */
                $order->set_address(array(
                    'first_name' => $billing_first_name,
                    'last_name' => $billing_last_name,
                    'company' => '',
                    'email' => $transaction['billingContact']['email'],
                    'phone' => $transaction['billingContact']['phone'],
                    'address_1' => $transaction['billingContact']['address']['address1'],
                    'address_2' => isset($transaction['billingContact']['address']['address2']) ? $transaction['billingContact']['address']['address1'] : '',
                    'city' => $transaction['billingContact']['address']['locality'],
                    'state' => $transaction['billingContact']['address']['region'],
                    'postcode' => $transaction['billingContact']['address']['postalCode'],
                    'country' => $transaction['billingContact']['address']['country'],
                ), 'billing');

                /* Set shipping address */
                $order->set_address(array(
                    'first_name' => $shipping_first_name,
                    'last_name' => $shipping_last_name,
                    'company' => '',
                    'email' => $transaction['shippingContact']['email'],
                    'phone' => $transaction['shippingContact']['phone'],
                    'address_1' => $transaction['shippingContact']['address']['address1'],
                    'address_2' => isset($transaction['shippingContact']['address']['address2']) ? $transaction['shippingContact']['address']['address2']: '',
                    'city' => $transaction['shippingContact']['address']['locality'],
                    'state' => $transaction['shippingContact']['address']['region'],
                    'postcode' => $transaction['shippingContact']['address']['postalCode'],
                    'country' => $transaction['shippingContact']['address']['country'],
                ), 'shipping');
                //@todo items are not being returned by the API
                /* Add products */
                foreach ($checkout_options['items'] as $item) {
                    /**
                     * WooCommerce may be overriding line breaks ("\n") and causing loss of formatting.
                     * This code modifies the product name so that each line appears as its own div and
                     * creates the appearance of line breaks.
                     */
                    $name = $item['name'];
                    $name = "<div>" . $name . "</div>";
                    $name = str_replace("\n", "</div><div>", $name);

                    $product = wc_get_product($item['sku']);
                    $args = array(
                        'name' => $name,
                        'subtotal' => $rbc_payplan_utilities->priceToDollars($item['price'], $item['quantity']),
                        'total' => $rbc_payplan_utilities->priceToDollars($item['price'], $item['quantity']),
                    );

                    //Set Variation data for variable products *
                    if ($product && $product->get_type() === 'variation') {
                        $variation = array();
                        foreach ($form as $input) {
                            if (preg_match('/attribute_(.+)/', $input['name'], $matches)) {
                                $variation[$matches[1]] = $input['value'];
                            }
                        }

                        foreach ($product->get_attributes() as $key => $value) {
                            if ($value) {
                                $variation[$key] = $value;
                            }
                        }
                        $args['variation'] = $variation;
                    }

                    $order->add_product($product, $item['quantity'], $args);
                }
                

                ///Add shipping
                $shippingItem = new \WC_Order_Item_Shipping();
                $shippingItem->set_method_title($checkout_options['shippingOptions'][0]['type']);
                $shippingItem->set_method_id($checkout_options['shippingOptions'][0]['typeId']);
                $shippingItem->set_total($rbc_payplan_utilities->priceToDollars($transaction['shippingAmount']['value'], 1));
                $order->add_item($shippingItem);
                $order->save();

                //Add discounts
                //@todo There should be better handling of coupons / validation of coupons so that RBC checkout should work irregardless
                if(isset($checkout_options['discounts']) && sizeof($checkout_options['discounts']) > 0) {
                    foreach ($checkout_options['discounts'] as $discount) {
                        $coupon_response = $order->apply_coupon((string) $discount['description']);
                        if (is_wp_error($coupon_response)) {
                            $message = esc_html__("Error: " . $coupon_response->get_error_message(), 'rbc_payplan');
                            $order->update_status("failed", $message);
                            wp_send_json_error(__($message, 'rbc_payplan'));
                        }
                    }
                }
                
                
                /* Add tax */
                /* For merchants using AvaTax, use Avalara method to calculate tax for order */
                /* Tax calculation MUST happen after discounts are added to grab the correct AvaTax amount */
                if ($rbc_payplan_utilities->isAvataxEnabled()) {
                    wc_avatax()->get_order_handler()->calculate_order_tax($order);
                }
                $order->calculate_totals();

                $rbc_payplan_plugin->get_rbc_gateway()->add_order_note($order, $transaction);
                /* Validate calculated totals */
                $validateTotalsResponse = $rbc_payplan_utilities->validateCalculatedTotals($order, $transaction);
                if (is_wp_error($validateTotalsResponse)) {
                    $message = esc_html__("ALERT: Transaction amount does not equal order total.", 'rbc_payplan');
                    $order->update_status("failed", $message);
                    wp_send_json_error(__("ALERT: RBC transaction total does not match order total.", 'rbc_payplan'));
                }
                /* Authorize RBC transaction */
                $transaction = $rbc_payplan_api->authorizeTransaction($tx_id, $transaction['totalAmount']['value'], $transaction['totalAmount']['currency']);
                if (strtoupper($transaction['status']) !== 'AUTHORIZED') {
                    $errorDescription = $transaction["description"];
                    $order->add_order_note($order, $transaction);
                    $errorInfo = $transaction;
                    $errorInfo['txId'] = $tx_id;
                    //$rbc_payplan_plugin->get_rbc_gateway()->log_Rbc_issue("error", "[AjaxHandlers] Transaction failed to authorize.", $errorInfo);

                    wp_send_json_error(array(
                        'message' => __("Transaction FAILED to authorize.", 'rbc_payplan'),
                        'response' => $errorDescription,
                        'spDecline' => '',
                    ));
                }

                $order->update_status('on-hold');
                $order->update_meta_data('rbc_tx_status', 'authorized');
                $order->save();

                /* Settle RBC transaction (if auto-settle enabled) */
                if ($rbc_payplan_plugin->get_rbc_gateway()->is_auto_settle()) {
                    $settle_transaction = $rbc_payplan_api->settleTransaction($tx_id, $transaction['totalAmount']['value'], $transaction['totalAmount']['currency']);
                    if(is_array($settle_transaction) && isset($settle_transaction['status'])) {
                        if (strtoupper($settle_transaction['status']) === 'SETTLED') {
                            $order->update_status('processing');
                            $order->update_meta_data('rbc_tx_status', 'settled');
                            $rbc_payplan_plugin->get_rbc_gateway()->add_order_note($order, $settle_transaction);
                            $order->save();
                        }
                    }
                    
                }

                /* Update RBC transaction with the order id */
                $rbc_payplan_api->updateTransaction($tx_id, array('externalID' => (string) $order->get_id()));

                /* Clear the cart if requested */
                $clear_cart = $rbc_payplan_utilities->getParam('clear_cart');
                if ($clear_cart) {
                    WC()->cart->empty_cart();
                }

                wp_send_json_success(array(
                    'transaction' => $order->get_meta_data('rbc_tx_status'),
                    'order_id' => $order->get_id(),
                    'redirect' => $order->get_checkout_order_received_url()
                ));
            } else {
                wp_send_json_error(array(
                    'message' => __('Transaction has already been recorded to order #', 'rbc_payplan') . $transaction['merchantOrderId']
                ));
            }
        } else {
            wp_send_json_error(array(
                'message' => $transaction['description']
            ));
        }
    }

}

Rbc_Payplan_Ajax::instance();
