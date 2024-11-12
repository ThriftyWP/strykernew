<?php
namespace WoolentorPro\Modules\AdvancedCoupon\Frontend;
use WooLentorPro\Traits\Singleton;
use Woolentor\Modules\AdvancedCoupon\Functions;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Coupon_Rule_Checker {
    use Singleton;

    public function __construct(){
        add_filter('woocommerce_coupon_is_valid', [$this,'woocommerce_coupon_is_valid'], 10, 2);
    }

    /**
     * Check Coupon Validility
     * @param mixed $is_valid
     * @param mixed $coupon
     * @return mixed
     */
    public function woocommerce_coupon_is_valid($is_valid, $coupon) {
        $coupon_id = $coupon->get_id();

        // Payment Method
        if( $is_valid ){
            $is_valid = $this->checked_payment_method($coupon_id, $is_valid);
        }
    
        return $is_valid;
    }

    /**
     * Checked Payment method
     * @param mixed $coupon_id
     * @param mixed $is_valid
     * @throws \Exception
     * @return mixed
     */
    public function checked_payment_method($coupon_id, $is_valid){

        $payment_method_ids     = Functions::get_multiple_meta_date($coupon_id, 'woolentor_payment_method_ids');
        $payment_restrict_type  = Functions::get_meta_data( $coupon_id , 'woolentor_payment_restrict_type');
        $payment_err_msg        = Functions::get_meta_data($coupon_id, 'woolentor_payment_error_message');
        $payment_err_msg        = !empty($payment_err_msg) ? $payment_err_msg : 'This coupon is not applicable with your selected payment method.';

        // If the payment method is empty, return the current validity status.
        if ( empty($payment_method_ids) ) {
            return $is_valid;
        }

        if (!empty($payment_method_ids)) {
            $woocommerce    = WC();
            $chosen_method  = isset( $woocommerce->session->chosen_payment_method ) ? $woocommerce->session->chosen_payment_method : '';

            // Determine the validity condition based on the restrict type
            $is_method_restricted = in_array($chosen_method, $payment_method_ids);
            $validity_condition = ($payment_restrict_type === 'allowed') ? !$is_method_restricted : $is_method_restricted;

            // Set $is_valid to false and throw an exception if the condition is not met
            if ($validity_condition) {
                $is_valid = false;
                throw new \Exception(esc_html($payment_err_msg), 109);
            }
        }

        return $is_valid;
    }
    

}