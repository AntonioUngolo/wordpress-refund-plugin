<?php
namespace ProductRefund;

class Admin {

    public static function register(): void {
        add_filter( 'manage_' . Cpt::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
        add_action( 'manage_' . Cpt::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
        add_action( 'add_meta_boxes', array( __CLASS__, 'metabox' ) );
        add_action( 'save_post_' . Cpt::POST_TYPE, array( __CLASS__, 'save' ), 10, 1 );
        add_action( 'pre_get_posts', array( __CLASS__, 'show_all_statuses' ) );
    }

    /**
     * The custom statuses are registered as internal, so the "Tutti" view of the
     * admin list excludes them by default. When no specific status is requested,
     * force the main query to include all of our statuses.
     */
    public static function show_all_statuses( $query ): void {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }
        if ( Cpt::POST_TYPE !== $query->get( 'post_type' ) ) {
            return;
        }
        if ( ! empty( $_GET['post_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }
        $query->set( 'post_status', array_keys( Cpt::statuses() ) );
    }

    public static function columns( array $cols ): array {
        $new = array(
            'cb'        => $cols['cb'] ?? '',
            'title'     => __( 'Numero', 'product-refund' ),
            'pr_order'  => __( 'Ordine', 'product-refund' ),
            'pr_email'  => __( 'Cliente', 'product-refund' ),
            'pr_term'   => __( 'Termine', 'product-refund' ),
            'pr_status' => __( 'Stato', 'product-refund' ),
            'date'      => __( 'Data', 'product-refund' ),
        );
        return $new;
    }

    public static function column_content( string $col, int $post_id ): void {
        switch ( $col ) {
            case 'pr_order':
                echo esc_html( (string) Request::field( $post_id, 'order_number' ) );
                break;
            case 'pr_email':
                echo esc_html( (string) Request::field( $post_id, 'customer_email' ) );
                break;
            case 'pr_term':
                $within = '1' === (string) Request::field( $post_id, 'within_term' );
                $days   = (int) Request::field( $post_id, 'days_elapsed' );
                $source = (string) Request::field( $post_id, 'reference_date_source' );
                printf(
                    '<span class="product-refund-badge %1$s">%2$s</span> <small>(%3$s, %4$s)</small>',
                    $within ? 'in' : 'out',
                    $within ? esc_html__( 'In termine', 'product-refund' ) : esc_html__( 'Fuori termine', 'product-refund' ),
                    esc_html( sprintf( _n( '%d giorno', '%d giorni', $days, 'product-refund' ), $days ) ),
                    esc_html( $source )
                );
                break;
            case 'pr_status':
                $status = get_post_status( $post_id );
                echo esc_html( Cpt::status_label( $status ) );
                break;
        }
    }

    public static function metabox(): void {
        add_meta_box( 'product_refund_detail', __( 'Dettaglio recesso', 'product-refund' ), array( __CLASS__, 'render_metabox' ), Cpt::POST_TYPE, 'normal', 'high' );
    }

    public static function render_metabox( \WP_Post $post ): void {
        wp_nonce_field( 'product_refund_admin', 'product_refund_admin_nonce' );
        $fields = array(
            'order_number'   => __( 'Ordine', 'product-refund' ),
            'customer_name'  => __( 'Nome', 'product-refund' ),
            'customer_email' => __( 'Email', 'product-refund' ),
            'customer_phone' => __( 'Telefono', 'product-refund' ),
            'reason'         => __( 'Motivo', 'product-refund' ),
            'integrity_text' => __( 'Dichiarazione condizioni accettata', 'product-refund' ),
            'reference_date' => __( 'Data di riferimento', 'product-refund' ),
            'received_at'    => __( 'Ricevuta il', 'product-refund' ),
        );
        echo '<table class="form-table">';
        foreach ( $fields as $key => $label ) {
            printf(
                '<tr><th>%s</th><td>%s</td></tr>',
                esc_html( $label ),
                esc_html( (string) Request::field( $post->ID, $key ) )
            );
        }
        echo '</table>';

        $current = $post->post_status;
        echo '<p><label for="pr_status"><strong>' . esc_html__( 'Stato gestione', 'product-refund' ) . '</strong></label><br />';
        echo '<select name="pr_status" id="pr_status">';
        foreach ( Cpt::statuses() as $slug => $label ) {
            printf( '<option value="%s" %s>%s</option>', esc_attr( $slug ), selected( $current, $slug, false ), esc_html( $label ) );
        }
        echo '</select></p>';

        $note = (string) Request::field( $post->ID, 'admin_note' );
        echo '<p><label for="pr_admin_note"><strong>' . esc_html__( 'Nota per il cliente (opzionale)', 'product-refund' ) . '</strong></label><br />';
        echo '<textarea name="pr_admin_note" id="pr_admin_note" rows="3" class="large-text">' . esc_textarea( $note ) . '</textarea></p>';
    }

    public static function save( int $post_id ): void {
        if ( ! isset( $_POST['product_refund_admin_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['product_refund_admin_nonce'] ), 'product_refund_admin' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        if ( isset( $_POST['pr_admin_note'] ) ) {
            update_post_meta( $post_id, '_admin_note', sanitize_textarea_field( wp_unslash( $_POST['pr_admin_note'] ) ) );
        }
        if ( isset( $_POST['pr_status'] ) ) {
            $status = sanitize_key( wp_unslash( $_POST['pr_status'] ) );
            if ( array_key_exists( $status, Cpt::statuses() ) && get_post_status( $post_id ) !== $status ) {
                remove_action( 'save_post_' . Cpt::POST_TYPE, array( __CLASS__, 'save' ), 10 );
                wp_update_post( array( 'ID' => $post_id, 'post_status' => $status ) );
                add_action( 'save_post_' . Cpt::POST_TYPE, array( __CLASS__, 'save' ), 10, 1 );
            }
        }
    }
}
