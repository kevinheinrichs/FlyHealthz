<?php
/**
 * Remove everything FlyWP Health stored: the must-use loader and both options.
 *
 * @package FlyWP_Health
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$flywp_health_loader = trailingslashit( defined( 'WPMU_PLUGIN_DIR' ) ? WPMU_PLUGIN_DIR : WP_CONTENT_DIR . '/mu-plugins' ) . 'flywp-health-loader.php';

// Only delete a loader this plugin wrote.
if ( is_readable( $flywp_health_loader ) && false !== strpos( (string) file_get_contents( $flywp_health_loader ), 'flywp-health-mu-loader' ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
	wp_delete_file( $flywp_health_loader );
}

delete_option( 'flywp_health_mu_mode' );
delete_option( 'flywp_health_access_key' );
