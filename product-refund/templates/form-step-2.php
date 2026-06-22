<?php
/** @var array $f */
/** @var \WC_Order $order */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<h2><?php esc_html_e( 'Conferma la richiesta', 'product-refund' ); ?></h2>
<p><?php echo esc_html( sprintf( __( 'Stai richiedendo il recesso per l\'ordine %1$s del %2$s.', 'product-refund' ), $order->get_order_number(), wc_format_datetime( $order->get_date_created() ) ) ); ?></p>
<ul class="product-refund-summary">
    <li><strong><?php esc_html_e( 'Nome:', 'product-refund' ); ?></strong> <?php echo esc_html( $f['customer_name'] ); ?></li>
    <li><strong><?php esc_html_e( 'Email:', 'product-refund' ); ?></strong> <?php echo esc_html( $f['customer_email'] ); ?></li>
    <?php if ( '' !== $f['reason'] ) : ?>
        <li><strong><?php esc_html_e( 'Motivo:', 'product-refund' ); ?></strong> <?php echo esc_html( $f['reason'] ); ?></li>
    <?php endif; ?>
    <li><strong><?php esc_html_e( 'Dichiarazione:', 'product-refund' ); ?></strong> <?php echo esc_html( \ProductRefund\Settings::integrity_text() ); ?></li>
</ul>
<form method="post" class="product-refund-step2">
    <?php wp_nonce_field( 'product_refund_confirm', 'product_refund_nonce' ); ?>
    <input type="hidden" name="product_refund_action" value="confirm" />
    <input type="hidden" name="order_number" value="<?php echo esc_attr( $f['order_number'] ); ?>" />
    <input type="hidden" name="customer_email" value="<?php echo esc_attr( $f['customer_email'] ); ?>" />
    <input type="hidden" name="customer_name" value="<?php echo esc_attr( $f['customer_name'] ); ?>" />
    <input type="hidden" name="customer_phone" value="<?php echo esc_attr( $f['customer_phone'] ); ?>" />
    <input type="hidden" name="reason" value="<?php echo esc_attr( $f['reason'] ); ?>" />
    <input type="hidden" name="pr_integrity" value="1" />
    <p><button type="submit" class="product-refund-button" style="<?php echo esc_attr( \ProductRefund\Settings::button_style() ); ?>"><?php esc_html_e( 'Conferma invio', 'product-refund' ); ?></button></p>
</form>
