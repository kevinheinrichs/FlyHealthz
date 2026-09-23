<?php
/**
 * FlyWP Health endpoint.
 *
 * Answers GET/HEAD requests to /flywphealth with "OK" and stops WordPress right there.
 * Loaded by the plugin file and, when the optional must-use mode is on, by the
 * small loader in wp-content/mu-plugins so the check runs before other plugins.
 *
 * @package FlyWP_Health
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'flywp_health_handle_request' ) ) {

	/**
	 * Respond to a health check request and exit; returns for every other request.
	 */
	function flywp_health_handle_request() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$path        = (string) wp_parse_url( $request_uri, PHP_URL_PATH );
		$home_path   = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		$endpoint    = rtrim( $home_path, '/' ) . '/flywphealth';

		if ( rtrim( $path, '/' ) !== $endpoint ) {
			return;
		}

		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';

		nocache_headers();
		header( 'X-Robots-Tag: noindex, nofollow' );
		header( 'Content-Type: text/plain; charset=utf-8' );

		if ( 'GET' !== $method && 'HEAD' !== $method ) {
			status_header( 405 );
			header( 'Allow: GET, HEAD' );
			exit;
		}

		$key = flywp_health_access_key();
		if ( '' !== $key && ! hash_equals( $key, flywp_health_request_key() ) ) {
			status_header( 403 );
			if ( 'HEAD' !== $method ) {
				echo 'Forbidden';
			}
			exit;
		}

		status_header( 200 );
		if ( 'HEAD' !== $method ) {
			echo 'OK';
		}
		exit;
	}

	/**
	 * Access key the endpoint requires; an empty string means the endpoint is open.
	 *
	 * The constant FLYWP_HEALTH_KEY in wp-config.php takes precedence over the setting.
	 *
	 * @return string
	 */
	function flywp_health_access_key() {
		if ( defined( 'FLYWP_HEALTH_KEY' ) ) {
			return (string) FLYWP_HEALTH_KEY;
		}

		return (string) get_option( 'flywp_health_access_key', '' );
	}

	/**
	 * Key sent with the request: X-FlyWP-Health-Key header, or the ?key= query parameter
	 * for monitors that cannot send headers.
	 *
	 * @return string
	 */
	function flywp_health_request_key() {
		if ( isset( $_SERVER['HTTP_X_FLYWP_HEALTH_KEY'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FLYWP_HEALTH_KEY'] ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only monitoring request, authenticated by the key itself.
		if ( isset( $_GET['key'] ) ) {
			return sanitize_text_field( wp_unslash( $_GET['key'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return '';
	}
}
