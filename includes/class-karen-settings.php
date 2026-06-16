<?php
/**
 * Settings Manager Class
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Karen_Settings {

    /**
     * Get setting
     *
     * @since 1.0.0
     *
     * @param string $key Setting key
     * @param mixed  $default Default value
     * @return mixed
     */
    public static function get( $key, $default = null ) {
        $settings = get_option( 'karen_settings', array() );
        return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
    }

    /**
     * Update setting
     *
     * @since 1.0.0
     *
     * @param string $key Setting key
     * @param mixed  $value Setting value
     * @return bool
     */
    public static function update( $key, $value ) {
        $settings = get_option( 'karen_settings', array() );
        $settings[ $key ] = $value;
        return update_option( 'karen_settings', $settings );
    }

    /**
     * Get SMS setting
     *
     * @since 1.0.0
     *
     * @param string $key Setting key
     * @param mixed  $default Default value
     * @return mixed
     */
    public static function get_sms( $key, $default = null ) {
        $settings = get_option( 'karen_sms_settings', array() );
        return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
    }

    /**
     * Update SMS setting
     *
     * @since 1.0.0
     *
     * @param string $key Setting key
     * @param mixed  $value Setting value
     * @return bool
     */
    public static function update_sms( $key, $value ) {
        $settings = get_option( 'karen_sms_settings', array() );
        $settings[ $key ] = $value;
        return update_option( 'karen_sms_settings', $settings );
    }

    /**
     * Get coupon setting
     *
     * @since 1.0.0
     *
     * @param string $key Setting key
     * @param mixed  $default Default value
     * @return mixed
     */
    public static function get_coupon( $key, $default = null ) {
        $settings = get_option( 'karen_coupon_settings', array() );
        return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
    }

    /**
     * Update coupon setting
     *
     * @since 1.0.0
     *
     * @param string $key Setting key
     * @param mixed  $value Setting value
     * @return bool
     */
    public static function update_coupon( $key, $value ) {
        $settings = get_option( 'karen_coupon_settings', array() );
        $settings[ $key ] = $value;
        return update_option( 'karen_coupon_settings', $settings );
    }
}
