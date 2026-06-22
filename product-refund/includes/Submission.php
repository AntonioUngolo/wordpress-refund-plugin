<?php
namespace ProductRefund;

class Submission {

    /**
     * Process a POSTed step. Returns a state array consumed by Form::render().
     *
     * @return array{step:string, errors:array, order:?\WC_Order, fields:array}
     */
    public static function handle(): array {
        $state = array(
            'step'   => 'form',
            'errors' => array(),
            'order'  => null,
            'fields' => self::default_fields(),
        );

        $action = isset( $_POST['product_refund_action'] ) ? sanitize_key( wp_unslash( $_POST['product_refund_action'] ) ) : '';
        if ( '' === $action ) {
            self::prefill_logged_in( $state['fields'] );
            return $state;
        }

        // Honeypot: must be empty.
        if ( ! empty( $_POST['pr_website'] ) ) {
            return $state;
        }

        $state['fields'] = self::read_fields();

        if ( 'verify' === $action ) {
            return self::handle_verify( $state );
        }
        if ( 'confirm' === $action ) {
            return self::handle_confirm( $state );
        }
        return $state;
    }

    private static function handle_verify( array $state ): array {
        if ( ! isset( $_POST['product_refund_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['product_refund_nonce'] ), 'product_refund_verify' ) ) {
            $state['errors'][] = __( 'Sessione scaduta, riprova.', 'product-refund' );
            return $state;
        }
        $f = $state['fields'];
        if ( '' === $f['order_number'] || ! is_email( $f['customer_email'] ) ) {
            $state['errors'][] = __( 'Inserisci numero ordine ed email validi.', 'product-refund' );
            return $state;
        }
        if ( empty( $f['integrity'] ) ) {
            $state['errors'][] = __( 'Devi confermare la dichiarazione sulle condizioni del prodotto.', 'product-refund' );
            return $state;
        }
        $order = OrderCheck::find( $f['order_number'], $f['customer_email'] );
        if ( ! $order ) {
            $state['errors'][] = __( 'Nessun ordine corrisponde a numero ed email inseriti.', 'product-refund' );
            return $state;
        }
        $state['order'] = $order;
        $state['step']  = 'confirm';
        return $state;
    }

    private static function handle_confirm( array $state ): array {
        if ( ! isset( $_POST['product_refund_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['product_refund_nonce'] ), 'product_refund_confirm' ) ) {
            $state['errors'][] = __( 'Sessione scaduta, riprova.', 'product-refund' );
            return $state;
        }
        $f     = $state['fields'];
        if ( empty( $f['integrity'] ) ) {
            $state['errors'][] = __( 'Devi confermare la dichiarazione sulle condizioni del prodotto.', 'product-refund' );
            return $state;
        }
        $order = OrderCheck::find( $f['order_number'], $f['customer_email'] );
        if ( ! $order ) {
            $state['errors'][] = __( 'Verifica ordine non riuscita, riprova.', 'product-refund' );
            return $state;
        }

        $term    = TermService::evaluate_for_order( $order );
        $post_id = Request::create(
            array(
                'order_id'              => $order->get_id(),
                'order_number'          => (string) $order->get_order_number(),
                'customer_user_id'      => get_current_user_id(),
                'customer_name'         => $f['customer_name'],
                'customer_email'        => $f['customer_email'],
                'customer_phone'        => $f['customer_phone'],
                'reference_date'        => $term['reference_date']->format( 'Y-m-d H:i:s' ),
                'reference_date_source' => $term['source'],
                'within_term'           => $term['within_term'],
                'days_elapsed'          => $term['days_elapsed'],
                'reason'                => $f['reason'],
                'integrity_accepted'    => true,
                'integrity_text'        => Settings::integrity_text(),
                'received_at'           => current_time( 'mysql' ),
            )
        );

        if ( ! $post_id ) {
            $state['errors'][] = __( 'Impossibile registrare la richiesta, riprova.', 'product-refund' );
            return $state;
        }

        Emails::send_customer_confirmation( $post_id );
        Emails::send_admin_notification( $post_id );

        $state['step']            = 'success';
        $state['fields']['number'] = (string) Request::field( $post_id, 'request_number' );
        $state['fields']['status_url'] = Request::status_page_url( $post_id );
        return $state;
    }

    private static function default_fields(): array {
        return array(
            'order_number'   => '',
            'customer_email' => '',
            'customer_name'  => '',
            'customer_phone' => '',
            'reason'         => '',
            'integrity'      => false,
        );
    }

    private static function read_fields(): array {
        return array(
            'order_number'   => isset( $_POST['order_number'] ) ? sanitize_text_field( wp_unslash( $_POST['order_number'] ) ) : '',
            'customer_email' => isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '',
            'customer_name'  => isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '',
            'customer_phone' => isset( $_POST['customer_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_phone'] ) ) : '',
            'reason'         => isset( $_POST['reason'] ) ? sanitize_textarea_field( wp_unslash( $_POST['reason'] ) ) : '',
            'integrity'      => ! empty( $_POST['pr_integrity'] ),
        );
    }

    private static function prefill_logged_in( array &$fields ): void {
        $user = wp_get_current_user();
        if ( $user && $user->ID ) {
            $fields['customer_email'] = $user->user_email;
            $fields['customer_name']  = trim( $user->first_name . ' ' . $user->last_name ) ?: $user->display_name;
        }
    }
}
