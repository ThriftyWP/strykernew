<?php
/*
Plugin Name: Jaroncito ElementAddons
Plugin URI: https://example.com
Description: Adds custom elements to Elementor Website Builder.
Version: 1.0.0
Author: Jaroncito
Author URI: https://example.com
License: GPLv2 or later
Text Domain: jaroncito-elementaddons
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Jaroncito_ElementAddons {

    /**
     * Plugin Version
     */
    const VERSION = '1.0.0';

    /**
     * Constructor.
     */
    public function __construct() {
        // Load the text domain for translation
        add_action( 'init', [ $this, 'load_textdomain' ] );

        // Register custom widgets with Elementor
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

        // Enqueue styles and scripts
        add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_scripts' ] );

        // Add a new category for Jaroncito widgets
        add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories' ] );
    }

    /**
     * Load the plugin text domain for translation.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'jaroncito-elementaddons', false, basename( dirname( __FILE__ ) ) . '/languages' );
    }

    /**
     * Register custom Elementor widgets.
     */
    public function register_widgets() {
        // Make sure Elementor is active before registering custom widgets
        if ( ! did_action( 'elementor/loaded' ) ) {
            return;
        }

        // Include the widget files
        require_once( __DIR__ . '/widgets/sample-widget.php' );
        require_once( __DIR__ . '/widgets/jaroncito-product-image-gallery.php' );

        // Register the widget
        \Elementor\Plugin::instance()->widgets_manager->register( new \Jaroncito_ElementAddons_Sample_Widget() );
        \Elementor\Plugin::instance()->widgets_manager->register( new \Jaroncito_ElementAddons_Jaroncito_Product_Image_Gallery_Widget() );
    }

    /**
     * Register custom scripts and styles for Elementor widgets.
     */
    public function register_scripts() {
        // Register custom scripts
        wp_register_script( 'jaroncito-elementaddons-js', plugins_url( '/assets/js/elementaddons.js', __FILE__ ), [ 'jquery' ], self::VERSION, true );
        wp_register_script( 'jaroncito-product-gallery-js', plugins_url( '/assets/js/product-gallery.js', __FILE__ ), [ 'jquery' ], self::VERSION, true );

        // Register custom styles
        wp_register_style( 'jaroncito-elementaddons-css', plugins_url( '/assets/css/elementaddons.css', __FILE__ ), [], self::VERSION );
        wp_register_style( 'jaroncito-product-gallery-css', plugins_url( '/assets/css/product-gallery.css', __FILE__ ), [], self::VERSION );
    }

    /**
     * Add a new category for Jaroncito widgets.
     */
    public function add_elementor_widget_categories( $elements_manager ) {
        $elements_manager->add_category(
            'jaroncito-widgets',
            [
                'title' => __( 'Jaroncito Widgets', 'jaroncito-elementaddons' ),
                'icon'  => 'fa fa-plug',
            ],
            1
        );
    }
}

// Initialize the plugin
new Jaroncito_ElementAddons();