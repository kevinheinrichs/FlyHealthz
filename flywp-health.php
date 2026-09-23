<?php
/**
 * Plugin Name:       FlyWP Health
 * Plugin URI:        https://www.flywpcode.com/
 * Description:       Lightweight health check and status endpoint for uptime monitoring. Answers /flywphealth with "OK", optionally before other plugins load.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            FlyWP Code
 * Author URI:        https://www.flywpcode.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       flywp-health
 *
 * @package FlyWP_Health
 */

defined( 'ABSPATH' ) || exit;

define( 'FLYWP_HEALTH_VERSION', '1.0.0' );
define( 'FLYWP_HEALTH_DIR', plugin_dir_path( __FILE__ ) );
define( 'FLYWP_HEALTH_BASENAME', plugin_basename( __FILE__ ) );

require_once FLYWP_HEALTH_DIR . 'includes/endpoint.php';
require_once FLYWP_HEALTH_DIR . 'includes/class-flywp-health-mu.php';
require_once FLYWP_HEALTH_DIR . 'includes/class-flywp-health-admin.php';

// Regular mode: answer as soon as this plugin is loaded. In must-use mode the loader has already answered.
flywp_health_handle_request();

if ( is_admin() ) {
	FlyWP_Health_Admin::init();
}

register_activation_hook( __FILE__, 'flywp_health_activate' );
register_deactivation_hook( __FILE__, 'flywp_health_deactivate' );

/**
 * Restore the loader when must-use mode was on before the plugin was deactivated.
 */
function flywp_health_activate() {
	if ( get_option( 'flywp_health_mu_mode', false ) ) {
		FlyWP_Health_MU::install();
	}
}

/**
 * The loader must not outlive the plugin. The setting stays for a later reactivation.
 */
function flywp_health_deactivate() {
	FlyWP_Health_MU::uninstall();
}
