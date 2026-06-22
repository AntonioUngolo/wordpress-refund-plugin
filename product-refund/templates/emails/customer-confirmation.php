<?php
/** @var int $post_id */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use ProductRefund\Request;
$number   = (string) Request::field( $post_id, 'request_number' );
$order    = (string) Request::field( $post_id, 'order_number' );
$name     = (string) Request::field( $post_id, 'customer_name' );
$received = (string) Request::field( $post_id, 'received_at' );
$reason   = (string) Request::field( $post_id, 'reason' );
$url      = Request::status_page_url( $post_id );
?>
<p><?php echo esc_html( sprintf( __( 'Gentile %s,', 'product-refund' ), $name ) ); ?></p>
<p><?php esc_html_e( 'abbiamo ricevuto la tua richiesta di recesso. Questa email costituisce conferma su supporto durevole.', 'product-refund' ); ?></p>
<ul>
    <li><strong><?php esc_html_e( 'Numero richiesta:', 'product-refund' ); ?></strong> <?php echo esc_html( $number ); ?></li>
    <li><strong><?php esc_html_e( 'Ordine:', 'product-refund' ); ?></strong> <?php echo esc_html( $order ); ?></li>
    <li><strong><?php esc_html_e( 'Data e ora di invio:', 'product-refund' ); ?></strong> <?php echo esc_html( $received ); ?></li>
    <?php if ( '' !== $reason ) : ?>
        <li><strong><?php esc_html_e( 'Motivo:', 'product-refund' ); ?></strong> <?php echo esc_html( $reason ); ?></li>
    <?php endif; ?>
</ul>
<p><?php esc_html_e( 'Puoi consultare lo stato della tua richiesta qui:', 'product-refund' ); ?><br />
<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $url ); ?></a></p>
