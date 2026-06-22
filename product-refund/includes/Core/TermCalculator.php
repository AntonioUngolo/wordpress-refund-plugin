<?php
namespace ProductRefund\Core;

use DateTimeImmutable;

class TermCalculator {

    public const WITHDRAWAL_DAYS = 14;

    /**
     * @return array{date: DateTimeImmutable, source: string}
     */
    public static function resolveReferenceDate(
        ?DateTimeImmutable $manualDelivery,
        ?DateTimeImmutable $completed,
        ?DateTimeImmutable $paid,
        ?DateTimeImmutable $created,
        int $businessDays
    ): array {
        if ( $manualDelivery instanceof DateTimeImmutable ) {
            return array(
                'date'   => $manualDelivery,
                'source' => 'manuale',
            );
        }

        $anchor = $completed ?? $paid ?? $created;
        if ( ! $anchor instanceof DateTimeImmutable ) {
            $anchor = new DateTimeImmutable( '@0' );
        }

        return array(
            'date'   => BusinessDays::add( $anchor, $businessDays ),
            'source' => 'stimata',
        );
    }

    /**
     * @return array{days_elapsed: int, within_term: bool}
     */
    public static function evaluate( DateTimeImmutable $reference, DateTimeImmutable $submission ): array {
        if ( $submission < $reference ) {
            return array(
                'days_elapsed' => 0,
                'within_term'  => true,
            );
        }
        $days = (int) $reference->diff( $submission )->days;
        return array(
            'days_elapsed' => $days,
            'within_term'  => $days <= self::WITHDRAWAL_DAYS,
        );
    }
}
