<?php
/**
 * SMS Gateway Adapter Base Class
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class Karen_SMS_Adapter {

    /**
     * Get adapter instance
     *
     * @since 1.0.0
     *
     * @param string $gateway Gateway name
     * @return Karen_SMS_Adapter|WP_Error
     */
    public static function get_adapter( $gateway ) {
        $gateway = sanitize_text_field( $gateway );

        // Load the specific gateway adapter
        if ( $gateway === 'melipayamak' ) {
            require_once KAREN_PLUGIN_DIR . 'includes/sms/class-karen-sms-melipayamak.php';
            return new Karen_SMS_Melipayamak();
        } elseif ( $gateway === 'kavenegar' ) {
            require_once KAREN_PLUGIN_DIR . 'includes/sms/class-karen-sms-kavenegar.php';
            return new Karen_SMS_Kavenegar();
        } elseif ( $gateway === 'faraz' ) {
            require_once KAREN_PLUGIN_DIR . 'includes/sms/class-karen-sms-faraz.php';
            return new Karen_SMS_Faraz();
        } else {
            return new WP_Error( 'unknown_gateway', 'Unknown SMS gateway: ' . $gateway );
        }
    }

    /**
     * Send SMS
     *
     * @since 1.0.0
     *
     * @param string $phone Phone number
     * @param string $message Message text
     * @return array|WP_Error
     */
    abstract public function send( $phone, $message );

    /**
     * Make HTTP request
     *
     * @since 1.0.0
     *
     * @param string $url URL
     * @param array $args Request arguments
     * @return array|WP_Error
     */
    protected function make_request( $url, $args = array() ) {
        $response = wp_remote_post( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        if ( $status_code !== 200 ) {
            return new WP_Error( 'http_error', 'HTTP Error: ' . $status_code, $body );
        }

        return array(
            'status_code' => $status_code,
            'body'        => $body,
        );
    }
}
