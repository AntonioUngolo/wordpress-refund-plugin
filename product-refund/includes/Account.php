<?php
namespace ProductRefund;

class Account {

    const ENDPOINT = 'rimborsi';

    public static function register(): void {
        add_action( 'init', array( __CLASS__, 'add_endpoint' ) );
        add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'menu_item' ) );
        add_action( 'woocommerce_account_' . self::ENDPOINT . '_endpoint', array( __CLASS__, 'content' ) );
        add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
    }

    public static function add_endpoint(): void {
        add_rewrite_endpoint( self::ENDPOINT, EP_ROOT | EP_PAGES );
    }

    public static function query_vars( array $vars ): array {
        $vars[] = self::ENDPOINT;
        return $vars;
    }

    /**
     * Insert "Rimborsi" right after "Orders".
     */
    public static function menu_item( array $items ): array {
        $new = array();
        foreach ( $items as $key => $label ) {
            $new[ $key ] = $label;
            if ( 'orders' === $key ) {
                $new[ self::ENDPOINT ] = __( 'Rimborsi', 'product-refund' );
            }
        }
        if ( ! isset( $new[ self::ENDPOINT ] ) ) {
            $new[ self::ENDPOINT ] = __( 'Rimborsi', 'product-refund' );
        }
        return $new;
    }

    public static function content(): void {
        wp_enqueue_style( 'product-refund' );
        // Form (handles its own POST) + user's requests table.
        echo Form::render( Submission::handle() ); // phpcs:ignore WordPress.Security.EscapeOutput
        $requests = self::user_requests( get_current_user_id() );
        include PRODUCT_REFUND_DIR . 'templates/account-rimborsi.php';
    }

    public static function user_requests( int $user_id ): array {
        if ( ! $user_id ) {
            return array();
        }
        return get_posts(
            array(
                'post_type'      => Cpt::POST_TYPE,
                'post_status'    => array_keys( Cpt::statuses() ),
                'meta_key'       => '_customer_user_id',
                'meta_value'     => $user_id,
                'posts_per_page' => 50,
                'orderby'        => 'date',
                'order'          => 'DESC',
            )
        );
    }
}
