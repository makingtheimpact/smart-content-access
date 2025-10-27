<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Smart Content Access - Elementor Content Gate Widget
 * 
 * Visual widget for gating content based on user permissions
 */
class SCA_Elementor_Content_Gate_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name(): string {
        return 'sca_content_gate';
    }

    /**
     * Get widget title
     */
    public function get_title(): string {
        return __( 'SCA Content Gate', 'smart-content-access' );
    }

    /**
     * Get widget icon
     */
    public function get_icon(): string {
        return 'eicon-lock-user';
    }

    /**
     * Get widget categories
     */
    public function get_categories(): array {
        return [ 'general' ];
    }

    /**
     * Register widget controls
     */
    protected function register_controls(): void {
        // Access Control Section
        $this->start_controls_section(
            'access_control_section',
            [
                'label' => __( 'Access Control', 'smart-content-access' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // MemberPress Product IDs
        $this->add_control(
            'mp_ids',
            [
                'label'       => __( 'MemberPress Product IDs', 'smart-content-access' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'description' => __( 'Comma-separated product IDs', 'smart-content-access' ),
                'default'     => '',
            ]
        );

        // Roles
        $this->add_control(
            'roles',
            [
                'label'       => __( 'Required User Roles', 'smart-content-access' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'description' => __( 'Comma-separated role slugs (e.g., subscriber, administrator)', 'smart-content-access' ),
                'default'     => '',
            ]
        );

        // Users
        $this->add_control(
            'users',
            [
                'label'       => __( 'Specific User IDs', 'smart-content-access' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'description' => __( 'Comma-separated user IDs', 'smart-content-access' ),
                'default'     => '',
            ]
        );

        // Require Logic
        $this->add_control(
            'require',
            [
                'label'   => __( 'Match Logic', 'smart-content-access' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'any' => __( 'Match ANY (OR)', 'smart-content-access' ),
                    'all' => __( 'Match ALL (AND)', 'smart-content-access' ),
                ],
                'default' => 'any',
            ]
        );

        // Invert Logic
        $this->add_control(
            'invert',
            [
                'label'     => __( 'Invert Result', 'smart-content-access' ),
                'type'      => \Elementor\Controls_Manager::SWITCHER,
                'label_on'  => __( 'Yes', 'smart-content-access' ),
                'label_off' => __( 'No', 'smart-content-access' ),
                'default'   => '',
            ]
        );

        $this->end_controls_section();

        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'smart-content-access' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Content to show when authorized
        $this->add_control(
            'authorized_content',
            [
                'label'   => __( 'Authorized Content', 'smart-content-access' ),
                'type'    => \Elementor\Controls_Manager::WYSIWYG,
                'default' => __( 'This content is visible to authorized users only.', 'smart-content-access' ),
            ]
        );

        // Content to show when NOT authorized (optional)
        $this->add_control(
            'unauthorized_content',
            [
                'label'   => __( 'Unauthorized Content (Optional)', 'smart-content-access' ),
                'type'    => \Elementor\Controls_Manager::WYSIWYG,
                'default' => '',
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __( 'Style', 'smart-content-access' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label'     => __( 'Text Color', 'smart-content-access' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sca-content-gate' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'typography',
                'selector' => '{{WRAPPER}} .sca-content-gate',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend
     */
    protected function render(): void {
        $settings = $this->get_settings_for_display();

        // Build auth args
        $auth_args = [
            'mp_product_ids' => ! empty( $settings['mp_ids'] ) ? explode( ',', str_replace( ' ', '', $settings['mp_ids'] ) ) : [],
            'roles'          => ! empty( $settings['roles'] ) ? explode( ',', str_replace( ' ', '', $settings['roles'] ) ) : [],
            'users'          => ! empty( $settings['users'] ) ? explode( ',', str_replace( ' ', '', $settings['users'] ) ) : [],
            'require'        => ! empty( $settings['require'] ) ? $settings['require'] : 'any',
            'invert'         => ! empty( $settings['invert'] ),
        ];

        // Sanitize and convert to proper format
        $auth_args['mp_product_ids'] = array_map( 'intval', $auth_args['mp_product_ids'] );
        $auth_args['roles']          = array_map( 'trim', $auth_args['roles'] );
        $auth_args['users']          = array_map( 'intval', $auth_args['users'] );
        $auth_args['require']        = in_array( $auth_args['require'], [ 'any', 'all' ], true ) ? $auth_args['require'] : 'any';

        // Check authorization
        $is_authorized = SCA_Engine::is_authorized( $auth_args );

        // Display appropriate content
        if ( $is_authorized ) {
            echo '<div class="sca-content-gate sca-authorized">';
            // Process shortcodes and sanitize
            echo do_shortcode( wp_kses_post( $settings['authorized_content'] ) );
            echo '</div>';
        } elseif ( ! empty( $settings['unauthorized_content'] ) ) {
            echo '<div class="sca-content-gate sca-unauthorized">';
            // Process shortcodes and sanitize
            echo do_shortcode( wp_kses_post( $settings['unauthorized_content'] ) );
            echo '</div>';
        }

        // Mark as dynamic to prevent caching
        $GLOBALS['sca_should_bust_cache'] = true;
    }

    /**
     * Render widget output in the editor
     */
    protected function content_template(): void {
        ?>
        <div class="sca-content-gate">
            <?php echo esc_html__( 'Content Gate - Preview is disabled in editor. Check frontend for authorization.', 'smart-content-access' ); ?>
        </div>
        <?php
    }
}

