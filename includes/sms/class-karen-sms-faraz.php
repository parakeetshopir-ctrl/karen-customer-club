<?php
/**
 * Faraz SMS Gateway
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Karen_SMS_Faraz extends Karen_SMS_Adapter {

    const API_URL = 'https://api.fraztext.com/ApiClient/SendSMS';

    /**
     * Send SMS via Faraz
     *
     * @since 1.0.0
     *
     * @param string $phone Phone number
     * @param string $message Message text
     * @return array|WP_Error
     */
    public function send( $phone, $message ) {
        $settings = get_option( 'karen_sms_settings', array() );

        if ( empty( $settings['username'] ) || empty( $settings['password'] ) ) {
            return new WP_Error( 'missing_credentials', 'Faraz username and password not configured' );
        }

        if ( empty( $settings['sender'] ) ) {
            return new WP_Error( 'missing_sender', 'Sender number not configured' );
        }

        $username = sanitize_text_field( $settings['username'] );
        $password = sanitize_text_field( $settings['password'] );
        $sender = sanitize_text_field( $settings['sender'] );

        $args = array(
            'method' => 'POST',
            'body'   => array(
                'username' => $username,
                'password' => $password,
                'from'     => $sender,
                'to'       => $phone,
                'message'  => $message,
            ),
            'timeout' => 30,
        );

        $response = $this->make_request( self::API_URL, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return array(
            'success' => true,
            'gateway' => 'faraz',
            'response' => $response['body'],
        );
    }
}
