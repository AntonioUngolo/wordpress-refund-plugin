<?php
/** @var array $f */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<h2><?php esc_html_e( 'Richiesta inviata', 'product-refund' ); ?></h2>
<p><?php echo esc_html( sprintf( __( 'La tua richiesta di recesso è stata registrata con il numero %s.', 'product-refund' ), $f['number'] ) ); ?></p>
<p><?php esc_html_e( 'Ti abbiamo inviato una email di conferma. Puoi consultare lo stato qui:', 'product-refund' ); ?><br />
<a href="<?php echo esc_url( $f['status_url'] ); ?>"><?php echo esc_html( $f['status_url'] ); ?></a></p>
