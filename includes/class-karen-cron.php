<?php
/**
 * Cron Job Manager Class
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Karen_Cron {

    /**
     * Initialize cron
     *
     * @since 1.0.0
     */
    public static function init() {
        add_action( 'karen_process_orders_cron', array( __CLASS__, 'process_orders' ) );
        add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_schedule' ) );
    }

    /**
     * Add custom cron schedule
     *
     * @since 1.0.0
     *
     * @param array $schedules Schedules
     * @return array
     */
    public static function add_cron_schedule( $schedules ) {
        $settings = get_option( 'karen_settings', array() );
        $interval = isset( $settings['check_interval'] ) ? intval( $settings['check_interval'] ) : 15;

        if ( ! isset( $schedules['karen_custom_interval'] ) ) {
            $schedules['karen_custom_interval'] = array(
                'interval' => $interval * 60,
                'display'  => sprintf( 'هر %d دقیقه', $interval ),
            );
        }

        return $schedules;
    }

    /**
     * Process failed orders
     *
     * @since 1.0.0
     */
    public static function process_orders() {
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-db.php';
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-normalizer.php';
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-coupon.php';
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-sms.php';

        $settings = get_option( 'karen_settings', array() );

        // Get check interval (in minutes)
        $check_interval = isset( $settings['check_interval'] ) ? intval( $settings['check_interval'] ) : 15;

        // Get order statuses to check
        $order_statuses = isset( $settings['order_statuses'] ) ? $settings['order_statuses'] : array( 'failed', 'cancelled', 'pending' );

        if ( empty( $order_statuses ) ) {
            return;
        }

        // Get historical scan period (in days)
        $scan_period = isset( $settings['scan_period'] ) ? intval( $settings['scan_period'] ) : 14;

        // Get success grace period (in days)
        $success_grace_period = isset( $settings['success_grace_period'] ) ? intval( $settings['success_grace_period'] ) : 14;

        // Convert to WC order status format
        $wc_statuses = array_map( function( $status ) {
            return 'wc-' . $status;
        }, $order_statuses );

        // Get orders
        $args = array(
            'status'       => $wc_statuses,
            'limit'        => 50, // Batch size to prevent heavy processing
            'offset'       => 0,
            'orderby'      => 'date',
            'order'        => 'ASC',
            'date_created' => '>' . ( current_time( 'timestamp' ) - ( $scan_period * 24 * 60 * 60 ) ),
        );

        $orders = wc_get_orders( $args );

        if ( empty( $orders ) ) {
            return;
        }

        // Group orders by phone number
        $customers = array();

        foreach ( $orders as $order ) {
            $phone = $order->get_billing_phone();

            if ( empty( $phone ) ) {
                continue;
            }

            $phone_normalized = Karen_Normalizer::normalize( $phone );

            if ( ! $phone_normalized ) {
                continue;
            }

            // Get or create customer group
            if ( ! isset( $customers[ $phone_normalized ] ) ) {
                $customers[ $phone_normalized ] = array(
                    'phone_raw'      => $phone,
                    'phone_normalized' => $phone_normalized,
                    'orders'         => array(),
                    'user'           => $order->get_user_id() ? get_user_by( 'id', $order->get_user_id() ) : null,
                );
            }

            $customers[ $phone_normalized ]['orders'][] = $order;
        }

        // Process each customer
        foreach ( $customers as $phone_normalized => $customer ) {
            self::process_customer( $customer, $success_grace_period );
        }
    }

    /**
     * Process single customer
     *
     * @since 1.0.0
     *
     * @param array $customer Customer data
     * @param int   $success_grace_period Grace period in days
     */
    private static function process_customer( $customer, $success_grace_period ) {
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-db.php';
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-coupon.php';
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-sms.php';

        $phone_normalized = $customer['phone_normalized'];
        $phone_raw = $customer['phone_raw'];
        $orders = $customer['orders'];
        $user = $customer['user'];

        // Check if already sent
        $sent_customer = Karen_DB::get_sent_customer( $phone_normalized );
        if ( $sent_customer ) {
            return;
        }

        // Get last failed order
        $last_failed_order = null;
        $last_failed_time = null;

        foreach ( $orders as $order ) {
            $order_time = $order->get_date_created()->getTimestamp();

            if ( $last_failed_time === null || $order_time > $last_failed_time ) {
                $last_failed_time = $order_time;
                $last_failed_order = $order;
            }
        }

        if ( ! $last_failed_order ) {
            return;
        }

        // Check if customer has successful order within grace period
        $args = array(
            'status'       => array( 'wc-processing', 'wc-completed' ),
            'limit'        => 1,
            'phone'        => $phone_raw,
            'date_created' => '>' . ( current_time( 'timestamp' ) - ( $success_grace_period * 24 * 60 * 60 ) ),
        );

        $successful_orders = wc_get_orders( $args );

        if ( ! empty( $successful_orders ) ) {
            // Customer has recent successful order, skip
            return;
        }

        // Generate coupon
        $coupon_code = Karen_Coupon::generate_code( $phone_normalized );

        // Get coupon settings
        $coupon_settings = get_option( 'karen_coupon_settings', array() );

        $discount_type = isset( $coupon_settings['discount_type'] ) ? $coupon_settings['discount_type'] : 'percent';
        $discount_amount = isset( $coupon_settings['discount_amount'] ) ? floatval( $coupon_settings['discount_amount'] ) : 10;

        // Calculate discount based on order amount
        if ( isset( $coupon_settings['use_range'] ) && $coupon_settings['use_range'] ) {
            $order_total = floatval( $last_failed_order->get_total() );
            $ranges = isset( $coupon_settings['ranges'] ) ? $coupon_settings['ranges'] : array();

            foreach ( $ranges as $range ) {
                $min = floatval( $range['min'] );
                $max = floatval( $range['max'] );

                if ( $order_total >= $min && ( $max === 0 || $order_total <= $max ) ) {
                    $discount_type = $range['type'];
                    $discount_amount = floatval( $range['amount'] );
                    break;
                }
            }
        }

        // Set coupon expiration
        $expiration_hours = isset( $coupon_settings['expiration_hours'] ) ? intval( $coupon_settings['expiration_hours'] ) : 24;
        $expiration_date = current_time( 'timestamp' ) + ( $expiration_hours * 60 * 60 );
        $expiration_date_obj = new DateTime();
        $expiration_date_obj->setTimestamp( $expiration_date );

        // Create coupon
        $coupon_args = array(
            'code'              => $coupon_code,
            'description'       => sprintf(
                'کوپن خودکار برای مشتری %s - سفارش #%d',
                Karen_Normalizer::format_for_display( $phone_normalized ),
                $last_failed_order->get_id()
            ),
            'discount_type'     => $discount_type,
            'coupon_amount'     => $discount_amount,
            'phone_normalized'  => $phone_normalized,
            'order_id'          => $last_failed_order->get_id(),
            'expiration_date'   => $expiration_date_obj,
            'usage_limit'       => 1,
        );

        $coupon_id = Karen_Coupon::create_coupon( $coupon_args );

        if ( is_wp_error( $coupon_id ) ) {
            return;
        }

        // Prepare SMS message
        $sms_settings = get_option( 'karen_sms_settings', array() );

        if ( empty( $sms_settings['gateway'] ) ) {
            return;
        }

        $message_template = isset( $sms_settings['message_template'] ) ? sanitize_textarea_field( $sms_settings['message_template'] ) : '';

        if ( empty( $message_template ) ) {
            $message_template = 'سلام {first_name}، کوپن تخفیف شما: {coupon}';
        }

        $first_name = '';
        $last_name = '';

        if ( $user ) {
            $first_name = $user->first_name;
            $last_name = $user->last_name;
        } else {
            // Try to get from order
            $first_name = $last_failed_order->get_billing_first_name();
            $last_name = $last_failed_order->get_billing_last_name();
        }

        $message = Karen_SMS::parse_template(
            $message_template,
            array(
                'first_name'      => $first_name,
                'last_name'       => $last_name,
                'coupon'          => $coupon_code,
                'coupon_expire'   => $expiration_date_obj->format( 'Y-m-d H:i' ),
                'mobile'          => Karen_Normalizer::format_for_display( $phone_normalized, 'national' ),
                'order_id'        => $last_failed_order->get_id(),
                'discount_amount' => $discount_amount . ( $discount_type === 'percent' ? '%' : ' تومان' ),
            )
        );

        // Create log entry
        $log_id = Karen_DB::insert_sms_log( array(
            'phone_raw'        => $phone_raw,
            'phone_normalized' => $phone_normalized,
            'order_id'         => $last_failed_order->get_id(),
            'coupon_code'      => $coupon_code,
            'gateway'          => $sms_settings['gateway'],
            'message'          => $message,
            'status'           => 'queued',
        ) );

        if ( ! $log_id ) {
            return;
        }

        // Send SMS
        $response = Karen_SMS::send( $sms_settings['gateway'], $phone_normalized, $message );

        if ( is_wp_error( $response ) ) {
            Karen_DB::update_sms_log_status( $log_id, 'failed', $response->get_error_message() );
            return;
        }

        // Update log status
        Karen_DB::update_sms_log_status( $log_id, 'sent', wp_json_encode( $response ) );

        // Record sent customer
        Karen_DB::upsert_sent_customer( array(
            'phone_normalized'   => $phone_normalized,
            'last_sent_order_id' => $last_failed_order->get_id(),
            'coupon_code'        => $coupon_code,
        ) );
    }
}

// Initialize cron
Karen_Cron::init();
