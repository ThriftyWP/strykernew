<?php
namespace WoolentorPro\Modules\AdvancedCoupon;
use WooLentorPro\Traits\ModuleBase;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Advanced_Coupon{
    use ModuleBase;

    /**
     * Class Constructor
     */
    public function __construct(){

        // Definded Constants
        $this->define_constants();

        // Include Nessary file
        $this->include();

        // initialize
        $this->init();

    }

    /**
     * Defined Required Constants
     *
     * @return void
     */
    public function define_constants(){
        define( 'WoolentorPro\Modules\AdvancedCoupon\MODULE_FILE', __FILE__ );
        define( 'WoolentorPro\Modules\AdvancedCoupon\MODULE_PATH', __DIR__ );
        define( 'WoolentorPro\Modules\AdvancedCoupon\ENABLED', self::$_enabled );
    }

    /**
     * Load Required File
     *
     * @return void
     */
    public function include(){
        require_once( MODULE_PATH. "/includes/classes/Admin.php" );
        require_once( MODULE_PATH. "/includes/classes/Frontend.php" );

    }

    /**
     * Module Initilize
     *
     * @return void
     */
    public function init(){
        // For Admin
        if ( $this->is_request( 'admin' ) ) {
            Admin::instance();
        }
        
        if( self::$_enabled ){
            // For Frontend
            if ( $this->is_request( 'frontend' ) ) {
                Frontend::instance();
            }
        }
    }


}
Advanced_Coupon::instance(true);