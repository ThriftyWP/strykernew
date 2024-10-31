<?php

if (!defined( 'ABSPATH')) exit;
 
class wcqv_frontend{
	
	public $wcqv_plugin_dir_url;
    public $wcqv_options;
    public $wcqv_style;
    public $wcqv_display;

	function __construct($wcqv_plugin_dir_url){

		$this->wcqv_plugin_dir_url 	= $wcqv_plugin_dir_url;
		$this->wcqv_options 		= get_option('wcqv_options');
  		$this->wcqv_style   		= get_option('wcqv_style');
  		$this->wcqv_display 		= get_option('wcqv_display');

        add_action( 'wp_enqueue_scripts', array($this,'wcqv_load_assets'));
		add_action( 'woocommerce_after_shop_loop_item', array($this,'wcqv_add_button') );
		add_action( 'wp_footer', array($this, 'wcqv_remodel_model'));
		add_action( 'wp_ajax_wcqv_get_product', array($this,'wcqv_get_product') );
        add_action( 'wp_ajax_nopriv_wcqv_get_product', array($this,'wcqv_get_product') );

 
        add_action('wcqv_show_product_sale_flash','woocommerce_show_product_sale_flash');
        add_action('wcqv_show_product_images', array($this,'wcqv_woocommerce_show_product_images'));

        if ($this->wcqv_options['image_click_popup'] == '1') {

            add_filter( 'post_thumbnail_html', array($this,'wcqv_post_thumbnail_html'),10, 5 );
            remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open' );
            //add_action( 'woocommerce_shop_loop_item_title', array( $this, 'wqv_woocommerce_template_loop_product_link_open') ,9 );
            add_action( 'woocommerce_before_shop_loop_item', array( $this, 'wqv_woocommerce_template_loop_product_link_open') ,9 );
        
		}
		

        if($this->wcqv_display['show_product_title'] === '1'){

        	add_action( 'wcqv_product_data', 'woocommerce_template_single_title');
        }
        if($this->wcqv_display['show_product_rating'] === '1'){

        	add_action( 'wcqv_product_data', 'woocommerce_template_single_rating');
        }
        if($this->wcqv_display['show_product_price'] === '1'){

        	add_action( 'wcqv_product_data', 'woocommerce_template_single_price');
        }
        if($this->wcqv_display['show_product_excerpt'] === '1'){

        	add_action( 'wcqv_product_data', 'woocommerce_template_single_excerpt');
        }
        if($this->wcqv_display['show_product_add_to_cart'] === '1'){

        	add_action( 'wcqv_product_data', array( $this, 'add_to_cart') );
        }
        if($this->wcqv_display['show_product_meta'] === '1' ){

        	add_action( 'wcqv_product_data', 'woocommerce_template_single_meta');
        }
        
	}


