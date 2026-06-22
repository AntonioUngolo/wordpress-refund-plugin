<?php
namespace ProductRefund;

class Privacy {

    const PAGE_SIZE = 50;

    public static function register(): void {
        add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_exporter' ) );
        add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_eraser' ) );
    }

    public static function register_exporter( array $exporters ): array {
        $exporters['product-refund'] = array(
            'exporter_friendly_name' => __( 'Richieste di recesso', 'product-refund' ),
            'callback'               => array( __CLASS__, 'export' ),
        );
        return $exporters;
    }

    public static function register_eraser( array $erasers ): array {
        $erasers['product-refund'] = array(
            'eraser_friendly_name' => __( 'Richieste di recesso', 'product-refund' ),
            'callback'             => array( __CLASS__, 'erase' ),
        );
        return $erasers;
    }

    private static function find_by_email( string $email, int $offset ): array {
        return get_posts(
            array(
                'post_type'      => Cpt::POST_TYPE,
                'post_status'    => array_keys( Cpt::statuses() ),
                'meta_key'       => '_customer_email',
                'meta_value'     => $email,
                'posts_per_page' => self::PAGE_SIZE,
                'offset'         => $offset,
            )
        );
    }

    public static function export( string $email, int $page = 1 ): array {
        $posts = self::find_by_email( $email, ( $page - 1 ) * self::PAGE_SIZE );
        $data  = array();
        foreach ( $posts as $post ) {
            $data[] = array(
                'group_id'    => 'product_refund',
                'group_label' => __( 'Richieste di recesso', 'product-refund' ),
                'item_id'     => 'recesso-' . $post->ID,
                'data'        => array(
                    array( 'name' => __( 'Numero', 'product-refund' ), 'value' => (string) Request::field( $post->ID, 'request_number' ) ),
                    array( 'name' => __( 'Ordine', 'product-refund' ), 'value' => (string) Request::field( $post->ID, 'order_number' ) ),
                    array( 'name' => __( 'Nome', 'product-refund' ), 'value' => (string) Request::field( $post->ID, 'customer_name' ) ),
                    array( 'name' => __( 'Email', 'product-refund' ), 'value' => (string) Request::field( $post->ID, 'customer_email' ) ),
                ),
            );
        }
        return array(
            'data' => $data,
            'done' => count( $posts ) < self::PAGE_SIZE,
        );
    }

    public static function erase( string $email, int $page = 1 ): array {
        $posts   = self::find_by_email( $email, 0 );
        $removed = false;
        foreach ( $posts as $post ) {
            if ( wp_delete_post( $post->ID, true ) ) {
                $removed = true;
            }
        }
        return array(
            'items_removed'  => $removed,
            'items_retained' => false,
            'messages'       => array(),
            'done'           => count( $posts ) < self::PAGE_SIZE,
        );
    }
}
