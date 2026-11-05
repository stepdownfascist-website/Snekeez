<?php
/**
 * Plugin Name:       BlockFlow
 * Plugin URI:        https://wpblockflow.com/
 * Description:       25+ responsive Gutenberg blocks with dark mode. Customize desktop, tablet & mobile separately. Fast, lightweight, no bloat and friendly to use.
 * Tags:              blocks, gutenberg, fse, page builder, editor
 * Author:            ThemeRuby
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Version:           1.1.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author URI:        https://themeruby.com/
 * Text Domain:       blockflow
 * Domain Path:       /languages
 *
 * @package BlockFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 */
define( 'BLOCKFLOW_VER', '1.1.0' );

/**
 * Plugin directory path.
 */
define( 'BLOCKFLOW_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'BLOCKFLOW_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'BLOCKFLOW_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Minimum WordPress version required.
 */
define( 'BLOCKFLOW_MIN_WP', '6.4' );

/**
 * Plugin activation hook.
 */
register_activation_hook( __FILE__, 'blockflow_activate' );
register_deactivation_hook( __FILE__, 'blockflow_deactivate' );


/**
 * Check if Pro version is active.
 *
 * @return bool True if Pro version is active.
 */
function blockflow_is_pro() {
	return defined( 'BLOCKFLOW_PRO_VER' );
}

/**
 * Initialize the plugin.
 */
function blockflow_init() {
	// Check WordPress version.
	if ( version_compare( get_bloginfo( 'version' ), BLOCKFLOW_MIN_WP, '<' ) ) {
		add_action( 'admin_notices', 'blockflow_wp_version_notice' );
		return;
	}

	// Load text domain.
	load_plugin_textdomain( 'blockflow', false, dirname( BLOCKFLOW_BASENAME ) . '/languages' );

	// Load required files.
	require_once BLOCKFLOW_DIR . 'includes/class-css-builder.php';
	require_once BLOCKFLOW_DIR . 'includes/class-css-helpers.php';
	require_once BLOCKFLOW_DIR . 'includes/class-html-helpers.php';
	require_once BLOCKFLOW_DIR . 'includes/class-css-generator-service.php';
	require_once BLOCKFLOW_DIR . 'includes/class-dark-mode-settings.php';
	require_once BLOCKFLOW_DIR . 'includes/css-generators/index.php';
	require_once BLOCKFLOW_DIR . 'includes/class-block-manager.php';
	require_once BLOCKFLOW_DIR . 'includes/class-asset-loader.php';
	require_once BLOCKFLOW_DIR . 'includes/class-svg-upload.php';
	require_once BLOCKFLOW_DIR . 'includes/class-svg-parser.php';
	require_once BLOCKFLOW_DIR . 'includes/class-icon-data.php';
	require_once BLOCKFLOW_DIR . 'includes/class-icon-renderer.php';
	require_once BLOCKFLOW_DIR . 'includes/class-typography-css.php';
	require_once BLOCKFLOW_DIR . 'includes/class-page-templates.php';
	require_once BLOCKFLOW_DIR . 'includes/class-video-helpers.php';

	// Load admin classes (settings needed for REST API).
	require_once BLOCKFLOW_DIR . 'includes/admin/class-admin-settings.php';
	require_once BLOCKFLOW_DIR . 'includes/admin/class-admin-rest-api.php';

	// Initialize REST API (needed for both admin and REST requests).
	ThemeRuby\BlockFlow\Admin\Admin_REST_API::init();

	// Load admin dashboard only in admin context.
	if ( is_admin() ) {
		require_once BLOCKFLOW_DIR . 'includes/admin/class-block-categories.php';
		require_once BLOCKFLOW_DIR . 'includes/admin/class-admin-dashboard.php';
		ThemeRuby\BlockFlow\Admin\Admin_Dashboard::init();
	}

	// Initialize managers.
	ThemeRuby\BlockFlow\Block_Manager::init();
	ThemeRuby\BlockFlow\Asset_Loader::init();
	ThemeRuby\BlockFlow\SVG_Upload::init();
	ThemeRuby\BlockFlow\Typography_CSS::init();
	ThemeRuby\BlockFlow\Page_Templates::init();
}
add_action( 'plugins_loaded', 'blockflow_init' );

/**
 * Display WordPress version notice.
 */
function blockflow_wp_version_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: Minimum WordPress version */
				esc_html__( 'BlockFlow requires WordPress %s or higher. Please update WordPress to use this plugin.', 'blockflow' ),
				esc_html( BLOCKFLOW_MIN_WP )
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Plugin activation hook.
 */
function blockflow_activate() {
	// Set activation flag for welcome redirect.
	set_transient( 'blockflow_activation_redirect', true, 30 );
}

/**
 * Plugin deactivation hook.
 */
function blockflow_deactivate() {
	// Clean up transients.
	delete_transient( 'blockflow_activation_redirect' );
}
