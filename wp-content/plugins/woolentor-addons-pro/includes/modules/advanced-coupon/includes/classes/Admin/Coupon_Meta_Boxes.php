<?php
namespace WoolentorPro\Modules\AdvancedCoupon\Admin;
use WooLentorPro\Traits\Singleton;
use Woolentor\Modules\AdvancedCoupon\Functions;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Coupon_Meta_Boxes {
    use Singleton;

    public function __construct(){

        // Add Payment option
        add_action('woolentor_coupon_payment_fields',[$this,'payment_method_fields'], 10, 1);

        // Save meta boxes data
        add_action('woocommerce_process_shop_coupon_meta', [$this, 'save_meta_boxes_data'], 11, 2);

    }

    /**
     * Payment Method related fields
     * @param mixed $coupon_id
     * @return void
     */
    public function payment_method_fields( $coupon_id ){
        ?>
            <!-- Payment restriction Start -->
            
            <p class="form-field">
                <label for="woolentor_payment_method_ids"><?php esc_html_e( 'Payment methods', 'woolentor' ); ?></label>
                <select id="woolentor_payment_method_ids" name="woolentor_payment_method_ids[]" style="width:50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select payment method', 'woolentor' ); ?>">
                    <?php
                        $payment_methods = WC()->payment_gateways->payment_gateways();

                        if ( ! empty( $payment_methods ) ) {
                            $payment_method_ids = Functions::get_multiple_meta_date( $coupon_id, 'woolentor_payment_method_ids' );
    
                            foreach ( $payment_methods as $payment_method ) {
                                if ( wc_string_to_bool( $payment_method->enabled ) ) {
                                    echo '<option value="' . esc_attr( $payment_method->id ) . '" ' . selected( in_array( $payment_method->id, $payment_method_ids ), true, false ) . '>' . esc_html( wp_strip_all_tags($payment_method->title) ) . '</option>';
                                }
                            }

                        }
                    ?>
                </select>
                <?php echo wc_help_tip( esc_html__( 'The coupon will only be applicable if the selected payment method matches either condition.', 'woolentor' ) ); ?>
            </p>

            <div class="options_group" style="border-top:0;">
                <?php
                    woocommerce_wp_select([
                        'id'          => 'woolentor_payment_restrict_type',
                        'type'        => 'select',
                        'value'       => Functions::get_meta_data( $coupon_id , 'woolentor_payment_restrict_type', 'allowed' ),
                        'options'     => [
                            'allowed'    => esc_html__( 'Allowed', 'woolentor' ),
                            'disallowed' => esc_html__( 'Disallowed', 'woolentor' ),
                        ],
                        'style'       => 'width:50%;',
                        'label'       => esc_html__( 'Payment restrict type', 'woolentor' ),
                        'description' => esc_html__( 'The type of implementation for this restriction. Select "allowed" to allow coupon only to payment method under the selected method. Select "disallowed" to only allow coupon to payment that don\'t fall under the selected method.', 'woolentor' ),
                        'desc_tip'    => true,
                    ]);

                    woocommerce_wp_textarea_input([
                        'id'          => 'woolentor_payment_error_message',
                        'label'       => esc_html__( 'Payment error message', 'woolentor' ),
                        'description' => esc_html__( 'Show a personalized error message to customers attempting to use a coupon before it state date.', 'woolentor' ),
                        'desc_tip'    => true,
                        'type'        => 'text',
                        'data_type'   => 'text',
                        'placeholder' => esc_html__('This coupon is not applicable with your selected payment method.','woolentor'),
                        'value'       => Functions::get_meta_data( $coupon_id , 'woolentor_payment_error_message' ),
                    ]);

                ?>
            </div>

            <!-- Payment restriction End -->
        <?php
    }

    /**
     * Manage Metaboxes
     * @param mixed $coupon_id
     * @param mixed $coupon
     * @return void
     */
    public function save_meta_boxes_data($coupon_id, $coupon){

        // Check nonce
        if ( empty($_POST['_wpnonce']) || empty($_POST['post_ID']) || 
            !wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST['_wpnonce'])),
                'update-post_' . sanitize_text_field(wp_unslash($_POST['post_ID']))
            )
        ) {
            return;
        }

        // Payment method restric
        $payment_restrict_type = ( !empty($_POST['woolentor_payment_restrict_type'] ) ? sanitize_text_field( wp_unslash($_POST['woolentor_payment_restrict_type']) ) : '');
        $payment_method_ids = !empty( $_POST['woolentor_payment_method_ids'] ) ? array_filter( array_map( 'sanitize_text_field', (array) $_POST['woolentor_payment_method_ids'] ) ) : [];
        $payment_error_msg = ( !empty($_POST['woolentor_payment_error_message'] ) ? sanitize_text_field( wp_unslash($_POST['woolentor_payment_error_message']) ) : '');

        update_post_meta($coupon_id, 'woolentor_payment_restrict_type', $payment_restrict_type);
        update_post_meta($coupon_id, 'woolentor_payment_method_ids', $payment_method_ids);
        update_post_meta($coupon_id, 'woolentor_payment_error_message', $payment_error_msg);

    }

}