<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SCA_Shortcodes {
    public static function init(): void {
        add_shortcode( 'sca_gate', [ __CLASS__, 'gate' ] );
        add_shortcode( 'sca_render', [ __CLASS__, 'render' ] );
        add_shortcode( 'sca_member', [ __CLASS__, 'member' ] );
        add_shortcode( 'sca_guest', [ __CLASS__, 'guest' ] );
    }

    private static function parse_auth_atts( $atts, string $shortcode = 'sca_gate', array $defaults = [] ): array {
        $defaults = wp_parse_args( $defaults, [
            'mp_ids'  => '',
            'require' => 'any',
            'roles'   => '',
            'users'   => '',
            'invert'  => '0',
        ] );

        $provided_require = is_array( $atts ) && array_key_exists( 'require', $atts );

        $atts = shortcode_atts( $defaults, $atts, $shortcode );

        return [
            'mp_product_ids'     => sca_csv_to_ints( $atts['mp_ids'] ),
            'require'            => strtolower( $atts['require'] ) === 'all' ? 'all' : 'any',
            'roles'              => sca_csv_to_strings( $atts['roles'] ),
            'users'              => sca_csv_to_ints( $atts['users'] ),
            'invert'             => filter_var( $atts['invert'], FILTER_VALIDATE_BOOLEAN ),
            '__provided_require' => $provided_require,
        ];
    }

    public static function gate( $atts = [], $content = '' ): string {
        $args = self::parse_auth_atts( $atts, 'sca_gate' );

        if ( SCA_Engine::is_authorized( $args ) ) {
            return do_shortcode( $content );
        }

        return '';
    }

    public static function member( $atts = [], $content = '' ): string {
        $args = self::parse_auth_atts( $atts, 'sca_member', [ 'invert' => '0' ] );

        if ( SCA_Engine::is_authorized( $args ) ) {
            return do_shortcode( $content );
        }

        return '';
    }

    public static function guest( $atts = [], $content = '' ): string {
        $args = self::parse_auth_atts( $atts, 'sca_guest', [ 'invert' => '1' ] );

        if ( SCA_Engine::is_authorized( $args ) ) {
            return do_shortcode( $content );
        }

        return '';
    }

    public static function render( $atts = [] ): string {
        $raw_atts = $atts;

        $atts = shortcode_atts( [
            'mp_ids'     => '',
            'require'    => 'any',
            'roles'      => '',
            'users'      => '',
            'invert'     => '0',
            'source'     => 'post_content',
            'short'      => '',
            'post_id'    => 'current',
            'image_size' => 'full',
        ], $atts, 'sca_render' );

        $auth_args = self::parse_auth_atts( $raw_atts, 'sca_render' );

        if ( ! SCA_Engine::is_authorized( $auth_args ) ) {
            return '';
        }

        $post_id = $atts['post_id'] === 'current' ? get_the_ID() : (int) $atts['post_id'];
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        $source = sanitize_key( $atts['source'] );

        switch ( $source ) {
            case 'featured_image':
                $thumb_id = get_post_thumbnail_id( $post_id );
                if ( $thumb_id ) {
                    return wp_get_attachment_image( $thumb_id, sanitize_key( $atts['image_size'] ) );
                }
                return '';

            case 'shortcode':
                $raw = (string) $atts['short'];
                if ( $raw === '' ) {
                    return '';
                }
                
                // Strip brackets if provided (for safety)
                $raw = trim( $raw, '[]' );
                
                // Reconstruct the shortcode safely
                $shortcode = '[' . $raw . ']';
                
                return do_shortcode( $shortcode );

            case 'post_excerpt':
                $excerpt = get_post_field( 'post_excerpt', $post_id );
                
                // If no excerpt is set, generate one from content
                if ( empty( $excerpt ) ) {
                    $content = get_post_field( 'post_content', $post_id );
                    
                    // Strip shortcodes to remove embeds, videos, etc.
                    $content = strip_shortcodes( $content );
                    
                    // Strip all HTML tags to get clean text only
                    $content = wp_strip_all_tags( $content );
                    
                    // Remove any remaining URLs (YouTube, Vimeo links, etc.)
                    $content = preg_replace( '#https?://\S+#', '', $content );
                    
                    // Clean up extra whitespace
                    $content = preg_replace( '/\s+/', ' ', $content );
                    $content = trim( $content );
                    
                    // Generate excerpt from cleaned content
                    $excerpt = wp_trim_words( $content, 55, '...' );
                }
                
                return apply_filters( 'the_excerpt', $excerpt );

            case 'post_content':
            default:
                $content = get_post_field( 'post_content', $post_id );
                return apply_filters( 'the_content', (string) $content );
        }
    }
}
