<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SCA_Menus {
    public static function init(): void {
        add_filter( 'wp_setup_nav_menu_item', [ __CLASS__, 'load_item_meta' ] );
        add_action( 'wp_update_nav_menu_item', [ __CLASS__, 'save_item_meta' ], 10, 3 );
        add_filter( 'wp_nav_menu_objects', [ __CLASS__, 'filter_menu_items' ], 25, 2 );
        add_action( 'wp_nav_menu_item_custom_fields', [ __CLASS__, 'render_menu_fields' ], 10, 4 );
    }

    public static function load_item_meta( $menu_item ) {
        $menu_item->_sca_menu_mode    = get_post_meta( $menu_item->ID, '_sca_menu_mode', true );
        $menu_item->_sca_menu_mp_ids  = get_post_meta( $menu_item->ID, '_sca_menu_mp_ids', true );
        $menu_item->_sca_menu_roles   = get_post_meta( $menu_item->ID, '_sca_menu_roles', true );
        $menu_item->_sca_menu_users   = get_post_meta( $menu_item->ID, '_sca_menu_users', true );
        $menu_item->_sca_menu_require = get_post_meta( $menu_item->ID, '_sca_menu_require', true );
        $menu_item->_sca_menu_invert  = get_post_meta( $menu_item->ID, '_sca_menu_invert', true );

        return $menu_item;
    }

    public static function save_item_meta( $menu_id, $menu_item_db_id, $args ): void {
        $mode_field   = 'sca_menu_mode_' . $menu_item_db_id;
        $mp_field     = 'sca_menu_membership_ids_' . $menu_item_db_id;
        $roles_field  = 'sca_menu_roles_' . $menu_item_db_id;
        $users_field  = 'sca_menu_users_' . $menu_item_db_id;
        $require_field = 'sca_menu_require_' . $menu_item_db_id;
        $invert_field  = 'sca_menu_invert_' . $menu_item_db_id;

        $mode = isset( $_POST[ $mode_field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $mode_field ] ) ) : '';
        $mode = in_array( $mode, [ 'logged-in', 'logged-out' ], true ) ? $mode : '';

        $mp_ids = isset( $_POST[ $mp_field ] ) ? wp_unslash( $_POST[ $mp_field ] ) : '';
        $roles  = isset( $_POST[ $roles_field ] ) ? wp_unslash( $_POST[ $roles_field ] ) : '';
        $users  = isset( $_POST[ $users_field ] ) ? wp_unslash( $_POST[ $users_field ] ) : '';

        $mp_ids_clean = sca_csv_to_ints( $mp_ids );
        $roles_clean  = sca_csv_to_strings( $roles );
        $users_clean  = sca_csv_to_ints( $users );

        $require = isset( $_POST[ $require_field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $require_field ] ) ) : '';
        $require = in_array( $require, [ 'any', 'all' ], true ) ? $require : '';

        $invert = isset( $_POST[ $invert_field ] ) ? '1' : '';

        update_post_meta( $menu_item_db_id, '_sca_menu_mode', $mode );
        update_post_meta( $menu_item_db_id, '_sca_menu_mp_ids', $mp_ids_clean ? implode( ', ', $mp_ids_clean ) : '' );
        update_post_meta( $menu_item_db_id, '_sca_menu_roles', $roles_clean ? implode( ', ', $roles_clean ) : '' );
        update_post_meta( $menu_item_db_id, '_sca_menu_users', $users_clean ? implode( ', ', $users_clean ) : '' );
        update_post_meta( $menu_item_db_id, '_sca_menu_require', $require );
        update_post_meta( $menu_item_db_id, '_sca_menu_invert', $invert );
    }

    public static function filter_menu_items( $items, $args ) {
        if ( empty( $items ) || ! is_array( $items ) ) {
            return $items;
        }

        $has_gating = false;
        $user_id    = get_current_user_id();

        foreach ( $items as $index => $item ) {
            $mode    = $item->_sca_menu_mode ?? get_post_meta( $item->ID, '_sca_menu_mode', true );
            $mp_ids  = $item->_sca_menu_mp_ids ?? get_post_meta( $item->ID, '_sca_menu_mp_ids', true );
            $roles   = $item->_sca_menu_roles ?? get_post_meta( $item->ID, '_sca_menu_roles', true );
            $users   = $item->_sca_menu_users ?? get_post_meta( $item->ID, '_sca_menu_users', true );
            $require = $item->_sca_menu_require ?? get_post_meta( $item->ID, '_sca_menu_require', true );
            $invert  = $item->_sca_menu_invert ?? get_post_meta( $item->ID, '_sca_menu_invert', true );

            $mode = in_array( $mode, [ 'logged-in', 'logged-out' ], true ) ? $mode : '';
            $invert = $invert === '1';

            if ( $mode ) {
                $has_gating = true;

                if ( 'logged-in' === $mode && ! is_user_logged_in() ) {
                    unset( $items[ $index ] );
                    continue;
                }

                if ( 'logged-out' === $mode && is_user_logged_in() ) {
                    unset( $items[ $index ] );
                    continue;
                }
            }

            $mp_ids_array = sca_csv_to_ints( $mp_ids );
            $roles_array  = sca_csv_to_strings( $roles );
            $users_array  = sca_csv_to_ints( $users );

            $has_advanced_rules = ! empty( $mp_ids_array ) || ! empty( $roles_array ) || ! empty( $users_array ) || $invert;

            if ( ! $has_advanced_rules ) {
                continue;
            }

            $has_gating = true;

            $args_for_engine = [
                'user_id'        => $user_id,
                'mp_product_ids' => $mp_ids_array,
                'roles'          => $roles_array,
                'users'          => $users_array,
                'invert'         => $invert,
            ];

            if ( in_array( $require, [ 'any', 'all' ], true ) ) {
                $args_for_engine['require']            = $require;
                $args_for_engine['__provided_require'] = true;
            }

            if ( ! SCA_Engine::is_authorized( $args_for_engine ) ) {
                unset( $items[ $index ] );
            }
        }

        if ( $has_gating ) {
            $GLOBALS['sca_should_bust_cache'] = true;
        }

        return array_values( $items );
    }

    public static function render_menu_fields( $item_id, $item, $depth, $args ): void {
        $mode    = get_post_meta( $item_id, '_sca_menu_mode', true );
        $mp_ids  = get_post_meta( $item_id, '_sca_menu_mp_ids', true );
        $roles   = get_post_meta( $item_id, '_sca_menu_roles', true );
        $users   = get_post_meta( $item_id, '_sca_menu_users', true );
        $require = get_post_meta( $item_id, '_sca_menu_require', true );
        $invert  = get_post_meta( $item_id, '_sca_menu_invert', true );
        ?>
        <div class="sca-menu-visibility">
            <p class="description description-wide">
                <label for="sca_menu_mode_<?php echo esc_attr( $item_id ); ?>">
                    <?php esc_html_e( 'Smart Content Access: Quick rule', 'smart-content-access' ); ?><br />
                    <select id="sca_menu_mode_<?php echo esc_attr( $item_id ); ?>" name="sca_menu_mode_<?php echo esc_attr( $item_id ); ?>">
                        <option value="" <?php selected( $mode, '' ); ?>><?php esc_html_e( '— Visible to everyone —', 'smart-content-access' ); ?></option>
                        <option value="logged-in" <?php selected( $mode, 'logged-in' ); ?>><?php esc_html_e( 'Logged-in users only', 'smart-content-access' ); ?></option>
                        <option value="logged-out" <?php selected( $mode, 'logged-out' ); ?>><?php esc_html_e( 'Logged-out visitors only', 'smart-content-access' ); ?></option>
                    </select>
                </label>
            </p>
            <p class="description description-wide">
                <label for="sca_menu_membership_ids_<?php echo esc_attr( $item_id ); ?>">
                    <?php esc_html_e( 'MemberPress Product IDs (comma separated)', 'smart-content-access' ); ?><br />
                    <input type="text" class="widefat" id="sca_menu_membership_ids_<?php echo esc_attr( $item_id ); ?>" name="sca_menu_membership_ids_<?php echo esc_attr( $item_id ); ?>" value="<?php echo esc_attr( $mp_ids ); ?>" />
                </label>
            </p>
            <p class="description description-wide">
                <label for="sca_menu_roles_<?php echo esc_attr( $item_id ); ?>">
                    <?php esc_html_e( 'Required user roles (slugs, comma separated)', 'smart-content-access' ); ?><br />
                    <input type="text" class="widefat" id="sca_menu_roles_<?php echo esc_attr( $item_id ); ?>" name="sca_menu_roles_<?php echo esc_attr( $item_id ); ?>" value="<?php echo esc_attr( $roles ); ?>" />
                </label>
            </p>
            <p class="description description-wide">
                <label for="sca_menu_users_<?php echo esc_attr( $item_id ); ?>">
                    <?php esc_html_e( 'Specific user IDs (comma separated)', 'smart-content-access' ); ?><br />
                    <input type="text" class="widefat" id="sca_menu_users_<?php echo esc_attr( $item_id ); ?>" name="sca_menu_users_<?php echo esc_attr( $item_id ); ?>" value="<?php echo esc_attr( $users ); ?>" />
                </label>
            </p>
            <p class="description description-wide">
                <label for="sca_menu_require_<?php echo esc_attr( $item_id ); ?>">
                    <?php esc_html_e( 'Matching logic for IDs/roles/users', 'smart-content-access' ); ?><br />
                    <select id="sca_menu_require_<?php echo esc_attr( $item_id ); ?>" name="sca_menu_require_<?php echo esc_attr( $item_id ); ?>">
                        <option value="" <?php selected( $require, '' ); ?>><?php esc_html_e( 'Inherit global default', 'smart-content-access' ); ?></option>
                        <option value="any" <?php selected( $require, 'any' ); ?>><?php esc_html_e( 'Match ANY rule', 'smart-content-access' ); ?></option>
                        <option value="all" <?php selected( $require, 'all' ); ?>><?php esc_html_e( 'Match ALL rules', 'smart-content-access' ); ?></option>
                    </select>
                </label>
            </p>
            <p class="description description-wide">
                <label for="sca_menu_invert_<?php echo esc_attr( $item_id ); ?>">
                    <input type="checkbox" id="sca_menu_invert_<?php echo esc_attr( $item_id ); ?>" name="sca_menu_invert_<?php echo esc_attr( $item_id ); ?>" value="1" <?php checked( $invert, '1' ); ?> />
                    <?php esc_html_e( 'Invert result (show menu item when conditions fail)', 'smart-content-access' ); ?>
                </label>
            </p>
            <p class="description">
                <?php esc_html_e( 'Leave fields blank to keep the item visible for everyone. MemberPress checks require the MemberPress plugin.', 'smart-content-access' ); ?>
            </p>
        </div>
        <?php
    }
}
