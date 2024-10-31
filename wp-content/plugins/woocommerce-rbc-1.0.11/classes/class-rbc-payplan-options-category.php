<?php

namespace Rbc_Payplan\Classes;

if (!defined('ABSPATH')) {
    exit;
}

class Rbc_Payplan_Options_Category extends Rbc_Payplan_Options_Cart {

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

    public function get_options($config) {
        $options = array(
            'buttonId' => $config['opts']['buttonId'],
            'allowCheckout' => false, // disable checkout from category pages
        );

        $items = $this->getItemsCategory($options, $config);
        $options['items'] = $items;
        $currency = get_woocommerce_currency();
        $options['currency'] = $currency;

        return array_merge($options, $this->getBillingContact(), $this->getShippingContact());
    }

    /**
     * @return array
     */
    public function getItemsCategory(&$options, $config) {
        $product = wc_get_product($config['productId']);

        switch ($product->get_type()) {
            case 'simple':
                return $this->getItemsSimple($config);
            case 'grouped':
                return $this->getItemsGrouped($options, $config);
            case 'variable':
                return $this->getItemsVariable($options, $config);
            case 'composite':
                return $this->getItemsComposite($options, $config);
            default:
                return array();
        }
    }

    public function getItemsSimple($config) {
        return array($this->getItem(wc_get_product($config['productId'])));
    }

    public function getItemsGrouped(&$options, $config) {
        /*
         * Borrowed From `WC_Product_Grouped->get_price_html`
         */

        /** @var \WC_Product_Grouped $product */
        $product = wc_get_product($config['productId']);
        $children = array_filter(array_map('wc_get_product', $product->get_children()), 'wc_products_array_filter_visible_grouped');

        $prices = array();

        /** @var \WC_Product $child */
        foreach ($children as $child) {
            if ('' !== $child->get_price()) {
                $prices[] = $this->rbc_payplan_utilities->priceToCents($child->get_price());
            }
        }

        $options['allowCheckout'] = false;
        $options['asLowAs'] = true;
        $options['customTotal'] = min($prices);

        return array();
    }

    public function getItemsVariable(&$options, $config) {
        /*
         * Borrowed from `WC_Products_Variable->get_price_html`
         */

        $options['allowCheckout'] = false;
        $options['asLowAs'] = true;

        /** @var \WC_Product_Variable $product */
        $product = wc_get_product($config['productId']);

        $prices = $product->get_variation_prices();

        if (empty($prices['price'])) {
            $options['customTotal'] = $this->rbc_payplan_utilities->priceToCents($product->get_price());
        } else {
            $variationPrices = array_map(function ($price) {
                return $this->rbc_payplan_utilities->priceToCents($price);
            }, $prices['price']);

            $options['customTotal'] = min($variationPrices);
        }

        return array();
    }

    public function getItemsComposite(&$options, $config) {

        /** @var \WC_Product_Composite $product */
        $product = wc_get_product($config['productId']);
        $compositePrice = $product->get_composite_price() ?: 0;
        $productTotal = $product->get_price() ?: 0;

        $options['allowCheckout'] = false;
        $options['asLowAs'] = true;
        $options['customTotal'] = $this->rbc_payplan_utilities->priceToCents($compositePrice) ?: $this->rbc_payplan_utilities->priceToCents($productTotal);

        return array();
    }

}
