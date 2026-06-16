<?php
/**
 * Kavenegar SMS Gateway
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Karen_SMS_Kavenegar extends Karen_SMS_Adapter {

    const API_URL = 'https://api.kavenegar.com/v1/%s/sms/send.json';

    /**
     * Send SMS via Kavenegar
     *
     * @since 1.0.0
     *
     * @param string $phone Phone number
     * @param string $message Message text
     * @return array|WP_Error
     */
    public function send( $phone, $message ) {
        $settings = get_option( 'karen_sms_settings', array() );

        if ( empty( $settings['api_key'] ) ) {
            return new WP_Error( 'missing_api_key', 'Kavenegar API key not configured' );
        }

        if ( empty( $settings['sender'] ) ) {
            return new WP_Error( 'missing_sender', 'Sender number not configured' );
        }

        $api_key = sanitize_text_field( $settings['api_key'] );
        $sender = sanitize_text_field( $settings['sender'] );

        $url = sprintf( self::API_URL, $api_key );

        $args = array(
            'method' => 'POST',
            'body'   => array(
                'receptor' => $phone,
                'sender'   => $sender,
                'message'  => $message,
            ),
            'timeout' => 30,
        );

        $response = $this->make_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return array(
            'success' => true,
            'gateway' => 'kavenegar',
            'response' => $response['body'],
        );
    }
}
