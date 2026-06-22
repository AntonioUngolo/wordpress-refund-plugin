<?php
namespace ProductRefund;

class Plugin {

    public function init(): void {
        add_action( 'init', array( Cpt::class, 'register' ) );
        add_action( 'init', array( __CLASS__, 'load_textdomain' ) );

        Settings::register();
        Form::register();
        StatusPage::register();
        Account::register();
        Admin::register();
        OrderDelivery::register();
        Privacy::register();
    }

    public static function load_textdomain(): void {
        load_plugin_textdomain( 'product-refund', false, dirname( plugin_basename( PRODUCT_REFUND_FILE ) ) . '/languages' );
    }
}
