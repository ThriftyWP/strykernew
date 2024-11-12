<?php
namespace WoolentorPro\Modules\Badges;
use WooLentorPro\Traits\Singleton;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Product_Badges {
    use Singleton;

    /**
     * Currency Fields;
     */
    public function Fields(){
        $fields = [
            [
                'name'   => 'badges_settings',
                'label'  => esc_html__( 'Product Badges', 'woolentor-pro' ),
                'type'   => 'module',
                'default'=> 'off',
                'section'  => 'woolentor_badges_settings',
                'option_id' => 'enable',
                'documentation' => esc_url('https://woolentor.com/doc/product-badges-module/'),
                'require_settings'  => true,
                'setting_fields' => [
                    [
                        'name'    => 'enable',
                        'label'   => esc_html__( 'Enable / Disable', 'woolentor-pro' ),
                        'desc'    => esc_html__( 'Enable / disable this module.', 'woolentor-pro' ),
                        'type'    => 'checkbox',
                        'default' => 'off',
                        'class'   => 'woolentor-action-field-left'
                    ],

                    [
                        'name'        => 'badges_list',
                        'label'       => esc_html__( 'Badge List', 'woolentor-pro' ),
                        'type'        => 'repeater',
                        'title_field' => 'badge_title',
                        'condition'   => [ 'enable', '==', 'true' ],
                        'options' => [
                            'button_label' => esc_html__( 'Add New Badge', 'woolentor-pro' ),  
                        ],
                        'fields'  => [
                            [
                                'name'        => 'badge_title',
                                'label'       => esc_html__( 'Badge Title', 'woolentor-pro' ),
                                'type'        => 'text',
                                'class'       => 'woolentor-action-field-left'
                            ],
                            [
                                'name'        => 'badge_type',
                                'label'       => esc_html__( 'Badge Type', 'woolentor-pro' ),
                                'type'        => 'select',
                                'default'     => 'text',
                                'options' => [
                                    'text' => esc_html__( 'Text', 'woolentor-pro' ),
                                    'image'=> esc_html__( 'Image', 'woolentor-pro' ),
                                ],
                                'class'       => 'woolentor-action-field-left'
                            ],
                            [
                                'name'        => 'badge_text',
                                'label'       => esc_html__( 'Badge Text', 'woolentor-pro' ),
                                'type'        => 'text',
                                'class'       => 'woolentor-action-field-left',
                                'condition' => [ 'badge_type', '==', 'text' ],
                            ],
                            [
                                'name'  => 'badge_text_color',
                                'label' => esc_html__( 'Text Color', 'woolentor-pro' ),
                                'desc'  => esc_html__( 'Badge text color.', 'woolentor-pro' ),
                                'type'  => 'color',
                                'class' => 'woolentor-action-field-left',
                                'condition' => [ 'badge_type', '==', 'text' ],
                            ],
                            [
                                'name'  => 'badge_bg_color',
                                'label' => esc_html__( 'Background Color', 'woolentor-pro' ),
                                'desc'  => esc_html__( 'Badge background color.', 'woolentor-pro' ),
                                'type'  => 'color',
                                'class' => 'woolentor-action-field-left',
                                'condition' => [ 'badge_type', '==', 'text' ],
                            ],
                            [
                                'name'              => 'badge_font_size',
                                'label'             => esc_html__( 'Text Font Size (PX)', 'woolentor-pro' ),
                                'desc'              => esc_html__( 'Set the font size for badge text.', 'woolentor-pro' ),
                                'min'               => 1,
                                'max'               => 1000,
                                'default'           => '15',
                                'step'              => '1',
                                'type'              => 'number',
                                'sanitize_callback' => 'number',
                                'condition' => [ 'badge_type', '==', 'text' ],
                                'class'       => 'woolentor-action-field-left',
                            ],
                            [
                                'name'    => 'badge_padding',
                                'label'   => esc_html__( 'Badge padding', 'woolentor-pro' ),
                                'desc'    => esc_html__( 'Badge area padding.', 'woolentor-pro' ),
                                'type'    => 'dimensions',
                                'options' => [
                                    'top'   => esc_html__( 'Top', 'woolentor-pro' ),
                                    'right' => esc_html__( 'Right', 'woolentor-pro' ),
                                    'bottom'=> esc_html__( 'Bottom', 'woolentor-pro' ),
                                    'left'  => esc_html__( 'Left', 'woolentor-pro' ),
                                    'unit'  => esc_html__( 'Unit', 'woolentor-pro' ),
                                ],
                                'class' => 'woolentor-action-field-left woolentor-dimention-field-left',
                                'condition' => [ 'badge_type', '==', 'text' ],
                            ],
                            [
                                'name'    => 'badge_border_radius',
                                'label'   => esc_html__( 'Badge border radius', 'woolentor-pro' ),
                                'desc'    => esc_html__( 'Badge area button border radius.', 'woolentor-pro' ),
                                'type'    => 'dimensions',
                                'options' => [
                                    'top'   => esc_html__( 'Top', 'woolentor-pro' ),
                                    'right' => esc_html__( 'Right', 'woolentor-pro' ),
                                    'bottom'=> esc_html__( 'Bottom', 'woolentor-pro' ),
                                    'left'  => esc_html__( 'Left', 'woolentor-pro' ),
                                    'unit'  => esc_html__( 'Unit', 'woolentor-pro' ),
                                ],
                                'class' => 'woolentor-action-field-left woolentor-dimention-field-left',
                                'condition' => [ 'badge_type', '==', 'text' ],
                            ],
                            [
                                'name'    => 'badge_image',
                                'label'   => esc_html__( 'Badge Image', 'woolentor-pro' ),
                                'desc'    => esc_html__( 'Upload your custom badge from here.', 'woolentor-pro' ),
                                'type'    => 'image_upload',
                                'options' => [
                                    'button_label'        => esc_html__( 'Upload', 'woolentor-pro' ),   
                                    'button_remove_label' => esc_html__( 'Remove', 'woolentor-pro' ),   
                                ],
                                'class' => 'woolentor-action-field-left',
                                'condition'   => [ 'badge_type', '==', 'image' ],
                            ],

                            [
                                'name'      => 'badge_setting_heading',
                                'headding'  => esc_html__( 'Badge Settings', 'woolentor-pro' ),
                                'type'      => 'title'
                            ],

                            [
                                'name'    => 'badge_position',
                                'label'   => esc_html__( 'Badge Position', 'woolentor-pro' ),
                                'desc'    => esc_html__( 'Choose a badge position from here.', 'woolentor-pro' ),
                                'type'    => 'select',
                                'default' => 'top_left',
                                'options' => [
                                    'top_left'   => esc_html__( 'Top Left', 'woolentor-pro' ),
                                    'top_right'  => esc_html__( 'Top Right', 'woolentor-pro' ),
                                    'bottom_left'=> esc_html__( 'Bottom Left', 'woolentor-pro' ),
                                    'bottom_right'=> esc_html__( 'Bottom Right', 'woolentor-pro' ),
                                    'custom_position'=> esc_html__( 'Custom Position', 'woolentor-pro' ),
                                ],
                                'class'       => 'woolentor-action-field-left',
                            ],
                            [
                                'name'    => 'badge_custom_position',
                                'label'   => esc_html__( 'Custom Position', 'woolentor-pro' ),
                                'desc'    => esc_html__( 'Badge Custom Position.', 'woolentor-pro' ),
                                'type'    => 'dimensions',
                                'options' => [
                                    'top'   => esc_html__( 'Top', 'woolentor-pro' ),
                                    'right' => esc_html__( 'Right', 'woolentor-pro' ),
                                    'bottom'=> esc_html__( 'Bottom', 'woolentor-pro' ),
                                    'left'  => esc_html__( 'Left', 'woolentor-pro' ),
                                    'unit'  => esc_html__( 'Unit', 'woolentor-pro' ),
                                ],
                                'class' => 'woolentor-action-field-left woolentor-dimention-field-left',
                                'condition' => [ 'badge_position', '==', 'custom_position' ],
                            ],
                            [
                                'name'    => 'badge_condition',
                                'label'   => esc_html__( 'Badge Condition', 'woolentor-pro' ),
                                'type'    => 'select',
                                'default' => 'none',
                                'options' => [
                                    'none' => esc_html__( 'Select Option', 'woolentor-pro' ),
                                    'all_product' => esc_html__( 'All Products', 'woolentor-pro' ),
                                    'selected_product'=> esc_html__( 'Selected Product', 'woolentor-pro' ),
                                    'category'=> esc_html__( 'Category', 'woolentor-pro' ),
                                    'on_sale'=> esc_html__( 'On Sale Only', 'woolentor-pro' ),
                                    'outof_stock'=> esc_html__( 'Out Of Stock', 'woolentor-pro' ),
                                ],
                                'class'       => 'woolentor-action-field-left',
                            ],

                            [
                                'name'        => 'categories',
                                'label'       => esc_html__( 'Select Categories', 'woolentor-pro' ),
                                'desc'        => esc_html__( 'Select the categories in which products the badge will be show.', 'woolentor-pro' ),
                                'type'        => 'multiselect',
                                'options'     => woolentor_taxonomy_list('product_cat','term_id'),
                                'condition'   => [ 'badge_condition', '==', 'category' ],
                                'class'       => 'woolentor-action-field-left'
                            ],

                            [
                                'name'        => 'products',
                                'label'       => esc_html__( 'Select Products', 'woolentor-pro' ),
                                'desc'        => esc_html__( 'Select individual products in which the badge will be show.', 'woolentor-pro' ),
                                'type'        => 'multiselect',
                                'options'     => woolentor_post_name( 'product' ),
                                'condition'   => [ 'badge_condition', '==', 'selected_product' ],
                                'class'       => 'woolentor-action-field-left'
                            ],

                            [
                                'name'        => 'exclude_products',
                                'label'       => esc_html__( 'Exclude Products', 'woolentor-pro' ),
                                'type'        => 'multiselect',
                                'options'     => woolentor_post_name( 'product' ),
                                'condition'   => [ 'badge_condition', '!=', 'none' ],
                                'class'       => 'woolentor-action-field-left'
                            ],


                        ],
                    ],

                ]
            ]
        ];

        return $fields;

    }

}