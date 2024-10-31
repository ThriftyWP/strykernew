<?php

/**
 * Plugin Name: Payplan by RBC
 * Description: Jump start your eCommerce business with PayPlan Powered by RBC on your Woocommerce site
 * Author: RBC
 * Author URI: https://www.rbcpayplan.com/
 * Version: 1.0.11
 * Text Domain: rbc_payplan
 * Domain Path: /i18n/languages/
 * WC requires at least: 3.0.0
 * WC tested up to: 7.6.0
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright: (c) 2022, RBC Payplan
 */

if (!defined('ABSPATH')) {
    die('Access denied.');
}

//Require minimums and constants
define('WC_RBC_PAYPLAN_VERSION', '1.0.11');
define('WC_RBC_PAYPLAN_MIN_PHP_VER', '5.6.0');
define('WC_RBC_PAYPLAN_MIN_WC_VER', '3.4.0');
define('WC_RBC_PAYPLAN_MAIN_FILE', __FILE__);
define('WC_RBC_PAYPLAN_PLUGIN_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
define('WC_RBC_PAYPLAN_PLUGIN_URL', untrailingslashit(plugin_dir_url(__FILE__)));

if (!class_exists('WC_Rbc_Payplan')) {

    /**
     * Class WC_Rbc_Payplan
     */
    class WC_Rbc_Payplan {

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
         * Private clone method to prevent cloning of the instance of the
         * *Singleton* instance.
         *
         * @return void
         */
        private function __clone() {
            wc_doing_it_wrong(__FUNCTION__, __('Nope'), '1.0');
        }

        /**
         * Private unserialize method to prevent unserializing of the *Singleton*
         * instance.
         *
         * @return void
         */
        public function __wakeup() {
            wc_doing_it_wrong(__FUNCTION__, __('Nope'), '1.0');
        }

        /**
         * Notices
         * 
         * @var array
         */
        public $notices = array();

        protected function __construct() {
            add_action('admin_notices', array($this, 'admin_notices'), 15);
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
            add_filter('plugin_row_meta',array($this, 'plugin_meta_links'),10,2);
            add_action('plugins_loaded', array($this, 'init'));
        }

        /**
         * Init plugin after plugins have been loaded
         */
        public function init() {
            //Load our gateway itself
            $this->init_gateway();
        }

        /**
         * Display any notices that we have so far
         */
        public function admin_notices() {
            foreach ((array) $this->notices as $key => $message) {
                echo "<div class='" . esc_attr($notice['class']) . "'><p>";
                echo wp_kses($notice['message'], array('a' => array('href' => array())));
                echo '</p></div>';
            }
        }

        /**
         * Adds plugin actions link
         * 
         * @param array $links Plugin action links for filtering
         * @return array Filter links
         */
        public function plugin_action_links($links) {
            $setting_link = $this->get_setting_link();
            $plugin_links = array(
                '<a href="' . $setting_link . '">' . __('Settings') . '</a>',
            );
            return array_merge($plugin_links, $links);
        }
        
        /**
         * Plugin meta info
         * 
         * @param type $links
         * @param type $file
         * @return string
         */
        public function plugin_meta_links($links, $file) {
            if(strpos($file, basename(__FILE__))) {
                $links[] = '<a href="https://www.rbcpayplan.com/" target="_blank" title="Get started"> Get Started </a>';
                $links[] = '<a href="https://rbcpayplan.readme.io/rbc-onboarding/docs/woocommerce" target="_blank" title="Payplan by RBC Docs"> Docs </a>';
            }
            return $links;
        }

        /**
         * Get setting link.
         *
         *
         * @return string Setting link
         */
        public function get_setting_link() {
            $section_slug = 'rbc_payplan';

            $params = array(
                'page' => 'wc-settings',
                'tab' => 'checkout',
                'section' => $section_slug,
            );

            $admin_url = add_query_arg($params, 'admin.php');
            return $admin_url;
        }

        /**
         * Include all the files needed for the plugin
         * 
         */
        public function init_gateway() {
            if (!class_exists('\WC_Payment_Gateway')) {
                return;
            }

            //Classes
            include_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-utilities.php';
            include_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-form-fields.php';
            include_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-api.php';
            include_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-gateway.php';
            include_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-plugin.php';
            include_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-ajax.php';
            include_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-options-cart.php';
            include_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-options-checkout.php';
            include_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-button-helper.php';
            include_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-options-category.php';
            include_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-options-product.php';
            include_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-options-cart-checkout.php';
            include_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-button.php';
            include_once WC_RBC_PAYPLAN_PLUGIN_PATH . '/classes/class-rbc-payplan-logger.php';
            
            add_filter('woocommerce_payment_gateways', array($this, 'add_gateways'));
        }

        /**
         * Add the Gateway to Woocommerce
         * 
         * @params array $methods 
         * @return array $methods
         */
        public function add_gateways($methods) {
            $methods[] = 'Rbc_Payplan\Classes\Rbc_Payplan_Gateway';
            return $methods;
        }

    }

}

$GLOBALS['wc_rbc_payplan'] = WC_Rbc_Payplan::instance();
