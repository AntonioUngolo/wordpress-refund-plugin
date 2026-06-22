<?php
namespace ProductRefund;

use ProductRefund\Core\RequestNumber;

class Request {

    /**
     * Create a request post and return its post ID.
     *
     * @param array $data Keys: order_id, order_number, customer_user_id, customer_name,
     *                    customer_email, customer_phone, reference_date (Y-m-d H:i:s),
     *                    reference_date_source, within_term (bool), days_elapsed (int),
     *                    reason, received_at (Y-m-d H:i:s).
     */
    public static function create( array $data ): int {
        $year     = (int) gmdate( 'Y' );
        $sequence = self::next_sequence( $year );
        if ( ! $sequence ) {
            return 0;
        }
        $number = RequestNumber::format( $sequence, $year );
        $token  = wp_generate_password( 32, false );

        $meta = array(
            '_request_number'        => $number,
            '_access_token'          => $token,
            '_order_id'              => (int) ( $data['order_id'] ?? 0 ),
            '_order_number'          => (string) ( $data['order_number'] ?? '' ),
            '_customer_user_id'      => (int) ( $data['customer_user_id'] ?? 0 ),
            '_customer_name'         => (string) ( $data['customer_name'] ?? '' ),
            '_customer_email'        => (string) ( $data['customer_email'] ?? '' ),
            '_customer_phone'        => (string) ( $data['customer_phone'] ?? '' ),
            '_reference_date'        => (string) ( $data['reference_date'] ?? '' ),
            '_reference_date_source' => (string) ( $data['reference_date_source'] ?? '' ),
            '_within_term'           => ! empty( $data['within_term'] ) ? '1' : '0',
            '_days_elapsed'          => (int) ( $data['days_elapsed'] ?? 0 ),
            '_reason'                => (string) ( $data['reason'] ?? '' ),
            '_integrity_accepted'    => ! empty( $data['integrity_accepted'] ) ? '1' : '0',
            '_integrity_text'        => (string) ( $data['integrity_text'] ?? '' ),
            '_received_at'           => (string) ( $data['received_at'] ?? current_time( 'mysql' ) ),
            '_admin_note'            => '',
        );

        $post_id = wp_insert_post(
            array(
                'post_type'   => Cpt::POST_TYPE,
                'post_status' => Cpt::ST_RICEVUTA,
                'post_title'  => $number,
                'meta_input'  => $meta,
            ),
            true
        );

        if ( is_wp_error( $post_id ) ) {
            self::rollback_sequence( $year, $sequence );
            return 0;
        }

        return (int) $post_id;
    }

    /**
     * Atomic per-year sequence using a MySQL named lock.
     */
    public static function next_sequence( int $year ): int {
        global $wpdb;
        $lock   = 'product_refund_seq_' . $year;
        $option = 'product_refund_seq_' . $year;

        $acquired = false;
        for ( $i = 0; $i < 3; $i++ ) {
            if ( '1' === (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 5)', $lock ) ) ) {
                $acquired = true;
                break;
            }
        }
        if ( ! $acquired ) {
            return 0;
        }
        $next = (int) get_option( $option, 0 ) + 1;
        update_option( $option, $next, false );
        $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) );

        return $next;
    }

    private static function rollback_sequence( int $year, int $sequence ): void {
        global $wpdb;
        $lock   = 'product_refund_seq_' . $year;
        $option = 'product_refund_seq_' . $year;
        if ( '1' === (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 5)', $lock ) ) ) {
            if ( (int) get_option( $option, 0 ) === $sequence ) {
                update_option( $option, $sequence - 1, false );
            }
            $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) );
        }
    }

    public static function get_by_number( string $number ): ?\WP_Post {
        $posts = get_posts(
            array(
                'post_type'      => Cpt::POST_TYPE,
                'post_status'    => array_keys( Cpt::statuses() ),
                'meta_key'       => '_request_number',
                'meta_value'     => $number,
                'posts_per_page' => 1,
                'no_found_rows'  => true,
            )
        );
        return $posts ? $posts[0] : null;
    }

    public static function field( int $post_id, string $key ) {
        return get_post_meta( $post_id, '_' . $key, true );
    }

    public static function status_page_url( int $post_id ): string {
        return Settings::status_page_url(
            (string) self::field( $post_id, 'request_number' ),
            (string) self::field( $post_id, 'access_token' )
        );
    }
}
