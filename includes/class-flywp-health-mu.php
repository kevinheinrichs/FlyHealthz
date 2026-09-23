<?php
/**
 * Optional must-use mode: a small loader in wp-content/mu-plugins that runs the
 * endpoint before regular plugins load.
 *
 * @package FlyWP_Health
 */

defined( 'ABSPATH' ) || exit;

/**
 * Installs and removes the must-use loader.
 */
class FlyWP_Health_MU {

	/** Marker that identifies a loader written by this plugin. */
	const MARKER = 'flywp-health-mu-loader';

	/**
	 * Absolute path of the loader file.
	 *
	 * @return string
	 */
	public static function path() {
		$dir = defined( 'WPMU_PLUGIN_DIR' ) ? WPMU_PLUGIN_DIR : WP_CONTENT_DIR . '/mu-plugins';
		return trailingslashit( $dir ) . 'flywp-health-loader.php';
	}

	/**
	 * Whether our loader is in place.
	 *
	 * @return bool
	 */
	public static function is_installed() {
		$file = self::path();
		return is_readable( $file ) && false !== strpos( (string) file_get_contents( $file ), self::MARKER ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
	}

	/**
	 * Write the loader. It only points at this plugin's endpoint file, so updates of the
	 * plugin need no copy step.
	 *
	 * @return bool
	 */
	public static function install() {
		$filesystem = self::filesystem();
		if ( ! $filesystem ) {
			return false;
		}

		$dir = dirname( self::path() );
		if ( ! $filesystem->is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
			return false;
		}

		$endpoint = FLYWP_HEALTH_DIR . 'includes/endpoint.php';
		$code     = "<?php\n"
			. "/**\n"
			. " * Plugin Name: FlyWP Health (early endpoint)\n"
			. " * Description: Answers /flywphealth before other plugins load. Managed by FlyWP Health: turn it off under Settings > FlyWP Health.\n"
			. ' * ' . self::MARKER . "\n"
			. " */\n\n"
			. "defined( 'ABSPATH' ) || exit;\n\n"
			. '$flywp_health_endpoint = ' . var_export( $endpoint, true ) . ";\n" // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export -- Writes a PHP string literal.
			. "if ( is_readable( \$flywp_health_endpoint ) ) {\n"
			. "\trequire_once \$flywp_health_endpoint;\n"
			. "\tflywp_health_handle_request();\n"
			. "}\n"
			. "unset( \$flywp_health_endpoint );\n";

		return (bool) $filesystem->put_contents( self::path(), $code, FS_CHMOD_FILE );
	}

	/**
	 * Remove the loader, but only a file this plugin wrote.
	 */
	public static function uninstall() {
		if ( self::is_installed() ) {
			wp_delete_file( self::path() );
		}
	}

	/**
	 * Direct filesystem access; the loader is written by PHP itself, never via FTP credentials.
	 *
	 * @return WP_Filesystem_Base|null
	 */
	private static function filesystem() {
		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( 'direct' !== get_filesystem_method() || ! WP_Filesystem() ) {
			return null;
		}

		return $wp_filesystem;
	}
}
