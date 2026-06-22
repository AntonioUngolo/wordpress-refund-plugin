<?php
namespace ProductRefund\Core;

class OrderMatcher {

    public static function matches( string $inputNumber, string $inputEmail, string $orderNumber, string $orderEmail ): bool {
        $input = ltrim( trim( $inputNumber ), '#' );
        if ( '' === $input ) {
            return false;
        }
        $order = ltrim( trim( $orderNumber ), '#' );
        return $input === $order
            && 0 === strcasecmp( trim( $inputEmail ), trim( $orderEmail ) );
    }
}
