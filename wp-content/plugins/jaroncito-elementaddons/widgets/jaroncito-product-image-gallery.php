<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Jaroncito_ElementAddons_Jaroncito_Product_Image_Gallery_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'jaroncito_product_image_gallery';
    }

    public function get_title() {
        return __( 'Jaroncito Product Image Gallery', 'jaroncito-elementaddons' );
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return [ 'jaroncito-widgets' ];
    }

    public function get_script_depends() {
        return [ 'swiper-js', 'jaroncito-product-gallery-js' ];
    }

    public function get_style_depends() {
        return [ 'swiper-css', 'jaroncito-product-gallery-css' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'jaroncito-elementaddons' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'dynamic_product_images',
            [
                'label' => __( 'Use Product Images', 'jaroncito-elementaddons' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Yes', 'jaroncito-elementaddons' ),
                'label_off' => __( 'No', 'jaroncito-elementaddons' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'images_per_view',
            [
                'label' => __( 'Images Per View', 'jaroncito-elementaddons' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'default' => 3,
            ]
        );

        $this->add_control(
            'slides_to_scroll',
            [
                'label' => __( 'Slides to Scroll', 'jaroncito-elementaddons' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'default' => 1,
            ]
        );

        $this->add_control(
            'enable_lightbox',
            [
                'label' => __('Enable Lightbox', 'jaroncito-elementaddons'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'jaroncito-elementaddons'),
                'label_off' => __('No', 'jaroncito-elementaddons'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Get product images
        if ( 'yes' === $settings['dynamic_product_images'] ) {
            global $product;
            if ( ! is_a( $product, 'WC_Product' ) ) {
                $product = wc_get_product();
                if ( ! is_a( $product, 'WC_Product' ) ) {
                    return;
                }
            }
            $attachment_ids = $product->get_gallery_image_ids();
            $main_image_id = $product->get_image_id();
        } else {
            $attachment_ids = $settings['images'] ?? [];
            $main_image_id = ! empty( $attachment_ids ) ? $attachment_ids[0]['id'] : null;
        }
    
        if ( empty( $attachment_ids ) ) {
            return;
        }
    
        // Start gallery container
        echo '<div class="jaroncito-gallery-container">';
        
        // Main slider
        echo '<div class="jaroncito-gallery-slider swiper-container">';
        echo '<div class="swiper-wrapper">';
        
        // Add main image
        if ( isset( $main_image_id ) && 'yes' === $settings['dynamic_product_images'] ) {
            echo '<div class="swiper-slide">';
            echo wp_get_attachment_image( 
                $main_image_id, 
                'full', 
                false, 
                [
                    'class' => 'jaroncito-gallery-image',
                    'data-lightbox' => $settings['enable_lightbox'],
                    'data-index' => '0'
                ]
            );
            echo '</div>';
        }
    
        // Add gallery images
        foreach ( $attachment_ids as $index => $attachment_id ) {
            echo '<div class="swiper-slide">';
            if ( 'yes' === $settings['dynamic_product_images'] ) {
                echo wp_get_attachment_image( $attachment_id, 'full', false, ['class' => 'jaroncito-gallery-image'] );
            } else {
                echo '<img src="' . esc_url( $attachment_id['url'] ) . '" alt="' . esc_attr( $attachment_id['id'] ) . '" class="jaroncito-gallery-image">';
            }
            echo '</div>';
        }
        
        echo '</div>'; // End swiper-wrapper
        
        // Navigation
        echo '<div class="swiper-button-prev"></div>';
        echo '<div class="swiper-button-next"></div>';
        
        echo '</div>'; // End gallery slider
        echo '</div>'; // End gallery container
    }

    protected function content_template() {
        ?>
        <# if ( settings.images && settings.images.length ) { #>
        <div class="jaroncito-product-gallery-container">
            <div class="jaroncito-main-image-container">
                <div class="jaroncito-main-image">
                    <img src="{{ settings.images[0].url }}" 
                        alt="{{ settings.images[0].id }}" 
                        class="jaroncito-main-product-image"
                        data-lightbox="{{ settings.enable_lightbox }}"
                        data-index="0">
                    <!-- Added navigation buttons for main image -->
                    <button class="main-image-prev">&lt;</button>
                    <button class="main-image-next">&gt;</button>
                </div>
            </div>
            <div class="jaroncito-gallery-carousel-container">
                <div class="jaroncito-gallery-carousel swiper-container" data-images-per-view="{{ settings.images_per_view }}" data-slides-to-scroll="{{ settings.slides_to_scroll }}">
                    <div class="swiper-wrapper">
                        <# if ( settings.images.length > 1 ) { #>
                            <# _.each( settings.images, function( image, index ) { if ( index === 0 ) return; #>
                            <div class="swiper-slide jaroncito-gallery-item">
                                <img src="{{ image.url }}" alt="{{ image.id }}" class="jaroncito-gallery-thumbnail">
                            </div>
                            <# } ); #>
                        <# } #>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </div>
        <# } #>
        <?php
    }
}
