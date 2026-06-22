<?php
/** @var int $post_id */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use ProductRefund\Request;
$number = (string) Request::field( $post_id, 'request_number' );
$order  = (string) Request::field( $post_id, 'order_number' );
$email  = (string) Request::field( $post_id, 'customer_email' );
$within = '1' === (string) Request::field( $post_id, 'within_term' );
$days   = (int) Request::field( $post_id, 'days_elapsed' );
$edit   = get_edit_post_link( $post_id, '' );
?>
<p><?php esc_html_e( 'Nuova richiesta di recesso ricevuta.', 'product-refund' ); ?></p>
<ul>
    <li><strong><?php esc_html_e( 'Numero:', 'product-refund' ); ?></strong> <?php echo esc_html( $number ); ?></li>
    <li><strong><?php esc_html_e( 'Ordine:', 'product-refund' ); ?></strong> <?php echo esc_html( $order ); ?></li>
    <li><strong><?php esc_html_e( 'Cliente:', 'product-refund' ); ?></strong> <?php echo esc_html( $email ); ?></li>
    <li><strong><?php esc_html_e( 'Termine:', 'product-refund' ); ?></strong>
        <?php echo $within ? esc_html__( 'IN TERMINE', 'product-refund' ) : esc_html__( 'FUORI TERMINE', 'product-refund' ); ?>
        (<?php echo esc_html( sprintf( _n( '%d giorno', '%d giorni', $days, 'product-refund' ), $days ) ); ?>)
    </li>
</ul>
<p><a href="<?php echo esc_url( $edit ); ?>"><?php esc_html_e( 'Apri la richiesta nel backend', 'product-refund' ); ?></a></p>
