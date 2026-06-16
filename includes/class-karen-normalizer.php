<?php
/**
 * Phone Number Normalizer Class
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Karen_Normalizer {

    /**
     * Normalize phone number
     *
     * Converts various phone formats to standard format: 989XXXXXXXXX
     * - 09123456789 -> 989123456789
     * - +989123456789 -> 989123456789
     * - 00989123456789 -> 989123456789
     * - ۰۹۱۲۳۴۵۶۷۸۹ -> 989123456789
     *
     * @since 1.0.0
     *
     * @param string $phone Raw phone number
     * @return string|false Normalized phone or false if invalid
     */
    public static function normalize( $phone ) {
        if ( empty( $phone ) ) {
            return false;
        }

        $phone = trim( $phone );

        // Convert Persian digits to Latin
        $persian_to_latin = array(
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
        );

        $phone = str_replace( array_keys( $persian_to_latin ), array_values( $persian_to_latin ), $phone );

        // Remove all non-numeric characters except + at the beginning
        $phone = preg_replace( '/[^\d+]/', '', $phone );

        // Remove + if present
        $phone = str_replace( '+', '', $phone );

        // Handle different prefixes
        if ( substr( $phone, 0, 4 ) === '0098' ) {
            // 0098XXXXXXXXX -> 98XXXXXXXXX
            $phone = substr( $phone, 2 );
        } elseif ( substr( $phone, 0, 2 ) === '00' ) {
            // 00XXXXXXXXX -> 98XXXXXXXXX (Iranian phone)
            $phone = '98' . substr( $phone, 2 );
        } elseif ( substr( $phone, 0, 2 ) === '98' ) {
            // Already in correct format
            $phone = $phone;
        } elseif ( substr( $phone, 0, 1 ) === '0' && strlen( $phone ) === 11 ) {
            // 09XXXXXXXXX -> 989XXXXXXXXX
            $phone = '98' . substr( $phone, 1 );
        } else {
            // Invalid format
            return false;
        }

        // Validate length
        if ( strlen( $phone ) !== 12 || ! preg_match( '/^\d+$/', $phone ) ) {
            return false;
        }

        // Validate starts with 98
        if ( substr( $phone, 0, 2 ) !== '98' ) {
            return false;
        }

        return $phone;
    }

    /**
     * Validate normalized phone
     *
     * @since 1.0.0
     *
     * @param string $phone Normalized phone
     * @return bool
     */
    public static function validate( $phone ) {
        if ( empty( $phone ) ) {
            return false;
        }

        // Must be exactly 12 digits
        if ( strlen( $phone ) !== 12 ) {
            return false;
        }

        // Must be all digits
        if ( ! preg_match( '/^\d+$/', $phone ) ) {
            return false;
        }

        // Must start with 98 (Iran country code)
        if ( substr( $phone, 0, 2 ) !== '98' ) {
            return false;
        }

        return true;
    }

    /**
     * Format phone for display
     *
     * Convert 989123456789 -> +98 912 345 6789 or 0912 345 6789
     *
     * @since 1.0.0
     *
     * @param string $phone Normalized phone
     * @param string $format Display format (international|national)
     * @return string
     */
    public static function format_for_display( $phone, $format = 'international' ) {
        if ( ! self::validate( $phone ) ) {
            return $phone;
        }

        if ( $format === 'national' ) {
            // 989123456789 -> 0912 345 6789
            $phone = '0' . substr( $phone, 2 );
            return implode( ' ', array(
                substr( $phone, 0, 4 ),
                substr( $phone, 4, 3 ),
                substr( $phone, 7 ),
            ) );
        } else {
            // 989123456789 -> +98 912 345 6789
            return '+' . implode( ' ', array(
                substr( $phone, 0, 2 ),
                substr( $phone, 2, 3 ),
                substr( $phone, 5, 3 ),
                substr( $phone, 8 ),
            ) );
        }
    }
}
