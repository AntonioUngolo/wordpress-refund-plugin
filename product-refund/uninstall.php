<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Remove sequence counters. Request posts and meta are intentionally kept
// (legal register); delete manually if required.
global $wpdb;
$like = $wpdb->esc_like( 'product_refund_seq_' ) . '%';
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) );
delete_option( 'product_refund_settings' );
