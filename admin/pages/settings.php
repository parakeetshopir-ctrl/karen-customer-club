<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$settings = get_option( 'karen_settings', array() );
?>
<div class="wrap karen-admin-page">
    <h1><?php esc_html_e( 'تنظیمات عمومی', KAREN_PLUGIN_TEXT_DOMAIN ); ?></h1>

    <form id="karen-general-settings-form" method="post">
        <?php wp_nonce_field( 'karen_admin_nonce', 'nonce' ); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="check_interval"><?php esc_html_e( 'فاصله بررسی (دقیقه)', KAREN_PLUGIN_TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <select id="check_interval" name="check_interval">
                        <option value="15" <?php selected( isset( $settings['check_interval'] ) ? $settings['check_interval'] : 15, 15 ); ?>>15 دقیقه</option>
                        <option value="30" <?php selected( isset( $settings['check_interval'] ) ? $settings['check_interval'] : 15, 30 ); ?>>30 دقیقه</option>
                        <option value="60" <?php selected( isset( $settings['check_interval'] ) ? $settings['check_interval'] : 15, 60 ); ?>>1 ساعت</option>
                        <option value="120" <?php selected( isset( $settings['check_interval'] ) ? $settings['check_interval'] : 15, 120 ); ?>>2 ساعت</option>
                        <option value="180" <?php selected( isset( $settings['check_interval'] ) ? $settings['check_interval'] : 15, 180 ); ?>>3 ساعت</option>
                    </select>
                    <p class="description"><?php esc_html_e( 'فاصله بررسی خودکار سفارشات ناموفق', KAREN_PLUGIN_TEXT_DOMAIN ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php esc_html_e( 'وضعیت‌های بررسی‌شونده', KAREN_PLUGIN_TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="order_statuses[]" value="failed" <?php checked( in_array( 'failed', isset( $settings['order_statuses'] ) ? $settings['order_statuses'] : array( 'failed' ) ) ); ?> />
                        <?php esc_html_e( 'ناموفق', KAREN_PLUGIN_TEXT_DOMAIN ); ?>
                    </label><br />
                    <label>
                        <input type="checkbox" name="order_statuses[]" value="cancelled" <?php checked( in_array( 'cancelled', isset( $settings['order_statuses'] ) ? $settings['order_statuses'] : array( 'failed' ) ) ); ?> />
                        <?php esc_html_e( 'لغو شده', KAREN_PLUGIN_TEXT_DOMAIN ); ?>
                    </label><br />
                    <label>
                        <input type="checkbox" name="order_statuses[]" value="pending" <?php checked( in_array( 'pending', isset( $settings['order_statuses'] ) ? $settings['order_statuses'] : array( 'failed' ) ) ); ?> />
                        <?php esc_html_e( 'در انتظار پرداخت', KAREN_PLUGIN_TEXT_DOMAIN ); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="scan_period"><?php esc_html_e( 'دوره بررسی سفارش‌های گذشته (روز)', KAREN_PLUGIN_TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <input type="number" id="scan_period" name="scan_period" value="<?php echo isset( $settings['scan_period'] ) ? intval( $settings['scan_period'] ) : 14; ?>" min="1" />
                    <p class="description"><?php esc_html_e( 'تعداد روزهایی که سفارش‌های قدیمی بررسی شوند', KAREN_PLUGIN_TEXT_DOMAIN ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="success_grace_period"><?php esc_html_e( 'دوره عدم ارسال برای سفارش موفق (روز)', KAREN_PLUGIN_TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <input type="number" id="success_grace_period" name="success_grace_period" value="<?php echo isset( $settings['success_grace_period'] ) ? intval( $settings['success_grace_period'] ) : 14; ?>" min="1" />
                    <p class="description"><?php esc_html_e( 'اگر مشتری در این مدت سفارش موفق داشته باشد، پیامک نفرستاده شود', KAREN_PLUGIN_TEXT_DOMAIN ); ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button( esc_html__( 'ذخیره تنظیمات', KAREN_PLUGIN_TEXT_DOMAIN ), 'primary', 'submit', true ); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#karen-general-settings-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var data = {};
        $.each(formData, function() {
            if (data[this.name] !== undefined) {
                if (!$.isArray(data[this.name])) {
                    data[this.name] = [data[this.name]];
                }
                data[this.name].push(this.value);
            } else {
                data[this.name] = this.value;
            }
        });
        
        $.ajax({
            url: karenAdminL10n.ajaxUrl,
            type: 'POST',
            data: {
                action: 'karen_save_settings',
                nonce: karenAdminL10n.nonce,
                type: 'general',
                data: data
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
});
</script>