	public function add_to_cart(){

	

		add_filter( 'woocommerce_loop_add_to_cart_link', function ( $html, $product ) {

			$args = array();
			$defaults = array(
				'quantity'   => 1,
				'class'      => implode(
					' ',
					array_filter(
						array(
							'button',
							'product_type_' . $product->get_type(),
							$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
							$product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
						)
					)
				),
				'attributes' => array(
					'data-product_id'  => $product->get_id(),
					'data-product_sku' => $product->get_sku(),
					'aria-label'       => $product->add_to_cart_description(),
					'rel'              => 'nofollow',
				),
			);

			$args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );

			if ( isset( $args['attributes']['aria-label'] ) ) {
				$args['attributes']['aria-label'] = wp_strip_all_tags( $args['attributes']['aria-label'] );
			}

			if ( $product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_sold_individually() ) {
				$html = '<form action="' . esc_url( $product->add_to_cart_url() ) . '" class="cart" method="post" enctype="multipart/form-data">';
				$html .= woocommerce_quantity_input( array(), $product, false );
				$html .= sprintf(
					'<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
					esc_url( $product->add_to_cart_url() ),
					esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
					esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
					isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
					esc_html( $product->add_to_cart_text() )
				);
				$html .= '</form>';
			}
			return $html;
		}, 10, 2 );
		woocommerce_template_loop_add_to_cart();
	}

	public function quantity_inputs_for_woocommerce_loop_add_to_cart_link( $html, $product ) {
		if ( $product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_sold_individually() ) {
			$html = '<form action="' . esc_url( $product->add_to_cart_url() ) . '" class="cart" method="post" enctype="multipart/form-data">';
			$html .= woocommerce_quantity_input( array(), $product, false );
			$html .= '<button type="submit" class="button alt">' . esc_html( $product->add_to_cart_text() ) . '</button>';
			$html .= '</form>';
		}
		return $html;
	}

	public function wqv_woocommerce_template_loop_product_link_open(){
		global $post;
		echo '<a data-product-id="'.$post->ID.'"class="woocommerce-LoopProduct-link woocommerce-loop-product__link" >';
	}
    

    public function wcqv_post_thumbnail_html( $img_html, $post_id, $post_thumbnail_id, $size){
        if( !is_product() ){
            $img_url = wp_get_attachment_image_src( $post_thumbnail_id,'large' );

            $img_html = str_replace('<img', "<a data-product-id = '".$post_id."' class='img_quick_view' href='".$img_url[0]."'><img" , $img_html);
            $img_html .= '</a>';
        }
        return $img_html;
    }

    public function wcqv_woocommerce_show_product_images(){

		global $post, $product, $woocommerce;

		?>
		<div class="images">
		<?php 

        if ( has_post_thumbnail() ) {
			$attachment_count = count( $product->get_gallery_image_ids() );
			$gallery          = $attachment_count > 0 ? '[product-gallery]' : '';
			$props            = wc_get_product_attachment_props( get_post_thumbnail_id(), $post );
			$image            = get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ), array(
				'title'	 => $props['title'],
				'alt'    => $props['alt'],
			) );
			echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<a href="%s" itemprop="image" class="woocommerce-main-image zoom" title="%s" data-rel="prettyPhoto' . $gallery . '">%s</a>', $props['url'], $props['caption'], $image ), $post->ID );
		} else {
			echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<img src="%s" alt="%s" />', wc_placeholder_img_src(), __( 'Placeholder', 'woocommerce' ) ), $post->ID );
		}


		$attachment_ids = $product->get_gallery_image_ids();
		if ( $attachment_ids ) :
			$loop 		= 0;
			$columns 	= apply_filters( 'woocommerce_product_thumbnails_columns', 3 );
			?>
			<div class="thumbnails <?php echo 'columns-' . $columns; ?>"><?php
				foreach ( $attachment_ids as $attachment_id ) {
					$classes = array( 'thumbnail' );
					if ( $loop === 0 || $loop % $columns === 0 )
						$classes[] = 'first';
					if ( ( $loop + 1 ) % $columns === 0 )
						$classes[] = 'last';
					$image_link = wp_get_attachment_url( $attachment_id );
					if ( ! $image_link )
						continue;
					$image_title 	= esc_attr( get_the_title( $attachment_id ) );
					$image_caption 	= esc_attr( get_post_field( 'post_excerpt', $attachment_id ) );
					$image       = wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' ), 0, $attr = array(
						'title'	=> $image_title,
						'alt'	=> $image_title
						) );
					$image_class = esc_attr( implode( ' ', $classes ) );
					echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', sprintf( '<a href="%s" class="%s" title="%s" >%s</a>', $image_link, $image_class, $image_caption, $image ), $attachment_id, $post->ID, $image_class );
					$loop++;
				}
			?> 
			</div>
		<?php endif;?>
        </div>
        <?php
    }




	public function wcqv_load_assets(){
        
        wp_enqueue_style  ( 'wcqv_remodal_default_css',    $this->wcqv_plugin_dir_url.'css/style.css');
		wp_register_script( 'wcqv_frontend_js', $this->wcqv_plugin_dir_url.'js/frontend.js',array('jquery'),'1.0.3', true);
		$frontend_data = array(

		'wcqv_nonce'          => wp_create_nonce('wcqv_nonce'),
		'ajaxurl'             => admin_url( 'admin-ajax.php' ),
		'wcqv_plugin_dir_url' => $this->wcqv_plugin_dir_url,
		'disable_links'       => $this->wcqv_options['disable_links']
 

		);

		wp_localize_script( 'wcqv_frontend_js', 'wcqv_frontend_obj', $frontend_data );
		wp_enqueue_script ( 'jquery' );
		wp_enqueue_script ( 'wcqv_frontend_js' );
		wp_register_script( 'wcqv_remodal_js',$this->wcqv_plugin_dir_url.'js/remodal.js',array('jquery'),'1.0', true);
		wp_enqueue_script('wcqv_remodal_js');

		global $woocommerce;
 
		$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$lightbox_en = get_option( 'woocommerce_enable_lightbox' ) == 'yes' ? true : false;
		 
		//if ( $lightbox_en ) {
		    wp_enqueue_script( 'prettyPhoto', $woocommerce->plugin_url() . '/assets/js/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array( 'jquery' ), '3.1.6', true );
		    wp_enqueue_style( 'woocommerce_prettyPhoto_css', $woocommerce->plugin_url() . '/assets/css/prettyPhoto.css' );
		//}
		wp_enqueue_script( 'wc-add-to-cart-variation' );

		wp_enqueue_script('thickbox');

 
	    $custom_css = '
	    .remodal .remodal-close{
	    	color:'.$this->wcqv_style['close_btn'].';
	    }
	    .remodal .remodal-close:hover{
	    	background-color:'.$this->wcqv_style['close_btn_bg'].';
	    }
	    .woocommerce .remodal{
	    	background-color:'.$this->wcqv_style['modal_bg'].';
	    }
	    .wcqv_prev h4,.wcqv_next h4{
	    	color :'.$this->wcqv_style['navigation_txt'].';
	    }
	    .wcqv_prev,.wcqv_next{
	    	background :'.$this->wcqv_style['navigation_bg'].';
	    }
        .woocommerce a.quick_view{
            background-color: '.$this->wcqv_style['close_btn'].' ;
        }';
        wp_add_inline_style( 'wcqv_remodal_default_css', $custom_css );


         
	}


	public function wcqv_remodel_model(){
 
		echo '<div class="remodal" data-remodal-id="modal" role="dialog" aria-labelledby="modalTitle" aria-describedby="modalDesc">
		  <button data-remodal-action="close" class="remodal-close" aria-label="Close"></button>
		    <div id = "wcqv_contend"></div>
		</div>';

		 
	}


	public function wcqv_add_button(){

        global $post;
		if($this->wcqv_options['button_icon']==='1'){

			echo '<a data-product-id="'.$post->ID.'"class="quick_view_icon button" >
            	<span><img src="'.$this->wcqv_plugin_dir_url.'img/eye-orange.png" />'.'</span></a>';
		}else{
			echo '<a data-product-id="'.$post->ID.'"class="quick_view button" >
        		<span>'.$this->wcqv_options['button_lable'].'</span></a>';
		}
        
	}


	public function wcqv_get_product(){

		global $woocommerce;

		$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$lightbox_en = get_option( 'woocommerce_enable_lightbox' ) == 'yes' ? true : false;

		
		global $post;
		$product_id = $_POST['product_id'];
		if(intval($product_id)){

			 wp( 'p=' . $product_id . '&post_type=product' );
 	         ob_start();
 	

		 	while ( have_posts() ) : the_post(); ?>
	 	    <script>
		 	    var url = <?php echo "'"."$this->wcqv_plugin_dir_url/js/prettyPhoto.init.js'"; ?>;
		 	    jQuery.getScript(url);
		 	    var wc_add_to_cart_variation_params = {"ajax_url":"\/wp-admin\/admin-ajax.php"};     
	            jQuery.getScript("<?php echo $woocommerce->plugin_url(); ?>/assets/js/frontend/add-to-cart-variation.min.js");
	 	    </script>
 	        <div class="product">  

 	                <div  id="product-<?php the_ID(); ?>" <?php post_class('product'); ?> >  
 	                        <?php  
 	                            if($this->wcqv_display['show_product_sale_flash']==='1'){
 	                        		do_action('wcqv_show_product_sale_flash');
 	                        	} 
 	                         
                            	if($this->wcqv_display['show_product_images']==='1'){
                        			do_action('wcqv_show_product_images');
                        		} 
 	                        ?>
                               
	 	                        <div class="summary entry-summary scrollable">
	 	                                <div class="summary-content">   
	                                       <?php
	                                        	do_action( 'wcqv_product_data' );
	                                        ?>
	 	                                </div>
	 	                        </div>
	 	                        <div class="scrollbar_bg"></div>
 
 	                </div> 
 	        </div>
 	       
 	        <?php endwhile;

            	$post                  = get_post($product_id);
                $same_cat_nav          = ($this->wcqv_options['navigation_same_cat']=="1")?true:false;
            	$next_post             = get_next_post( $same_cat_nav, '', 'product_cat');
			    $prev_post             = get_previous_post( $same_cat_nav , '', 'product_cat');
			    $next_post_id          = ($next_post != null)?$next_post->ID:'';
			    $prev_post_id          = ($prev_post != null)?$prev_post->ID:'';
			    $next_post_title       = ($next_post != null)?$next_post->post_title:'';
 		     	$prev_post_title       = ($prev_post != null)?$prev_post->post_title:'';
			 	$next_thumbnail        = ($next_post != null)?get_the_post_thumbnail( $next_post->ID,
			 		                  'shop_thumbnail',''):'';
 		     	$prev_thumbnail        = ($prev_post != null)?get_the_post_thumbnail( $prev_post->ID,
 		     		                   'shop_thumbnail',''):'';

 	        ?> 
            
 	        <div class ="wcqv_prev_data" data-wcqv-prev-id = "<?php echo $prev_post_id; ?>">
 	        <?php echo $prev_post_title; ?>
 	            <?php echo $prev_thumbnail; ?> 
 	        </div> 
 	        <div class ="wcqv_next_data" data-wcqv-next-id = "<?php echo $next_post_id; ?>">
 	        <?php echo $next_post_title; ?>
 	             <?php echo $next_thumbnail; ?> 
 	        </div> 

 	        <?php
 	                  
 	        echo  ob_get_clean();
 	
 	        exit();
            
			
	    }
	}
	// Add this to class.frontend.php
	public function get_quick_view_button($product_id) {
		if (!$product_id) {
			return '';
		}
		
		if ($this->wcqv_options['button_icon'] === '1') {
			return sprintf(
				'<a data-product-id="%d" class="quick_view_icon button"><span><img src="%simg/eye-orange.png" /></span></a>',
				$product_id,
				$this->wcqv_plugin_dir_url
			);
		} else {
			return sprintf(
				'<a data-product-id="%d" class="quick_view button"><span>%s</span></a>',
				$product_id,
				$this->wcqv_options['button_lable']
			);
		}
	}
	
}
?>