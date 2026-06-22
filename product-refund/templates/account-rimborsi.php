<?php
/** @var \WP_Post[] $requests */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use ProductRefund\Request;
use ProductRefund\Cpt;
?>
<h3><?php esc_html_e( 'Le tue richieste di recesso', 'product-refund' ); ?></h3>
<?php if ( empty( $requests ) ) : ?>
    <p><?php esc_html_e( 'Non hai ancora inviato richieste.', 'product-refund' ); ?></p>
<?php else : ?>
    <table class="shop_table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Numero', 'product-refund' ); ?></th>
                <th><?php esc_html_e( 'Ordine', 'product-refund' ); ?></th>
                <th><?php esc_html_e( 'Data', 'product-refund' ); ?></th>
                <th><?php esc_html_e( 'Stato', 'product-refund' ); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $requests as $r ) : ?>
            <?php $slug = str_replace( 'recesso_', '', $r->post_status ); ?>
            <tr>
                <td><?php echo esc_html( (string) Request::field( $r->ID, 'request_number' ) ); ?></td>
                <td><?php echo esc_html( (string) Request::field( $r->ID, 'order_number' ) ); ?></td>
                <td><?php echo esc_html( get_the_date( '', $r ) ); ?></td>
                <td><span class="product-refund-badge <?php echo esc_attr( $slug ); ?>"><?php echo esc_html( Cpt::status_label( $r->post_status ) ); ?></span></td>
                <td><a href="<?php echo esc_url( Request::status_page_url( $r->ID ) ); ?>"><?php esc_html_e( 'Dettaglio', 'product-refund' ); ?></a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
