<?php
/**
 * Admin Pages Handler
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Karen_Admin {

    /**
     * Single instance of the class
     *
     * @var Karen_Admin
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @since 1.0.0
     * @return Karen_Admin
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'wp_ajax_karen_send_test_sms', array( $this, 'send_test_sms' ) );
        add_action( 'wp_ajax_karen_save_settings', array( $this, 'save_settings' ) );
        add_action( 'wp_ajax_karen_delete_expired_coupons', array( $this, 'delete_expired_coupons' ) );
    }

    /**
     * Dashboard page
     *
     * @since 1.0.0
     */
    public function page_dashboard() {
        require KAREN_PLUGIN_DIR . 'admin/pages/dashboard.php';
    }

    /**
     * Settings page
     *
     * @since 1.0.0
     */
    public function page_settings() {
        require KAREN_PLUGIN_DIR . 'admin/pages/settings.php';
    }

    /**
     * SMS Settings page
     *
     * @since 1.0.0
     */
    public function page_sms_settings() {
        require KAREN_PLUGIN_DIR . 'admin/pages/sms-settings.php';
    }

    /**
     * Reports page
     *
     * @since 1.0.0
     */
    public function page_reports() {
        require KAREN_PLUGIN_DIR . 'admin/pages/reports.php';
    }

    /**
     * Coupons page
     *
     * @since 1.0.0
     */
    public function page_coupons() {
        require KAREN_PLUGIN_DIR . 'admin/pages/coupons.php';
    }

    /**
     * SMS Test page
     *
     * @since 1.0.0
     */
    public function page_sms_test() {
        require KAREN_PLUGIN_DIR . 'admin/pages/sms-test.php';
    }

    /**
     * Send test SMS via AJAX
     *
     * @since 1.0.0
     */
    public function send_test_sms() {
        check_ajax_referer( 'karen_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized', KAREN_PLUGIN_TEXT_DOMAIN ) ) );
        }

        $phone = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
        $message = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';

        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-normalizer.php';
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-sms.php';

        $phone_normalized = Karen_Normalizer::normalize( $phone );

        if ( ! $phone_normalized ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid phone number', KAREN_PLUGIN_TEXT_DOMAIN ) ) );
        }

        $sms_settings = get_option( 'karen_sms_settings', array() );

        if ( empty( $sms_settings['gateway'] ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'SMS gateway not configured', KAREN_PLUGIN_TEXT_DOMAIN ) ) );
        }

        $response = Karen_SMS::send( $sms_settings['gateway'], $phone_normalized, $message );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => esc_html__( 'Test SMS sent successfully!', KAREN_PLUGIN_TEXT_DOMAIN ) ) );
    }

    /**
     * Save settings via AJAX
     *
     * @since 1.0.0
     */
    public function save_settings() {
        check_ajax_referer( 'karen_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized', KAREN_PLUGIN_TEXT_DOMAIN ) ) );
        }

        $type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
        $data = isset( $_POST['data'] ) ? map_deep( $_POST['data'], 'sanitize_text_field' ) : array();

        if ( $type === 'general' ) {
            update_option( 'karen_settings', $data );
        } elseif ( $type === 'sms' ) {
            update_option( 'karen_sms_settings', $data );
        } elseif ( $type === 'coupon' ) {
            update_option( 'karen_coupon_settings', $data );
        }

        wp_send_json_success( array( 'message' => esc_html__( 'Settings saved successfully!', KAREN_PLUGIN_TEXT_DOMAIN ) ) );
    }

    /**
     * Delete expired coupons
     *
     * @since 1.0.0
     */
    public function delete_expired_coupons() {
        check_ajax_referer( 'karen_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized', KAREN_PLUGIN_TEXT_DOMAIN ) ) );
        }

        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-coupon.php';

        $deleted = Karen_Coupon::delete_expired_coupons();

        wp_send_json_success( array( 'message' => sprintf( esc_html__( '%d expired coupons deleted', KAREN_PLUGIN_TEXT_DOMAIN ), $deleted ) ) );
    }
}
