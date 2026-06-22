<?php
/**
 * Plugin Name:       Product Refund — Recesso art. 54-bis
 * Description:        Funzione di recesso online conforme all'art. 54-bis del Codice del Consumo per WooCommerce.
 * Version:           0.5.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Text Domain:       product-refund
 * Domain Path:       /languages
 *
 * @package ProductRefund
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PRODUCT_REFUND_VERSION', '0.5.1' );
define( 'PRODUCT_REFUND_FILE', __FILE__ );
define( 'PRODUCT_REFUND_DIR', plugin_dir_path( __FILE__ ) );
define( 'PRODUCT_REFUND_URL', plugin_dir_url( __FILE__ ) );

// Composer autoloader if present, else minimal PSR-4 fallback.
if ( file_exists( PRODUCT_REFUND_DIR . 'vendor/autoload.php' ) ) {
    require_once PRODUCT_REFUND_DIR . 'vendor/autoload.php';
} else {
    spl_autoload_register(
        function ( $class ) {
            $prefix = 'ProductRefund\\';
            if ( strpos( $class, $prefix ) !== 0 ) {
                return;
            }
            $relative = substr( $class, strlen( $prefix ) );
            $path     = PRODUCT_REFUND_DIR . 'includes/' . str_replace( '\\', '/', $relative ) . '.php';
            if ( file_exists( $path ) ) {
                require_once $path;
            }
        }
    );
}

// HPOS compatibility declaration.
add_action(
    'before_woocommerce_init',
    function () {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', PRODUCT_REFUND_FILE, true );
        }
    }
);

add_action(
    'plugins_loaded',
    function () {
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action(
                'admin_notices',
                function () {
                    echo '<div class="notice notice-error"><p>' .
                        esc_html__( 'Product Refund richiede WooCommerce attivo.', 'product-refund' ) .
                        '</p></div>';
                }
            );
            return;
        }
        ( new \ProductRefund\Plugin() )->init();
    }
);

register_activation_hook(
    __FILE__,
    function () {
        \ProductRefund\Cpt::register();
        \ProductRefund\Account::add_endpoint();
        \ProductRefund\Settings::ensure_pages();
        flush_rewrite_rules();
    }
);

register_deactivation_hook(
    __FILE__,
    function () {
        flush_rewrite_rules();
    }
);
