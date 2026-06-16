<?php
/**
 * Plugin Name: باشگاه مشتریان کارن
 * Plugin URI: https://github.com/parakeetshopir-ctrl/karen-customer-club
 * Description: افزونه باشگاه مشتریان برای ووکامرس - فرستادن کوپن تخفیف خودکار به مشتریانی که سفارشات ناموفق دارند
 * Version: 1.0.0
 * Author: بهنام اعتمادی فر
 * Author URI: https://github.com/parakeetshopir-ctrl
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: karen-customer-club
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC tested up to: 8.5.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'KAREN_PLUGIN_VERSION', '1.0.0' );
define( 'KAREN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KAREN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'KAREN_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'KAREN_PLUGIN_TEXT_DOMAIN', 'karen-customer-club' );

// Require main plugin class
require_once KAREN_PLUGIN_DIR . 'includes/class-karen-plugin.php';

/**
 * Returns the main instance of Karen_Plugin to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return Karen_Plugin
 */
function karen_customer_club() {
    return Karen_Plugin::instance();
}

// Start the plugin
karen_customer_club();

// Activation hook
register_activation_hook( __FILE__, array( 'Karen_Plugin', 'activate' ) );

// Deactivation hook
register_deactivation_hook( __FILE__, array( 'Karen_Plugin', 'deactivate' ) );

// Uninstall hook
register_uninstall_hook( __FILE__, array( 'Karen_Plugin', 'uninstall' ) );
