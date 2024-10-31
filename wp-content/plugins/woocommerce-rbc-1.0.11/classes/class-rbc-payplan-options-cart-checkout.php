<?php

namespace Rbc_Payplan\Classes;

if (!defined('ABSPATH')) {
    exit;
}

class Rbc_Payplan_Options_Cart_Checkout extends Rbc_Payplan_Options_Cart {

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

    public function get_options($config, $form = array()) {
        $options = array(
            'allowCheckout' => false,
            'buttonId' => $config['opts']['buttonId'],
        );


        $this->updateCartQuantities($form);

        $options['items'] = $this->getItems();

        $cartTotal = $this->rbc_payplan_utilities->priceToCents(WC()->cart->get_total('float'));
        $options['customTotal'] = $cartTotal;
        $options['currency'] = get_woocommerce_currency();

        return array_merge($options, $this->getContact(), $this->getDiscounts());
    }

    /**
     * Update the active shopping cart quantities with those on the form at the time the RBC
     * button was clicked. Normally, a user would need to click 'Update Cart' to trigger a quantity
     * update, but we can't rely on a user to do so before clicking the RBC button.
     *
     * @param $form
     */
    private function updateCartQuantities($form) {

        foreach (WC()->cart->get_cart() as $id => $item) {

            $qtyField = array_filter($form, function ($field) use ($id) {
                return $field['name'] === sprintf('cart[%s][qty]', $id);
            });

            if (count($qtyField) === 1) {
                $qtyField = array_pop($qtyField);
                WC()->cart->set_quantity($id, intval($qtyField['value']));
            }
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

}
