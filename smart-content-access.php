<?php
/**
 * Plugin Name: Smart Content Access
 * Description: Cache-agnostic content gating via shortcodes. Supports MemberPress (optional), global plan IDs, roles, and user rules. Works with Elementor & Beaver via shortcodes.
 * Version: 1.0.0
 * Author: Making The Impact LLC
 * License: GPL-2.0+
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SCA_VERSION', '1.0.0' );
define( 'SCA_PATH', plugin_dir_path( __FILE__ ) );
define( 'SCA_URL', plugin_dir_url( __FILE__ ) );

require_once SCA_PATH . 'includes/helpers.php';
require_once SCA_PATH . 'includes/class-sca-engine.php';
require_once SCA_PATH . 'includes/class-sca-memberpress.php';
require_once SCA_PATH . 'includes/class-sca-menus.php';
require_once SCA_PATH . 'includes/class-sca-shortcodes.php';
require_once SCA_PATH . 'includes/class-sca-settings.php';

add_action( 'plugins_loaded', static function() {
    SCA_Settings::init();
    SCA_Shortcodes::init();
    SCA_Menus::init();

    $el = SCA_PATH . 'includes/integrations/class-sca-elementor.php';
    if ( file_exists( $el ) ) {
        require_once $el;
    }

    $bb = SCA_PATH . 'includes/integrations/class-sca-beaver.php';
    if ( file_exists( $bb ) ) {
        require_once $bb;
    }
} );
