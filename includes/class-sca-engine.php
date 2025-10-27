<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SCA_Engine {
    private static $options_cache = null;

    public static function is_authorized( array $args = [] ): bool {
        $current_user_id = get_current_user_id();
        $defaults        = [
            'user_id'        => $current_user_id,
            'mp_product_ids' => [],
            'require'        => 'any',
            'roles'          => [],
            'users'          => [],
            'invert'         => false,
        ];

        $provided_require = ! empty( $args['__provided_require'] );
        unset( $args['__provided_require'] );

        $args = wp_parse_args( $args, $defaults );

        $args['mp_product_ids'] = array_map( 'intval', (array) $args['mp_product_ids'] );
        $args['roles']          = array_map( 'sanitize_key', (array) $args['roles'] );
        $args['users']          = array_map( 'intval', (array) $args['users'] );
        $args['require']        = strtolower( (string) $args['require'] ) === 'all' ? 'all' : 'any';
        $args['invert']         = (bool) $args['invert'];

        // Cache options to avoid repeated database calls on same request
        if ( null === self::$options_cache ) {
            self::$options_cache = sca_get_options();
        }
        $options = self::$options_cache;

        if ( empty( $args['mp_product_ids'] ) && ! empty( $options['global_mp_ids'] ) ) {
            $args['mp_product_ids'] = sca_csv_to_ints( $options['global_mp_ids'] );
            if ( empty( $args['mp_product_ids'] ) ) {
                $args['mp_product_ids'] = [];
            }
            if ( ! $provided_require ) {
                $args['require'] = $options['global_require'] === 'all' ? 'all' : 'any';
            }
        }

        $criteria_present = ! empty( $args['mp_product_ids'] ) || ! empty( $args['roles'] ) || ! empty( $args['users'] );

        if ( ! $criteria_present ) {
            $default = $options['default_behavior'] === 'open' ? true : is_user_logged_in();
            return $args['invert'] ? ! $default : $default;
        }

        $user_id = (int) $args['user_id'];
        $checks  = [];

        if ( ! empty( $args['users'] ) ) {
            $checks[] = in_array( $user_id, $args['users'], true );
        }

        if ( ! empty( $args['roles'] ) ) {
            $user       = $user_id > 0 ? get_userdata( $user_id ) : false;
            $user_roles = $user && isset( $user->roles ) && is_array( $user->roles ) ? $user->roles : [];

            if ( $args['require'] === 'all' ) {
                $checks[] = empty( array_diff( $args['roles'], $user_roles ) );
            } else {
                $checks[] = (bool) array_intersect( $args['roles'], $user_roles );
            }
        }

        if ( ! empty( $args['mp_product_ids'] ) ) {
            $checks[] = SCA_MemberPress::user_has_access( $user_id, $args['mp_product_ids'], $args['require'] );
        }

        if ( empty( $checks ) ) {
            $allowed = false;
        } elseif ( $args['require'] === 'all' ) {
            $allowed = ! in_array( false, $checks, true );
        } else {
            $allowed = in_array( true, $checks, true );
        }

        // Allow filtering before invert logic
        $allowed = apply_filters( 'sca_is_authorized', $allowed, $args, $checks );
        
        // Debug logging
        if ( function_exists( 'sca_debug_log' ) ) {
            sca_debug_log( [
                'user_id' => $user_id,
                'args'    => $args,
                'checks'  => $checks,
                'allowed' => $allowed,
            ], 'debug' );
        }

        return $args['invert'] ? ! $allowed : $allowed;
    }
}
