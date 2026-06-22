<?php
namespace ProductRefund;

class Form {

    public static function register(): void {
        add_shortcode( 'recesso_form', array( __CLASS__, 'shortcode' ) );
        add_shortcode( 'recesso_pulsante', array( __CLASS__, 'button_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
    }

    /**
     * Reusable "Recedere dal contratto qui" button linking to the form page.
     */
    public static function button_shortcode(): string {
        wp_enqueue_style( 'product-refund' );
        $page_id = (int) Settings::get( 'form_page_id' );
        $url     = $page_id ? get_permalink( $page_id ) : home_url( '/' );
        $label   = (string) Settings::get( 'button_label' );
        return sprintf(
            '<a class="product-refund-button" style="%s" href="%s">%s</a>',
            esc_attr( Settings::button_style() ),
            esc_url( $url ),
            esc_html( $label )
        );
    }

    public static function assets(): void {
        wp_register_style( 'product-refund', PRODUCT_REFUND_URL . 'assets/css/recesso.css', array(), PRODUCT_REFUND_VERSION );
    }

    public static function shortcode(): string {
        wp_enqueue_style( 'product-refund' );
        $state = Submission::handle();
        return self::render( $state );
    }

    public static function render( array $state ): string {
        ob_start();
        echo '<div class="product-refund-form">';
        if ( ! empty( $state['errors'] ) ) {
            echo '<div class="product-refund-errors">';
            foreach ( $state['errors'] as $error ) {
                echo '<p>' . esc_html( $error ) . '</p>';
            }
            echo '</div>';
        }
        $template = 'form-step-1';
        if ( 'confirm' === $state['step'] ) {
            $template = 'form-step-2';
        } elseif ( 'success' === $state['step'] ) {
            $template = 'form-success';
        }
        $f     = $state['fields'];
        $order = $state['order'];
        include PRODUCT_REFUND_DIR . 'templates/' . $template . '.php';
        echo '</div>';
        return (string) ob_get_clean();
    }
}
