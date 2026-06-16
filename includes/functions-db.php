<?php
/**
 * Database Utilities for Karen Plugin
 *
 * Provides utility functions for database operations
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get table name
 *
 * @param string $table Table name (without prefix)
 * @return string
 */
function karen_get_table( $table ) {
    global $wpdb;
    return $wpdb->prefix . 'karen_' . $table;
}

/**
 * Check if plugin tables exist
 *
 * @return bool
 */
function karen_tables_exist() {
    global $wpdb;

    $table1 = $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . "karen_sms_logs'" );
    $table2 = $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . "karen_sent_customers'" );

    return ! is_null( $table1 ) && ! is_null( $table2 );
}

/**
 * Get database version
 *
 * @return string
 */
function karen_get_db_version() {
    return get_option( 'karen_db_version', '0' );
}

/**
 * Update database version
 *
 * @param string $version Version
 * @return bool
 */
function karen_update_db_version( $version ) {
    return update_option( 'karen_db_version', $version );
}
