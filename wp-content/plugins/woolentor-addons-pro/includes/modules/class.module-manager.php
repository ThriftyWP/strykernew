<?php  
use WooLentorPro\Traits\Singleton;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Woolentor_Module_Manager_Pro{
    use Singleton;

    /**
     * Constructor
     */
    public function __construct(){
        add_filter( 'woolentor_module_list',[$this, 'module_list'] );
    }

    // Pro Module List
    public function module_list( $module_list ){
        $pro_module_list = [
            
            'partial-payment' => [
                'slug'   =>'partial-payment',
                'title'  => esc_html__('Partial Payment','woolentor'),
                'option' => [
                    'key'     => 'enable',
                    'section' => 'woolentor_partial_payment_settings',
                    'default' => 'off'
                ],
                'main_class' => '',
                'is_pro'     => true,
                'manage_setting' => false
            ],
            'pre-orders' => [
                'slug'   =>'pre-orders',
                'title'  => esc_html__('Pre Orders','woolentor'),
                'option' => [
                    'key'     => 'enable',
                    'section' => 'woolentor_pre_order_settings',
                    'default' => 'off'
                ],
                'main_class' => '',
                'is_pro'     => true,
                'manage_setting' => false
            ],
            'gtm-conversion-tracking' => [
                'slug'   =>'gtm-conversion-tracking',
                'title'  => esc_html__('GTM Conversion Tracking','woolentor'),
                'option' => [
                    'key'     => 'enable',
                    'section' => 'woolentor_gtm_convertion_tracking_settings',
                    'default' => 'off'
                ],
                'main_class' => '',
                'is_pro'     => true,
                'manage_setting' => false
            ],
            'size-chart' => [
                'slug'   =>'size-chart',
                'title'  => esc_html__('Size Chart','woolentor'),
                'option' => [
                    'key'     => 'enable',
                    'section' => 'woolentor_size_chart_settings',
                    'default' => 'off'
                ],
                'main_class' => '',
                'is_pro'     => true,
                'manage_setting' => false
            ],
            'email-customizer' => [
                'slug'   =>'email-customizer',
                'title'  => esc_html__('Email Customizer','woolentor'),
                'option' => [
                    'key'     => 'enable',
                    'section' => 'woolentor_email_customizer_settings',
                    'default' => 'off'
                ],
                'main_class' => '',
                'is_pro'     => true,
                'manage_setting' => false
            ],
            'email-automation' => [
                'slug'   =>'email-automation',
                'title'  => esc_html__('Email Automation','woolentor'),
                'option' => [
                    'key'     => 'enable',
                    'section' => 'woolentor_email_automation_settings',
                    'default' => 'off'
                ],
                'main_class' => '',
                'is_pro'     => true,
                'manage_setting' => false
            ],
            'order-bump' => [
                'slug'   =>'order-bump',
                'title'  => esc_html__('Order Bump','woolentor'),
                'option' => [
                    'key'     => 'enable',
                    'section' => 'woolentor_order_bump_settings',
                    'default' => 'off'
                ],
                'main_class' => '',
                'is_pro'     => true,
                'manage_setting' => false
            ],
            'product-filter' => [
                'slug'   =>'product-filter',
                'title'  => esc_html__('Product Filter','woolentor'),
                'option' => [
                    'key'     => 'enable',
                    'section' => 'woolentor_product_filter_settings',
                    'default' => 'off'
                ],
                'main_class' => 'Woolentor_Product_Filter',
                'is_pro'     => true,
                'manage_setting' => true
            ],
            'side-mini-cart' => [
                'slug'   =>'side-mini-cart',
                'title'  => esc_html__('Side Mini Cart','woolentor'),
                'option' => [
                    'key'     => 'mini_side_cart',
                    'section' => 'woolentor_others_tabs',
                    'default' => 'off'
                ],
                'main_class' => '\Woolentor\Modules\SideMiniCart\Side_Mini_Cart',
                'is_pro'     => true,
                'manage_setting' => true
            ],
            'quick-checkout' => [
                'slug'   =>'quick-checkout',
                'title'  => esc_html__('Quick Checkout','woolentor'),
                'option' => [
                    'key'     => 'enable',
                    'section' => 'woolentor_quick_checkout_settings',
                    'default' => 'off'
                ],
                'main_class' => '\Woolentor\Modules\QuickCheckout\Quick_Checkout',
                'is_pro'     => true,
                'manage_setting' => true
            ]

        ];

        $pro_module_list = apply_filters('woolentor_pro_module_list', $pro_module_list);

        $final_module_list = array_merge($module_list, $pro_module_list);

        return $final_module_list;
        
    }


}

Woolentor_Module_Manager_Pro::instance();