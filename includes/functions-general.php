<?php
/**
 * General Utility Functions
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get plugin version
 *
 * @return string
 */
function karen_get_version() {
    return defined( 'KAREN_PLUGIN_VERSION' ) ? KAREN_PLUGIN_VERSION : '0.0.0';
}

/**
 * Get plugin URL
 *
 * @param string $path Path to append
 * @return string
 */
function karen_get_plugin_url( $path = '' ) {
    $url = defined( 'KAREN_PLUGIN_URL' ) ? KAREN_PLUGIN_URL : '';
    return $url . ltrim( $path, '/' );
}

/**
 * Get plugin directory
 *
 * @param string $path Path to append
 * @return string
 */
function karen_get_plugin_dir( $path = '' ) {
    $dir = defined( 'KAREN_PLUGIN_DIR' ) ? KAREN_PLUGIN_DIR : '';
    return $dir . ltrim( $path, '/' );
}

/**
 * Check if WooCommerce is active
 *
 * @return bool
 */
function karen_is_woocommerce_active() {
    return class_exists( 'WooCommerce' );
}

/**
 * Log debug message
 *
 * @param string $message Message
 * @param array  $data Data
 * @return void
 */
function karen_log( $message, $data = array() ) {
    if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
        return;
    }

    $log_message = '[Karen Customer Club] ' . $message;

    if ( ! empty( $data ) ) {
        $log_message .= ' ' . wp_json_encode( $data );
    }

    error_log( $log_message );
}

/**
 * Get admin notice
 *
 * @param string $message Message
 * @param string $type Type (success, error, warning, info)
 * @return string
 */
function karen_get_admin_notice( $message, $type = 'success' ) {
    $classes = "notice notice-$type is-dismissible";
    return sprintf(
        '<div class="%s"><p>%s</p></div>',
        esc_attr( $classes ),
        wp_kses_post( $message )
    );
}

/**
 * Sanitize phone number
 *
 * @param string $phone Phone number
 * @return string
 */
function karen_sanitize_phone( $phone ) {
    require_once KAREN_PLUGIN_DIR . 'includes/class-karen-normalizer.php';
    return Karen_Normalizer::normalize( $phone );
}

/**
 * Format phone for display
 *
 * @param string $phone Phone number (normalized)
 * @param string $format Format (international|national)
 * @return string
 */
function karen_format_phone( $phone, $format = 'international' ) {
    require_once KAREN_PLUGIN_DIR . 'includes/class-karen-normalizer.php';
    return Karen_Normalizer::format_for_display( $phone, $format );
}
