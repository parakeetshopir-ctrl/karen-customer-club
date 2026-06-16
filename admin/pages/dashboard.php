<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap karen-admin-page">
    <h1><?php esc_html_e( 'باشگاه مشتریان کارن - داشبورد', KAREN_PLUGIN_TEXT_DOMAIN ); ?></h1>

    <div class="karen-dashboard-stats">
        <?php
        require_once KAREN_PLUGIN_DIR . 'includes/class-karen-db.php';
        $stats = Karen_DB::get_dashboard_stats();
        ?>
        <div class="stat-card">
            <h3><?php esc_html_e( 'کل پیامک‌های ارسال‌شده', KAREN_PLUGIN_TEXT_DOMAIN ); ?></h3>
            <p class="stat-value"><?php echo intval( $stats['total_sent'] ); ?></p>
        </div>

        <div class="stat-card">
            <h3><?php esc_html_e( 'امروز', KAREN_PLUGIN_TEXT_DOMAIN ); ?></h3>
            <p class="stat-value"><?php echo intval( $stats['today_sent'] ); ?></p>
        </div>

        <div class="stat-card">
            <h3><?php esc_html_e( 'این هفته', KAREN_PLUGIN_TEXT_DOMAIN ); ?></h3>
            <p class="stat-value"><?php echo intval( $stats['week_sent'] ); ?></p>
        </div>

        <div class="stat-card">
            <h3><?php esc_html_e( 'این ماه', KAREN_PLUGIN_TEXT_DOMAIN ); ?></h3>
            <p class="stat-value"><?php echo intval( $stats['month_sent'] ); ?></p>
        </div>

        <div class="stat-card error">
            <h3><?php esc_html_e( 'پیامک‌های ناموفق', KAREN_PLUGIN_TEXT_DOMAIN ); ?></h3>
            <p class="stat-value"><?php echo intval( $stats['failed_count'] ); ?></p>
        </div>
    </div>

    <div class="karen-dashboard-actions">
        <h2><?php esc_html_e( 'اقدامات سریع', KAREN_PLUGIN_TEXT_DOMAIN ); ?></h2>
        <p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=karen-settings' ) ); ?>" class="button button-primary">
                <?php esc_html_e( 'تنظیمات عمومی', KAREN_PLUGIN_TEXT_DOMAIN ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=karen-sms-settings' ) ); ?>" class="button button-primary">
                <?php esc_html_e( 'تنظیمات پیامک', KAREN_PLUGIN_TEXT_DOMAIN ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=karen-reports' ) ); ?>" class="button">
                <?php esc_html_e( 'مشاهده گزارش‌ها', KAREN_PLUGIN_TEXT_DOMAIN ); ?>
            </a>
        </p>
    </div>
</div>
