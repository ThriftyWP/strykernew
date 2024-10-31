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
        return [ 'swiper-js', 'product-gallery-js' ];
    }

    public function get_style_depends() {
        return [ 'swiper-css', 'product-gallery-css' ];
    }

    protected function _register_controls() {
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

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

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

        $images_per_view = $settings['images_per_view'];
        $slides_to_scroll = $settings['slides_to_scroll'];

        echo '<div class="jaroncito-product-gallery-container">';
        echo '<div class="jaroncito-main-image-container">';
        echo '<div class="jaroncito-main-image">';
        if ( isset( $main_image_id ) && 'yes' === $settings['dynamic_product_images'] ) {
            echo wp_get_attachment_image( $main_image_id, 'full', false, [ 'class' => 'jaroncito-main-product-image' ] );
        } elseif ( ! empty( $settings['images'] ) ) {
            echo '<img src="' . esc_url( $settings['images'][0]['url'] ) . '" alt="' . esc_attr( $settings['images'][0]['id'] ) . '" class="jaroncito-main-product-image">';
        }
        echo '</div>';
        echo '</div>';

        echo '<div class="jaroncito-gallery-carousel-container">';
        echo '<div class="jaroncito-gallery-carousel swiper-container" data-images-per-view="' . esc_attr( $images_per_view ) . '" data-slides-to-scroll="' . esc_attr( $slides_to_scroll ) . '">';
        echo '<div class="swiper-wrapper">';
        foreach ( $attachment_ids as $index => $attachment_id ) {
            if ( 'yes' === $settings['dynamic_product_images'] ) {
                if ( $index === 0 ) continue; // Skip the main product image if it is part of the gallery
                echo '<div class="swiper-slide jaroncito-gallery-item">';
                echo wp_get_attachment_image( $attachment_id, 'thumbnail', false, [ 'class' => 'jaroncito-gallery-thumbnail' ] );
                echo '</div>';
            } else {
                if ( $index === 0 ) continue;
                echo '<div class="swiper-slide jaroncito-gallery-item">';
                echo '<img src="' . esc_url( $attachment_id['url'] ) . '" alt="' . esc_attr( $attachment_id['id'] ) . '" class="jaroncito-gallery-thumbnail">';
                echo '</div>';
            }
        }
        echo '</div>'; // End of .swiper-wrapper
        echo '<div class="swiper-button-next"></div>'; // Navigation next button
        echo '<div class="swiper-button-prev"></div>'; // Navigation prev button
        echo '<div class="swiper-pagination"></div>'; // Pagination
        echo '</div>'; // End of .swiper-container
        echo '</div>'; // End of .jaroncito-gallery-carousel-container
        echo '</div>'; // End of .jaroncito-product-gallery-container
    }

    protected function _content_template() {
        ?>
        <# if ( settings.images && settings.images.length ) { #>
        <div class="jaroncito-product-gallery-container">
            <div class="jaroncito-main-image-container">
                <div class="jaroncito-main-image">
                    <img src="{{ settings.images[0].url }}" alt="{{ settings.images[0].id }}" class="jaroncito-main-product-image">
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

add_action( 'wp_enqueue_scripts', function() {
    // Enqueue Swiper.js and Swiper.css
    wp_enqueue_script( 'swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js', [], '9.0.0', true );
    wp_enqueue_style( 'swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css', [], '9.0.0' );

    // Custom script to initialize the Swiper instance
    wp_add_inline_script( 'swiper-js', "
        document.addEventListener('DOMContentLoaded', function() {
            var swiperContainer = document.querySelector('.swiper-container');
            if (!swiperContainer) return;

            var imagesPerView = parseInt(swiperContainer.dataset.imagesPerView, 10) || 3;
            var slidesToScroll = parseInt(swiperContainer.dataset.slidesToScroll, 10) || 1;

            var gallerySwiper = new Swiper('.swiper-container', {
                loop: false,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                slidesPerView: imagesPerView,
                slidesPerGroup: slidesToScroll,
                spaceBetween: 10,
                breakpoints: {
                    768: {
                        slidesPerView: imagesPerView,
                    }
                }
            });

            // Update the main image when clicking on a gallery image
            document.querySelectorAll('.jaroncito-gallery-item img').forEach(function (thumbnail) {
                thumbnail.addEventListener('click', function () {
                    var mainImage = document.querySelector('.jaroncito-main-product-image');
                    if (mainImage) {
                        mainImage.src = this.src;
                    }
                });
            });
        });
    " );
});
