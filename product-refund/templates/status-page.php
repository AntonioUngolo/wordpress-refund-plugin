<?php
/** @var int $post_id */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use ProductRefund\Request;
use ProductRefund\Cpt;
$post   = get_post( $post_id );
$number = (string) Request::field( $post_id, 'request_number' );
$order  = (string) Request::field( $post_id, 'order_number' );
$within = '1' === (string) Request::field( $post_id, 'within_term' );
$note   = (string) Request::field( $post_id, 'admin_note' );
$status = $post ? $post->post_status : Cpt::ST_RICEVUTA;
$status_slug = str_replace( 'recesso_', '', $status );
?>
<h2><?php echo esc_html( sprintf( __( 'Richiesta %s', 'product-refund' ), $number ) ); ?></h2>
<p>
    <strong><?php esc_html_e( 'Stato:', 'product-refund' ); ?></strong>
    <span class="product-refund-badge <?php echo esc_attr( $status_slug ); ?>"><?php echo esc_html( Cpt::status_label( $status ) ); ?></span>
</p>
<p>
    <strong><?php esc_html_e( 'Termine:', 'product-refund' ); ?></strong>
    <span class="product-refund-badge <?php echo esc_attr( $within ? 'in' : 'out' ); ?>"><?php echo $within ? esc_html__( 'In termine', 'product-refund' ) : esc_html__( 'Fuori termine', 'product-refund' ); ?></span>
</p>
<ul>
    <li><strong><?php esc_html_e( 'Ordine:', 'product-refund' ); ?></strong> <?php echo esc_html( $order ); ?></li>
    <li><strong><?php esc_html_e( 'Ricevuta il:', 'product-refund' ); ?></strong> <?php echo esc_html( (string) Request::field( $post_id, 'received_at' ) ); ?></li>
</ul>
<?php
$integrity_text = (string) Request::field( $post_id, 'integrity_text' );
if ( '1' === (string) Request::field( $post_id, 'integrity_accepted' ) && '' !== $integrity_text ) :
    ?>
    <p><strong><?php esc_html_e( 'Dichiarazione accettata:', 'product-refund' ); ?></strong> <?php echo esc_html( $integrity_text ); ?></p>
<?php endif; ?>
<?php if ( '' !== $note ) : ?>
    <p><strong><?php esc_html_e( 'Nota:', 'product-refund' ); ?></strong> <?php echo esc_html( $note ); ?></p>
<?php endif; ?>

<?php
$order_id = (int) Request::field( $post_id, 'order_id' );
$wc_order = ( $order_id && function_exists( 'wc_get_order' ) ) ? wc_get_order( $order_id ) : false;
if ( $wc_order ) :
    ?>
    <h3><?php esc_html_e( 'Dettaglio ordine', 'product-refund' ); ?></h3>
    <ul>
        <li><strong><?php esc_html_e( 'Data ordine:', 'product-refund' ); ?></strong>
            <?php echo esc_html( $wc_order->get_date_created() ? wc_format_datetime( $wc_order->get_date_created() ) : '' ); ?></li>
        <li><strong><?php esc_html_e( 'Stato ordine:', 'product-refund' ); ?></strong>
            <?php echo esc_html( wc_get_order_status_name( $wc_order->get_status() ) ); ?></li>
    </ul>
    <table class="shop_table product-refund-order-items">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Prodotto', 'product-refund' ); ?></th>
                <th><?php esc_html_e( 'Quantità', 'product-refund' ); ?></th>
                <th><?php esc_html_e( 'Subtotale', 'product-refund' ); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $wc_order->get_items() as $item ) : ?>
            <tr>
                <td><?php echo esc_html( $item->get_name() ); ?></td>
                <td><?php echo esc_html( (string) $item->get_quantity() ); ?></td>
                <td><?php echo wp_kses_post( $wc_order->get_formatted_line_subtotal( $item ) ); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2"><?php esc_html_e( 'Totale', 'product-refund' ); ?></th>
                <td><?php echo wp_kses_post( $wc_order->get_formatted_order_total() ); ?></td>
            </tr>
        </tfoot>
    </table>
    <?php
endif;
?>
