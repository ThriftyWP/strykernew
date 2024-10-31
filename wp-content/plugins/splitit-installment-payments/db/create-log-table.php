<?php
/**
 * @package     Splitit_WooCommerce_Plugin
 *
 * File - create-log-table.php
 * Function for create log table
 */

require_once dirname( __DIR__ ) . '/classes/class-splitit-flexfields-payment-plugin-log.php';

function splitit_flexfields_payment_plugin_create_log_table() {
	global $wpdb;

	$log_data = array(
		'user_id' => null,
		'method'  => __( 'splitit_flexfields_payment_plugin_create_log_table() Splitit', 'splitit_ff_payment' ),
	);

	if ( is_multisite() ) {
		$sites = get_sites();
		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );

			$table_name = $wpdb->prefix . 'splitit_log';

			$charset_collate = $wpdb->get_charset_collate();

			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {

				SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'Create_splitit_log_table: ' . $table_name, 'info' );

				$sql = "CREATE TABLE $table_name (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    user_id bigint(20) unsigned NULL DEFAULT NULL,
                    method varchar(191) DEFAULT NULL NULL,
                    message TEXT DEFAULT NULL NULL,
                    date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    FOREIGN KEY (user_id) REFERENCES " . $wpdb->base_prefix . "users(ID) ON DELETE CASCADE,
                    PRIMARY KEY  (id)
                ) $charset_collate;";

				SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'create_splitit_log_table sql: ' . $sql, 'info' );

				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				dbDelta( $sql );

				if ( $wpdb->last_error ) {
					SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'create_splitit_log_table Error: ' . $wpdb->last_error, 'error' );
				} else {
					if ( '' !== $sql ) {
						SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'splitit_log_table successfully created', 'info' );
					}
				}
			}

			restore_current_blog();
		}
	} else {
		$table_name = $wpdb->prefix . 'splitit_log';

		$charset_collate = $wpdb->get_charset_collate();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {

			SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'Create_splitit_log_table: ' . $table_name, 'info' );

			$sql = "CREATE TABLE $table_name (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned NULL DEFAULT NULL,
                method varchar(191) DEFAULT NULL NULL,
                message TEXT DEFAULT NULL NULL,
                date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                FOREIGN KEY (user_id) REFERENCES " . $wpdb->base_prefix . "users(ID) ON DELETE CASCADE,
                PRIMARY KEY  (id)
            ) $charset_collate;";

			SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'create_splitit_log_table sql: ' . $sql, 'info' );

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			if ( $wpdb->last_error ) {
				SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'create_splitit_log_table Error: ' . $wpdb->last_error, 'error' );
			} else {
				if ( '' !== $sql ) {
					SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'splitit_log_table successfully created', 'info' );
				}
			}
		}
	}
}
