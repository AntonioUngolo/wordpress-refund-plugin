<?php
namespace ProductRefund;

class Cpt {

    const POST_TYPE    = 'recesso_richiesta';
    const ST_RICEVUTA  = 'recesso_ricevuta';
    const ST_GESTITA   = 'recesso_gestita';
    const ST_RIFIUTATA = 'recesso_rifiutata';

    public static function statuses(): array {
        return array(
            self::ST_RICEVUTA  => __( 'Ricevuta', 'product-refund' ),
            self::ST_GESTITA   => __( 'Gestita', 'product-refund' ),
            self::ST_RIFIUTATA => __( 'Rifiutata', 'product-refund' ),
        );
    }

    public static function status_label( string $status ): string {
        $map = self::statuses();
        return $map[ $status ] ?? $status;
    }

    public static function register(): void {
        register_post_type(
            self::POST_TYPE,
            array(
                'labels'          => array(
                    'name'          => __( 'Richieste di recesso', 'product-refund' ),
                    'singular_name' => __( 'Richiesta di recesso', 'product-refund' ),
                    'menu_name'     => __( 'Recessi', 'product-refund' ),
                ),
                'public'          => false,
                'show_ui'         => true,
                'show_in_menu'    => true,
                'menu_icon'       => 'dashicons-undo',
                'capability_type' => 'post',
                'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
                'map_meta_cap'    => true,
                'supports'        => array( 'title' ),
                'has_archive'     => false,
                'rewrite'         => false,
            )
        );

        foreach ( self::statuses() as $slug => $label ) {
            register_post_status(
                $slug,
                array(
                    'label'                     => $label,
                    'public'                    => false,
                    'internal'                  => true,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    /* translators: %s: count */
                    'label_count'               => _n_noop( $label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>', 'product-refund' ),
                )
            );
        }
    }
}
