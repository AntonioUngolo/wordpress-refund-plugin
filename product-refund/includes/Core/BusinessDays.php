<?php
namespace ProductRefund\Core;

use DateTimeImmutable;
use DateInterval;

class BusinessDays {

    /**
     * Add N business days (Mon-Fri) to a date. Holidays are not considered.
     */
    public static function add( DateTimeImmutable $start, int $days ): DateTimeImmutable {
        if ( $days < 0 ) {
            throw new \InvalidArgumentException( 'BusinessDays::add() requires a non-negative number of days.' );
        }
        $result = $start;
        $added  = 0;
        while ( $added < $days ) {
            $result = $result->add( new DateInterval( 'P1D' ) );
            if ( (int) $result->format( 'N' ) < 6 ) {
                $added++;
            }
        }
        return $result;
    }
}
