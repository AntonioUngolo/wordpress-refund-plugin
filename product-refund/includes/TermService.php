<?php
namespace ProductRefund;

use ProductRefund\Core\TermCalculator;
use DateTimeImmutable;

class TermService {

    const DELIVERY_META = '_recesso_delivery_date';

    /**
     * Build the term evaluation for an order at submission time.
     *
     * @param \WC_Order $order
     * @return array{reference_date: DateTimeImmutable, source: string, within_term: bool, days_elapsed: int}
     */
    public static function evaluate_for_order( $order ): array {
        $manual = self::to_date( $order->get_meta( self::DELIVERY_META ) );

        $completed = $order->get_date_completed() ? self::to_date( $order->get_date_completed()->date( 'Y-m-d H:i:s' ) ) : null;
        $paid      = $order->get_date_paid() ? self::to_date( $order->get_date_paid()->date( 'Y-m-d H:i:s' ) ) : null;
        $created   = $order->get_date_created() ? self::to_date( $order->get_date_created()->date( 'Y-m-d H:i:s' ) ) : null;

        $resolved   = TermCalculator::resolveReferenceDate( $manual, $completed, $paid, $created, Settings::business_days() );
        $submission = new DateTimeImmutable( current_time( 'mysql' ), wp_timezone() );
        $eval       = TermCalculator::evaluate( $resolved['date'], $submission );

        return array(
            'reference_date' => $resolved['date'],
            'source'         => $resolved['source'],
            'within_term'    => $eval['within_term'],
            'days_elapsed'   => $eval['days_elapsed'],
        );
    }

    private static function to_date( $value ): ?DateTimeImmutable {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return null;
        }
        try {
            return new DateTimeImmutable( $value, wp_timezone() );
        } catch ( \Exception $e ) {
            return null;
        }
    }
}
