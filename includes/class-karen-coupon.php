<?php
/**
 * Coupon Management Class
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Karen_Coupon {

    /**
     * Generate unique coupon code
     *
     * @since 1.0.0
     *
     * @param string $phone_normalized Normalized phone number
     * @return string
     */
    public static function generate_code( $phone_normalized ) {
        $settings = get_option( 'karen_coupon_settings', array() );

        $prefix = isset( $settings['prefix'] ) ? sanitize_text_field( $settings['prefix'] ) : 'KAREN';
        $max_length = 15;

        // Calculate available length for random part
        $random_length = $max_length - strlen( $prefix ) - 1;

        if ( $random_length < 3 ) {
            $random_length = 3;
        }

        // Generate random string
        $random = self::generate_random_string( $random_length );

        // Create coupon code
        $code = $prefix . '-' . $random;

        // Trim if too long
        $code = substr( $code, 0, $max_length );

        return strtoupper( $code );
    }

    /**
     * Generate random string
     *
     * @since 1.0.0
     *
     * @param int $length String length
     * @return string
     */
    private static function generate_random_string( $length = 8 ) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $string = '';

        for ( $i = 0; $i < $length; $i++ ) {
            $string .= $characters[ mt_rand( 0, strlen( $characters ) - 1 ) ];
        }

        return $string;
    }

    /**
     * Create coupon
     *
     * @since 1.0.0
     *
     * @param array $args Coupon arguments
     * @return int|WP_Error Coupon ID or error
     */
    public static function create_coupon( $args = array() ) {
        $defaults = array(
            'code'              => '',
            'description'       => '',
            'discount_type'     => 'percent', // percent or fixed
            'coupon_amount'     => 0,
            'phone_normalized'  => '',
            'order_id'          => 0,
            'expiration_date'   => '',
            'usage_limit'       => 1,
        );

        $args = wp_parse_args( $args, $defaults );

        // Validate
        if ( empty( $args['code'] ) || empty( $args['phone_normalized'] ) ) {
            return new WP_Error( 'invalid_args', 'Code and phone are required' );
        }

        try {
            $coupon = new WC_Coupon();
            $coupon->set_code( sanitize_text_field( $args['code'] ) );
            $coupon->set_description( sanitize_text_field( $args['description'] ) );
            $coupon->set_discount_type( sanitize_text_field( $args['discount_type'] ) );
            $coupon->set_amount( floatval( $args['coupon_amount'] ) );
            $coupon->set_usage_limit( intval( $args['usage_limit'] ) );
            $coupon->set_individual_use( true );

            if ( ! empty( $args['expiration_date'] ) ) {
                $coupon->set_date_expires( $args['expiration_date'] );
            }

            // Save metadata
            $coupon->add_meta_data( 'karen_plugin', true );
            $coupon->add_meta_data( 'karen_phone_normalized', sanitize_text_field( $args['phone_normalized'] ) );
            $coupon->add_meta_data( 'karen_order_id', intval( $args['order_id'] ) );
            $coupon->add_meta_data( 'karen_created_at', current_time( 'mysql' ) );

            $coupon_id = $coupon->save();

            return $coupon_id;
        } catch ( Exception $e ) {
            return new WP_Error( 'coupon_error', $e->getMessage() );
        }
    }

    /**
     * Get coupon by code
     *
     * @since 1.0.0
     *
     * @param string $code Coupon code
     * @return WC_Coupon|null
     */
    public static function get_coupon( $code ) {
        try {
            $coupon = new WC_Coupon( $code );

            if ( $coupon->get_id() === 0 ) {
                return null;
            }

            return $coupon;
        } catch ( Exception $e ) {
            return null;
        }
    }

    /**
     * Delete coupon
     *
     * @since 1.0.0
     *
     * @param int $coupon_id Coupon ID
     * @return bool
     */
    public static function delete_coupon( $coupon_id ) {
        if ( empty( $coupon_id ) ) {
            return false;
        }

        wp_delete_post( intval( $coupon_id ), true );
        return true;
    }

    /**
     * Get coupons created by plugin
     *
     * @since 1.0.0
     *
     * @param array $args Query arguments
     * @return array
     */
    public static function get_plugin_coupons( $args = array() ) {
        $defaults = array(
            'limit'  => 20,
            'offset' => 0,
            'status' => 'any', // any, publish, etc.
        );

        $args = wp_parse_args( $args, $defaults );

        $query_args = array(
            'post_type'      => 'shop_coupon',
            'posts_per_page' => $args['limit'],
            'offset'         => $args['offset'],
            'post_status'    => $args['status'],
            'meta_query'     => array(
                array(
                    'key'   => 'karen_plugin',
                    'value' => true,
                ),
            ),
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        $coupons = get_posts( $query_args );
        $result = array();

        foreach ( $coupons as $coupon_post ) {
            try {
                $coupon = new WC_Coupon( $coupon_post->ID );
                $result[] = $coupon;
            } catch ( Exception $e ) {
                continue;
            }
        }

        return $result;
    }

    /**
     * Count plugin coupons
     *
     * @since 1.0.0
     *
     * @return int
     */
    public static function count_plugin_coupons() {
        $query_args = array(
            'post_type'      => 'shop_coupon',
            'posts_per_page' => 1,
            'post_status'    => 'any',
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'   => 'karen_plugin',
                    'value' => true,
                ),
            ),
        );

        $posts = get_posts( $query_args );
        return count( $posts );
    }

    /**
     * Get coupon usage count
     *
     * @since 1.0.0
     *
     * @param string $coupon_code Coupon code
     * @return int
     */
    public static function get_coupon_usage_count( $coupon_code ) {
        try {
            $coupon = new WC_Coupon( $coupon_code );
            return $coupon->get_usage_count();
        } catch ( Exception $e ) {
            return 0;
        }
    }

    /**
     * Check if coupon exists
     *
     * @since 1.0.0
     *
     * @param string $code Coupon code
     * @return bool
     */
    public static function coupon_exists( $code ) {
        $coupon = self::get_coupon( $code );
        return $coupon !== null;
    }

    /**
     * Delete expired coupons
     *
     * @since 1.0.0
     *
     * @return int Number of deleted coupons
     */
    public static function delete_expired_coupons() {
        $query_args = array(
            'post_type'      => 'shop_coupon',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'   => 'karen_plugin',
                    'value' => true,
                ),
            ),
        );

        $coupon_ids = get_posts( $query_args );
        $deleted = 0;

        foreach ( $coupon_ids as $coupon_id ) {
            try {
                $coupon = new WC_Coupon( $coupon_id );
                $expiration = $coupon->get_date_expires();

                if ( $expiration && $expiration->getTimestamp() < current_time( 'timestamp' ) ) {
                    wp_delete_post( $coupon_id, true );
                    $deleted++;
                }
            } catch ( Exception $e ) {
                continue;
            }
        }

        return $deleted;
    }
}
