<?php
/**
 * @package     Splitit_WooCommerce_Plugin
 *
 * File - create-log-table.php
 * Function for create transaction tracking table
 */

require_once dirname( __DIR__ ) . '/classes/class-splitit-flexfields-payment-plugin-log.php';

function splitit_flexfields_payment_plugin_create_transactions_tracking_table() {
	global $wpdb;

	$log_data = array(
		'user_id' => null,
		'method'  => __( 'splitit_flexfields_payment_plugin_create_transactions_tracking_table() Splitit', 'splitit_ff_payment' ),
	);

	if ( is_multisite() ) {
		$sites = get_sites();
		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );

			$table_name = $wpdb->prefix . 'splitit_transactions_log';

			SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'Create_splitit_transactions_log: ' . $table_name, 'info' );
			SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'table_from_db: ' . json_encode( $wpdb->get_var( "show tables like '$table_name'" ) ), 'info' );

			$charset_collate = $wpdb->get_charset_collate();

			$sql = '';
			if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {

				SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'try to Create_splitit_transactions_log: ' . $table_name, 'info' );

				$sql = "CREATE TABLE $table_name (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					user_id bigint(20) unsigned NULL DEFAULT NULL,
					order_id bigint(20) unsigned NULL DEFAULT NULL,
					installment_plan_number varchar(100) DEFAULT NULL NULL,
					number_of_installments varchar(100) DEFAULT NULL NULL,
					processing varchar(50) DEFAULT NULL NULL,
					plan_create_succeed tinyint(4) NOT NULL DEFAULT 0,
					date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					FOREIGN KEY (user_id) REFERENCES " . $wpdb->base_prefix . 'users(ID) ON DELETE CASCADE,
					FOREIGN KEY (order_id) REFERENCES ' . $wpdb->prefix . "posts(ID) ON DELETE CASCADE,
					PRIMARY KEY  (id)
				) $charset_collate;";
			}

			SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'Create_splitit_transactions_log sql: ' . $sql, 'info' );

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			if ( $wpdb->last_error ) {
				SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'create_transactions_log_table failed. Error: ' . $wpdb->last_error, 'error' );

				// let's try it again with different parameters.
				SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, '2 attempt create_transactions_log_table: ' . $table_name, 'info' );

				$charset_collate = 'ENGINE=MyISAM ' . $wpdb->get_charset_collate();

				$sql = '';
				if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {

					SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, '2 attempt try to create transactions_log_table: ' . $table_name, 'info' );

					$sql = "CREATE TABLE $table_name (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						user_id bigint(20) unsigned NULL DEFAULT NULL,
						order_id bigint(20) unsigned NULL DEFAULT NULL,
						installment_plan_number varchar(100) DEFAULT NULL NULL,
						number_of_installments varchar(100) DEFAULT NULL NULL,
						processing varchar(50) DEFAULT NULL NULL,
						plan_create_succeed tinyint(4) NOT NULL DEFAULT 0,
						date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
						FOREIGN KEY (user_id) REFERENCES " . $wpdb->base_prefix . 'users(ID) ON DELETE CASCADE,
						FOREIGN KEY (order_id) REFERENCES ' . $wpdb->prefix . "posts(ID) ON DELETE CASCADE,
						PRIMARY KEY  (id)
					) $charset_collate;";
				}

				SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, '2 attempt sql: ' . $sql, 'info' );

				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				dbDelta( $sql );

				if ( $wpdb->last_error ) {
					SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, '2 attempt create_transactions_log_table failed. Error: ' . $wpdb->last_error, 'error' );
				} else {
					$ms = '' === $sql ? '2 attempt create_transactions_log_table already exist' : '2 attempt create_transactions_log_table successfully created';
					SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, $ms, 'info' );
				}
			} else {
				$ms = '' === $sql ? 'create_transactions_log_table already exist' : 'create_transactions_log_table successfully created';
				SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, $ms, 'info' );
			}

			restore_current_blog();
		}
	} else {
		$table_name = $wpdb->prefix . 'splitit_transactions_log';

		SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'Create_splitit_transactions_log: ' . $table_name, 'info' );
		SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'table_from_db: ' . json_encode( $wpdb->get_var( "show tables like '$table_name'" ) ), 'info' );

		$charset_collate = $wpdb->get_charset_collate();

		$sql = '';
		if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {

			SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'try to Create_splitit_transactions_log: ' . $table_name, 'info' );

			$sql = "CREATE TABLE $table_name (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NULL DEFAULT NULL,
				order_id bigint(20) unsigned NULL DEFAULT NULL,
				installment_plan_number varchar(100) DEFAULT NULL NULL,
				number_of_installments varchar(100) DEFAULT NULL NULL,
				processing varchar(50) DEFAULT NULL NULL,
				plan_create_succeed tinyint(4) NOT NULL DEFAULT 0,
				date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				FOREIGN KEY (user_id) REFERENCES " . $wpdb->base_prefix . 'users(ID) ON DELETE CASCADE,
				FOREIGN KEY (order_id) REFERENCES ' . $wpdb->prefix . "posts(ID) ON DELETE CASCADE,
				PRIMARY KEY  (id)
			) $charset_collate;";
		}

		SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'Create_splitit_transactions_log sql: ' . $sql, 'info' );

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		if ( $wpdb->last_error ) {
			SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, 'create_transactions_log_table failed. Error: ' . $wpdb->last_error, 'error' );

			// let's try it again with different parameters.
			SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, '2 attempt create_transactions_log_table: ' . $table_name, 'info' );

			$charset_collate = 'ENGINE=MyISAM ' . $wpdb->get_charset_collate();

			$sql = '';
			if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {

				SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, '2 attempt try to create transactions_log_table: ' . $table_name, 'info' );

				$sql = "CREATE TABLE $table_name (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					user_id bigint(20) unsigned NULL DEFAULT NULL,
					order_id bigint(20) unsigned NULL DEFAULT NULL,
					installment_plan_number varchar(100) DEFAULT NULL NULL,
					number_of_installments varchar(100) DEFAULT NULL NULL,
					processing varchar(50) DEFAULT NULL NULL,
					plan_create_succeed tinyint(4) NOT NULL DEFAULT 0,
					date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
					FOREIGN KEY (user_id) REFERENCES " . $wpdb->base_prefix . 'users(ID) ON DELETE CASCADE,
					FOREIGN KEY (order_id) REFERENCES ' . $wpdb->prefix . "posts(ID) ON DELETE CASCADE,
					PRIMARY KEY  (id)
				) $charset_collate;";
			}

			SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, '2 attempt sql: ' . $sql, 'info' );

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			if ( $wpdb->last_error ) {
				SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, '2 attempt create_transactions_log_table failed. Error: ' . $wpdb->last_error, 'error' );
			} else {
				$ms = '' === $sql ? '2 attempt create_transactions_log_table already exist' : '2 attempt create_transactions_log_table successfully created';
				SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, $ms, 'info' );
			}
		} else {
			$ms = '' === $sql ? 'create_transactions_log_table already exist' : 'create_transactions_log_table successfully created';
			SplitIt_FlexFields_Payment_Plugin_Log::save_log_info( $log_data, $ms, 'info' );
		}
	}
}
