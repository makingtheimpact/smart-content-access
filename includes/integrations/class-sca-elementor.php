<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
    return;
}

/**
 * Smart Content Access - Elementor Integration
 * 
 * Registers native Elementor widgets for content gating
 */
class SCA_Elementor_Integration {

    public static function init(): void {
        // Register Elementor widgets
        add_action( 'elementor/widgets/widgets_registered', [ __CLASS__, 'register_widgets' ] );
        
        // Force dynamic rendering (no cache)
        add_action( 'elementor/frontend/before_render', [ __CLASS__, 'set_as_dynamic' ] );
    }

    /**
     * Register custom Elementor widgets
     */
    public static function register_widgets(): void {
        require_once SCA_PATH . 'includes/integrations/elementor/widget-sca-content-gate.php';
        
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new SCA_Elementor_Content_Gate_Widget() );
    }

    /**
     * Mark SCA widgets as dynamic to prevent caching
     */
    public static function set_as_dynamic( $element ): void {
        if ( isset( $element->get_name ) && strpos( $element->get_name(), 'sca_' ) === 0 ) {
            $GLOBALS['sca_should_bust_cache'] = true;
        }
    }
}

SCA_Elementor_Integration::init();
