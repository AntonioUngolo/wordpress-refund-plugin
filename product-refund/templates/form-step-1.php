<?php
/** @var array $f */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<h2><?php esc_html_e( 'Richiesta di recesso', 'product-refund' ); ?></h2>
<p><?php esc_html_e( 'Inserisci i dati dell\'ordine per cui vuoi esercitare il diritto di recesso (14 giorni).', 'product-refund' ); ?></p>
<form method="post" class="product-refund-step1">
    <?php wp_nonce_field( 'product_refund_verify', 'product_refund_nonce' ); ?>
    <input type="hidden" name="product_refund_action" value="verify" />
    <p style="display:none;"><label>Website<input type="text" name="pr_website" value="" autocomplete="off" /></label></p>
    <p>
        <label for="pr_order"><?php esc_html_e( 'Numero ordine', 'product-refund' ); ?> *</label>
        <input type="text" id="pr_order" name="order_number" required value="<?php echo esc_attr( $f['order_number'] ); ?>" />
    </p>
    <p>
        <label for="pr_email"><?php esc_html_e( 'Email dell\'ordine', 'product-refund' ); ?> *</label>
        <input type="email" id="pr_email" name="customer_email" required value="<?php echo esc_attr( $f['customer_email'] ); ?>" />
    </p>
    <p>
        <label for="pr_name"><?php esc_html_e( 'Nome e cognome', 'product-refund' ); ?> *</label>
        <input type="text" id="pr_name" name="customer_name" required value="<?php echo esc_attr( $f['customer_name'] ); ?>" />
    </p>
    <p>
        <label for="pr_phone"><?php esc_html_e( 'Telefono (opzionale)', 'product-refund' ); ?></label>
        <input type="text" id="pr_phone" name="customer_phone" value="<?php echo esc_attr( $f['customer_phone'] ); ?>" />
    </p>
    <p>
        <label for="pr_reason"><?php esc_html_e( 'Motivo (opzionale)', 'product-refund' ); ?></label>
        <textarea id="pr_reason" name="reason" rows="3"><?php echo esc_textarea( $f['reason'] ); ?></textarea>
    </p>
    <p>
        <label><input type="checkbox" name="pr_declare" required /> <?php esc_html_e( 'Dichiaro di voler recedere dal contratto per l\'ordine indicato.', 'product-refund' ); ?></label>
    </p>
    <p>
        <label><input type="checkbox" name="pr_integrity" value="1" required <?php checked( ! empty( $f['integrity'] ) ); ?> /> <?php echo esc_html( \ProductRefund\Settings::integrity_text() ); ?></label>
    </p>
    <p><button type="submit" class="product-refund-button" style="<?php echo esc_attr( \ProductRefund\Settings::button_style() ); ?>"><?php esc_html_e( 'Continua', 'product-refund' ); ?></button></p>
</form>
