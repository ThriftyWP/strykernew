<?php 

namespace Rbc_Payplan\Classes;

if (!defined('ABSPATH')) {
    exit;
}

class Rbc_Payplan_Plugin {
    
    
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
     * @var array   Supported product-types
     */
    public $supported_products = array('simple', 'grouped', 'variable', 'composite');

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
        $this->set_rbc_gateway();
    }
    
    public function set_rbc_gateway() {
        if(!$this->rbc_payplan_gateway) {
            $this->rbc_payplan_gateway = new Rbc_Payplan_Gateway();
        }    
    }
    
    public function get_rbc_gateway() {
        $this->set_rbc_gateway();
        return $this->rbc_payplan_gateway;
    }

    
    /**
     * @param $product \WC_Product
     *
     * @return bool
     */
    public function supports_product($product) {
        return in_array($product->get_type(), $this->supported_products);
    }

}