<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SCA_MemberPress {
    public static function is_active(): bool {
        return class_exists( '\MeprUser' );
    }

    public static function user_has_access( int $user_id, array $product_ids, string $require = 'any' ): bool {
        if ( ! self::is_active() || $user_id <= 0 || empty( $product_ids ) ) {
            return false;
        }

        try {
            $user = new \MeprUser( $user_id );
        } catch ( \Throwable $e ) {
            return false;
        }

        $matches = [];

        try {
            if ( method_exists( $user, 'active_product_subscriptions' ) ) {
                foreach ( (array) $user->active_product_subscriptions() as $subscription ) {
                    $product_id = self::extract_product_id( $subscription );
                    if ( $product_id && in_array( $product_id, $product_ids, true ) ) {
                        $matches[ $product_id ] = true;
                    }
                }
            }
        } catch ( \Throwable $e ) {
        }

        try {
            if ( method_exists( $user, 'subscriptions' ) ) {
                foreach ( (array) $user->subscriptions() as $subscription ) {
                    $product_id = self::extract_product_id( $subscription );
                    if ( ! $product_id || ! in_array( $product_id, $product_ids, true ) ) {
                        continue;
                    }

                    if ( self::subscription_is_active_like( $subscription ) ) {
                        $matches[ $product_id ] = true;
                    }
                }
            }
        } catch ( \Throwable $e ) {
        }

        if ( empty( $matches ) ) {
            return false;
        }

        if ( $require === 'all' ) {
            return count( array_intersect( $product_ids, array_keys( $matches ) ) ) === count( $product_ids );
        }

        return true;
    }

    private static function extract_product_id( $subscription ): int {
        $product_id = null;

        if ( is_object( $subscription ) ) {
            if ( isset( $subscription->product_id ) ) {
                $product_id = $subscription->product_id;
            } elseif ( method_exists( $subscription, 'product_id' ) ) {
                try {
                    $product_id = $subscription->product_id();
                } catch ( \Throwable $e ) {
                }
            } elseif ( isset( $subscription->product->ID ) ) {
                $product_id = $subscription->product->ID;
            } elseif ( method_exists( $subscription, 'product' ) ) {
                try {
                    $product = $subscription->product();
                    if ( is_object( $product ) && isset( $product->ID ) ) {
                        $product_id = $product->ID;
                    }
                } catch ( \Throwable $e ) {
                }
            }
        } elseif ( is_array( $subscription ) ) {
            $product_id = $subscription['product_id'] ?? $subscription['ID'] ?? null;
        } elseif ( is_numeric( $subscription ) ) {
            $product_id = (int) $subscription;
        }

        $product_id = (int) $product_id;
        return $product_id > 0 ? $product_id : 0;
    }

    private static function subscription_is_active_like( $subscription ): bool {
        $status          = '';
        $paid_through_ts = 0;

        if ( is_object( $subscription ) ) {
            $status = (string) ( $subscription->status ?? ( method_exists( $subscription, 'status' ) ? $subscription->status() : '' ) );

            if ( isset( $subscription->paid_through ) ) {
                $paid_through_ts = is_numeric( $subscription->paid_through )
                    ? (int) $subscription->paid_through
                    : strtotime( (string) $subscription->paid_through );
            } elseif ( method_exists( $subscription, 'paid_through' ) ) {
                try {
                    $paid_through_ts = strtotime( (string) $subscription->paid_through() );
                } catch ( \Throwable $e ) {
                }
            } elseif ( isset( $subscription->expires_at ) ) {
                $paid_through_ts = is_numeric( $subscription->expires_at )
                    ? (int) $subscription->expires_at
                    : strtotime( (string) $subscription->expires_at );
            }
        } elseif ( is_array( $subscription ) ) {
            $status = (string) ( $subscription['status'] ?? '' );

            if ( isset( $subscription['paid_through'] ) ) {
                $paid_through_ts = is_numeric( $subscription['paid_through'] )
                    ? (int) $subscription['paid_through']
                    : strtotime( (string) $subscription['paid_through'] );
            } elseif ( isset( $subscription['expires_at'] ) ) {
                $paid_through_ts = is_numeric( $subscription['expires_at'] )
                    ? (int) $subscription['expires_at']
                    : strtotime( (string) $subscription['expires_at'] );
            }
        }

        $status_lc      = strtolower( $status );
        $now            = time();
        $is_active      = strpos( $status_lc, 'active' ) !== false;
        $is_canceled    = strpos( $status_lc, 'cancel' ) !== false;
        $paid_in_future = $paid_through_ts > $now;

        return ( $is_active || ( $is_canceled && $paid_in_future ) );
    }
}
