<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once KAREN_PLUGIN_DIR . 'includes/class-karen-db.php';

$paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
$per_page = 20;
$offset = ( $paged - 1 ) * $per_page;

$logs = Karen_DB::get_sms_logs( array( 'limit' => $per_page, 'offset' => $offset ) );
$total = Karen_DB::count_sms_logs();
$total_pages = ceil( $total / $per_page );
?>
<div class="wrap karen-admin-page">
    <h1><?php esc_html_e( 'گزارش ارسال پیامک', KAREN_PLUGIN_TEXT_DOMAIN ); ?></h1>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'شماره موبایل', KAREN_PLUGIN_TEXT_DOMAIN ); ?></th>
                <th><?php esc_html_e( 'کوپن', KAREN_PLUGIN_TEXT_DOMAIN ); ?></th>
                <th><?php esc_html_e( 'سفارش', KAREN_PLUGIN_TEXT_DOMAIN ); ?></th>
                <th><?php esc_html_e( 'درگاه', KAREN_PLUGIN_TEXT_DOMAIN ); ?></th>
                <th><?php esc_html_e( 'وضعیت', KAREN_PLUGIN_TEXT_DOMAIN ); ?></th>
                <th><?php esc_html_e( 'تاریخ ارسال', KAREN_PLUGIN_TEXT_DOMAIN ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $logs ) ) : ?>
                <?php foreach ( $logs as $log ) : ?>
                    <tr>
                        <td><?php echo esc_html( substr( $log->phone_normalized, -10 ) ); ?></td>
                        <td><?php echo esc_html( $log->coupon_code ); ?></td>
                        <td>
                            <a href="<?php echo esc_url( admin_url( 'post.php?post=' . intval( $log->order_id ) . '&action=edit' ) ); ?>" target="_blank">
                                #<?php echo intval( $log->order_id ); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html( $log->gateway ); ?></td>
                        <td>
                            <span class="status-<?php echo esc_attr( $log->status ); ?>">
                                <?php echo esc_html( $log->status ); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html( $log->created_at ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6"><?php esc_html_e( 'هیچ گزارشی موجود نیست', KAREN_PLUGIN_TEXT_DOMAIN ); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="displaying-num"><?php echo sprintf( esc_html__( '%d گزارش', KAREN_PLUGIN_TEXT_DOMAIN ), intval( $total ) ); ?></span>
            <span class="pagination-links">
                <?php if ( $paged > 1 ) : ?>
                    <a class="first-page button" href="<?php echo esc_url( admin_url( 'admin.php?page=karen-reports&paged=1' ) ); ?>">&laquo;</a>
                    <a class="prev-page button" href="<?php echo esc_url( admin_url( 'admin.php?page=karen-reports&paged=' . ( $paged - 1 ) ) ); ?>">&lsaquo;</a>
                <?php endif; ?>
                <span class="paging-input">
                    <span class="total-pages"><?php echo intval( $total_pages ); ?></span> صفحه
                </span>
                <?php if ( $paged < $total_pages ) : ?>
                    <a class="next-page button" href="<?php echo esc_url( admin_url( 'admin.php?page=karen-reports&paged=' . ( $paged + 1 ) ) ); ?>">&rsaquo;</a>
                    <a class="last-page button" href="<?php echo esc_url( admin_url( 'admin.php?page=karen-reports&paged=' . intval( $total_pages ) ) ); ?>">&raquo;</a>
                <?php endif; ?>
            </span>
        </div>
    </div>
</div>
