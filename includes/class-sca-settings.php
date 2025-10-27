<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SCA_Settings {
    public static function init(): void {
        add_action( 'admin_init', [ __CLASS__, 'register' ] );
        add_action( 'admin_menu', [ __CLASS__, 'add_menu' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
    }

    public static function register(): void {
        register_setting( 'sca_options_group', 'sca_options', [
            'type'              => 'array',
            'sanitize_callback' => [ __CLASS__, 'sanitize' ],
            'default'           => [],
        ] );

        add_settings_section( 'sca_main', __( 'Smart Content Access', 'smart-content-access' ), '__return_false', 'sca' );

        add_settings_field(
            'global_mp_ids',
            __( 'Global MemberPress Product IDs (CSV)', 'smart-content-access' ),
            [ __CLASS__, 'render_text_field' ],
            'sca',
            'sca_main',
            [ 'key' => 'global_mp_ids' ]
        );

        add_settings_field(
            'global_require',
            __( 'Global Require', 'smart-content-access' ),
            [ __CLASS__, 'render_select_field' ],
            'sca',
            'sca_main',
            [
                'key'     => 'global_require',
                'choices' => [
                    'any' => __( 'Any', 'smart-content-access' ),
                    'all' => __( 'All', 'smart-content-access' ),
                ],
            ]
        );

        add_settings_field(
            'default_behavior',
            __( 'Default Behavior (no rules)', 'smart-content-access' ),
            [ __CLASS__, 'render_select_field' ],
            'sca',
            'sca_main',
            [
                'key'     => 'default_behavior',
                'choices' => [
                    'loggedin' => __( 'Logged-in only', 'smart-content-access' ),
                    'open'     => __( 'Open to all', 'smart-content-access' ),
                ],
            ]
        );
    }

    public static function sanitize( $input ): array {
        $output = [
            'global_mp_ids'    => '',
            'global_require'   => 'any',
            'default_behavior' => 'loggedin',
        ];

        $output['global_mp_ids'] = sanitize_text_field( $input['global_mp_ids'] ?? '' );

        $global_require = strtolower( (string) ( $input['global_require'] ?? 'any' ) );
        $output['global_require'] = in_array( $global_require, [ 'any', 'all' ], true ) ? $global_require : 'any';

        $default_behavior = strtolower( (string) ( $input['default_behavior'] ?? 'loggedin' ) );
        $output['default_behavior'] = in_array( $default_behavior, [ 'loggedin', 'open' ], true ) ? $default_behavior : 'loggedin';

        return $output;
    }

    public static function add_menu(): void {
        add_options_page(
            __( 'Smart Content Access', 'smart-content-access' ),
            __( 'Smart Content Access', 'smart-content-access' ),
            'manage_options',
            'sca',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function enqueue_assets( string $hook ): void {
        if ( 'settings_page_sca' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'sca-admin', SCA_URL . 'assets/admin.css', [], SCA_VERSION );
    }

    public static function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap" id="sca-settings">
            <h1><?php esc_html_e( 'Smart Content Access', 'smart-content-access' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'sca_options_group' );
                do_settings_sections( 'sca' );
                submit_button();
                ?>
            </form>
            <hr />
            <h2><?php esc_html_e( 'Usage', 'smart-content-access' ); ?></h2>
            <pre>[sca_gate mp_ids="123,456" require="any" roles="subscriber" users="9050"]<?php esc_html_e( 'Hidden for guests', 'smart-content-access' ); ?>[/sca_gate]</pre>
            <pre>[sca_render source="post_content"]</pre>
            <pre>[sca_render source="featured_image" image_size="large"]</pre>
            <pre>[sca_render source="shortcode" short='[render_secure_video_embed_aw]']</pre>
            <pre>[sca_member]<?php esc_html_e( 'For authorized', 'smart-content-access' ); ?>[/sca_member]   [sca_guest]<?php esc_html_e( 'For guests', 'smart-content-access' ); ?>[/sca_guest]</pre>
        </div>
        <?php
    }

    public static function render_text_field( array $args ): void {
        $options = sca_get_options();
        $key     = $args['key'];
        printf(
            '<input type="text" name="sca_options[%1$s]" value="%2$s" class="regular-text" />',
            esc_attr( $key ),
            esc_attr( $options[ $key ] ?? '' )
        );
    }

    public static function render_select_field( array $args ): void {
        $options = sca_get_options();
        $key     = $args['key'];
        $choices = (array) ( $args['choices'] ?? [] );

        echo '<select name="sca_options[' . esc_attr( $key ) . ']">';
        foreach ( $choices as $value => $label ) {
            printf(
                '<option value="%1$s" %2$s>%3$s</option>',
                esc_attr( $value ),
                selected( $options[ $key ] ?? '', $value, false ),
                esc_html( $label )
            );
        }
        echo '</select>';
    }
}
