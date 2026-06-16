<?php
/**
 * Database Class
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Karen_DB {

    /**
     * Create plugin tables
     *
     * @since 1.0.0
     */
    public static function create_tables() {
        global $wpdb;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $charset_collate = $wpdb->get_charset_collate();

        // Table 1: SMS Logs
        $table_sms_logs = $wpdb->prefix . 'karen_sms_logs';
        $sql_sms_logs = "CREATE TABLE IF NOT EXISTS $table_sms_logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            phone_raw VARCHAR(20) NOT NULL,
            phone_normalized VARCHAR(20) NOT NULL,
            order_id BIGINT UNSIGNED NOT NULL,
            coupon_code VARCHAR(100) NOT NULL,
            gateway VARCHAR(50) NOT NULL,
            message LONGTEXT NOT NULL,
            status ENUM('sent', 'failed', 'queued') DEFAULT 'queued',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            response_raw LONGTEXT,
            INDEX idx_phone_normalized (phone_normalized),
            INDEX idx_order_id (order_id),
            INDEX idx_coupon_code (coupon_code),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        ) $charset_collate;";

        dbDelta( $sql_sms_logs );

        // Table 2: Sent Customers
        $table_sent_customers = $wpdb->prefix . 'karen_sent_customers';
        $sql_sent_customers = "CREATE TABLE IF NOT EXISTS $table_sent_customers (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            phone_normalized VARCHAR(20) NOT NULL UNIQUE,
            last_sent_order_id BIGINT UNSIGNED,
            last_sent_at DATETIME,
            coupon_code VARCHAR(100),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_phone_normalized (phone_normalized)
        ) $charset_collate;";

        dbDelta( $sql_sent_customers );
    }

    /**
     * Drop plugin tables
     *
     * @since 1.0.0
     */
    public static function drop_tables() {
        global $wpdb;

        $table_sms_logs = $wpdb->prefix . 'karen_sms_logs';
        $table_sent_customers = $wpdb->prefix . 'karen_sent_customers';

        $wpdb->query( "DROP TABLE IF EXISTS $table_sms_logs" );
        $wpdb->query( "DROP TABLE IF EXISTS $table_sent_customers" );
    }

    /**
     * Insert SMS log
     *
     * @since 1.0.0
     *
     * @param array $data Log data
     * @return int|false
     */
    public static function insert_sms_log( $data ) {
        global $wpdb;

        $table = $wpdb->prefix . 'karen_sms_logs';

        $defaults = array(
            'phone_raw'       => '',
            'phone_normalized' => '',
            'order_id'        => 0,
            'coupon_code'     => '',
            'gateway'         => '',
            'message'         => '',
            'status'          => 'queued',
            'response_raw'    => '',
        );

        $data = wp_parse_args( $data, $defaults );

        return $wpdb->insert(
            $table,
            array(
                'phone_raw'         => sanitize_text_field( $data['phone_raw'] ),
                'phone_normalized'  => sanitize_text_field( $data['phone_normalized'] ),
                'order_id'          => intval( $data['order_id'] ),
                'coupon_code'       => sanitize_text_field( $data['coupon_code'] ),
                'gateway'           => sanitize_text_field( $data['gateway'] ),
                'message'           => wp_kses_post( $data['message'] ),
                'status'            => sanitize_text_field( $data['status'] ),
                'response_raw'      => wp_kses_post( $data['response_raw'] ),
            ),
            array( '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
        );
    }

    /**
     * Update SMS log status
     *
     * @since 1.0.0
     *
     * @param int    $log_id Log ID
     * @param string $status Status
     * @param string $response Response
     * @return int|false
     */
    public static function update_sms_log_status( $log_id, $status, $response = '' ) {
        global $wpdb;

        $table = $wpdb->prefix . 'karen_sms_logs';

        return $wpdb->update(
            $table,
            array(
                'status'       => sanitize_text_field( $status ),
                'response_raw' => wp_kses_post( $response ),
            ),
            array( 'id' => intval( $log_id ) ),
            array( '%s', '%s' ),
            array( '%d' )
        );
    }

    /**
     * Insert or update sent customer
     *
     * @since 1.0.0
     *
     * @param array $data Customer data
     * @return int|false
     */
    public static function upsert_sent_customer( $data ) {
        global $wpdb;

        $table = $wpdb->prefix . 'karen_sent_customers';

        $defaults = array(
            'phone_normalized' => '',
            'last_sent_order_id' => 0,
            'coupon_code'      => '',
        );

        $data = wp_parse_args( $data, $defaults );

        $phone_normalized = sanitize_text_field( $data['phone_normalized'] );

        // Check if record exists
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id FROM $table WHERE phone_normalized = %s",
                $phone_normalized
            )
        );

        if ( $existing ) {
            return $wpdb->update(
                $table,
                array(
                    'last_sent_order_id' => intval( $data['last_sent_order_id'] ),
                    'last_sent_at'       => current_time( 'mysql' ),
                    'coupon_code'        => sanitize_text_field( $data['coupon_code'] ),
                ),
                array( 'phone_normalized' => $phone_normalized ),
                array( '%d', '%s', '%s' ),
                array( '%s' )
            );
        } else {
            return $wpdb->insert(
                $table,
                array(
                    'phone_normalized'   => $phone_normalized,
                    'last_sent_order_id' => intval( $data['last_sent_order_id'] ),
                    'coupon_code'        => sanitize_text_field( $data['coupon_code'] ),
                ),
                array( '%s', '%d', '%s' )
            );
        }
    }

    /**
     * Get sent customer
     *
     * @since 1.0.0
     *
     * @param string $phone_normalized Normalized phone
     * @return object|null
     */
    public static function get_sent_customer( $phone_normalized ) {
        global $wpdb;

        $table = $wpdb->prefix . 'karen_sent_customers';

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE phone_normalized = %s",
                sanitize_text_field( $phone_normalized )
            )
        );
    }

    /**
     * Get SMS logs
     *
     * @since 1.0.0
     *
     * @param array $args Query arguments
     * @return array
     */
    public static function get_sms_logs( $args = array() ) {
        global $wpdb;

        $table = $wpdb->prefix . 'karen_sms_logs';

        $defaults = array(
            'limit'  => 20,
            'offset' => 0,
            'status' => '',
            'phone'  => '',
            'order_id' => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $query = "SELECT * FROM $table WHERE 1=1";

        if ( ! empty( $args['status'] ) ) {
            $query .= $wpdb->prepare( " AND status = %s", $args['status'] );
        }

        if ( ! empty( $args['phone'] ) ) {
            $query .= $wpdb->prepare( " AND phone_normalized LIKE %s", '%' . $args['phone'] . '%' );
        }

        if ( ! empty( $args['order_id'] ) ) {
            $query .= $wpdb->prepare( " AND order_id = %d", $args['order_id'] );
        }

        $query .= " ORDER BY created_at DESC";
        $query .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['limit'], $args['offset'] );

        return $wpdb->get_results( $query );
    }

    /**
     * Count SMS logs
     *
     * @since 1.0.0
     *
     * @param array $args Query arguments
     * @return int
     */
    public static function count_sms_logs( $args = array() ) {
        global $wpdb;

        $table = $wpdb->prefix . 'karen_sms_logs';

        $defaults = array(
            'status'  => '',
            'phone'   => '',
            'order_id' => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $query = "SELECT COUNT(*) FROM $table WHERE 1=1";

        if ( ! empty( $args['status'] ) ) {
            $query .= $wpdb->prepare( " AND status = %s", $args['status'] );
        }

        if ( ! empty( $args['phone'] ) ) {
            $query .= $wpdb->prepare( " AND phone_normalized LIKE %s", '%' . $args['phone'] . '%' );
        }

        if ( ! empty( $args['order_id'] ) ) {
            $query .= $wpdb->prepare( " AND order_id = %d", $args['order_id'] );
        }

        return intval( $wpdb->get_var( $query ) );
    }

    /**
     * Get dashboard stats
     *
     * @since 1.0.0
     *
     * @return array
     */
    public static function get_dashboard_stats() {
        global $wpdb;

        $table = $wpdb->prefix . 'karen_sms_logs';

        $stats = array(
            'total_sent'      => 0,
            'today_sent'      => 0,
            'week_sent'       => 0,
            'month_sent'      => 0,
            'failed_count'    => 0,
        );

        // Total sent
        $stats['total_sent'] = intval( $wpdb->get_var(
            "SELECT COUNT(*) FROM $table WHERE status = 'sent'"
        ) );

        // Today
        $stats['today_sent'] = intval( $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE status = 'sent' AND DATE(created_at) = %s",
                current_time( 'Y-m-d' )
            )
        ) );

        // This week
        $stats['week_sent'] = intval( $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE status = 'sent' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            )
        ) );

        // This month
        $stats['month_sent'] = intval( $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE status = 'sent' AND YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())"
            )
        ) );

        // Failed
        $stats['failed_count'] = intval( $wpdb->get_var(
            "SELECT COUNT(*) FROM $table WHERE status = 'failed'"
        ) );

        return $stats;
    }
}
