<?php
/**
 * Booster for WooCommerce - Widget - Country Switcher
 *
 * @version 6.0.2
 * @since  1.0.0
 * @author  Pluggabl LLC.
 * @package Booster_Plus_For_WooCommerce/Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCJ_Widget_Country_Switcher' ) ) :

		/**
		 * WCJ_Widget_Country_Switcher.
		 *
		 * @version 6.0.0
		 * @since   1.0.0
		 */
	class WCJ_Widget_Country_Switcher extends WCJ_Widget {

		/**
		 * Get_data.
		 *
		 * @version 6.0.0
		 * @since   1.0.0
		 * @param int $id Get id base widget data.
		 */
		public function get_data( $id ) {
			switch ( $id ) {
				case 'id_base':
					return 'wcj_widget_country_switcher';
				case 'name':
					return __( 'Booster - Country Switcher', 'woocommerce-jetpack' );
				case 'description':
					return __( 'Booster: Country Switcher Widget', 'woocommerce-jetpack' );
			}
		}

		/**
		 * Get_content.
		 *
		 * @version 6.0.2
		 * @since   1.0.0
		 * @param array $instance Saved values from database.
		 */
		public function get_content( $instance ) {
			if ( ! wcj_is_module_enabled( 'price_by_country' ) ) {
				return __( 'Prices and Currencies by Country module not enabled!', 'woocommerce-jetpack' );
			} elseif ( 'by_ip' === wcj_get_option( 'wcj_price_by_country_customer_country_detection_method', 'by_ip' ) ) {
				return __( 'Customer Country Detection Method must include "by user selection"!', 'woocommerce-jetpack' );
			} else {
				if ( ! isset( $instance['replace_with_currency'] ) ) {
					$instance['replace_with_currency'] = 'no';
				}
				if ( ! isset( $instance['countries'] ) ) {
					$instance['countries'] = '';
				}
				return do_shortcode(
					'[wcj_country_select_drop_down_list' .
					' form_method="' . ( ! empty( $instance['form_method'] ) ? $instance['form_method'] : 'post' ) . '"' .
					' countries="' . $instance['countries'] . '" replace_with_currency="' . $instance['replace_with_currency'] . '"]'
				);
			}
		}

		/**
		 * Get_options.
		 *
		 * @version 6.0.0
		 * @since   1.0.0
		 * @todo    (maybe) `switcher_type`
		 */
		public function get_options() {
			return array(
				array(
					'title'   => __( 'Title', 'woocommerce-jetpack' ),
					'id'      => 'title',
					'default' => '',
					'type'    => 'text',
					'class'   => 'widefat',
				),
				array(
					'title'   => __( 'Countries', 'woocommerce-jetpack' ),
					'id'      => 'countries',
					'default' => '',
					'type'    => 'text',
					'class'   => 'widefat',
				),
				array(
					'title'   => __( 'Replace with currency', 'woocommerce-jetpack' ),
					'id'      => 'replace_with_currency',
					'default' => 'no',
					'type'    => 'select',
					'class'   => 'widefat',
					'options' => array(
						'no'  => __( 'No', 'woocommerce-jetpack' ),
						'yes' => __( 'Yes', 'woocommerce-jetpack' ),
					),
				),
				array(
					'title'   => __( 'Form Method', 'woocommerce-jetpack' ),
					'desc'    => '* ' . __( 'HTML form method for "Drop down" and "Radio list" types.', 'woocommerce-jetpack' ),
					'id'      => 'form_method',
					'default' => 'post',
					'type'    => 'select',
					'options' => array(
						'post' => __( 'Post', 'woocommerce-jetpack' ),
						'get'  => __( 'Get', 'woocommerce-jetpack' ),
					),
					'class'   => 'widefat',
				),
			);
		}

	}

endif;

if ( ! function_exists( 'register_wcj_widget_country_switcher' ) ) {
	/**
	 * Register WCJ_Widget_Country_Switcher widget.
	 */
	function register_wcj_widget_country_switcher() {
		register_widget( 'WCJ_Widget_Country_Switcher' );
	}
}
add_action( 'widgets_init', 'register_wcj_widget_country_switcher' );
