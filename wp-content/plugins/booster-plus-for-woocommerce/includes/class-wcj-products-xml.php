<?php
/**
 * Booster for WooCommerce - Module - Products XML
 *
 * @version 6.0.0
 * @since  1.0.0
 * @author  Pluggabl LLC.
 * @todo    create all files at once (manually and synchronize update)
 * @package Booster_Plus_For_WooCommerce/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WCJ_Products_XML' ) ) :
		/**
		 * WCJ_Products_XML.
		 */
	class WCJ_Products_XML extends WCJ_Module {

		/**
		 * Constructor.
		 *
		 * @version 6.0.0
		 * @since  1.0.0
		 */
		public function __construct() {

			$this->id         = 'products_xml';
			$this->short_desc = __( 'Products XML Feeds', 'woocommerce-jetpack' );
			$this->desc       = __( 'Products XML feeds (1 file allowed in free version.).', 'woocommerce-jetpack' );
			$this->desc_pro   = __( 'Products XML feeds.', 'woocommerce-jetpack' );
			$this->link_slug  = 'woocommerce-products-xml-feeds';
			parent::__construct();

			if ( $this->is_enabled() ) {
				add_action( 'init', array( $this, 'schedule_the_events' ) );
				add_action( 'admin_init', array( $this, 'schedule_the_events' ) );
				add_action( 'admin_init', array( $this, 'wcj_create_products_xml' ) );
				add_action( 'admin_notices', array( $this, 'admin_notices' ) );
				add_filter( 'cron_schedules', array( $this, 'cron_add_custom_intervals' ) );
				$total_number = apply_filters( 'booster_option', 1, wcj_get_option( 'wcj_products_xml_total_files', 1 ) );
				for ( $i = 1; $i <= $total_number; $i++ ) {
					add_action( 'wcj_create_products_xml_hook_' . $i, array( $this, 'create_products_xml_cron' ), PHP_INT_MAX, 2 );
				}
			}
		}

		/**
		 * On an early action hook, check if the hook is scheduled - if not, schedule it.
		 *
		 * @version 6.0.0
		 * @since  1.0.0
		 */
		public function schedule_the_events() {
			$update_intervals = array(
				'minutely',
				'hourly',
				'twicedaily',
				'daily',
				'weekly',
			);
			$total_number     = apply_filters( 'booster_option', 1, wcj_get_option( 'wcj_products_xml_total_files', 1 ) );
			for ( $i = 1; $i <= $total_number; $i++ ) {
				$event_hook = 'wcj_create_products_xml_hook_' . $i;
				if ( 'yes' === wcj_get_option( 'wcj_products_xml_enabled_' . $i, 'yes' ) ) {
					$selected_interval = apply_filters( 'booster_option', 'weekly', wcj_get_option( 'wcj_create_products_xml_period_' . $i, 'weekly' ) );
					foreach ( $update_intervals as $interval ) {
						$event_timestamp = wp_next_scheduled( $event_hook, array( $interval, $i ) );
						if ( $selected_interval === $interval ) {
							update_option( 'wcj_create_products_xml_cron_time_' . $i, $event_timestamp );
						}
						if ( ! $event_timestamp && $selected_interval === $interval ) {
							wp_schedule_event( time(), $selected_interval, $event_hook, array( $selected_interval, $i ) );
						} elseif ( $event_timestamp && $selected_interval !== $interval ) {
							wp_unschedule_event( $event_timestamp, $event_hook, array( $interval, $i ) );
						}
					}
				} else { // Unschedule all events.
					update_option( 'wcj_create_products_xml_cron_time_' . $i, '' );
					foreach ( $update_intervals as $interval ) {
						$event_timestamp = wp_next_scheduled( $event_hook, array( $interval, $i ) );
						if ( $event_timestamp ) {
							wp_unschedule_event( $event_timestamp, $event_hook, array( $interval, $i ) );
						}
					}
				}
			}
		}

		/**
		 * Cron_add_custom_intervals.
		 *
		 * @version 6.0.0
		 * @since  1.0.0
		 * @param  array $schedules defines the schedules.
		 */
		public function cron_add_custom_intervals( $schedules ) {
			$schedules['weekly']   = array(
				'interval' => 604800,
				'display'  => __( 'Once Weekly', 'woocommerce-jetpack' ),
			);
			$schedules['minutely'] = array(
				'interval' => 60,
				'display'  => __( 'Once a Minute', 'woocommerce-jetpack' ),
			);
			return $schedules;
		}

		/**
		 * Admin_notices.
		 *
		 * @version 6.0.0
		 * @since  1.0.0
		 */
		public function admin_notices() {
			$wpnonce = isset( $_REQUEST['wcj-cat-nonce'] ) ? wp_verify_nonce( sanitize_key( $_REQUEST['wcj-cat-nonce'] ), 'wcj-cat-nonce' ) : false;
			if ( $wpnonce && isset( $_GET['wcj_create_products_xml_result'] ) ) {
				if ( 0 === (int) $_GET['wcj_create_products_xml_result'] ) {
					$class   = 'notice notice-error';
					$message = __( 'An error has occurred while creating products XML file.', 'woocommerce-jetpack' );
				} else {
					$class = 'notice notice-success is-dismissible';
					/* translators: %s: translation added */
					$message = sprintf( __( 'Products XML file #%s created successfully.', 'woocommerce-jetpack' ), sanitize_text_field( wp_unslash( $_GET['wcj_create_products_xml_result'] ) ) );
				}
				echo '<div class="' . wp_kses_post( $class ) . '"><p>' . wp_kses_post( $message ) . '</p></div>';
			}
		}

		/**
		 * Wcj_create_products_xml.
		 *
		 * @version 6.0.0
		 * @since  1.0.0
		 */
		public function wcj_create_products_xml() {
			$wpnonce = isset( $_REQUEST['wcj-cat-nonce'] ) ? wp_verify_nonce( sanitize_key( $_REQUEST['wcj-cat-nonce'] ), 'wcj-cat-nonce' ) : false;
			if ( $wpnonce && isset( $_GET['wcj_create_products_xml'] ) ) {
				$file_num = sanitize_text_field( wp_unslash( $_GET['wcj_create_products_xml'] ) );
				$result   = $this->create_products_xml( $file_num );
				if ( false !== $result ) {
					update_option( 'wcj_products_time_file_created_' . $file_num, wcj_get_timestamp_date_from_gmt() );
				}
				wp_safe_redirect( add_query_arg( 'wcj_create_products_xml_result', ( false === $result ? 0 : $file_num ), remove_query_arg( 'wcj_create_products_xml' ) ) );
				exit;
			}
		}

		/**
		 * Create_products_xml_cron.
		 *
		 * @version 6.0.0
		 * @since  1.0.0
		 * @param  string $interval defines the interval.
		 * @param  int    $file_num defines the file_num.
		 */
		public function create_products_xml_cron( $interval, $file_num ) {
			$result = $this->create_products_xml( $file_num );
			if ( false !== $result ) {
				update_option( 'wcj_products_time_file_created_' . $file_num, wcj_get_timestamp_date_from_gmt() );
			}
			die();
		}

		/**
		 * Process_shortcode.
		 *
		 * @version 6.0.0
		 * @since  1.0.0
		 * @todo    [dev] (maybe) re-think `str_replace( '&', '&amp;', $content )`
		 * @param  string $content defines the content.
		 */
		public function process_shortcode( $content ) {
			return str_replace( '&', '&amp;', html_entity_decode( do_shortcode( $content ) ) );
		}

		/**
		 * Create_products_xml.
		 *
		 * @version 6.0.0
		 * @since  1.0.0
		 * @param  int $file_num defines the file_num.
		 */
		public function create_products_xml( $file_num ) {
			global $wp_filesystem;
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
			$xml_items            = '';
			$xml_header_template  = wcj_get_option( 'wcj_products_xml_header_' . $file_num, '' );
			$xml_footer_template  = wcj_get_option( 'wcj_products_xml_footer_' . $file_num, '' );
			$xml_item_template    = wcj_get_option( 'wcj_products_xml_item_' . $file_num, '' );
			$products_in_ids      = wcj_maybe_convert_string_to_array( wcj_get_option( 'wcj_products_xml_products_incl_' . $file_num, '' ) );
			$products_ex_ids      = wcj_maybe_convert_string_to_array( wcj_get_option( 'wcj_products_xml_products_excl_' . $file_num, '' ) );
			$products_cats_in_ids = wcj_get_option( 'wcj_products_xml_cats_incl_' . $file_num, '' );
			$products_cats_ex_ids = wcj_get_option( 'wcj_products_xml_cats_excl_' . $file_num, '' );
			$products_tags_in_ids = wcj_get_option( 'wcj_products_xml_tags_incl_' . $file_num, '' );
			$products_tags_ex_ids = wcj_get_option( 'wcj_products_xml_tags_excl_' . $file_num, '' );
			$products_scope       = wcj_get_option( 'wcj_products_xml_scope_' . $file_num, 'all' );
			$order_by             = wcj_get_option( 'wcj_products_xml_orderby_' . $file_num, 'date' );
			$order                = wcj_get_option( 'wcj_products_xml_order_' . $file_num, 'DESC' );
			$max                  = wcj_get_option( 'wcj_products_xml_max_' . $file_num, -1 );
			$block_size           = wcj_get_option( 'wcj_products_xml_block_size', 256 );
			$offset               = 0;
			$counter              = 0;
			while ( true ) {
				$args = array(
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'posts_per_page' => $block_size,
					'orderby'        => $order_by,
					'order'          => $order,
					'offset'         => $offset,
				);
				if ( 'all' !== $products_scope ) {
					$args['meta_query'] = WC()->query->get_meta_query(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					switch ( $products_scope ) {
						case 'sale_only':
							$args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
							break;
						case 'not_sale_only':
							$args['post__not_in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
							break;
						case 'featured_only':
							$args['post__in'] = array_merge( array( 0 ), wc_get_featured_product_ids() );
							break;
						case 'not_featured_only':
							$args['post__not_in'] = array_merge( array( 0 ), wc_get_featured_product_ids() );
							break;
					}
				}
				if ( ! empty( $products_in_ids ) ) {
					$args['post__in'] = $products_in_ids;
				}
				if ( ! empty( $products_ex_ids ) ) {
					$args['post__not_in'] = $products_ex_ids;
				}
				if ( ! empty( $products_cats_in_ids ) ) {
					if ( ! isset( $args['tax_query'] ) ) {
						$args['tax_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					}
					$args['tax_query'][] = array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $products_cats_in_ids,
						'operator' => 'IN',
					);
				}
				if ( ! empty( $products_cats_ex_ids ) ) {
					if ( ! isset( $args['tax_query'] ) ) {
						$args['tax_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					}
					$args['tax_query'][] = array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $products_cats_ex_ids,
						'operator' => 'NOT IN',
					);
				}
				if ( ! empty( $products_tags_in_ids ) ) {
					if ( ! isset( $args['tax_query'] ) ) {
						$args['tax_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					}
					$args['tax_query'][] = array(
						'taxonomy' => 'product_tag',
						'field'    => 'term_id',
						'terms'    => $products_tags_in_ids,
						'operator' => 'IN',
					);
				}
				if ( ! empty( $products_tags_ex_ids ) ) {
					if ( ! isset( $args['tax_query'] ) ) {
						$args['tax_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					}
					$args['tax_query'][] = array(
						'taxonomy' => 'product_tag',
						'field'    => 'term_id',
						'terms'    => $products_tags_ex_ids,
						'operator' => 'NOT IN',
					);
				}
				$loop = new WP_Query( $args );
				if ( ! $loop->have_posts() ) {
					break;
				}
				while ( $loop->have_posts() ) {
					if ( '-1' !== $max && $counter >= $max ) {
						break;
					}
					$loop->the_post();
					$xml_items .= $this->process_shortcode( $xml_item_template );
					$counter++;
				}
				$offset += $block_size;
				if ( '-1' !== $max && $counter >= $max ) {
					break;
				}
			}
			wp_reset_postdata();
			return $wp_filesystem->put_contents(
				ABSPATH . wcj_get_option( 'wcj_products_xml_file_path_' . $file_num, ( ( '1' === $file_num ) ? 'products.xml' : 'products_' . $file_num . '.xml' ) ),
				$this->process_shortcode( $xml_header_template ) . $xml_items . $this->process_shortcode( $xml_footer_template ),
				FS_CHMOD_FILE
			);
		}

	}

endif;

return new WCJ_Products_XML();
