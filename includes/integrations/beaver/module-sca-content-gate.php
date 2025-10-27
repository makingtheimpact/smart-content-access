<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( '\FLBuilderModule' ) ) {
    return;
}

/**
 * Smart Content Access - Beaver Builder Content Gate Module
 * 
 * Visual module for gating content based on user permissions
 */
class SCA_Beaver_Content_Gate_Module extends \FLBuilderModule {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct( [
            'name'            => __( 'SCA Content Gate', 'smart-content-access' ),
            'description'     => __( 'Show or hide content based on user permissions.', 'smart-content-access' ),
            'category'        => __( 'Basic Modules', 'smart-content-access' ),
            'icon'            => 'lock.svg',
            'partial_refresh' => false, // Disable partial refresh to ensure dynamic rendering
        ] );
    }

    /**
     * Render module output on the frontend
     */
    public function render(): void {
        // Build auth args
        $settings = $this->settings;
        
        $auth_args = [
            'mp_product_ids' => ! empty( $settings->mp_ids ) ? explode( ',', str_replace( ' ', '', $settings->mp_ids ) ) : [],
            'roles'          => ! empty( $settings->roles ) ? explode( ',', str_replace( ' ', '', $settings->roles ) ) : [],
            'users'          => ! empty( $settings->users ) ? explode( ',', str_replace( ' ', '', $settings->users ) ) : [],
            'require'        => ! empty( $settings->require ) ? $settings->require : 'any',
            'invert'         => ! empty( $settings->invert ) && $settings->invert === '1',
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
            echo wp_kses_post( $settings->authorized_content );
            echo '</div>';
        } elseif ( ! empty( $settings->unauthorized_content ) ) {
            echo '<div class="sca-content-gate sca-unauthorized">';
            echo wp_kses_post( $settings->unauthorized_content );
            echo '</div>';
        }

        // Mark as dynamic to prevent caching
        $GLOBALS['sca_should_bust_cache'] = true;
    }
}

