<?php
namespace ProductRefund;

class Settings {

    const OPTION = 'product_refund_settings';

    public static function defaults(): array {
        return array(
            'business_days'  => 3,
            'admin_email'    => get_option( 'admin_email' ),
            'form_page_id'   => 0,
            'status_page_id' => 0,
            'button_label'   => __( 'Recedere dal contratto qui', 'product-refund' ),
            'button_bg'      => '#2271b1',
            'button_color'   => '#ffffff',
            'button_padding' => '12px 20px',
            'button_radius'  => 4,
            'integrity_text' => __( 'Confermo che il prodotto è integro, non utilizzato e con la confezione/sigillatura originale intatta.', 'product-refund' ),
        );
    }

    public static function all(): array {
        return wp_parse_args( (array) get_option( self::OPTION, array() ), self::defaults() );
    }

    public static function get( string $key ) {
        $all = self::all();
        return $all[ $key ] ?? null;
    }

    public static function business_days(): int {
        return max( 0, (int) self::get( 'business_days' ) );
    }

    public static function admin_email(): string {
        $email = (string) self::get( 'admin_email' );
        return is_email( $email ) ? $email : get_option( 'admin_email' );
    }

    public static function integrity_text(): string {
        return (string) self::get( 'integrity_text' );
    }

    /**
     * Inline CSS for the [recesso_pulsante] button, from the saved options.
     */
    public static function button_style(): string {
        $s = self::all();
        return sprintf(
            'background:%s;color:%s;padding:%s;border-radius:%dpx;border:0;cursor:pointer;',
            $s['button_bg'],
            $s['button_color'],
            $s['button_padding'],
            (int) $s['button_radius']
        );
    }

    /**
     * Validate a CSS spacing value (1–4 lengths like "12px 20px"); fall back if invalid.
     */
    private static function sanitize_spacing( $value, string $fallback ): string {
        $value = trim( (string) $value );
        if ( preg_match( '/^\d+(px|em|rem|%)?( \d+(px|em|rem|%)?){0,3}$/', $value ) ) {
            return $value;
        }
        return $fallback;
    }

    public static function status_page_url( string $request_number, string $token ): string {
        $page_id = (int) self::get( 'status_page_id' );
        $base = $page_id ? get_permalink( $page_id ) : '';
        if ( ! $base ) {
            $base = home_url( '/' );
        }
        return add_query_arg(
            array(
                'id' => rawurlencode( $request_number ),
                'k'  => rawurlencode( $token ),
            ),
            $base
        );
    }

    /**
     * Ensure the form and status pages exist and use the canonical permalink.
     * Creates them when missing and renames the slug of existing pages so the
     * status/form links always resolve to the short, expected URL.
     */
    public static function ensure_pages(): void {
        $settings = self::all();
        $changed  = false;

        $form = self::ensure_page( (int) $settings['form_page_id'], __( 'Richiesta di recesso', 'product-refund' ), '[recesso_form]', 'richiesta-di-recesso' );
        if ( $form && $form !== (int) $settings['form_page_id'] ) {
            $settings['form_page_id'] = $form;
            $changed                  = true;
        }

        $status = self::ensure_page( (int) $settings['status_page_id'], __( 'Stato della richiesta di recesso', 'product-refund' ), '[recesso_stato]', 'richiesta-recesso' );
        if ( $status && $status !== (int) $settings['status_page_id'] ) {
            $settings['status_page_id'] = $status;
            $changed                    = true;
        }

        if ( $changed ) {
            update_option( self::OPTION, $settings );
        }
    }

    /**
     * Resolve a managed page: if it exists, enforce the canonical slug; otherwise
     * reuse a page already at that slug, or create a fresh one. Returns the page ID.
     */
    private static function ensure_page( int $page_id, string $title, string $content, string $slug ): int {
        if ( $page_id ) {
            $post = get_post( $page_id );
            if ( $post && 'page' === $post->post_type && 'publish' === $post->post_status ) {
                if ( $post->post_name !== $slug ) {
                    wp_update_post(
                        array(
                            'ID'        => $page_id,
                            'post_name' => $slug,
                        )
                    );
                }
                return $page_id;
            }
        }

        $existing = get_page_by_path( $slug );
        if ( $existing instanceof \WP_Post ) {
            return (int) $existing->ID;
        }

        $id = wp_insert_post(
            array(
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_type'    => 'page',
            )
        );
        return is_wp_error( $id ) ? 0 : (int) $id;
    }

