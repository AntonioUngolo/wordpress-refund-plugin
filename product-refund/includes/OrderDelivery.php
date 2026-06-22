<?php
namespace ProductRefund;

class OrderDelivery {

    public static function register(): void {
        add_action( 'woocommerce_admin_order_data_after_order_details', array( __CLASS__, 'render_field' ) );
        add_action( 'woocommerce_process_shop_order_meta', array( __CLASS__, 'save_field' ), 10, 1 );
    }

    public static function render_field( $order ): void {
        $value = $order->get_meta( TermService::DELIVERY_META );
        wp_nonce_field( 'product_refund_delivery', 'product_refund_delivery_nonce' );
        ?>
        <p class="form-field form-field-wide">
            <label for="product_refund_delivery_date"><?php esc_html_e( 'Data di consegna effettiva (recesso)', 'product-refund' ); ?></label>
            <input type="date" id="product_refund_delivery_date" name="product_refund_delivery_date" value="<?php echo esc_attr( $value ? substr( (string) $value, 0, 10 ) : '' ); ?>" />
            <span class="description"><?php esc_html_e( 'Se vuota, il termine di recesso usa la data di completamento + giorni lavorativi stimati.', 'product-refund' ); ?></span>
        </p>
        <?php
    }

    public static function save_field( $order_id ): void {
        if ( ! isset( $_POST['product_refund_delivery_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['product_refund_delivery_nonce'] ), 'product_refund_delivery' ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_shop_orders' ) ) {
            return;
        }
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        $raw = isset( $_POST['product_refund_delivery_date'] ) ? sanitize_text_field( wp_unslash( $_POST['product_refund_delivery_date'] ) ) : '';
        if ( '' === $raw ) {
            $order->delete_meta_data( TermService::DELIVERY_META );
        } elseif ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ) {
            $order->update_meta_data( TermService::DELIVERY_META, $raw . ' 00:00:00' );
        }
        $order->save();
    }
}
