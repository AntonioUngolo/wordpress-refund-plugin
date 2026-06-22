<?php
namespace ProductRefund;

class Emails {

    public static function send_customer_confirmation( int $post_id ): void {
        $to = (string) Request::field( $post_id, 'customer_email' );
        if ( ! is_email( $to ) ) {
            return;
        }
        $subject = sprintf(
            /* translators: %s request number */
            __( 'Conferma richiesta di recesso %s', 'product-refund' ),
            (string) Request::field( $post_id, 'request_number' )
        );
        $body = self::render( 'customer-confirmation', array( 'post_id' => $post_id ) );
        $sent = wp_mail( $to, $subject, $body, self::headers() );
        if ( $sent ) {
            update_post_meta( $post_id, '_confirmation_sent_at', current_time( 'mysql' ) );
        }
    }

    public static function send_admin_notification( int $post_id ): void {
        $to      = Settings::admin_email();
        $subject = sprintf(
            /* translators: %s request number */
            __( 'Nuova richiesta di recesso %s', 'product-refund' ),
            (string) Request::field( $post_id, 'request_number' )
        );
        $body = self::render( 'admin-notification', array( 'post_id' => $post_id ) );
        wp_mail( $to, $subject, $body, self::headers() );
    }

    private static function headers(): array {
        return array( 'Content-Type: text/html; charset=UTF-8' );
    }

    private static function render( string $template, array $vars ): string {
        $path = apply_filters(
            'product_refund_email_template',
            PRODUCT_REFUND_DIR . 'templates/emails/' . $template . '.php',
            $template
        );
        if ( ! file_exists( $path ) ) {
            return '';
        }
        extract( $vars, EXTR_SKIP ); // phpcs:ignore
        ob_start();
        include $path;
        return (string) ob_get_clean();
    }
}
