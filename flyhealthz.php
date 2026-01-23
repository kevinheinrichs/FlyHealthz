<?php
/**
 * Plugin Name: FlyHealthz
 * Plugin URI: https://www.kevinheinrichs.com/
 * Description: Ultra-fast Healthcheck for PHP 8.5+. Automatically installs and syncs as an MU-plugin for maximum performance.
 * Version: 1.0.0
 * Author: Kevin Heinrichs
 * Author URI: https://www.kevinheinrichs.com/
 * License: GPL-2.0+
 * Text Domain: flyhealthz
 */

/**
 * PHASE 1: HIGH PERFORMANCE ENGINE
 * This part executes early to intercept /healthz requests with minimal overhead.
 */
$fly_uri = $_SERVER['REQUEST_URI'] ?? '';

if ( str_starts_with( $fly_uri, '/healthz' ) ) {
    
    // Allow the secret agent to be defined in wp-config.php for even easier management
    $secret_agent = defined( 'FLYHEALTHZ_SECRET' ) ? FLYHEALTHZ_SECRET : 'FlyHealthz';
    $current_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Security Gate: Constant-time comparison
    if ( ! hash_equals( $secret_agent, $current_agent ) ) {
        http_response_code( 403 );
        header( "Content-Type: text/plain; charset=utf-8" );
        die( 'Forbidden' );
    }

    // Performance Headers
    header( "Cache-Control: no-cache, no-store, must-revalidate" );
    header( "X-Robots-Tag: noindex, nofollow" );
    header( "X-FlyHealthz: High-Performance-Active-PHP8.5" );
    header( "Content-Type: text/plain; charset=utf-8" );
    header( "Pragma: no-cache" );
    header( "Expires: 0" );

    // Handle HEAD requests
    if ( ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) === 'HEAD' ) {
        http_response_code( 200 );
        die();
    }

    http_response_code( 200 );
    die( 'OK' );
}

/**
 * PHASE 2: AUTO-INSTALLER & SYNC
 * Only runs within the WordPress core context.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'plugins_loaded', function(): void {
    if ( ! is_admin() ) {
        return;
    }

    $mu_dir    = defined( 'WPMU_PLUGIN_DIR' ) ? (string) WPMU_PLUGIN_DIR : WP_CONTENT_DIR . '/mu-plugins';
    $mu_target = $mu_dir . '/flyhealthz.php';

    // Synchronize file if the source has changed
    if ( ! str_contains( __FILE__, $mu_target ) && file_exists( __FILE__ ) ) {
        $current_hash = md5_file( __FILE__ );
        $mu_hash      = file_exists( $mu_target ) ? md5_file( $mu_target ) : '';

        if ( $current_hash !== $mu_hash ) {
            if ( ! is_dir( $mu_dir ) ) {
                @mkdir( $mu_dir, 0755, true );
            }
            if ( is_dir( $mu_dir ) && is_writable( $mu_dir ) ) {
                @copy( __FILE__, $mu_target );
            }
        }
    }
} );

/**
 * Lifecycle Hooks
 */
register_deactivation_hook( __FILE__, function(): void {
    $mu_dir    = defined( 'WPMU_PLUGIN_DIR' ) ? (string) WPMU_PLUGIN_DIR : WP_CONTENT_DIR . '/mu-plugins';
    $mu_target = $mu_dir . '/flyhealthz.php';
    if ( file_exists( $mu_target ) ) {
        @unlink( $mu_target );
    }
} );
