<?php
namespace Woolentor\Modules\SideMiniCart\Admin;
use WooLentorPro\Traits\Singleton;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Fields {
    use Singleton;

    public function __construct(){
        add_filter( 'woolentor_admin_fields', [ $this, 'admin_fields' ], 99, 1 );
    }

    public function admin_fields( $fields ){

        array_splice( $fields['woolentor_others_tabs']['modules'], 22, 0, $this->side_mini_cart_sitting_fields() );

        if( \Woolentor\Modules\SideMiniCart\ENABLED ){
            $fields['woolentor_elements_tabs'][] = [
                'name'  => 'wl_mini_cart',
                'label' => esc_html__( 'Mini Cart', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ];

            // Block
            $fields['woolentor_gutenberg_tabs']['blocks'][] = [
                'name'  => 'side_mini_cart',
                'label' => esc_html__( 'Side Mini Cart', 'woolentor' ),
                'type'  => 'element',
                'default' => 'on',
            ];

        }

        return $fields;
    }

    /**
     * Currency Fields;
     */
    public function side_mini_cart_sitting_fields(){
        $fields = [
            [
                'name'   => 'mini_side_cart',
                'label'  => esc_html__( 'Side Mini Cart', 'woolentor-pro' ),
                'type'   => 'element',
                'default'=> 'off',
                'class'  =>'side_mini_cart',
                'require_settings'  => true,
                'documentation' => esc_url('https://woolentor.com/doc/side-mini-cart-for-woocommerce/'),
                'setting_fields' => [
                    
                    [
                        'name'    => 'mini_cart_position',
                        'label'   => esc_html__( 'Position', 'woolentor-pro' ),
                        'desc'    => esc_html__( 'Set the position of the Mini Cart .', 'woolentor-pro' ),
                        'type'    => 'select',
                        'default' => 'left',
                        'options' => [
                            'left'   => esc_html__( 'Left','woolentor-pro' ),
                            'right'  => esc_html__( 'Right','woolentor-pro' ),
                        ],
                        'class' => 'woolentor-action-field-left',
                    ],
        
                    [
                        'name'    => 'mini_cart_icon',
                        'label'   => esc_html__( 'Icon', 'woolentor-pro' ),
                        'desc'    => esc_html__( 'You can manage the side mini cart toggler icon.', 'woolentor-pro' ),
                        'type'    => 'text',
                        'default' => 'sli sli-basket-loaded',
                        'class'   => 'woolentor_icon_picker woolentor-action-field-left'
                    ],

                    [
                        'name'  => 'empty_mini_cart_hide',
                        'label' => esc_html__( 'Hide mini cart when empty?', 'woolentor-pro' ),
                        'desc'  => esc_html__( 'Hide side mini cart when the cart is empty.', 'woolentor-pro' ),
                        'type'  => 'checkbox',
                        'default' => 'off',
                        'class' => 'woolentor-action-field-left'
                    ],
        
                    [
                        'name'  => 'mini_cart_icon_color',
                        'label' => esc_html__( 'Icon color', 'woolentor' ),
                        'desc'  => esc_html__( 'Side mini cart icon color', 'woolentor' ),
                        'type'  => 'color',
                        'class' => 'woolentor-action-field-left'
                    ],
        
                    [
                        'name'  => 'mini_cart_icon_bg_color',
                        'label' => esc_html__( 'Icon background color', 'woolentor' ),
                        'desc'  => esc_html__( 'Side mini cart icon background color', 'woolentor' ),
                        'type'  => 'color',
                        'class' => 'woolentor-action-field-left'
                    ],
        
                    [
                        'name'  => 'mini_cart_icon_border_color',
                        'label' => esc_html__( 'Icon border color', 'woolentor-pro' ),
                        'desc'  => esc_html__( 'Side mini cart icon border color', 'woolentor-pro' ),
                        'type'  => 'color',
                        'class' => 'woolentor-action-field-left'
                    ],
        
                    [
                        'name'  => 'mini_cart_counter_color',
                        'label' => esc_html__( 'Counter color', 'woolentor-pro' ),
                        'desc'  => esc_html__( 'Side mini cart counter color', 'woolentor-pro' ),
                        'type'  => 'color',
                        'class' => 'woolentor-action-field-left'
                    ],
        
                    [
                        'name'  => 'mini_cart_counter_bg_color',
                        'label' => esc_html__( 'Counter background color', 'woolentor-pro' ),
                        'desc'  => esc_html__( 'Side mini cart counter background color', 'woolentor-pro' ),
                        'type'  => 'color',
                        'class' => 'woolentor-action-field-left'
                    ],

                    [
                        'name'      => 'mini_cart_button_heading',
                        'headding'  => esc_html__( 'Buttons', 'woolentor-pro' ),
                        'type'      => 'title'
                    ],

                    [
                        'name'  => 'mini_cart_buttons_color',
                        'label' => esc_html__( 'Color', 'woolentor-pro' ),
                        'desc'  => esc_html__( 'Side mini cart buttons color', 'woolentor-pro' ),
                        'type'  => 'color',
                        'class' => 'woolentor-action-field-left'
                    ],
                    [
                        'name'  => 'mini_cart_buttons_bg_color',
                        'label' => esc_html__( 'Background color', 'woolentor-pro' ),
                        'desc'  => esc_html__( 'Side mini cart buttons background color', 'woolentor-pro' ),
                        'type'  => 'color',
                        'class' => 'woolentor-action-field-left'
                    ],

                    [
                        'name'  => 'mini_cart_buttons_hover_color',
                        'label' => esc_html__( 'Hover color', 'woolentor-pro' ),
                        'desc'  => esc_html__( 'Side mini cart buttons hover color', 'woolentor-pro' ),
                        'type'  => 'color',
                        'class' => 'woolentor-action-field-left'
                    ],
                    [
                        'name'  => 'mini_cart_buttons_hover_bg_color',
                        'label' => esc_html__( 'Hover background color', 'woolentor-pro' ),
                        'desc'  => esc_html__( 'Side mini cart buttons hover background color', 'woolentor-pro' ),
                        'type'  => 'color',
                        'class' => 'woolentor-action-field-left'
                    ]

                ]
            ]
        ];

        return $fields;

    }

}