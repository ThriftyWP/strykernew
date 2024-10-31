<?php

namespace Rbc_Payplan\Classes;

if (!defined('ABSPATH')) {
    exit;
}

if (class_exists('\WC_Payment_Gateway')) {

    /**
     * Rbc_Payplan_Gateway class
     * 
     * @extends WC_payment_Gateway
     */
    class Rbc_Payplan_Gateway extends \WC_payment_Gateway {

        /**
         * @var string GatewayId
         */
        const WC_RBC_PAYPLAN_ID = 'rbc_payplan';
        
        /**
         * Sentry
         */
        const SENTRY_SDK = 'https://browser.sentry-cdn.com/6.18.2/bundle.min.js';

        /**
         * Utility helper class
         */
        public $rbc_payplan_utilities = false;
        
        /**
         * Sandbox SDK
         */
        public $sandbox_sdk = 'https://connect-preview.rbc.breadpayments.com/sdk.js';
        
        /**
         * Prod SDK 
         */
        public $prod_sdk = 'https://connect.rbcpayplan.com/sdk.js';
        
        /**
         * Sandbox API
         */
        public $sandbox_api = "https://api-preview.rbc.breadpayments.com/api";
        
        /**
         * Production API
         */
        public $production_api = "https://api.rbcpayplan.com/api";

        /**
         * RBC API instance
         */
        public $rbc_payplan_api = false;

        public function __construct() {

            $this->id = self::WC_RBC_PAYPLAN_ID;
            $this->method_title = __('Payplan by RBC', 'rbc_payplan');
            $this->method_description = __('Jump start your eCommerce business with PayPlan Powered by RBC.', 'rbc_payplan');
            $this->has_fields = false;
            $this->supports = array('refunds', 'products');

            // Load the form fields.
            $this->init_form_fields();

            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'save_advanced_settings'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'validate_product_id_list'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'validate_api_keys'), 11);
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

            add_action('woocommerce_after_checkout_validation', array($this, 'prevent_order_creation_during_validation'), 10, 2);
            add_action('before_woocommerce_init', array($this, 'anonymize_tax_and_shipping_ajax'));
            add_action('before_woocommerce_init', array($this, 'init_rbc_cart'));
            add_action('woocommerce_init', array($this, 'empty_rbc_cart'));
            add_action('init', array($this, 'add_rewrite_tags'));
            add_filter('update_user_metadata', array($this, 'prevent_rbc_cart_persistence'), 10, 5);
            add_action('template_redirect', array($this, 'process_rbc_cart_order'));
            add_action('woocommerce_add_to_cart', array($this, 'handle_rbc_cart_action'), 99, 6);
            
            add_filter('woocommerce_order_status_completed', array($this, 'settle_transaction'));
	    add_filter('woocommerce_order_status_cancelled', array($this, 'cancel_transaction'));
            add_filter('woocommerce_order_status_refunded', array($this, 'process_refund'));
            
            add_action('added_post_meta', array($this,'sendAdvancedShipmentTrackingInfo'),10,4);
            add_action('updated_post_meta', array($this,'sendAdvancedShipmentTrackingInfo'),10,4);
        }

        public function init_form_fields() {
            $this->form_fields = Rbc_Payplan_Form_Fields::fields();
        }

        public function enqueue_scripts() {
            if (!$this->rbc_payplan_utilities) {
                $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
            }

            if ('yes' !== $this->enabled) {
                return;
            }

            //Add RBC SDK
            wp_register_script(
                    'rbc-sdk',
                    $this->get_environment() === 'production' ? $this->prod_sdk : $this->sandbox_sdk,
                    array(),
                    null,
                    true
            );

            //Add JS Helper
            wp_register_script(
                    'knockout',
                    plugins_url('assets/js/v2/knockout-3.5.1.js', WC_RBC_PAYPLAN_MAIN_FILE),
                    array(),
                    WC_RBC_PAYPLAN_VERSION,
                    true
            );

            wp_register_script(
                    'knockback',
                    plugins_url('assets/js/v1/mwp/knockback.min.js', WC_RBC_PAYPLAN_MAIN_FILE),
                    array(),
                    WC_RBC_PAYPLAN_VERSION,
                    true
            );

            wp_register_script(
                    'mwp-settings',
                    plugins_url('assets/js/v1/mwp/mwp.settings.js', WC_RBC_PAYPLAN_MAIN_FILE),
                    array('mwp', 'knockback'),
                    WC_RBC_PAYPLAN_VERSION,
                    true
            );

            wp_register_script(
                    'mwp',
                    plugins_url('assets/js/v1/mwp/mwp.framework.js', WC_RBC_PAYPLAN_MAIN_FILE),
                    array('jquery', 'underscore', 'backbone', 'knockout'),
                    WC_RBC_PAYPLAN_VERSION,
                    true
            );

            wp_localize_script('mwp', 'mw_localized_data', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'ajaxnonce' => wp_create_nonce('mwp-ajax-nonce'),
            ));

            //Add JS Helper
            wp_register_script(
                    'rbc-main',
                    plugins_url('assets/js/v2/main.js', WC_RBC_PAYPLAN_MAIN_FILE),
                    array('rbc-sdk', 'mwp'),
                    WC_RBC_PAYPLAN_VERSION,
                    true
            );

            //Localize params
            $params = array(
                'page_type' => $this->rbc_payplan_utilities->getPageType(),
                'integration_key' => $this->get_integration_key(),
                'product_type' => $this->rbc_payplan_utilities->getProductType(),
                'gateway_token' => self::WC_RBC_PAYPLAN_ID,
                'debug' => $this->rbc_payplan_utilities->toBool($this->get_configuration_setting('debug')),
                'sentry_enabled' => $this->rbc_payplan_utilities->toBool($this->get_configuration_setting('sentry_enabled'))
            );
            
            //Add styling
            wp_register_style(
                    'rbc-main',
                    plugins_url('assets/css/style.css', WC_RBC_PAYPLAN_MAIN_FILE),
                    array(),
                    WC_RBC_PAYPLAN_VERSION
            );

            //Enqueue scripts
            wp_localize_script('rbc-main', 'mw_localized_data', $params);
            wp_enqueue_script('rbc-sdk');
            wp_enqueue_script('rbc-main');
            wp_enqueue_style('rbc-main');
            
            $is_sentry_enabled = $this->rbc_payplan_utilities->toBool($this->get_configuration_setting('sentry_enabled'));
            if ($is_sentry_enabled) {
                wp_enqueue_script('rbc-sentry-import', self::SENTRY_SDK, array('rbc-sdk'));
            }

            //Defer sdk loading
            add_filter('script_loader_tag', array($this, 'add_defer_tags_to_scripts'));
        }

        /**
         * @param $order_id
         * @return array[]
         */
        public function process_payment($order_id) {
            $rbc_tx_token = $this->rbc_payplan_utilities->getParam('rbc_tx_token');
            if (empty($rbc_tx_token)) {
                wc_add_notice("An error occured. Missing " . $this->get_option('title') . " transaction token.", 'error');
                return $this->error_result(esc_html__('Could not complete checkout with ' . $this->get_option('title') . '.', 'rbc_payplan'));
            }

            return $this->process_checkout($order_id, $rbc_tx_token);
        }

        /**
         * @param $order_id
         * @return array[]
         */
        public function process_checkout($order_id, $txToken) {
            try {
                if (!$this->rbc_payplan_utilities) {
                    $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
                }

                $this->rbc_payplan_api = Rbc_Payplan_Api::instance();

                $order = wc_get_order($order_id);

                $transaction = $this->parse_api_response($this->rbc_payplan_api->getTransaction($txToken));

                if ($this->has_error($transaction)) {
                    return $this->error_result($transaction);
                }
                $order->add_meta_data('rbc_tx_id', $transaction['id']);
                $order->save();

                // Validate Transaction Amount is within 2 cents
                $validate_totals_response = $this->rbc_payplan_utilities->validateCalculatedTotals($order, $transaction);
                if (is_wp_error($validate_totals_response)) {
                    wc_add_notice("An error occurred. RBC transaction total does not match order total. Please try again.", 'error');
                    return $this->error_result($validate_totals_response);
                }

                // Authorize Transaction
                $authorized_transaction = $this->parse_api_response(
                        $this->rbc_payplan_api->authorizeTransaction(
                                $txToken, $transaction['totalAmount']['value'],
                                $transaction['totalAmount']['currency'],
                                $order_id
                        )
                );
                if ($this->has_error($authorized_transaction)) {
                    return $this->error_result($authorized_transaction);
                }
                
                // Validate Transaction Status / set order status
                if (strtoupper($authorized_transaction['status']) !== 'AUTHORIZED') {
                    $message = esc_html__('Transaction status is not currently AUTHORIZED', 'rbc_payplan');
                    $order->update_status('failed', $message);
                    return $this->error_result($message);
                }
                $this->add_order_note($order, $authorized_transaction);
                $order->update_status('on-hold');

                // Update billing contact from RBC transaction
                $contact = array_merge(
                        array(
                            'lastName' => $authorized_transaction['billingContact']['name']['familyName'],
                            'firstName' => $authorized_transaction['billingContact']['name']['givenName'],
                            'address2' => '',
                            'country' => $order->get_billing_country()
                        ),
                        $authorized_transaction['billingContact']
                );

                $order->set_address(array(
                    'first_name' => $contact['firstName'],
                    'last_name' => $contact['lastName'],
                    'address_1' => $contact['address']['address1'],
                    'address_2' => $contact['address']['address2'],
                    'city' => $contact['address']['locality'],
                    'state' => $contact['address']['region'],
                    'postcode' => $contact['address']['postalCode'],
                    'country' => $contact['address']['country'],
                    'email' => $contact['email'],
                    'phone' => $contact['phone']
                        ), 'billing');

                $this->updateOrderTxStatus($order, $authorized_transaction);
                $order->save();

                // Settle RBC transaction (if auto-settle enabled)
                if ($this->is_auto_settle()) {
                    $order->update_status('processing');
                }

                /**
                 * To reduce stock from RBC plugin, uncomment below
                 */
                //wc_reduce_stock_levels( $order );
                WC()->cart->empty_cart();

                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order)
                );
            } catch (\Exception $e) {
                Rbc_Payplan_Logger::log( "Error: " . $e->getMessage() );
                return array(
                    'result' => 'failure',
                    'redirect' => ''
                );
            }
        }

        //------------------------------------------------------------------------
        //Custom RBC functions 
        //------------------------------------------------------------------------

        /**
         * Add a RBC status note to the order. Automatically calls the corresponding note function based
         * on the current transaction status.
         *
         * @param $order
         * @param $tx
         */
        public function add_order_note($order, $tx) {
            call_user_func_array(array($this, 'add_note_' . strtolower($tx['status'])), array($order, $tx));
        }

        /**
         * @param $order \WC_Order
         * @param $tx array
         */
        private function add_note_authorized($order, $tx) {
            $note = $this->method_title . " Transaction Authorized for " . wc_price($tx['adjustedAmount']['value'] / 100) . ".";
            $note .= " (Transaction ID " . $tx['id'] . ")";
            $order->add_order_note($note);
        }

        private function add_note_pending($order, $tx) {
            $order->add_order_note($this->method_title . " Transaction ID " . $tx['id'] . " Pending.");
        }

        /**
         * @param $order \WC_Order
         * @param $tx array
         */
        private function add_note_settled($order, $tx) {
            $order->add_order_note($this->method_title . " Transaction ID " . $tx['id'] . " Settled.");
        }

        /**
         * @param $order \WC_Order
         * @param $tx array
         */
        private function add_note_refunded($order, $tx, $amount = null) {
            $refundAmount = $amount ? ' ' . wc_price($amount) . ' ' : '';
            $order->add_order_note($this->method_title . " Transaction ID " . $tx['id'] . $refundAmount . " Refunded.");
        }

        /**
         * @param $order \WC_Order
         * @param $tx array
         */
        private function add_note_canceled($order, $tx) {
            $order->add_order_note($this->method_title . " Transaction ID " . $tx['id'] . " Cancelled.");
        }

        /**
         * @param $order \WC_Order
         * @param $tx array
         */
        private function add_note_cancelled($order, $tx) {
            $order->add_order_note($this->method_title . " Transaction ID " . $tx['id'] . " Cancelled.");
        }
        
        /**
         * @param $order \WC_Order
         * @param $error \WP_Error
         *
         * @return \WP_Error
         */
        private function add_note_error($order, $error) {
            $order->add_order_note($error->get_error_message());
            return $error;
        }

        /**
         * Update the order w/ the current RBC transaction status.
         *
         * @param $order \WC_Order
         * @param $tx array RBC API transaction object
         */
        private function updateOrderTxStatus($order, $tx) {
            $order->update_meta_data('rbc_tx_status', strtolower($tx['status']));
        }

        /**
         * Parse the RBC API response.
         *
         * Pass every response through this function to automatically check for errors and return either
         * the original response or an error response.
         *
         * @param $response array|\WP_Error
         *
         * @return array
         */
        private function parse_api_response($response) {

            if ($response == null) {
                return $response;
            }

            // curl or other error (WP_Error)
            if (is_wp_error($response)) {
                return array('error' => $response->get_error_message());
            }

            // api error
            if (array_key_exists('error', $response)) {
                $description = isset($response['description']) ? $response['description'] : '';
                return array(
                    'error' => $response['error'],
                    'description' => $description,
                );
            }

            return $response;
        }

        /**
         * @param $response array
         *
         * @return bool
         */
        private function has_error($response) {
            if(!is_null($response) && is_array($response)) {
                return ( array_key_exists('error', $response) );
            }
            return array();
        }

        /**
         * @param string|array $error The error message of a transaction error response object.
         *
         * @return array
         */
        private function error_result($error) {
            return array(
                'result' => 'failure',
                'message' => is_array($error) ? $error['error'] : $error
            );
        }

        /**
         * @param $tag
         * @return string
         */
        function add_defer_tags_to_scripts($tag) {
            $scripts_to_defer = array('rbc-sdk');

            foreach ($scripts_to_defer as $current_script) {
                if (true == strpos($tag, $current_script))
                    return str_replace(' src', ' src', $tag);
            }

            return $tag;
        }

        /**
         * Add Cors headers 
         */
        public function add_cors_headers($headers) {
            header("Access-Control-Allow-Origin: " . $this->get_api_base_url());
        }

        /**
         * Load main script
         */
        public function should_load_main_script($page_type) {
            switch (strtolower($page_type)) {
                case 'category':
                case 'product':
                    return strlen($this->get_configuration_setting('button_location_' . strtolower($page_type)));
                case 'cart_summary':
                    return strlen($this->get_configuration_setting('button_location_cart'));
                case 'checkout':
                    return strlen($this->get_configuration_setting('button_location_checkout'));
                default:
                    return true;
            }
        }

        /**
         * Check API and secret Key api for validation
         */
        public function validate_api_keys() {
            $rbc_payplan_api = Rbc_Payplan_Api::instance();
            //Get the API key and secret
            $response = $rbc_payplan_api->get_token();
            $is_valid_response = !is_wp_error($response) && isset($response["token"]);
            if ($is_valid_response) {
                $auth_token = $this->get_option('rbc_auth_token');
                if ($auth_token) {
                    update_option('rbc_auth_token', $response['token']);
                } else {
                    add_option('rbc_auth_token', $response['token']);
                }
            }

            if ($response == null || !$is_valid_response) {
                add_action('admin_notices', array($this, 'display_validate_api_keys_error'));
            }
        }
        
        public function display_validate_api_keys_error() {
            ?>
            <div class="error notice">
                <p>Your API and/or Secret key appear to be incorrect. Please ensure the inputted keys match the keys in your merchant portal.</p>
            </div>
            <?php
        }

        /**
         * Create RBC cart opts
         */
        public function create_cart_opts($order) {
            $orderRef = strval($order->get_id());

            $opts = array(
                "options" => array(
                    "orderRef" => $orderRef,
                    "errorUrl" => home_url() . '?orderRef=' . $orderRef,
                    "completeUrl" => home_url(),
                    "customTotal" => intval($order->get_total() * 100),
                    "disableEditShipping" => true,
                ),
                "cartOrigin" => "woocommerce_carts",
            );

            $opts["options"]["shippingContact"] = array(
                "firstName" => $order->get_shipping_first_name(),
                "lastName" => $order->get_shipping_last_name(),
                "address" => $order->get_shipping_address_1(),
                "address2" => $order->get_shipping_address_2(),
                "city" => $order->get_shipping_city(),
                "state" => $order->get_shipping_state(),
                "zip" => $order->get_shipping_postcode(),
                "phone" => $order->get_billing_phone(),
            );
            $opts["options"]["billingContact"] = array(
                "firstName" => $order->get_billing_first_name(),
                "lastName" => $order->get_billing_last_name(),
                "email" => $order->get_billing_email(),
                "address" => $order->get_billing_address_1(),
                "address2" => $order->get_billing_address_2(),
                "city" => $order->get_billing_city(),
                "state" => $order->get_billing_state(),
                "zip" => $order->get_billing_postcode(),
                "phone" => $order->get_billing_phone(),
            );

            if ($this->rbc_payplan_utilities->isAvataxEnabled()) {
                wc_avatax()->get_order_handler()->calculate_order_tax($order);
            }
            $opts["options"]["tax"] = $this->rbc_payplan_utilities->priceToCents($order->get_cart_tax() + $order->get_shipping_tax());

            /* Add discounts */
            $discount_amount = $this->rbc_payplan_utilities->priceToCents($order->get_discount_total());
            if ($discount_amount > 0) {
                $opts["options"]["discounts"][0] = array(
                    "description" => "Discounts: " . implode(", ", $order->get_coupon_codes()),
                    "amount" => $discount_amount,
                );
            }

            /* Add selected shipping option */
            $opts["options"]["shippingOptions"][0] = array(
                "type" => "Shipping",
                "typeId" => "ShippingId",
                "cost" => intval($order->get_shipping_total() * 100),
            );

            /* Add line items */
            $items = array();
            foreach ($order->get_items() as $item_id => $item_data) {
                $product = wc_get_product($item_data['product_id']);
                if (!$product)
                    break;

                $imageId = $product->get_image_id();
                $imageUrl = $imageId ? wp_get_attachment_image_src($imageId)[0] : "";
                $detailUrl = get_permalink($product->get_id()) ?: "";

                $item = array(
                    "quantity" => $item_data->get_quantity(),
                    "price" => intval($item_data->get_total() * 100),
                    "imageUrl" => $imageUrl,
                    "detailUrl" => $detailUrl,
                    "name" => $product->get_name(),
                    "sku" => $product->get_sku(),
                );
                array_push($items, $item);
            }
            $opts["options"]["items"] = $items;
            return $opts;
        }

        public function validate_cart_opts($opts) {

            if ($opts["options"]["customTotal"] == 0) {
                return "total";
            }
                

            $items = array(
                "firstName", "lastName", "address", "city", "state", "zip", "phone"
            );

            /* Check if billing contact is complete */
            foreach ($items as $item) {
                if (strlen($opts["options"]["billingContact"][$item]) === 0) {
                    return "billing " . $item;
                }
            }

            /* If shipping option provided, check if shipping contact is complete */
            if (count($opts["options"]["shippingOptions"]) > 0) {
                foreach ($items as $item) {
                    if (strlen($opts["options"]["shippingContact"][$item]) === 0) {
                        return "shipping " . $item;
                    }
                }
            }

            return "";
        }

        /**
         * 
         * @param type $data
         * @param type $errors
         * @return type
         */
        public function prevent_order_creation_during_validation($data, $errors) {
            if (!$this->rbc_payplan_utilities) {
                $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
            }
            
            $validate = $this->rbc_payplan_utilities->getParam('rbc_validate');
            if (!$validate) {
                return;
            }

            if (empty($errors->get_error_messages())) {
                wp_send_json(array('result' => 'success'));
                wp_die(0);
            }
        }

        /**
         * 
         * @return string
         */
        public function is_production() {
            return ( $this->get_option('environment') === 'production' );
        }

        /**
         * 
         * @return string
         */
        public function get_environment() {
            return $this->get_option('environment');
        }

        /**
         * 
         * @return string
         */
        public function get_api_key() {
            return $this->get_option($this->get_environment() . '_api_key');
        }

        /**
         * 
         * @return string
         */
        public function get_api_secret_key() {
            return $this->get_option($this->get_environment() . '_api_secret_key');
        }

        /**
         * 
         * @return string
         */
        public function get_api_base_url() {
            return $this->get_environment() === 'production' ? $this->production_api : $this->sandbox_api;
        }

        /**
         * 
         * @return string
         */
        public function get_integration_key() {
            return $this->get_option($this->get_environment() . '_integration_key');
        }

        /**
         * Get a configuration item
         */
        public function get_configuration_setting($setting_slug) {
            if ($this->get_option($setting_slug)) {
                return $this->get_option($setting_slug);
            }
            return null;
        }

        /**
         * 
         * @return string
         */
        public function display_advanced_settings() {
            $settings = $this->get_option('advanced_settings');
            return isset($settings[0]['display_advanced_settings']) && $settings[0]['display_advanced_settings'] === 'on';
        }

        public function is_auto_settle() {
            $settings = $this->get_option('advanced_settings');
            return isset($settings[0]['auto_settle_enabled']) && $settings[0]['auto_settle_enabled'] === 'on';
        }

        public function is_healthcare_mode() {
            $settings = $this->get_option('advanced_settings');
            return isset($settings[0]['healthcare_mode_enabled']) && $settings[0]['healthcare_mode_enabled'] === 'on';
        }

        public function default_show_in_window() {
            $settings = $this->get_option('advanced_settings');
            return isset($settings[0]['show_in_new_window_enabled']) && $settings[0]['show_in_new_window_enabled'] === 'on';
        }

        public function is_price_threshold_enabled() {
            $settings = $this->get_option('advanced_settings');
            return isset($settings[0]['price_threshold_enabled']) && $settings[0]['price_threshold_enabled'] === 'on';
        }

        public function show_button_for_composite() {
            if ($this->is_price_threshold_enabled()) {
                $settings = $this->get_option('advanced_settings');
                return isset($settings[0]['price_threshold_composite']) && $settings[0]['price_threshold_composite'] === 'on';
            } else {
                return false;
            }
        }

        public function get_price_threshold() {
            $settings = $this->get_option('advanced_settings');
            $threshold_exists = $this->is_price_threshold_enabled() && isset($settings[0]['price_threshold_amount']);
            return $threshold_exists ? $settings[0]['price_threshold_amount'] : 0;
        }

        public function is_targeted_financing_enabled() {
            $settings = $this->get_option('advanced_settings');
            return isset($settings[0]['targeted_financing_enabled']) && $settings[0]['targeted_financing_enabled'] === 'on';
        }

        public function get_financing_program_id() {
            $settings = $this->get_option('advanced_settings');
            $financing_id_exists = $this->is_targeted_financing_enabled() && isset($settings[0]['financing_program_id']);
            return $financing_id_exists ? $settings[0]['financing_program_id'] : "";
        }

        public function get_tf_price_threshold() {
            $settings = $this->get_option('advanced_settings');
            $tf_threshold_exists = $this->is_targeted_financing_enabled() && isset($settings[0]['tf_price_threshold_amount']);
            return $tf_threshold_exists ? $settings[0]['tf_price_threshold_amount'] : 0;
        }

        public function get_products_to_exclude() {
            $settings = $this->get_option('advanced_settings');
            return isset($settings[0]['products_to_exclude']) ? $settings[0]['products_to_exclude'] : "";
        }

        /**
         * Save advanced settings
         */
        public function save_advanced_settings() {

            $advanced_settings = array();
            $refs = array(
                'display-advanced-settings' => 'display_advanced_settings',
                'auto-settle' => 'auto_settle_enabled',
                'products-to-exclude' => 'products_to_exclude',
            );

            foreach ($refs as $name => $setting) {
                if (isset($_POST[$name])) {
                    $advanced_settings[$setting] = wc_clean(wp_unslash($_POST[$name]));
                }
            }

            $this->update_option('advanced_settings', array($advanced_settings));
        }

        public function generate_advanced_settings_html() {
            ob_start();

            $display_advanced_settings = $this->display_advanced_settings() ? 'checked' : '';
            $auto_settle_enabled = $this->is_auto_settle() ? 'checked' : '';
            $products_to_exclude = $this->get_products_to_exclude();
            ?>
            <tr>
                <th><?php echo esc_html__('Advanced Settings', 'rbc_payplan'); ?></th>
                <td>
            <?php
            echo '<input type="checkbox" name="display-advanced-settings" id="display-advanced-settings" ' . esc_attr($display_advanced_settings) . '/> 
							Display advanced settings.'
            ?>
                </td>
            </tr>
            <tr class="rbc-advanced-settings">
                <th><?php echo esc_html__('Auto-Settle', 'rbc_payplan'); ?></th>
                <td>
                    <?php
                    echo '<input type="checkbox" name="auto-settle" ' . esc_attr($auto_settle_enabled) . '/> 
							Auto-settle transactions from RBC Payplan.';
                    ?>
                </td>
            </tr>        
            <tr class="rbc-advanced-settings">
                <th><?php echo esc_html__('Disable RBC Payplan for Specific Product IDs', 'rbc_payplan'); ?></th>
                <td>
                    <?php
                    echo '<div><br/>
									<textarea rows="3" cols="20" class="input-text wide-input" name="products-to-exclude" type="textarea">' . $products_to_exclude . '</textarea>
									<p class="description">Enter a comma-separated list of product IDs where RBC should be disabled (ex: ID1, ID2, ID3).</p>
								</div>'
                    ?>
                </td>
            </tr>
            <script type="text/javascript">
                jQuery(function () {
                    var $display_advanced_settings = jQuery('#display-advanced-settings');
                    var $rbc_advanced_settings = jQuery('.rbc-advanced-settings');

                    if ($display_advanced_settings.attr('checked'))
                        $rbc_advanced_settings.show()
                    else
                        $rbc_advanced_settings.hide();

                    $display_advanced_settings.on('change', function () {
                        $rbc_advanced_settings.toggle(this.checked);
                    });
                    return false;
                });
            </script>
            <?php
            return ob_get_clean();
        }

        /**
         * Validate the supplied product Ids
         */
        public function validate_product_id_list() {
            $product_array = explode(",", $this->get_products_to_exclude());
            $unknownProducts = array();
            if (!empty($product_array)) {
                foreach ($product_array as $product_id) {
                    $product_id = trim($product_id);
                    if (!empty($product_id)) {
                        $product = wc_get_product($product_id);
                        if (is_wp_error($product) || !$product)  {
                            array_push($unknownProducts, $product_id);
                        }                 
                    }
                }
            }

            if (count($unknownProducts) > 0) {
                add_filter( 'unknownProducts', function() use($unknownProducts) { return implode(", ", $unknownProducts); } );
                add_action('admin_notices', array($this, 'display_validate_product_id_list_error'));
            }
        }
        
        public function display_validate_product_id_list_error() {
            ?>
            <div class="error notice">
                <p>The following product IDs were not found: "<?= apply_filters('unknownProducts', null);?>"</p>
            </div>
            <?php
        }

        /**
         * 
         * Get the app icon if enabled
         * 
         * @return type
         */
        public function get_icon() {
            if ('yes' === $this->get_option('display_icon')) {
                $icon_src = plugins_url('/assets/image/logo.png?width=100', WC_RBC_PAYPLAN_MAIN_FILE);
                $icon_html = '<img src="' . $icon_src . '" alt="RBC Payplan" style="border-radius:0px"/>';
                return apply_filters('wc_rbc_payplan_checkout_icon_html', $icon_html);
            }
        }

        /**
         * Prevent WooCommerce from loading/saving to the main cart session when performing certain AJAX requests.
         *
         * To properly calculate tax and shipping we need to create a `WC_Cart` session with the selected products
         * and user data. This is complicated by the fact that WooCommerce will attempt to load the user's cart
         * when creating an instance of `WC_Cart`, first by using the cart cookie, then from the logged-in user
         * if the cookie fails.
         *
         * By using a custom null session handler we are able to create in-memory carts, disconnected from the
         * user's main cart session, for the purposes of accurately calculating tax & shipping.
         *
         */
        public function anonymize_tax_and_shipping_ajax() {
            if (!$this->rbc_payplan_utilities) {
                $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
            }
            if (!( defined('DOING_AJAX') && DOING_AJAX )) {
                return;
            }

            // @formatter:off
            $action = $this->rbc_payplan_utilities->getParam('action');
            if (!( $action && strpos($action, 'rbc') === 0 )) {
                return;
            }
            // @formatter:on
            // We want to use the main cart session when the user is on the "view cart" page.
            $source = $this->rbc_payplan_utilities->getParam('source');
            if ($source === 'cart_summary') {
                return;
            }

            if (in_array($action, ['rbc_calculate_tax', 'rbc_calculate_shipping'])) {

                require_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-session-handler.php';

                add_filter('woocommerce_session_handler', function ($handler) {
                    return "\Rbc_Payplan\Classes\Rbc_Payplan_Session_Handler";
                }, 99, 1);
            }
        }

        /**
         * 
         * @return type
         */
        public function init_rbc_cart() {
            if (!$this->rbc_payplan_utilities) {
                $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
            }
            $add_to_cart = $this->rbc_payplan_utilities->getParam('add-to-cart');
            if (!$add_to_cart) {
                return;
            }

            // @formatter:off
            $action = $this->rbc_payplan_utilities->getParam('action');
            if (!( $action && in_array($action, ['rbc_get_options', 'rbc_calculate_tax', 'rbc_calculate_shipping']) )) {
                return;
            }
            // @formatter:on

            require_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-session-handler.php';

            add_filter('woocommerce_session_handler', function ($handler) {
                return "\Rbc_Payplan\Classes\Rbc_Payplan_Session_Handler";
            }, 99, 1);
        }

        public function prevent_rbc_cart_persistence($check, $object_id, $meta_key, $meta_value, $prev_value) {
            if (!$this->rbc_payplan_utilities) {
                $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
            }
            
            $add_to_cart = $this->rbc_payplan_utilities->getParam('add-to-cart');
            if (!$add_to_cart) {
                return $check;
            }

            // @formatter:off'
            $action = $this->rbc_payplan_utilities->getParam('action');
            if (!( $action && in_array($action, ['rbc_get_options', 'rbc_calculate_tax', 'rbc_calculate_shipping']) )) {
                return $check;
            }
            // @formatter:on

            return strpos($meta_key, '_woocommerce_persistent_cart') === 0;
        }

        public function empty_rbc_cart() {
            if (!$this->rbc_payplan_utilities) {
                $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
            }
            
            $add_to_cart = $this->rbc_payplan_utilities->getParam('add-to-cart');
            if (!$add_to_cart) {
                return;
            }

            $action = $this->rbc_payplan_utilities->getParam('action');
            if (!( $action && strpos($action, 'rbc') === 0 )) {
                return;
            }

            WC()->cart->empty_cart();
        }

        public function add_rewrite_tags() {
            add_rewrite_tag('%orderRef%', '([^&]+)');
            add_rewrite_tag('%transactionId%', '([^&]+)');
        }

        public function process_rbc_cart_order() {
            if (!$this->rbc_payplan_utilities) {
                $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
            }
            
            ob_start();

            if (!$this->enabled) {
                return;
            }

            $order_id = get_query_var('orderRef');
            $tx_id = get_query_var('transactionId');

            if (strlen($tx_id) > 0 && strlen($order_id) > 0) {
                /* Complete URL Route */

                $tx = $this->get_transaction($tx_id);
                $order = wc_get_order($order_id);
                $this->rbc_payplan_utilities->validateCalculatedTotals($order, $tx);

                $response = $this->process_rbc_cart_payment($order_id, $tx_id);
                $this->expire_cart($order->get_meta("rbc_cart_id"));
                $order->update_meta_data('rbc_cart_link', 'RBC cart link has expired');

                if ($response['result'] === 'error') {
                    $order->update_status('failed');
                    $order->add_order_note($response['message']);
                    $order->save();

                    $errorInfo = array(
                        'response' => $response,
                        'txId' => $tx_id,
                        'orderId' => $order_id
                    );

                    $this->log_Rbc_issue("error", "[Plugin] " . $response['message'], $errorInfo);
                }

                wp_redirect($order->get_checkout_order_received_url());
                ob_flush();
                exit;
            } else if (strlen($order_id) > 0) {
                /* Error URL Route */

                $errorMessage = 'Note: Customer was not approved for financing or attempted to use an expired RBC cart link';
                $order = wc_get_order($order_id);
                $order->add_order_note($errorMessage);
                $order->save();

                $this->log_Rbc_issue("warning", "[Plugin] " . $errorMessage, array('orderId' => $order_id));

                wp_redirect(home_url());
                ob_flush();
                exit;
            }
        }

        /**
         * @param $order_id
         * @param $tx_id
         * @return array[]
         */
        public function process_rbc_cart_payment($order_id, $tx_id) {
            $order = wc_get_order($order_id);
            $this->rbc_payplan_api = Rbc_Payplan_Api::instance();

            $tx = $this->rbc_payplan_api->getTransaction($tx_id);
            if ($this->has_error($tx)) {
                return array(
                    'result' => 'error',
                    'message' => 'Error retrieving transaction',
                    'tx' => $tx,
                );
            }
            $order->add_meta_data('rbc_tx_id', $tx['id']);
            $order->save();

            $authorized_tx = $this->rbc_payplan_api->authorizeTransaction($tx_id, $tx['totalAmount']['value'], $tx['totalAmount']['currency'], $order_id);
            if ($this->has_error($authorized_tx)) {
                return array(
                    'result' => 'error',
                    'message' => 'Transaction was NOT AUTHORIZED. Please create a new cart and try again.',
                    'tx' => json_encode($authorized_tx)
                );
            }

            $order->update_status('on-hold');
            $this->updateOrderTxStatus($order, $authorized_tx);
            $order->save();

            update_post_meta($order_id, '_payment_method', 'rbc_payplan'); // Ensure RBC is selected payment method
            if ($this->is_auto_settle()) {
                $order->update_status('processing');
            }

            return array(
                'result' => 'success',
            );
        }

        /**
         * 
         * @param type $cart_item_key
         * @param type $product_id
         * @param type $quantity
         * @param type $variation_id
         * @param type $variation
         * @param type $cart_item_data
         * @return type
         */
        public function handle_rbc_cart_action($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
            if (!$this->rbc_payplan_utilities) {
                $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
            }
            
            $add_to_cart = $this->rbc_payplan_utilities->getParam('add-to-cart');
            if (!$add_to_cart) {
                return;
            }

            $action = $this->rbc_payplan_utilities->getParam('action');
            if (!( $action && strpos($action, 'rbc') === 0 )) {
                return;
            }

            try {
                $shippingContact = $this->rbc_payplan_utilities->getParam('shipping_contact');
                $billingContact = $this->rbc_payplan_utilities->getParam('billing_contact');

                $buttonHelper = Rbc_Payplan_Button_Helper::instance();
                $error_message = "Error getting RBC options.";

                switch ($action) {
                    case 'rbc_get_options':
                        wp_send_json_success($buttonHelper->get_rbc_options());
                        break;

                    case 'rbc_calculate_tax':
                        $error_message = "Error calculating sales tax.";
                        $buttonHelper->update_cart_contact($shippingContact, $billingContact);
                        wp_send_json($buttonHelper->getTax());
                        break;

                    case 'rbc_calculate_shipping':
                        $error_message = "Error calculating shipping.";
                        $buttonHelper->update_cart_contact($shippingContact, $billingContact);
                        wp_send_json($buttonHelper->getShipping());
                        break;
                }
            } catch (\Exception $e) {
                wp_send_json_error(__($error_message, 'rbc_payplan'));
            }
        }
        
        public function settle_transaction($order_id) {
            $order = wc_get_order($order_id);
            if ($order->get_payment_method() == self::WC_RBC_PAYPLAN_ID) {
                $rbc_payplan_api = Rbc_Payplan_Api::instance();

                $transactionId = $order->get_meta('rbc_tx_id');
                $transactionStatus = strtolower($order->get_meta('rbc_tx_status'));
                if ('settled' === strtolower($transactionStatus)) {
                    return true;
                }

                if ($transactionStatus === 'unsettled' || $transactionStatus === 'pending') {
                    $transactionStatus = 'authorized';
                }

                $trx = $this->parse_api_response($rbc_payplan_api->getTransaction($transactionId));

                if (strtolower($trx['status']) === 'settled') {
                    $order->update_meta_data('rbc_tx_status', 'settled');
                    return true;
                }

                if (strtolower($trx['status']) === 'authorized') {
                    $transactionStatus = 'authorized';
                }

                if ($transactionStatus !== 'authorized') {

                    if ($transactionStatus === '') {
                        $transactionStatus = 'undefined';
                        $order->update_meta_data('rbc_tx_status', $transactionStatus);
                    }

                    $error = new \WP_Error('rbc-error-settle', __("Transaction status is $transactionStatus. Unable to settle.", 'rbc_payplan'));
                    $order->update_status('on-hold', $error->get_error_message());
                    return $error;
                }

                $amount = $trx['totalAmount']['value'];
                $currency = $trx['totalAmount']['currency'];
                $tx = $this->parse_api_response(
                        $rbc_payplan_api->settleTransaction($transactionId, $amount, $currency));

                if ($this->has_error($tx)) {
                    $tx_duplicate = $this->parse_api_response($rbc_payplan_api->getTransaction($transactionId));
                    if (strtolower($tx_duplicate['status']) === 'settled') {
                        $order->update_meta_data('rbc_tx_status', 'settled');
                        return true;
                    }

                    $error = new \WP_Error('rbc-error-settle', $tx['error']);
                    $order->update_status('on-hold', $error->get_error_message());
                    return $error;
                }

                $this->add_order_note($order, $tx);
                $this->updateOrderTxStatus($order, $tx);
                $order->save();

                return true;
            }
        }

        public function cancel_transaction($order_id) {
            $order = wc_get_order($order_id);
            if ($order->get_payment_method() == self::WC_RBC_PAYPLAN_ID) {
                $rbc_payplan_api = Rbc_Payplan_Api::instance();

                $transactionId = $order->get_meta('rbc_tx_id');
                $transactionStatus = $order->get_meta('rbc_tx_status');

                if (in_array($transactionStatus, ['pending', 'canceled', 'refunded'])) {
                    return $this->add_note_error($order, new \WP_Error('rbc-error-cancel', __("Transaction status is $transactionStatus. Unable to cancel.", 'rbc_payplan')));
                }

                $trx = $this->parse_api_response($rbc_payplan_api->getTransaction($transactionId));

                if ('authorized' === strtolower($transactionStatus)) {
                    $tx = $this->parse_api_response($rbc_payplan_api->cancelTransaction($transactionId, $trx['totalAmount']['value'], $trx['totalAmount']['currency']));
                }

                if ($this->has_error($tx)) {
                    return $this->add_note_error($order, new \WP_Error('rbc-error-cancel', $tx['error']));
                }

                $this->add_order_note($order, $tx);
                $this->updateOrderTxStatus($order, $tx);
                $order->update_meta_data('rbc_tx_status', 'canceled');
                $order->save();
                return true;
            }
        }

        public function process_refund($order_id, $amount = null, $reason = '') {
            $order = wc_get_order($order_id);
            if ($order->get_payment_method() == self::WC_RBC_PAYPLAN_ID) {
                $rbc_payplan_api = Rbc_Payplan_Api::instance();
                $rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();

                $transactionId = $order->get_meta('rbc_tx_id');
                $transactionStatus = $order->get_meta('rbc_tx_status');

                if ('refunded' === strtolower($transactionStatus)) {
                    return true;
                }

                $trx = $this->parse_api_response($rbc_payplan_api->getTransaction($transactionId));

                $refundAmount = $amount !== null ? $rbc_payplan_utilities->priceToCents($amount) : $trx['totalAmount']['value'];

                $tx = $this->parse_api_response($rbc_payplan_api->refundTransaction($transactionId, $refundAmount, $order->get_currency()));

                if ($this->has_error($tx)) {
                    return new \WP_Error('rbc-error-refund', $tx['error']);
                }

                if (strtolower($tx['status']) === 'refunded') {
                    $order->update_status('refunded');
                }

                $this->updateOrderTxStatus($order, $tx);
                $this->add_note_refunded($order, $tx, $amount);
                $order->update_meta_data('rbc_tx_status', 'refunded');
                $order->save();

                return true;
            }
        }
        
        /**
         * Integration with Advanced Shipment Tracking [https://wordpress.org/plugins/woo-advanced-shipment-tracking/]
	 *
	 * Send shipment tracking information to Bread
         * @param type $meta_id
         * @param type $object_id
         * @param type $meta_key
         * @param type $meta_value
         */
        public function sendAdvancedShipmentTrackingInfo($meta_id, $object_id, $meta_key, $meta_value) {
            if ($meta_key === '_wc_shipment_tracking_items') {
                if ($order = wc_get_order($object_id)) {
                    if ($order->get_payment_method() == self::WC_RBC_PAYPLAN_ID) {
                        if ($transactionId = $order->get_meta('rbc_tx_id')) {
                            if (!empty($meta_value)) {
                                // $meta_value is an array of shipments
                                $shipment = end($meta_value);
                                $api = Rbc_Payplan_Api::instance();
                                $response = $api->updateShipment($transactionId, array(
                                    'trackingNumber' => $shipment['tracking_number'],
                                    'carrierName' => $shipment['tracking_provider'],
                                ));
                            }
                        }
                    }
                }
            }
        }

        public function log_Rbc_issue($level, $event, $info) {
            if (!$this->rbc_payplan_utilities) {
                $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
            }
            
            $isSentryEnabled = $this->rbc_payplan_utilities->toBool($this->get_option('sentry_enabled'));

            if ($isSentryEnabled) {
                global $errorLevel, $errorInfo;
                $errorLevel = $level ? $level : "debug";
                $errorInfo = $info ? $info : array();
                \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
                    $scope->setExtra('issue_type', 'RBCIssue');

                    $levelString = 'Sentry\Severity::' . $GLOBALS["errorLevel"];
                    $scope->setLevel($levelString());

                    foreach ($GLOBALS["errorInfo"] as $key => $value) {
                        if ($key === 'txId') {
                            $scope->setTag($key, $value);
                        } else {
                            $scope->setExtra($key, json_encode($value));
                        }
                    }

                    $sentryInfo = $this->get_sentry_info();
                    $scope->setTag('plugin_version', $sentryInfo['plugin_version']);
                    $scope->setTag('merchant_api_key', $this->get_api_key());
                });

                if (is_string($event) || is_array($event)) {
                    \Sentry\captureMessage(json_encode($event));
                } else {
                    \Sentry\captureException($event);
                }
            }
        }

    }

}
