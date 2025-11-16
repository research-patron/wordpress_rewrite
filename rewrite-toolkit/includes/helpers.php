<?php
/**
 * Helper functions for Rewrite Toolkit.
 */

/**
 * Sanitize rewrite rule data.
 *
 * @param array $data Raw input.
 *
 * @return array
 */
function rewrite_toolkit_sanitize_rule( $data ) {
    $rule = array(
        'id'       => ! empty( $data['id'] ) ? sanitize_key( $data['id'] ) : sanitize_key( uniqid( 'rule_', true ) ),
        'label'    => sanitize_text_field( $data['label'] ?? '' ),
        'pattern'  => trim( wp_unslash( $data['pattern'] ?? '' ) ),
        'query'    => trim( wp_unslash( $data['query'] ?? '' ) ),
        'priority' => in_array( $data['priority'] ?? 'top', array( 'top', 'bottom' ), true ) ? $data['priority'] : 'top',
        'enabled'  => ! empty( $data['enabled'] ),
    );

    return $rule;
}

/**
 * Sanitize rewrite tag data.
 *
 * @param array $data Raw input.
 *
 * @return array
 */
function rewrite_toolkit_sanitize_tag( $data ) {
    $tag = array(
        'id'        => ! empty( $data['id'] ) ? sanitize_key( $data['id'] ) : sanitize_key( uniqid( 'tag_', true ) ),
        'name'      => sanitize_key( $data['name'] ?? '' ),
        'regex'     => trim( wp_unslash( $data['regex'] ?? '' ) ),
        'query_var' => sanitize_key( $data['query_var'] ?? '' ),
    );

    return $tag;
}

/**
 * Helper for formatting timestamps.
 *
 * @param int $timestamp Timestamp.
 *
 * @return string
 */
function rewrite_toolkit_format_datetime( $timestamp ) {
    if ( empty( $timestamp ) ) {
        return __( 'Not flushed yet', 'rewrite-toolkit' );
    }

    return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
}
