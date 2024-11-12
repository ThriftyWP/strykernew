<?php
   
    require_once "WooLentorProBase.php";

	class WooLentorPro {
        public $plugin_file=WOOLENTOR_ADDONS_PL_ROOT_PRO;
        public $responseObj;
        public $licenseMessage;
        public $showMessage=false;
        public $slug="woolentor-pro";
        function __construct() {
    	    add_action( 'admin_print_styles', [ $this, 'SetAdminStyle' ] );
    	    $licenseKey=get_option("WooLentorPro_lic_Key","");
    	    $liceEmail=get_option( "WooLentorPro_lic_email","");
            WooLentorProBase::addOnDelete(function(){
               delete_option("WooLentorPro_lic_Key");
            });
    	    if(WooLentorProBase::CheckWPPlugin($licenseKey,$liceEmail,$this->licenseMessage,$this->responseObj,WOOLENTOR_ADDONS_PL_ROOT_PRO)){
    		    add_action( 'admin_menu', [$this,'ActiveAdminMenu'], 228 );
    		    add_action( 'admin_post_WooLentorPro_el_deactivate_license', [ $this, 'action_deactivate_license' ] );
    	    }else{
    	        if(!empty($licenseKey) && !empty($this->licenseMessage)){
    	           $this->showMessage=true;
                }
    		    update_option("WooLentorPro_lic_Key","") || add_option("WooLentorPro_lic_Key","");
    		    add_action( 'admin_post_WooLentorPro_el_activate_license', [ $this, 'action_activate_license' ] );
    		    add_action( 'admin_menu', [$this,'InactiveMenu'], 228 );
                
    	    }

            // Promo Banner For Agency / Bundle
            if( $this->hasAgencyAndBundle() ){
                add_filter('woolentor_sidebar_probanner', function( $html ){ return ''; });
            }else{
                add_filter( 'woolentor_sidebar_probanner', [ $this,'sideBarPromoBanner'] );
                add_action( 'admin_head', [ $this, 'admin_promo_notice' ] );
            }

        }

    	function SetAdminStyle() {
            wp_register_style( "WooLentorProLic", WOOLENTOR_ADDONS_PL_URL_PRO . 'includes/licence/style.css', [] );
    		wp_enqueue_style( "WooLentorProLic" );
    	}

        // Check has Agency or Bundle Plan
        function hasAgencyAndBundle(){
            if( isset( $this->responseObj->license_title ) && !empty( $this->responseObj->license_title ) ){
                if( strpos( $this->responseObj->license_title, 'Website' ) !== false || strpos( $this->responseObj->license_title, 'Developer' ) !== false ){
                    return false;
                }else{
                    return true;
                }
            }
            return true;
        }

        function ActiveAdminMenu(){
            add_submenu_page(
                'woolentor_page', 
                esc_html__( 'License', 'woolentor-pro' ),
                esc_html__( 'License', 'woolentor-pro' ), 
                'manage_options', 
                $this->slug, 
                array ( $this, 'Activated' ) 
            );
        }

        function InactiveMenu() {
            add_submenu_page(
                'woolentor_page', 
                esc_html__( 'License', 'woolentor-pro' ),
                esc_html__( 'License', 'woolentor-pro' ), 
                'manage_options', 
                $this->slug, 
                array ( $this, 'LicenseForm' ) 
            );

        }

        // Banner content
        public function banner_content(){
            $bannerContent = [
                'plusyearly' => [
                    'image' => 'https://library.shoplentor.com/wp-content/uploads/2024/02/personal-yearly-to-bundle-notice.png',
                    'sidebarimage'=>"https://library.shoplentor.com/wp-content/uploads/2024/02/personal-yearly-to-bundle.png",
                    'title' => __('Bundle Mega Offer for Personal Yearly plan','woolentor-pro'),
                    'message' => 'Seize the Chance - Transform Your Personal Plan to the Bundle plan at an Unbeatable Price, starting at only $239!',
                    'button'=>[
                        'url' => 'https://woolentor.com/personal-yearly-to-bundle-lifetime/',
                        'text' => __('Upgrade Now','woolentor-pro'),
                    ]
                ],
                'pluslifetime' => [
                    'image' => 'https://library.shoplentor.com/wp-content/uploads/2024/02/personal-lifetime-to-bundle-notice.png',
                    'sidebarimage' => "https://library.shoplentor.com/wp-content/uploads/2024/02/personal-lifetime-to-bundle.png",
                    'title' => __('Bundle Mega Offer for Personal lifetime plan','woolentor-pro'),
                    'message' => 'Seize the Chance - Transform Your Personal Plan to the Bundle plan at an Unbeatable Price, starting at only $149!',
                    'button'=>[
                        'url' => 'https://woolentor.com/personal-lifetime-to-bundle-lifetime/',
                        'text' => __('Upgrade Now','woolentor-pro'),
                    ]
                ],
                'eliteyearly' => [
                    'image' => 'https://library.shoplentor.com/wp-content/uploads/2024/02/developer-yearly-to-bundle-notice.png',
                    'sidebarimage' => "https://library.shoplentor.com/wp-content/uploads/2024/02/developer-yearly-to-bundle.png",
                    'title' => __('Bundle Mega Offer for Developer yearly plan','woolentor-pro'),
                    'message' => 'Seize the Chance - Transform Your Developer Plan to the Bundle plan at an Unbeatable Price, starting at only $139!',
                    'button'=>[
                        'url' => 'https://woolentor.com/developer-yearly-to-bundle-lifetime/',
                        'text' => __('Upgrade Now','woolentor-pro'),
                    ]
                ],
                'elitelifetime' => [
                    'image' => 'https://library.shoplentor.com/wp-content/uploads/2024/02/developer-lifetime-to-bundle-notice.png',
                    'sidebarimage'=>"https://library.shoplentor.com/wp-content/uploads/2024/02/developer-lifetime-to-bundle.png",
                    'title' => __('Bundle Mega Offer for developer lifetime plan','woolentor-pro'),
                    'message' => 'Seize the Chance - Transform Your Developer Plan to the Bundle plan at an Unbeatable Price, starting at only $39!',
                    'button'=>[
                        'url' => 'https://woolentor.com/developer-lifetime-to-bundle-lifetime/',
                        'text' => __('Upgrade Now','woolentor-pro'),
                    ]
                ],

            ];

            if( isset( $this->responseObj->license_title ) && !empty( $this->responseObj->license_title ) ){
                if( strpos( $this->responseObj->license_title, 'Website' ) !== false ){
                    if( strpos( $this->responseObj->license_title, 'Year' ) !== false ){
                        return $bannerContent['plusyearly'];
                    }else{
                        return $bannerContent['pluslifetime'];
                    }
                }else{
                    if( strpos( $this->responseObj->license_title, 'Developer' ) !== false ){
                        if( strpos( $this->responseObj->license_title, 'Yearly' ) !== false ){
                            return $bannerContent['eliteyearly'];
                        }else{
                            return $bannerContent['elitelifetime'];
                        }
                    }
                }
            }
            return '';

        }

        // Admin Notice
        public function admin_promo_notice(){

            $bannerContent = $this->banner_content();

            if( empty( $bannerContent ) ){
                return;
            }
            
            if( !class_exists('\WooLentor_Notices') ){
                return;
            }

            $bannerImage = '<img src="'.$bannerContent['image'].'" alt="'.esc_attr($bannerContent['title']).'"/>';
            $banner['image'] = $bannerImage;
            $banner['url']   = $bannerContent['button']['url'].'?utm_source=admin&utm_medium=prouser&utm_campaign=offer_upgrade_bundle';
            \WooLentor_Notices::set_notice(
                [
                    'id'          => 'wlagency-bundle-promo-banner',
                    'type'        => 'info',
                    'dismissible' => true,
                    'banner'      => $banner,
                    'close_by'    => 'user',
                    'priority'    => 2
                ]
            );
               
        }

        // Sidebar Upgrade banner
        function sideBarPromoBanner( $html ){

            $bannerContent = $this->banner_content();

            if( empty( $bannerContent ) ){
                return;
            }

            $bannerLink = $bannerContent['button']['url'].'?utm_source=admin&utm_medium=sidebar&utm_campaign=offer_upgrade_bundle';
            $proBannerImage ='<img src='.$bannerContent['sidebarimage'].' alt='.$bannerContent['title'].'/>';
            $html = sprintf( '<div class="woolentor-promo-banner"><a href="%1$s" target="_blank">%2$s</a></div>', $bannerLink, $proBannerImage );

            return $html;

        }
        
        function action_activate_license(){
            check_admin_referer( 'el-license' );
            $licenseKey=!empty($_POST['el_license_key'])?$_POST['el_license_key']:"";
            $licenseEmail=!empty($_POST['el_license_email'])?$_POST['el_license_email']:"";
            update_option("WooLentorPro_lic_Key",$licenseKey) || add_option("WooLentorPro_lic_Key",$licenseKey);
            update_option("WooLentorPro_lic_email",$licenseEmail) || add_option("WooLentorPro_lic_email",$licenseEmail);
            wp_safe_redirect(admin_url( 'admin.php?page='.$this->slug));
        }
        function action_deactivate_license() {
    	    check_admin_referer( 'el-license' );
    	    if(WooLentorProBase::RemoveLicenseKey(__FILE__,$message)){
    		    update_option("WooLentorPro_lic_Key","") || add_option("WooLentorPro_lic_Key","");
    	    }
    	    wp_safe_redirect(admin_url( 'admin.php?page='.$this->slug));
        }
        function Activated(){
            ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="WooLentorPro_el_deactivate_license"/>
                <div class="el-license-container">
                    <h3 class="el-license-title"><i class="dashicons-before dashicons-star-filled"></i> <?php _e("WooLentor Pro License Info",$this->slug);?> </h3>
                    <hr>
                    <ul class="el-license-info">
                    <li>
                        <div>
                            <span class="el-license-info-title"><?php _e("Status",$this->slug);?></span>

    			            <?php if ( $this->responseObj->is_valid ) : ?>
                                <span class="el-license-valid"><?php _e("Valid",$this->slug);?></span>
    			            <?php else : ?>
                                <span class="el-license-valid"><?php _e("Invalid",$this->slug);?></span>
    			            <?php endif; ?>
                        </div>
                    </li>

                    <li>
                        <div>
                            <span class="el-license-info-title"><?php _e("License Type",$this->slug);?></span>
    			            <?php echo $this->responseObj->license_title; ?>
                        </div>
                    </li>

                    <li>
                        <div>
                            <span class="el-license-info-title"><?php _e("License Expired on",$this->slug);?></span>
    			            <?php echo $this->responseObj->expire_date; ?>
                        </div>
                    </li>

                    <li>
                        <div>
                            <span class="el-license-info-title"><?php _e("Support Expired on",$this->slug);?></span>
    			            <?php echo $this->responseObj->support_end; ?>
                        </div>
                    </li>
                        <li>
                            <div>
                                <span class="el-license-info-title"><?php _e("Your License Key",$this->slug);?></span>
                                <span class="el-license-key"><?php echo esc_attr( substr($this->responseObj->license_key,0,9)."XXXXXXXX-XXXXXXXX".substr($this->responseObj->license_key,-9) ); ?></span>
                            </div>
                        </li>
                    </ul>
                    <div class="el-license-active-btn">
    				    <?php wp_nonce_field( 'el-license' ); ?>
    				    <?php submit_button('Deactivate'); ?>
                    </div>
                </div>
            </form>
    	<?php
        }

        function LicenseForm() {
    	    ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    	    <input type="hidden" name="action" value="WooLentorPro_el_activate_license"/>
    	    <div class="el-license-container">
    		    <h3 class="el-license-title"><i class="dashicons-before dashicons-star-filled"></i> <?php _e("WooLentor Pro Licensing",$this->slug);?></h3>
    		    <hr>
                <?php
                if(!empty($this->showMessage) && !empty($this->licenseMessage)){
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php echo _e($this->licenseMessage,$this->slug); ?></p>
                    </div>
                    <?php
                }
                ?>
    		    <p><?php _e("Enter your license key here, to activate the product, and get future updates and premium support.",$this->slug);?></p>
    		    <div class="el-license-field">
    			    <label for="el_license_key"><?php _e("License code",$this->slug);?></label>
    			    <input type="text" class="regular-text code" name="el_license_key" size="50" placeholder="xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx" required="required">
    		    </div>
                <div class="el-license-field">
                    <label for="el_license_key"><?php _e("Email Address",$this->slug);?></label>
                    <?php
                        $purchaseEmail   = get_option( "WooLentorPro_lic_email", get_bloginfo( 'admin_email' ));
                    ?>
                    <input type="text" class="regular-text code" name="el_license_email" size="50" value="<?php echo $purchaseEmail; ?>" placeholder="" required="required">
                    <div><small><?php _e("We will send update news of this product by this email address, don't worry, we hate spam",$this->slug);?></small></div>
                </div>
    		    <div class="el-license-active-btn">
    			    <?php wp_nonce_field( 'el-license' ); ?>
    			    <?php submit_button('Activate'); ?>
    		    </div>
    	    </div>
        </form>
    	    <?php
        }
    }

    new WooLentorPro();