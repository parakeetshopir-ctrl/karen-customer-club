<?php
/**
 * Main Plugin Class
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Karen_Plugin {

    /**
     * Single instance of the class
     *
     * @var Karen_Plugin
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @since 1.0.0
     * @return Karen_Plugin
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init_hooks();
        $this->includes();
    }

    /**
     * Init hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Check if WooCommerce is active
        add_action( 'admin_init', array( $this, 'check_woocommerce' ) );

        // Load text domain
        add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

        // Register plugin tables
        add_action( 'init', array( $this, 'register_tables' ) );

        // Admin scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

        // Admin menu
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    /**
     * Include required files
     *
     * @since 1.0.0
     */
    private function includes() {
        // Core classes
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-db.php';
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-normalizer.php';
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-coupon.php';
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-sms.php';
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-cron.php';
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-settings.php';

        // Admin classes
        if ( is_admin() ) {
            require_once KAREN_PLUGIN_DIR . 'admin/class-karen-admin.php';
        }
    }

    /**
     * Check if WooCommerce is active
     *
     * @since 1.0.0
     */
    public function check_woocommerce() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
        }
    }

    /**
     * WooCommerce missing notice
     *
     * @since 1.0.0
     */
    public function woocommerce_missing_notice() {
        echo '<div class="notice notice-error"><p>';
        printf(
            esc_html__( '%1$s requires %2$s to be installed and activated.', KAREN_PLUGIN_TEXT_DOMAIN ),
            '<strong>باشگاه مشتریان کارن</strong>',
            '<strong>WooCommerce</strong>'
        );
        echo '</p></div>';
    }

    /**
     * Load plugin text domain
     *
     * @since 1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            KAREN_PLUGIN_TEXT_DOMAIN,
            false,
            dirname( KAREN_PLUGIN_BASENAME ) . '/languages'
        );
    }

    /**
     * Register plugin tables
     *
     * @since 1.0.0
     */
    public function register_tables() {
        global $wpdb;
        $wpdb->karen_sms_logs = $wpdb->prefix . 'karen_sms_logs';
        $wpdb->karen_sent_customers = $wpdb->prefix . 'karen_sent_customers';
    }

    /**
     * Admin enqueue scripts
     *
     * @since 1.0.0
     */
    public function admin_enqueue_scripts( $hook ) {
        // Only load on plugin pages
        if ( strpos( $hook, 'karen' ) === false ) {
            return;
        }

        wp_enqueue_style( 'karen-admin-css', KAREN_PLUGIN_URL . 'assets/css/admin.css', array(), KAREN_PLUGIN_VERSION );
        wp_enqueue_script( 'karen-admin-js', KAREN_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), KAREN_PLUGIN_VERSION, true );

        // Localize script
        wp_localize_script(
            'karen-admin-js',
            'karenAdminL10n',
            array(
                'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce( 'karen_admin_nonce' ),
                'loading'      => esc_html__( 'در حال بارگذاری...', KAREN_PLUGIN_TEXT_DOMAIN ),
                'error'        => esc_html__( 'خطا', KAREN_PLUGIN_TEXT_DOMAIN ),
                'success'      => esc_html__( 'موفق', KAREN_PLUGIN_TEXT_DOMAIN ),
            )
        );
    }

    /**
     * Admin menu
     *
     * @since 1.0.0
     */
    public function admin_menu() {
        add_menu_page(
            esc_html__( 'باشگاه مشتریان کارن', KAREN_PLUGIN_TEXT_DOMAIN ),
            esc_html__( 'باشگاه مشتریان کارن', KAREN_PLUGIN_TEXT_DOMAIN ),
            'manage_options',
            'karen-admin',
            array( Karen_Admin::instance(), 'page_dashboard' ),
            'dashicons-groups',
            56
        );

        add_submenu_page(
            'karen-admin',
            esc_html__( 'داشبورد', KAREN_PLUGIN_TEXT_DOMAIN ),
            esc_html__( 'داشبورد', KAREN_PLUGIN_TEXT_DOMAIN ),
            'manage_options',
            'karen-admin',
            array( Karen_Admin::instance(), 'page_dashboard' )
        );

        add_submenu_page(
            'karen-admin',
            esc_html__( 'تنظیمات', KAREN_PLUGIN_TEXT_DOMAIN ),
            esc_html__( 'تنظیمات', KAREN_PLUGIN_TEXT_DOMAIN ),
            'manage_options',
            'karen-settings',
            array( Karen_Admin::instance(), 'page_settings' )
        );

        add_submenu_page(
            'karen-admin',
            esc_html__( 'تنظیمات پیامک', KAREN_PLUGIN_TEXT_DOMAIN ),
            esc_html__( 'تنظیمات پیامک', KAREN_PLUGIN_TEXT_DOMAIN ),
            'manage_options',
            'karen-sms-settings',
            array( Karen_Admin::instance(), 'page_sms_settings' )
        );

        add_submenu_page(
            'karen-admin',
            esc_html__( 'گزارش‌ها', KAREN_PLUGIN_TEXT_DOMAIN ),
            esc_html__( 'گزارش‌ها', KAREN_PLUGIN_TEXT_DOMAIN ),
            'manage_options',
            'karen-reports',
            array( Karen_Admin::instance(), 'page_reports' )
        );

        add_submenu_page(
            'karen-admin',
            esc_html__( 'کوپن‌های صادرشده', KAREN_PLUGIN_TEXT_DOMAIN ),
            esc_html__( 'کوپن‌های صادرشده', KAREN_PLUGIN_TEXT_DOMAIN ),
            'manage_options',
            'karen-coupons',
            array( Karen_Admin::instance(), 'page_coupons' )
        );

        add_submenu_page(
            'karen-admin',
            esc_html__( 'تست ارسال پیامک', KAREN_PLUGIN_TEXT_DOMAIN ),
            esc_html__( 'تست ارسال پیامک', KAREN_PLUGIN_TEXT_DOMAIN ),
            'manage_options',
            'karen-sms-test',
            array( Karen_Admin::instance(), 'page_sms_test' )
        );
    }

    /**
     * Plugin activation
     *
     * @since 1.0.0
     */
    public static function activate() {
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-db.php';
        Karen_DB::create_tables();
        
        // Schedule cron job
        if ( ! wp_next_scheduled( 'karen_process_orders_cron' ) ) {
            wp_schedule_event( time(), 'karen_15min', 'karen_process_orders_cron' );
        }
    }

    /**
     * Plugin deactivation
     *
     * @since 1.0.0
     */
    public static function deactivate() {
        // Unschedule cron job
        wp_clear_scheduled_hook( 'karen_process_orders_cron' );
    }

    /**
     * Plugin uninstall
     *
     * @since 1.0.0
     */
    public static function uninstall() {
        // Check if user wants to delete data
        $delete_data = get_option( 'karen_delete_data_on_uninstall', false );

        if ( $delete_data ) {
            require_once KAREN_PLUGIN_DIR . 'includes/class-karen-db.php';
            Karen_DB::drop_tables();
        }

        // Delete all plugin options
        delete_option( 'karen_delete_data_on_uninstall' );
        delete_option( 'karen_settings' );
        delete_option( 'karen_sms_settings' );
        delete_option( 'karen_coupon_settings' );
        delete_option( 'karen_gateway_discounts' );
    }
}
