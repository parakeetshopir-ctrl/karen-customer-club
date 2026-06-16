<?php
/**
 * Melipayamak SMS Gateway
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Karen_SMS_Melipayamak extends Karen_SMS_Adapter {

    const API_URL = 'https://api.melipayamak.com/send/api/SendSMS';

    /**
     * Send SMS via Melipayamak
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
            return new WP_Error( 'missing_api_key', 'Melipayamak API key not configured' );
        }

        if ( empty( $settings['sender'] ) ) {
            return new WP_Error( 'missing_sender', 'Sender number not configured' );
        }

        $api_key = sanitize_text_field( $settings['api_key'] );
        $sender = sanitize_text_field( $settings['sender'] );

        $args = array(
            'method' => 'POST',
            'body'   => array(
                'key'      => $api_key,
                'senderId' => $sender,
                'mobile'   => $phone,
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
            'gateway' => 'melipayamak',
            'response' => $response['body'],
        );
    }
}
