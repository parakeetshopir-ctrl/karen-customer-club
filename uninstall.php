<?php
/**
 * Uninstall Karen Customer Club Plugin
 *
 * @since 1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Check if user wants to delete data
$delete_data = get_option( 'karen_delete_data_on_uninstall', false );

if ( $delete_data ) {
    global $wpdb;

    // Drop plugin tables
    $table_sms_logs = $wpdb->prefix . 'karen_sms_logs';
    $table_sent_customers = $wpdb->prefix . 'karen_sent_customers';

    $wpdb->query( "DROP TABLE IF EXISTS $table_sms_logs" );
    $wpdb->query( "DROP TABLE IF EXISTS $table_sent_customers" );
}

// Delete all plugin options
delete_option( 'karen_delete_data_on_uninstall' );
delete_option( 'karen_settings' );
delete_option( 'karen_sms_settings' );
delete_option( 'karen_coupon_settings' );
delete_option( 'karen_gateway_discounts' );

// Clear cron
wp_clear_scheduled_hook( 'karen_process_orders_cron' );