    /**
     * Self-healing setup: ensure the pages exist once per plugin version, on a
     * normal page load. Recovers even when the plugin is updated in place
     * (files replaced) without a deactivate/reactivate cycle, so the activation
     * hook never fired.
     */
    public static function maybe_setup(): void {
        if ( ( defined( 'DOING_CRON' ) && DOING_CRON )
            || ( defined( 'DOING_AJAX' ) && DOING_AJAX )
            || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
            return;
        }
        if ( get_option( 'product_refund_setup_version' ) === PRODUCT_REFUND_VERSION ) {
            return;
        }
        self::ensure_pages();
        update_option( 'product_refund_setup_version', PRODUCT_REFUND_VERSION );
    }

    public static function register(): void {
        add_action( 'init', array( __CLASS__, 'maybe_setup' ) );
        add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
        add_action( 'admin_init', array( __CLASS__, 'fields' ) );
    }

    public static function menu(): void {
        add_submenu_page(
            'edit.php?post_type=' . Cpt::POST_TYPE,
            __( 'Impostazioni Recesso', 'product-refund' ),
            __( 'Impostazioni', 'product-refund' ),
            'manage_woocommerce',
            'product-refund-settings',
            array( __CLASS__, 'render_page' )
        );
    }

    public static function fields(): void {
        register_setting(
            'product_refund',
            self::OPTION,
            array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) )
        );
    }

    public static function sanitize( $input ): array {
        $out                   = self::all();
        $out['business_days']  = isset( $input['business_days'] ) ? max( 0, (int) $input['business_days'] ) : $out['business_days'];
        $out['admin_email']    = isset( $input['admin_email'] ) && is_email( $input['admin_email'] ) ? sanitize_email( $input['admin_email'] ) : $out['admin_email'];
        $out['form_page_id']   = isset( $input['form_page_id'] ) ? self::validate_page_id( $input['form_page_id'] ) : $out['form_page_id'];
        $out['status_page_id'] = isset( $input['status_page_id'] ) ? self::validate_page_id( $input['status_page_id'] ) : $out['status_page_id'];
        $out['button_label']   = isset( $input['button_label'] ) ? sanitize_text_field( $input['button_label'] ) : $out['button_label'];
        $out['button_bg']      = isset( $input['button_bg'] ) ? ( sanitize_hex_color( $input['button_bg'] ) ?: $out['button_bg'] ) : $out['button_bg'];
        $out['button_color']   = isset( $input['button_color'] ) ? ( sanitize_hex_color( $input['button_color'] ) ?: $out['button_color'] ) : $out['button_color'];
        $out['button_padding'] = isset( $input['button_padding'] ) ? self::sanitize_spacing( $input['button_padding'], $out['button_padding'] ) : $out['button_padding'];
        $out['button_radius']  = isset( $input['button_radius'] ) ? max( 0, min( 100, (int) $input['button_radius'] ) ) : $out['button_radius'];
        $out['integrity_text'] = isset( $input['integrity_text'] ) ? sanitize_textarea_field( $input['integrity_text'] ) : $out['integrity_text'];
        return $out;
    }

    private static function validate_page_id( $id ): int {
        $id   = (int) $id;
        $post = $id ? get_post( $id ) : null;
        return ( $post && 'page' === $post->post_type && 'publish' === $post->post_status ) ? $id : 0;
    }

    public static function render_page(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }
        $s = self::all();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Impostazioni Recesso', 'product-refund' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'product_refund' ); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="pr_business_days"><?php esc_html_e( 'Giorni lavorativi stimati per la consegna', 'product-refund' ); ?></label></th>
                        <td>
                            <input name="<?php echo esc_attr( self::OPTION ); ?>[business_days]" id="pr_business_days" type="number" min="0" value="<?php echo esc_attr( $s['business_days'] ); ?>" />
                            <p class="description"><?php esc_html_e( 'Usato solo quando la data di consegna manuale non è inserita sull\'ordine.', 'product-refund' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="pr_admin_email"><?php esc_html_e( 'Email per le notifiche admin', 'product-refund' ); ?></label></th>
                        <td><input name="<?php echo esc_attr( self::OPTION ); ?>[admin_email]" id="pr_admin_email" type="email" class="regular-text" value="<?php echo esc_attr( $s['admin_email'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="pr_button_label"><?php esc_html_e( 'Etichetta pulsante', 'product-refund' ); ?></label></th>
                        <td><input name="<?php echo esc_attr( self::OPTION ); ?>[button_label]" id="pr_button_label" type="text" class="regular-text" value="<?php echo esc_attr( $s['button_label'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Stile pulsante', 'product-refund' ); ?></th>
                        <td>
                            <p>
                                <label for="pr_button_bg" style="display:inline-block;min-width:140px;"><?php esc_html_e( 'Colore sfondo', 'product-refund' ); ?></label>
                                <input name="<?php echo esc_attr( self::OPTION ); ?>[button_bg]" id="pr_button_bg" type="color" value="<?php echo esc_attr( $s['button_bg'] ); ?>" />
                            </p>
                            <p>
                                <label for="pr_button_color" style="display:inline-block;min-width:140px;"><?php esc_html_e( 'Colore testo', 'product-refund' ); ?></label>
                                <input name="<?php echo esc_attr( self::OPTION ); ?>[button_color]" id="pr_button_color" type="color" value="<?php echo esc_attr( $s['button_color'] ); ?>" />
                            </p>
                            <p>
                                <label for="pr_button_padding" style="display:inline-block;min-width:140px;"><?php esc_html_e( 'Padding', 'product-refund' ); ?></label>
                                <input name="<?php echo esc_attr( self::OPTION ); ?>[button_padding]" id="pr_button_padding" type="text" value="<?php echo esc_attr( $s['button_padding'] ); ?>" placeholder="12px 20px" />
                                <span class="description"><?php esc_html_e( 'Es. "12px 20px" (verticale orizzontale).', 'product-refund' ); ?></span>
                            </p>
                            <p>
                                <label for="pr_button_radius" style="display:inline-block;min-width:140px;"><?php esc_html_e( 'Border radius (px)', 'product-refund' ); ?></label>
                                <input name="<?php echo esc_attr( self::OPTION ); ?>[button_radius]" id="pr_button_radius" type="number" min="0" max="100" value="<?php echo esc_attr( $s['button_radius'] ); ?>" />
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="pr_integrity_text"><?php esc_html_e( 'Dichiarazione condizioni prodotto', 'product-refund' ); ?></label></th>
                        <td>
                            <textarea name="<?php echo esc_attr( self::OPTION ); ?>[integrity_text]" id="pr_integrity_text" rows="3" class="large-text"><?php echo esc_textarea( $s['integrity_text'] ); ?></textarea>
                            <p class="description"><?php esc_html_e( 'Testo della checkbox obbligatoria mostrata nel form di recesso.', 'product-refund' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="pr_form_page"><?php esc_html_e( 'Pagina del form di recesso', 'product-refund' ); ?></label></th>
                        <td><?php wp_dropdown_pages( array( 'name' => self::OPTION . '[form_page_id]', 'id' => 'pr_form_page', 'selected' => $s['form_page_id'], 'show_option_none' => __( '— Seleziona —', 'product-refund' ), 'option_none_value' => 0 ) ); ?>
                        <p class="description"><?php echo esc_html( sprintf( __( 'Inserisci lo shortcode %s in questa pagina.', 'product-refund' ), '[recesso_form]' ) ); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="pr_status_page"><?php esc_html_e( 'Pagina di stato richiesta', 'product-refund' ); ?></label></th>
                        <td><?php wp_dropdown_pages( array( 'name' => self::OPTION . '[status_page_id]', 'id' => 'pr_status_page', 'selected' => $s['status_page_id'], 'show_option_none' => __( '— Seleziona —', 'product-refund' ), 'option_none_value' => 0 ) ); ?>
                        <p class="description"><?php echo esc_html( sprintf( __( 'Inserisci lo shortcode %s in questa pagina.', 'product-refund' ), '[recesso_stato]' ) ); ?></p></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
