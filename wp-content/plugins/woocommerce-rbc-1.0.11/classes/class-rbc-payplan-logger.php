<?php

/**
 * RBC Payplan logger
 * 
 * @package Rbc_Payplan\Classes
 */

 namespace Rbc_Payplan\Classes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Rbc_Payplan_Logger 
{
    /**
     * The logger instance.
     *
     * @var WC_Logger|null
     */
    private static $logger;

    /**
     * The filename for the logger.
     */
    const WC_LOG_FILENAME = 'rbc-payplan-log';

    /**
     * Log a message
     *
     * @param string $message The message to log
     * @param array $context Any additional context to include in the log message
     * 
     * @since 1.0.10
     * @version 1.0.10
     */
    public static function log( $message, $context = array() ) {
        if ( ! class_exists( 'WC_Logger' ) ) {
            return;
        }

        if (apply_filters('wc_rbc_payplan_logging', true, $message)) {
            if (self::$logger === null) {
                self::$logger = wc_get_logger();
            }

            $logEntry = "\n" . '==== RBC Payplan Version: ' . WC_RBC_PAYPLAN_VERSION . ' ====' . "\n";
            $logEntry .= '==== Start Log ====' . "\n" . $message . "\n" . '==== End Log ====' . "\n\n";

            self::$logger->debug( $logEntry, [ 'source' => self::WC_LOG_FILENAME ], $context );
        }
    }
}

