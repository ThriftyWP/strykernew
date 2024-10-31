<?php
function my_theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );


//**************************************//   
//Add in my custom script for to do things
//**************************************//   
function add_custom_script() {
    wp_register_script('custom_script', '/wp-content/themes/NewStryker/js/custom_script.js', array( 'jquery' ));
    wp_enqueue_script('custom_script');
}  
add_action( 'wp_enqueue_scripts', 'add_custom_script' );

function enqueue_slick_slider_and_custom_script() {
    // Enqueue Slick Slider CSS and JS
    wp_enqueue_style( 'slick-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css' );
    wp_enqueue_style( 'slick-theme-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css' );
    wp_enqueue_script( 'slick-js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true );
    
    // Enqueue your custom slider JS
    wp_enqueue_script( 'custom-slider-js', '/wp-content/themes/NewStryker/js/custom-slider.js', array('jquery', 'slick-js'), null, true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_slick_slider_and_custom_script' );


// Register the shortcode for the video gallery slider
function true_health_video_slider_shortcode() {
    // Check if there are rows in the repeater field
    if( have_rows('video_gallery') ):
        ob_start(); // Start output buffering
        ?>
        <div class="video-slider">
        <?php while( have_rows('video_gallery') ): the_row(); 
            $video_url = get_sub_field('video_url'); // Or 'video_file' if using file upload

            // Check if it's a YouTube URL and convert it to embed URL if necessary
            if (strpos($video_url, 'youtube.com/watch') !== false) {
                $video_url = str_replace('watch?v=', 'embed/', $video_url);
            }
        ?>
            <div class="video-slide">
                <div class="video-wrapper">
                    <iframe src="<?php echo esc_url( $video_url ); ?>" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
        <?php
        return ob_get_clean(); // Return the buffered output
    else:
        return '<p>No videos available.</p>'; // If no videos are found
    endif;
}
add_shortcode( 'video_slider', 'true_health_video_slider_shortcode' );

// function add_video_to_product_gallery( $html, $attachment_id ) {
//     // Get the video URL from ACF or custom meta field
//     $video_url = get_field('product_video_url'); // ACF custom field

//     // Only add the video if a URL is provided
//     if ( $video_url ) {
//         // Build the video iframe (you can adjust this for Vimeo or other services)
//         $video_iframe = '<div class="woocommerce-product-gallery__image">
//                             <iframe width="100%" height="500px" src="' . esc_url( $video_url ) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
//                          </div>';

//         // Add the video as the first gallery item
//         $html = $video_iframe . $html;
//     }

//     return $html;
// }
// add_filter( 'woocommerce_single_product_image_thumbnail_html', 'add_video_to_product_gallery', 10, 2 );


function modify_elementor_cross_sell_query( $query ) {
    if ( is_product() && 'cross-sells' === $query->get( 'source' ) ) {
        global $product;

        if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
            $product = wc_get_product( get_the_ID() );
        }

        $cross_sell_ids = $product->get_cross_sell_ids();

        if ( ! empty( $cross_sell_ids ) ) {
            $query->set( 'post__in', $cross_sell_ids );
            $query->set( 'orderby', 'post__in' );

            // **This is the key change:**
            // Remove the default WooCommerce filter that checks for cart items
            remove_filter( 'woocommerce_product_is_cross_sell', 'woocommerce_product_is_cross_sell' ); 
        } else {
            $query->set( 'post__in', array( 0 ) );
        }
    }
}
add_action( 'elementor/query/cross-sells', 'modify_elementor_cross_sell_query' );

function custom_cross_sell_query( $query ) {
    if ( is_product() ) {
        global $product;

        if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
            $product = wc_get_product( get_the_ID() );
        }

        $cross_sell_ids = $product->get_cross_sell_ids();

        if ( ! empty( $cross_sell_ids ) ) {
            $query->set( 'post_type', 'product' );
            $query->set( 'post__in', $cross_sell_ids );
            $query->set( 'orderby', 'post__in' );
        } else {
            $query->set( 'post__in', array( 0 ) ); // Show nothing if no cross-sells
        }
    }
}
add_action( 'elementor/query/custom_cross_sells', 'custom_cross_sell_query' ); // Note the unique query ID


// Add cross-sell products to the single product template in WooCommerce

function display_cross_sells_on_single_product() {
    $product = wc_get_product( get_the_ID() );
    $cross_sells = $product ? $product->get_cross_sell_ids() : array();

    if ( ! empty( $cross_sells ) ) {
        $args = array(
            'post_type' => 'product',
            'post__in' => $cross_sells,
            'posts_per_page' => -1,
            'orderby' => 'date'
        );

        $cross_sell_query = new WP_Query( $args );

        if ( $cross_sell_query->have_posts() ) {
            echo '<div class="cross-sells">';
            // echo '<h2>Related Products</h2>';
            echo '<ul class="products">';

            while ( $cross_sell_query->have_posts() ) {
                $cross_sell_query->the_post();
                wc_get_template_part( 'content', 'product' );
            }

            echo '</ul>';
            echo '</div>';
        }

        wp_reset_postdata();
    } else {
        echo '<p>No cross-sell products found</p>';
    }
}
add_action( 'woocommerce_after_single_product_summary', 'display_cross_sells_on_single_product', 25 );


