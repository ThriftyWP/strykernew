<?php

define( 'ABSPATH', '/apps/bread/wordpress/5.6.beta-1/' );

define( 'WP_DEBUG', false );

// WARNING WARNING WARNING!
// tests DROPS ALL TABLES in the database. DO NOT use a production database

define( 'DB_NAME', 'wp_tests' );
define( 'DB_USER', 'wptest' );
define( 'DB_PASSWORD', 'wptest' );
define( 'DB_HOST', '127.0.0.1' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_'; // Only numbers, letters, and underscores please!

define( 'WP_TESTS_DOMAIN', 'localhost' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Payplan by RBC' );

define( 'WP_PHP_BINARY', 'php' );

define( 'WPLANG', 'en' );