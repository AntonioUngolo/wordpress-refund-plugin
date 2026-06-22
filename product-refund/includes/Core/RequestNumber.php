<?php
namespace ProductRefund\Core;

class RequestNumber {

    public static function format( int $sequence, int $year ): string {
        return sprintf( 'REC-%04d-%04d', $year, $sequence );
    }
}
