<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$sms_settings = get_option( 'karen_sms_settings', array() );
$coupon_settings = get_option( 'karen_coupon_settings', array() );
require_once KAREN_PLUGIN_DIR . 'includes/class-karen-sms.php';
$gateways = Karen_SMS::get_available_gateways();
?>
<div class="wrap karen-admin-page">
    <h1><?php esc_html_e( 'تنظیمات پیامک و کوپن', KAREN_PLUGIN_TEXT_DOMAIN ); ?></h1>

    <h2><?php esc_html_e( 'تنظیمات درگاه پیامک', KAREN_PLUGIN_TEXT_DOMAIN ); ?></h2>
    <form id="karen-sms-settings-form" method="post">
        <?php wp_nonce_field( 'karen_admin_nonce', 'nonce' ); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="gateway"><?php esc_html_e( 'درگاه پیامک', KAREN_PLUGIN_TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <select id="gateway" name="gateway">
                        <option value=""><?php esc_html_e( '-- انتخاب کنید --', KAREN_PLUGIN_TEXT_DOMAIN ); ?></option>
                        <?php foreach ( $gateways as $key => $gateway ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $sms_settings['gateway'] ) ? $sms_settings['gateway'] : '', $key ); ?>>
                                <?php echo esc_html( $gateway['name'] ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="api_key"><?php esc_html_e( 'API Key / نام کاربری', KAREN_PLUGIN_TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <input type="password" id="api_key" name="api_key" value="<?php echo isset( $sms_settings['api_key'] ) ? esc_attr( $sms_settings['api_key'] ) : ''; ?>" style="width: 100%; max-width: 400px;" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="password"><?php esc_html_e( 'رمز عبور (اختیاری)', KAREN_PLUGIN_TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <input type="password" id="password" name="password" value="<?php echo isset( $sms_settings['password'] ) ? esc_attr( $sms_settings['password'] ) : ''; ?>" style="width: 100%; max-width: 400px;" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="sender"><?php esc_html_e( 'شماره فرستنده', KAREN_PLUGIN_TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <input type="text" id="sender" name="sender" value="<?php echo isset( $sms_settings['sender'] ) ? esc_attr( $sms_settings['sender'] ) : ''; ?>" style="width: 100%; max-width: 300px;" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="message_template"><?php esc_html_e( 'قالب پیامک', KAREN_PLUGIN_TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <textarea id="message_template" name="message_template" rows="4" style="width: 100%; max-width: 500px;"><?php echo isset( $sms_settings['message_template'] ) ? esc_textarea( $sms_settings['message_template'] ) : ''; ?></textarea>
                    <p class="description"><?php esc_html_e( 'متغیرهای دستیاب: {first_name}, {last_name}, {coupon}, {coupon_expire}, {mobile}, {order_id}, {site_name}, {discount_amount}', KAREN_PLUGIN_TEXT_DOMAIN ); ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button( esc_html__( 'ذخیره تنظیمات پیامک', KAREN_PLUGIN_TEXT_DOMAIN ), 'primary', 'submit_sms', true ); ?>
    </form>

    <h2><?php esc_html_e( 'تنظیمات کوپن', KAREN_PLUGIN_TEXT_DOMAIN ); ?></h2>
    <form id="karen-coupon-settings-form" method="post">
        <?php wp_nonce_field( 'karen_admin_nonce', 'nonce' ); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="coupon_prefix"><?php esc_html_e( 'پیشوند کوپن', KAREN_PLUGIN_TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <input type="text" id="coupon_prefix" name="prefix" value="<?php echo isset( $coupon_settings['prefix'] ) ? esc_attr( $coupon_settings['prefix'] ) : 'KAREN'; ?>" maxlength="10" />
                    <p class="description"><?php esc_html_e( 'مثال: KAREN یا DG10', KAREN_PLUGIN_TEXT_DOMAIN ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="discount_type"><?php esc_html_e( 'نوع تخفیف', KAREN_PLUGIN_TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <select id="discount_type" name="discount_type">
                        <option value="percent" <?php selected( isset( $coupon_settings['discount_type'] ) ? $coupon_settings['discount_type'] : 'percent', 'percent' ); ?>><?php esc_html_e( 'درصد', KAREN_PLUGIN_TEXT_DOMAIN ); ?></option>
                        <option value="fixed" <?php selected( isset( $coupon_settings['discount_type'] ) ? $coupon_settings['discount_type'] : 'percent', 'fixed' ); ?>><?php esc_html_e( 'مبلغ ثابت', KAREN_PLUGIN_TEXT_DOMAIN ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="discount_amount"><?php esc_html_e( 'میزان تخفیف', KAREN_PLUGIN_TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <input type="number" id="discount_amount" name="discount_amount" value="<?php echo isset( $coupon_settings['discount_amount'] ) ? floatval( $coupon_settings['discount_amount'] ) : 10; ?>" step="0.01" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="expiration_hours"><?php esc_html_e( 'مدت انقضای کوپن (ساعت)', KAREN_PLUGIN_TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <select id="expiration_hours" name="expiration_hours">
                        <option value="24" <?php selected( isset( $coupon_settings['expiration_hours'] ) ? $coupon_settings['expiration_hours'] : 24, 24 ); ?>>24 ساعت</option>
                        <option value="48" <?php selected( isset( $coupon_settings['expiration_hours'] ) ? $coupon_settings['expiration_hours'] : 24, 48 ); ?>>48 ساعت</option>
                        <option value="72" <?php selected( isset( $coupon_settings['expiration_hours'] ) ? $coupon_settings['expiration_hours'] : 24, 72 ); ?>>72 ساعت</option>
                    </select>
                </td>
            </tr>
        </table>

        <?php submit_button( esc_html__( 'ذخیره تنظیمات کوپن', KAREN_PLUGIN_TEXT_DOMAIN ), 'primary', 'submit_coupon', true ); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#karen-sms-settings-form').on('submit', function(e) {
        e.preventDefault();
        var data = $(this).serializeArray();
        var formData = {};
        $.each(data, function() {
            formData[this.name] = this.value;
        });
        
        $.ajax({
            url: karenAdminL10n.ajaxUrl,
            type: 'POST',
            data: {
                action: 'karen_save_settings',
                nonce: karenAdminL10n.nonce,
                type: 'sms',
                data: formData
            },
            success: function(response) {
                alert(response.data.message);
            }
        });
    });

    $('#karen-coupon-settings-form').on('submit', function(e) {
        e.preventDefault();
        var data = $(this).serializeArray();
        var formData = {};
        $.each(data, function() {
            formData[this.name] = this.value;
        });
        
        $.ajax({
            url: karenAdminL10n.ajaxUrl,
            type: 'POST',
            data: {
                action: 'karen_save_settings',
                nonce: karenAdminL10n.nonce,
                type: 'coupon',
                data: formData
            },
            success: function(response) {
                alert(response.data.message);
            }
        });
    });
});
</script>
