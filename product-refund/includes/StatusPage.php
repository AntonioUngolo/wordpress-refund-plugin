<?php
namespace ProductRefund;

class StatusPage {

    public static function register(): void {
        add_shortcode( 'recesso_stato', array( __CLASS__, 'shortcode' ) );
    }

    /**
     * Resolve the request post from id + token, enforcing access rules.
     * Returns post ID or 0 if access denied / not found.
     */
    public static function resolve(): int {
        $number = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
        $token  = isset( $_GET['k'] ) ? sanitize_text_field( wp_unslash( $_GET['k'] ) ) : '';
        if ( '' === $number || '' === $token ) {
            return 0;
        }
        $post = Request::get_by_number( $number );
        if ( ! $post ) {
            return 0;
        }
        $stored = (string) Request::field( $post->ID, 'access_token' );
        if ( '' === $stored || ! hash_equals( $stored, $token ) ) {
            return 0;
        }
        // Defense in depth for logged-in owners: if request has an owner, and the
        // current user is logged in but different, still allow (token is the gate),
        // but never widen access — token already validated above.
        return (int) $post->ID;
    }

    public static function shortcode(): string {
        wp_enqueue_style( 'product-refund' );
        $post_id = self::resolve();
        ob_start();
        echo '<div class="product-refund-status">';
        if ( ! $post_id ) {
            echo '<p>' . esc_html__( 'Richiesta non trovata o link non valido.', 'product-refund' ) . '</p>';
        } else {
            include PRODUCT_REFUND_DIR . 'templates/status-page.php';
        }
        echo '</div>';
        return (string) ob_get_clean();
    }
}
