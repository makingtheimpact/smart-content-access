<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function sca_get_options(): array {
    $defaults = [
        'global_mp_ids'    => '',
        'global_require'   => 'any',
        'default_behavior' => 'loggedin',
    ];

    $options = get_option( 'sca_options', [] );
    $options = is_array( $options ) ? $options : [];

    return wp_parse_args( $options, $defaults );
}

function sca_csv_to_ints( $csv ): array {
    if ( is_array( $csv ) ) {
        $csv = implode( ',', $csv );
    }

    $out = [];
    foreach ( array_filter( array_map( 'trim', explode( ',', (string) $csv ) ) ) as $value ) {
        $value = (int) $value;
        if ( $value > 0 ) {
            $out[] = $value;
        }
    }

    return array_values( array_unique( $out ) );
}

function sca_csv_to_strings( $csv ): array {
    if ( is_array( $csv ) ) {
        $csv = implode( ',', $csv );
    }

    $out = [];
    foreach ( array_filter( array_map( 'trim', explode( ',', (string) $csv ) ) ) as $value ) {
        if ( $value !== '' ) {
            $out[] = sanitize_key( $value );
        }
    }

    return array_values( array_unique( $out ) );
}

function sca_content_has_shortcodes( string $content ): bool {
    return has_shortcode( $content, 'sca_gate' )
        || has_shortcode( $content, 'sca_render' )
        || has_shortcode( $content, 'sca_member' )
        || has_shortcode( $content, 'sca_guest' );
}

add_filter( 'the_content', static function( $content ) {
    if ( sca_content_has_shortcodes( (string) $content ) ) {
        $GLOBALS['sca_should_bust_cache'] = true;
    }

    return $content;
}, 0 );

add_action( 'template_redirect', static function() {
    static $sent = false;

    if ( $sent || is_admin() || headers_sent() ) {
        return;
    }

    $should_send = ! empty( $GLOBALS['sca_should_bust_cache'] );

    if ( ! $should_send ) {
        global $post;
        if ( $post instanceof \WP_Post ) {
            $should_send = sca_content_has_shortcodes( (string) $post->post_content );
        }
    }

    if ( ! $should_send ) {
        return;
    }

    $sent = true;

    nocache_headers();
    header( 'Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0' );
    header( 'Pragma: no-cache' );
    header( 'Vary: Cookie', false );
}, 0 );
