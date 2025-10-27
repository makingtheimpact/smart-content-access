<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( '\FLBuilder' ) ) {
    return;
}

/**
 * Smart Content Access - Beaver Builder Integration
 * 
 * Registers native Beaver Builder modules for content gating
 */
class SCA_Beaver_Integration {

    public static function init(): void {
        add_action( 'init', [ __CLASS__, 'register_modules' ] );
    }

    /**
     * Register Beaver Builder modules
     */
    public static function register_modules(): void {
        if ( class_exists( '\FLBuilder' ) ) {
            require_once SCA_PATH . 'includes/integrations/beaver/module-sca-content-gate.php';
            
            \FLBuilder::register_module( 'SCA_Beaver_Content_Gate_Module', [
                'access_control' => [
                    'title'    => __( 'Access Control', 'smart-content-access' ),
                    'sections' => [
                        'access' => [
                            'title'  => '',
                            'fields' => [
                                'mp_ids'  => [
                                    'type'        => 'text',
                                    'label'       => __( 'MemberPress Product IDs', 'smart-content-access' ),
                                    'description' => __( 'Comma-separated product IDs', 'smart-content-access' ),
                                ],
                                'roles'   => [
                                    'type'        => 'text',
                                    'label'       => __( 'Required User Roles', 'smart-content-access' ),
                                    'description' => __( 'Comma-separated role slugs', 'smart-content-access' ),
                                ],
                                'users'   => [
                                    'type'        => 'text',
                                    'label'       => __( 'Specific User IDs', 'smart-content-access' ),
                                    'description' => __( 'Comma-separated user IDs', 'smart-content-access' ),
                                ],
                                'require' => [
                                    'type'    => 'select',
                                    'label'   => __( 'Match Logic', 'smart-content-access' ),
                                    'options' => [
                                        'any' => __( 'Match ANY (OR)', 'smart-content-access' ),
                                        'all' => __( 'Match ALL (AND)', 'smart-content-access' ),
                                    ],
                                    'default' => 'any',
                                ],
                                'invert'  => [
                                    'type'        => 'select',
                                    'label'       => __( 'Invert Result', 'smart-content-access' ),
                                    'options'     => [
                                        '0' => __( 'No', 'smart-content-access' ),
                                        '1' => __( 'Yes', 'smart-content-access' ),
                                    ],
                                    'default'     => '0',
                                ],
                            ],
                        ],
                    ],
                ],
                'content'   => [
                    'title'    => __( 'Content', 'smart-content-access' ),
                    'sections' => [
                        'content' => [
                            'title'  => '',
                            'fields' => [
                                'authorized_content'   => [
                                    'type'  => 'editor',
                                    'label' => __( 'Authorized Content', 'smart-content-access' ),
                                ],
                                'unauthorized_content' => [
                                    'type'  => 'editor',
                                    'label' => __( 'Unauthorized Content (Optional)', 'smart-content-access' ),
                                ],
                            ],
                        ],
                    ],
                ],
            ] );
        }
    }
}

SCA_Beaver_Integration::init();
