<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once KAREN_PLUGIN_DIR . 'includes/class-karen-coupon.php';

$paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
$per_page = 20;
$offset = ( $paged - 1 ) * $per_page;

$coupons = Karen_Coupon::get_plugin_coupons( array( 'limit' => $per_page, 'offset' => $offset ) );
$total = Karen_Coupon::count_plugin_coupons();
$total_pages = ceil( $total / $per_page );
?>
<div class="wrap karen-admin-page">
    <h1><?php esc_html_e( 'کوپن‌های صادرشده', KAREN_PLUGIN_TEXT_DOMAIN ); ?></h1>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'کد کوپن', KAREN_PLUGIN_TEXT_DOMAIN ); ?></th>
                <th><?php esc_html_e( 'شماره موبایل', KAREN_PLUGIN_TEXT_DOMAIN ); ?></th>
                <th><?php esc_html_e( 'نوع تخفیف', KAREN_PLUGIN_TEXT_DOMAIN ); ?></th>
                <th><?php esc_html_e( 'میزان', KAREN_PLUGIN_TEXT_DOMAIN ); ?></th>
                <th><?php esc_html_e( 'تاریخ انقضا', KAREN_PLUGIN_TEXT_DOMAIN ); ?></th>
                <th><?php esc_html_e( 'استفاده شده', KAREN_PLUGIN_TEXT_DOMAIN ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $coupons ) ) : ?>
                <?php foreach ( $coupons as $coupon ) : ?>
                    <tr>
                        <td><?php echo esc_html( $coupon->get_code() ); ?></td>
                        <td><?php echo esc_html( $coupon->get_meta( 'karen_phone_normalized' ) ? substr( $coupon->get_meta( 'karen_phone_normalized' ), -10 ) : '-' ); ?></td>
                        <td><?php echo esc_html( $coupon->get_discount_type() === 'percent' ? 'درصد' : 'مبلغ ثابت' ); ?></td>
                        <td><?php echo esc_html( $coupon->get_amount() ); ?></td>
                        <td><?php echo $coupon->get_date_expires() ? esc_html( $coupon->get_date_expires()->format( 'Y-m-d H:i' ) ) : '-'; ?></td>
                        <td><?php echo esc_html( $coupon->get_usage_count() ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6"><?php esc_html_e( 'هیچ کوپنی وجود ندارد', KAREN_PLUGIN_TEXT_DOMAIN ); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="displaying-num"><?php echo sprintf( esc_html__( '%d کوپن', KAREN_PLUGIN_TEXT_DOMAIN ), intval( $total ) ); ?></span>
            <span class="pagination-links">
                <?php if ( $paged > 1 ) : ?>
                    <a class="first-page button" href="<?php echo esc_url( admin_url( 'admin.php?page=karen-coupons&paged=1' ) ); ?>">&laquo;</a>
                    <a class="prev-page button" href="<?php echo esc_url( admin_url( 'admin.php?page=karen-coupons&paged=' . ( $paged - 1 ) ) ); ?>">&lsaquo;</a>
                <?php endif; ?>
                <span class="paging-input">
                    <span class="total-pages"><?php echo intval( $total_pages ); ?></span> صفحه
                </span>
                <?php if ( $paged < $total_pages ) : ?>
                    <a class="next-page button" href="<?php echo esc_url( admin_url( 'admin.php?page=karen-coupons&paged=' . ( $paged + 1 ) ) ); ?>">&rsaquo;</a>
                    <a class="last-page button" href="<?php echo esc_url( admin_url( 'admin.php?page=karen-coupons&paged=' . intval( $total_pages ) ) ); ?>">&raquo;</a>
                <?php endif; ?>
            </span>
        </div>
    </div>
</div>
