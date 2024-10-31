<?php

namespace Rbc_Payplan\Classes;

if (!defined('ABSPATH')) {
    exit;
}

class Rbc_Payplan_Api {

    /**
     * Reference singleton instance of this class
     *
     * @var $instance
     */
    private static $instance;

    /**
     * Gateway instance
     */
    public $rbc_payplan_gateway = false;

    /**
     * API auth credentials
     *
     * @var $basic_auth_credentials
     */
    public $basic_auth_credentials;

    /**
     *
     * @var class
     */
    public $rbc_payplan_utilities = false;
    
    public $api_base_url;
    public $integration_key;


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
        $this->set_rbc_payplan_gateway();
        $this->set_rbc_payplan_utilities();
        $this->api_base_url = $this->rbc_payplan_gateway->get_api_base_url();
        $this->integration_key = $this->rbc_payplan_gateway->get_integration_key();
        $this->api_key = $this->rbc_payplan_gateway->get_api_key();
        $this->api_secret = $this->rbc_payplan_gateway->get_api_secret_key();
        $this->basic_auth_credentials = 'Basic ' . base64_encode($this->rbc_payplan_gateway->get_api_key() . ':' . $this->rbc_payplan_gateway->get_api_secret_key());
        
    }
    
    
    protected function set_rbc_payplan_gateway() {
        if(!$this->rbc_payplan_gateway) {
            $this->rbc_payplan_gateway = new Rbc_Payplan_Gateway();
        }
    }
    
    protected function set_rbc_payplan_utilities() {
        if(!$this->rbc_payplan_utilities) {
            $this->rbc_payplan_utilities = Rbc_Payplan_Utilities::instance();
        }
    }
    

    /**
     * Instance of
     *
     * @return object
     */
    public function get_rbc_gateway() {
        if($this->rbc_payplan_gateway) {
            return $this->rbc_payplan_gateway;
        }

        $this->rbc_payplan_gateway = new Rbc_Payplan_Gateway();
        return $this->rbc_payplan_gateway;
    }

    public function get_token() {
        $wp_remote = 'wp_remote_post';

        $api_url = join('/', [rtrim($this->api_base_url, '/'), 'auth/service/authorize']);

        $result = call_user_func($wp_remote, $api_url, array(
            'method' => 'POST',
            'headers' => array('Content-Type' => 'application/json', 'Authorization' => $this->basic_auth_credentials),
        ));

        if (!is_wp_error($result)) {
            return json_decode($result['body'], true);
        }

        return $result;
    }

    public function getTransaction($tx_id) {
        $token = get_option('rbc_auth_token');
        $response = $this->makeRequest('GET', $token, $this->api_base_url, "transaction/$tx_id", []);
        return $response;
    }

    public function authorizeTransaction($tx_id, $amount, $currency, $order_id = null) {   
        $params = '{"amount": {"currency":"' . $currency . '","value":' . $amount . '}}';
        $token = get_option('rbc_auth_token');
        $response  = $this->makeRequest('POST', $token, $this->api_base_url, "transaction/$tx_id/authorize", $params, false);
        return $response;
    }

    public function cancelTransaction($tx_id, $amount, $currency, $order_id = null) {
        $params = '{"amount": {"currency":"' . $currency . '","value":' . $amount . '}}';
        $token = get_option('rbc_auth_token');
        return $this->makeRequest('POST', $token, $this->api_base_url, "transaction/$tx_id/cancel", $params, false);
    }
    
    public function updateTransaction($tx_id, $params = array()) {
        $token = get_option('rbc_auth_token');
        $response = $this->makeRequest('PATCH', $token, $this->api_base_url, "transaction/$tx_id", $params);
        return $response;
    }

    public function settleTransaction($tx_id, $amount, $currency, $order_id = null) {
        $params = '{"amount": {"currency":"' . $currency . '","value":' . $amount . '}}';
        $token = get_option('rbc_auth_token');
        $response = $this->makeRequest('POST', $token, $this->api_base_url, "transaction/$tx_id/settle", $params, false);
        return $response;
    }

    public function refundTransaction($tx_id, $amount, $currency, $order_id = null) {
        $params = '{"amount": {"currency":"' . $currency . '","value":' . $amount . '}}';
        error_log($params);
        $token = get_option('rbc_auth_token');
        return $this->makeRequest('POST', $token, $this->api_base_url, "transaction/$tx_id/refund", $params, false);
    }
    
    public function updateShipment($tx_id, $payload) {
        $token = get_option('bread_auth_token');
        $params = '{"tracking_number":"' . $payload['trackingNumber'] . '","carrier":"' . $payload['carrierName'] . '"}';
        return $this->makeRequest('POST', $token, $this->api_base_url, "transaction/$tx_id/fulfillment", $params, false);
    }

    public function makeRequest($method, $token, $base_url, $endpoint, $payload, $jsonEncode = true) {
        $wp_remote = $method == 'GET' ? 'wp_remote_get' : 'wp_remote_post';
        $api_url = join('/', [rtrim($base_url, '/'), $endpoint]);
        $wp_payload = $method == 'GET' ? $payload : json_encode($payload);
        if (!$jsonEncode) {
            $wp_payload = $payload;
        }

        $request = [
            'method' => $method,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token),
            'body' => $wp_payload,
        ];

        Rbc_Payplan_Logger::log( "{$api_url} request: " . print_r( $request, true ) );

        $result = call_user_func($wp_remote, $api_url, $request);

        $authorization_error_check = wp_remote_retrieve_response_code($result);
        if ($authorization_error_check == '403' || $authorization_error_check == '401') {
            $response = $this->get_token();
            $is_valid_response = !is_wp_error($response) && isset($response["token"]);
            if ($is_valid_response) {
                $rbc_auth_token = get_option('rbc_auth_token');
                if ($rbc_auth_token) {
                    update_option('rbc_auth_token', $response['token']);
                } else {
                    add_option('rbc_auth_token', $response['token']);
                }
            }

            $result = call_user_func($wp_remote, $api_url, array(
                'method' => $method,
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $response['token']),
                'body' => $wp_payload,
            ));

            $authorization_error_check = wp_remote_retrieve_response_code($result);
            if ($authorization_error_check === '403' || $authorization_error_check === '401') {
                return array(
                    'error' => 'jwt_auth_error',
                    'description' => 'Token validation error'
                );
            }
        }

        if ( is_wp_error( $response ) || empty( $result['body'] ) ) {
            Rbc_Payplan_Logger::log(
                'Error response: ' . print_r( $result, true ) . PHP_EOL . 'Failed request: ' . print_r(
                    [
                        'api_url'         => $api_url,
                        'request'         => $request
                    ],
                    true
                )
            );
        }

        if (!is_wp_error($result)) {
            return json_decode($result['body'], true);
        }

        return $result;
    }

}
