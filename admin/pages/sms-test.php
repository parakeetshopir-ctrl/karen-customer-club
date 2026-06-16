<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once KAREN_PLUGIN_DIR . 'includes/class-karen-sms.php';
$sms_settings = get_option( 'karen_sms_settings', array() );
$message_template = isset( $sms_settings['message_template'] ) ? $sms_settings['message_template'] : 'سلام {first_name}، کوپن تخفیف شما: {coupon}';
?>
<div class="wrap karen-admin-page">
    <h1><?php esc_html_e( 'تست ارسال پیامک', KAREN_PLUGIN_TEXT_DOMAIN ); ?></h1>

    <form id="karen-sms-test-form" method="post">
        <?php wp_nonce_field( 'karen_admin_nonce', 'nonce' ); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="test_phone"><?php esc_html_e( 'شماره موبایل', KAREN_PLUGIN_TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <input type="text" id="test_phone" name="phone" placeholder="09123456789" style="width: 100%; max-width: 300px;" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="test_message"><?php esc_html_e( 'متن پیامک', KAREN_PLUGIN_TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <textarea id="test_message" name="message" rows="4" style="width: 100%; max-width: 500px;"><?php echo esc_textarea( $message_template ); ?></textarea>
                    <p class="description"><?php esc_html_e( 'متغیرهای دستیاب: {first_name}, {last_name}, {coupon}, {coupon_expire}, {mobile}, {order_id}, {site_name}, {discount_amount}', KAREN_PLUGIN_TEXT_DOMAIN ); ?></p>
                    <div id="message-stats">
                        <span id="char-count">0</span> حرف | <span id="sms-count">1</span> پیامک
                    </div>
                </td>
            </tr>
        </table>

        <p>
            <button type="button" id="btn-send-test-sms" class="button button-primary"><?php esc_html_e( 'ارسال تست', KAREN_PLUGIN_TEXT_DOMAIN ); ?></button>
        </p>

        <div id="test-result" style="display: none; margin-top: 20px; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#test_message').on('keyup', function() {
        var message = $(this).val();
        var stats = calculateMessageStats(message);
        $('#char-count').text(stats.characters);
        $('#sms-count').text(stats.sms_count);
    });

    $('#btn-send-test-sms').on('click', function(e) {
        e.preventDefault();
        var phone = $('#test_phone').val();
        var message = $('#test_message').val();

        if (!phone || !message) {
            alert('لطفا تمام فیلد‌ها را پر کنید');
            return;
        }

        $.ajax({
            url: karenAdminL10n.ajaxUrl,
            type: 'POST',
            data: {
                action: 'karen_send_test_sms',
                nonce: karenAdminL10n.nonce,
                phone: phone,
                message: message
            },
            success: function(response) {
                if (response.success) {
                    $('#test-result').html('<p style="color: green;">' + response.data.message + '</p>').show();
                } else {
                    $('#test-result').html('<p style="color: red;">' + response.data.message + '</p>').show();
                }
            },
            error: function() {
                $('#test-result').html('<p style="color: red;">خطا در ارسال درخواست</p>').show();
            }
        });
    });

    function calculateMessageStats(message) {
        var length = message.length;
        var sms_length = 160;
        var sms_count = 1;
        if (length > sms_length) {
            sms_length = 153;
            sms_count = Math.ceil(length / sms_length);
        }
        return {
            characters: length,
            sms_count: sms_count
        };
    }
});
</script>
