<?php
/**
 * SMS Gateway Management Class
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Karen_SMS {

    /**
     * Get available gateways
     *
     * @since 1.0.0
     *
     * @return array
     */
    public static function get_available_gateways() {
        return array(
            'melipayamak' => array(
                'name' => 'ملی پیامک',
                'fields' => array(
                    'api_key' => array(
                        'label' => 'API Key',
                        'type' => 'password',
                    ),
                ),
            ),
            'kavenegar' => array(
                'name' => 'کاوه نگار',
                'fields' => array(
                    'api_key' => array(
                        'label' => 'API Key',
                        'type' => 'password',
                    ),
                ),
            ),
            'faraz' => array(
                'name' => 'فراز',
                'fields' => array(
                    'username' => array(
                        'label' => 'نام کاربری',
                        'type' => 'text',
                    ),
                    'password' => array(
                        'label' => 'رمز عبور',
                        'type' => 'password',
                    ),
                ),
            ),
        );
    }

    /**
     * Send SMS
     *
     * @since 1.0.0
     *
     * @param string $gateway Gateway name
     * @param string $phone Phone number (normalized)
     * @param string $message Message text
     * @return array|WP_Error Response
     */
    public static function send( $gateway, $phone, $message ) {
        // Require SMS gateway adapter
        require_once KAREN_PLUGIN_DIR . 'includes/sms/class-karen-sms-adapter.php';

        $adapter = Karen_SMS_Adapter::get_adapter( $gateway );

        if ( is_wp_error( $adapter ) ) {
            return $adapter;
        }

        return $adapter->send( $phone, $message );
    }

    /**
     * Parse message template
     *
     * @since 1.0.0
     *
     * @param string $template Message template
     * @param array $data Replacement data
     * @return string
     */
    public static function parse_template( $template, $data = array() ) {
        $defaults = array(
            'first_name'       => '',
            'last_name'        => '',
            'coupon'           => '',
            'coupon_expire'    => '',
            'mobile'           => '',
            'order_id'         => '',
            'site_name'        => get_bloginfo( 'name' ),
            'discount_amount'  => '',
        );

        $data = wp_parse_args( $data, $defaults );

        $template = str_replace( '{first_name}', $data['first_name'], $template );
        $template = str_replace( '{last_name}', $data['last_name'], $template );
        $template = str_replace( '{coupon}', $data['coupon'], $template );
        $template = str_replace( '{coupon_expire}', $data['coupon_expire'], $template );
        $template = str_replace( '{mobile}', $data['mobile'], $template );
        $template = str_replace( '{order_id}', $data['order_id'], $template );
        $template = str_replace( '{site_name}', $data['site_name'], $template );
        $template = str_replace( '{discount_amount}', $data['discount_amount'], $template );

        return $template;
    }

    /**
     * Get character count and SMS count
     *
     * @since 1.0.0
     *
     * @param string $message Message text
     * @return array
     */
    public static function get_message_stats( $message ) {
        $length = mb_strlen( $message );

        // SMS length calculation
        $sms_length = 160;
        $sms_count = 1;

        if ( $length > $sms_length ) {
            $sms_length = 153; // For longer SMS
            $sms_count = ceil( $length / $sms_length );
        }

        return array(
            'characters' => $length,
            'sms_count'  => $sms_count,
        );
    }

    /**
     * Validate gateway credentials
     *
     * @since 1.0.0
     *
     * @param string $gateway Gateway name
     * @return bool|WP_Error
     */
    public static function validate_gateway( $gateway ) {
        $settings = get_option( 'karen_sms_settings', array() );

        if ( empty( $settings ) || ! isset( $settings['gateway'] ) ) {
            return new WP_Error( 'gateway_not_configured', 'Gateway not configured' );
        }

        if ( $settings['gateway'] !== $gateway ) {
            return new WP_Error( 'gateway_mismatch', 'Gateway mismatch' );
        }

        require_once KAREN_PLUGIN_DIR . 'includes/sms/class-karen-sms-adapter.php';

        $adapter = Karen_SMS_Adapter::get_adapter( $gateway );

        if ( is_wp_error( $adapter ) ) {
            return $adapter;
        }

        return true;
    }
}
