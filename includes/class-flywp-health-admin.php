<?php
/**
 * Settings page: Settings > FlyWP Health.
 *
 * @package FlyWP_Health
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the settings and renders the page.
 */
class FlyWP_Health_Admin {

	/** Settings group and page slug. */
	const PAGE = 'flywp-health';

	/**
	 * Hook into WordPress.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'update_option_flywp_health_mu_mode', array( __CLASS__, 'mu_mode_changed' ), 10, 2 );
		add_action( 'add_option_flywp_health_mu_mode', array( __CLASS__, 'mu_mode_added' ), 10, 2 );
		add_filter( 'plugin_action_links_' . FLYWP_HEALTH_BASENAME, array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Add the page under Settings.
	 */
	public static function menu() {
		add_options_page(
			__( 'FlyWP Health', 'flywp-health' ),
			__( 'FlyWP Health', 'flywp-health' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Register both options with their sanitizers.
	 */
	public static function register() {
		register_setting(
			self::PAGE,
			'flywp_health_mu_mode',
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => array( __CLASS__, 'sanitize_bool' ),
			)
		);
		register_setting(
			self::PAGE,
			'flywp_health_access_key',
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( __CLASS__, 'sanitize_key_value' ),
			)
		);
	}

	/**
	 * Checkbox value to 0/1.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_bool( $value ) {
		return empty( $value ) ? 0 : 1;
	}

	/**
	 * Access key: printable characters without spaces, at most 128.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_key_value( $value ) {
		$value = preg_replace( '/[^A-Za-z0-9._~-]/', '', (string) $value );
		return substr( (string) $value, 0, 128 );
	}

	/**
	 * Install or remove the loader when the setting changes.
	 *
	 * @param mixed $old_value Previous value.
	 * @param mixed $value     New value.
	 */
	public static function mu_mode_changed( $old_value, $value ) {
		self::apply_mu_mode( ! empty( $value ) );
	}

	/**
	 * First save of the setting.
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  Value.
	 */
	public static function mu_mode_added( $option, $value ) {
		self::apply_mu_mode( ! empty( $value ) );
	}

	/**
	 * Bring the loader in line with the setting; report a failed write.
	 *
	 * @param bool $enabled Whether must-use mode is on.
	 */
	public static function apply_mu_mode( $enabled ) {
		if ( ! $enabled ) {
			FlyWP_Health_MU::uninstall();
			return;
		}
		if ( ! FlyWP_Health_MU::install() ) {
			update_option( 'flywp_health_mu_mode', 0 );
			add_settings_error(
				self::PAGE,
				'flywp-health-mu',
				__( 'The must-use loader could not be written. Check that wp-content/mu-plugins is writable by WordPress. Must-use mode stays off.', 'flywp-health' )
			);
		}
	}

	/**
	 * "Settings" link in the plugin list.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public static function action_links( $links ) {
		$url = admin_url( 'options-general.php?page=' . self::PAGE );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'flywp-health' ) . '</a>' );
		return $links;
	}

	/**
	 * Render the settings page.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$endpoint = home_url( '/flywphealth' );
		$key      = flywp_health_access_key();
		$locked   = defined( 'FLYWP_HEALTH_KEY' );
		?>
		<div class="wrap">
			<h1 style="display:flex;align-items:center;gap:10px;">
				<img src="<?php echo esc_url( plugins_url( 'assets/logo.png', FLYWP_HEALTH_DIR . 'flywp-health.php' ) ); ?>" width="40" height="40" alt="">
				<?php esc_html_e( 'FlyWP Health', 'flywp-health' ); ?>
			</h1>
			<?php settings_errors( self::PAGE ); ?>

			<p>
				<?php esc_html_e( 'Your status endpoint for uptime monitoring:', 'flywp-health' ); ?>
				<code><?php echo esc_html( $endpoint ); ?></code>
			</p>
			<p><?php esc_html_e( 'It answers GET and HEAD requests with "OK" (HTTP 200) and never reveals versions, paths or other details about the site.', 'flywp-health' ); ?></p>

			<form method="post" action="options.php">
				<?php settings_fields( self::PAGE ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Must-use mode', 'flywp-health' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="flywp_health_mu_mode" value="1" <?php checked( (bool) get_option( 'flywp_health_mu_mode', false ) ); ?>>
								<?php esc_html_e( 'Answer health checks before other plugins load', 'flywp-health' ); ?>
							</label>
							<p class="description">
								<?php
								printf(
									/* translators: %s: file path */
									esc_html__( 'Writes a small loader to %s. It makes the check faster and keeps it working when another plugin breaks. Turning this off, deactivating or deleting the plugin removes the file again.', 'flywp-health' ),
									'<code>' . esc_html( str_replace( ABSPATH, '', FlyWP_Health_MU::path() ) ) . '</code>'
								);
								?>
							</p>
							<?php if ( get_option( 'flywp_health_mu_mode', false ) && ! FlyWP_Health_MU::is_installed() ) : ?>
								<p class="description"><strong><?php esc_html_e( 'The loader file is missing. Save the settings again to recreate it.', 'flywp-health' ); ?></strong></p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="flywp_health_access_key"><?php esc_html_e( 'Access key (optional)', 'flywp-health' ); ?></label></th>
						<td>
							<input type="text" class="regular-text code" id="flywp_health_access_key" name="flywp_health_access_key" value="<?php echo esc_attr( $locked ? '' : (string) get_option( 'flywp_health_access_key', '' ) ); ?>" <?php disabled( $locked ); ?> autocomplete="off">
							<p class="description">
								<?php
								if ( $locked ) {
									esc_html_e( 'Set by the FLYWP_HEALTH_KEY constant in wp-config.php.', 'flywp-health' );
								} else {
									esc_html_e( 'Leave empty for an open endpoint. With a key, requests must send it in the X-FlyWP-Health-Key header or as ?key=… and everything else gets HTTP 403. Letters, digits and . _ ~ - only.', 'flywp-health' );
								}
								?>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<h2><?php esc_html_e( 'Test', 'flywp-health' ); ?></h2>
			<p><code><?php echo esc_html( 'curl -i ' . ( '' !== $key ? '-H "X-FlyWP-Health-Key: …" ' : '' ) . $endpoint ); ?></code></p>
		</div>
		<?php
	}
}
