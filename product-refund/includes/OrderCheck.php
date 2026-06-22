<?php
namespace ProductRefund;

use ProductRefund\Core\OrderMatcher;

class OrderCheck {

    /**
     * Find a WooCommerce order that matches the given number + email.
     * Returns the WC_Order on success, null otherwise.
     */
    public static function find( string $input_number, string $input_email ) {
        $id = absint( ltrim( trim( $input_number ), '#' ) );
        if ( ! $id ) {
            return null;
        }
        $id = (int) apply_filters( 'product_refund_resolve_order_id', $id, $input_number );

        $order = wc_get_order( $id );
        if ( ! $order ) {
            return null;
        }

        if ( OrderMatcher::matches( $input_number, $input_email, (string) $order->get_order_number(), (string) $order->get_billing_email() ) ) {
            return $order;
        }
        return null;
    }
}
