<?php
/**
 * Admin Dashboard Class
 *
 * Handles admin menu registration and dashboard page rendering.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow\Admin;

use ThemeRuby\BlockFlow\Asset_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Dashboard class.
 */
class Admin_Dashboard {

	/**
	 * Admin page slug.
	 */
	const PAGE_SLUG = 'blockflow';

	/**
	 * Initialize the dashboard.
	 */
	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'add_admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
		add_action( 'admin_init', [ __CLASS__, 'maybe_redirect_after_activation' ] );
		add_filter( 'admin_body_class', [ __CLASS__, 'add_admin_body_class' ] );
	}

	/**
	 * Add body class for admin page.
	 *
	 * @param string $classes Space-separated list of CSS classes.
	 * @return string Modified class list.
	 */
	public static function add_admin_body_class( $classes ) {
		$screen = get_current_screen();
		if ( $screen && strpos( $screen->id, self::PAGE_SLUG ) !== false ) {
			$classes .= ' blockflow-has-sidebar';
		}
		return $classes;
	}

	/**
	 * Add admin menu page.
	 */
	public static function add_admin_menu() {
		add_menu_page(
			esc_html__( 'BlockFlow', 'blockflow' ),
			esc_html__( 'BlockFlow', 'blockflow' ),
			'manage_options',
			self::PAGE_SLUG,
			[ __CLASS__, 'render_dashboard' ],
			self::get_menu_icon(),
			59
		);

		// Add submenu pages (same as parent to rename first item).
		add_submenu_page(
			self::PAGE_SLUG,
			esc_html__( 'Blocks', 'blockflow' ),
			esc_html__( 'Blocks', 'blockflow' ),
			'manage_options',
			self::PAGE_SLUG,
			[ __CLASS__, 'render_dashboard' ]
		);
	}

	/**
	 * Render the dashboard page.
	 */
	public static function render_dashboard() {
		echo '<div id="blockflow-admin-root" class="blockflow-admin"></div>';
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public static function enqueue_admin_assets( $hook_suffix ) {
		// Only load on our admin pages.
		if ( strpos( $hook_suffix, self::PAGE_SLUG ) === false ) {
			return;
		}

		$asset_file = BLOCKFLOW_DIR . 'build/admin/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		// Ensure blockflow-data script is registered (provides fonts/icons).
		Asset_Loader::register_data_script();

		// Add blockflow-data as dependency for fonts data.
		$dependencies   = $asset['dependencies'];
		$dependencies[] = 'blockflow-data';

		// Enqueue admin styles.
		wp_enqueue_style(
			'blockflow-admin',
			BLOCKFLOW_URL . 'build/admin/index.css',
			[ 'wp-components' ],
			$asset['version']
		);

		// Enqueue admin scripts with data dependency.
		wp_enqueue_script(
			'blockflow-admin',
			BLOCKFLOW_URL . 'build/admin/index.js',
			$dependencies,
			$asset['version'],
			true
		);

		// Localize script data.
		wp_localize_script(
			'blockflow-admin',
			'blockflowAdmin',
			self::get_localized_data()
		);

		// Set script translations.
		wp_set_script_translations(
			'blockflow-admin',
			'blockflow',
			BLOCKFLOW_DIR . 'languages'
		);
	}

	/**
	 * Get localized data for admin scripts.
	 *
	 * @return array Localized data.
	 */
	private static function get_localized_data() {
		/**
		 * Filter to allow pro addons to add settings sections.
		 *
		 * Each section should have: key, title, description, isPro (optional).
		 *
		 * @param array $sections Array of settings sections.
		 */
		$settings_sections = apply_filters( 'blockflow_settings_sections', [] );

		/**
		 * Filter to allow pro addons to add block categories.
		 *
		 * @param array $categories Array of block categories.
		 */
		$block_categories = apply_filters(
			'blockflow_block_categories',
			Block_Categories::get_categories_for_js()
		);

		$data = [
			'apiUrl'           => rest_url( Admin_REST_API::NAMESPACE . '/' ),
			'nonce'            => wp_create_nonce( 'wp_rest' ),
			'pluginUrl'        => BLOCKFLOW_URL,
			'version'          => BLOCKFLOW_VER,
			'isPro'            => blockflow_is_pro(),
			'settings'         => Admin_Settings::get_all(),
			'blocks'           => Admin_Settings::get_available_blocks(),
			'categories'       => $block_categories,
			'settingsSections' => $settings_sections,
			'strings'          => [
				'save'           => esc_html__( 'Save Changes', 'blockflow' ),
				'saved'          => esc_html__( 'Saved', 'blockflow' ),
				'saving'         => esc_html__( 'Saving...', 'blockflow' ),
				'error'          => esc_html__( 'Error saving settings', 'blockflow' ),
				'enableAll'      => esc_html__( 'Enable All', 'blockflow' ),
				'disableAll'     => esc_html__( 'Disable All', 'blockflow' ),
				'blocks'         => esc_html__( 'Blocks', 'blockflow' ),
				'settings'       => esc_html__( 'Settings', 'blockflow' ),
				'performance'    => esc_html__( 'Performance', 'blockflow' ),
				'systemInfo'     => esc_html__( 'System Info', 'blockflow' ),
				'help'           => esc_html__( 'Help', 'blockflow' ),
				'upgradeToPro'   => esc_html__( 'Upgrade to Pro', 'blockflow' ),
				'proFeature'     => esc_html__( 'Pro Feature', 'blockflow' ),
				'enabled'        => esc_html__( 'Enabled', 'blockflow' ),
				'disabled'       => esc_html__( 'Disabled', 'blockflow' ),
				'resetToDefault' => esc_html__( 'Reset to Default', 'blockflow' ),
				'unsavedChanges' => esc_html__( 'You have unsaved changes.', 'blockflow' ),
			],
		];

		/**
		 * Filter to allow pro addons to extend admin localized data.
		 *
		 * @param array $data Localized data array.
		 */
		return apply_filters( 'blockflow_admin_data', $data );
	}

	/**
	 * Get menu icon SVG.
	 *
	 * @return string Base64 encoded SVG icon.
	 */
	private static function get_menu_icon() {
		return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjguNSA4LjUgODMgODMiIGZpbGw9IiNhN2FhYWQiPjxwYXRoIGQ9Ik03OS45LDMwLjdjLTIuNi01LjEtOS4yLTE0LjYtMjEuNS05Yy03LjYsMy41LTExLjksNS41LTExLjksNS41bC0xMSw0LjhjLTMuMSwxLjUtOS45LTAuNi0xMy43LTJjLTEuMS0wLjQtMi4xLDAuOC0xLjYsMS45YzIuNiw1LjEsOS4yLDE0LjYsMjEuNSw5YzcuNi0zLjUsMjIuOS0xMC4xLDIyLjktMTAuMWMzLjEtMS41LDkuOSwwLjYsMTMuNywyQzc5LjQsMzMsODAuNCwzMS45LDc5LjksMzAuN3oiLz48cGF0aCBkPSJNNTMuNSw0Ni44Yy0xLjQsMC44LTYuOSwzLjMtNi45LDMuM2wtNS41LDIuNGMtMi43LDEuNS04LjYtMC41LTEyLjEtMS45Yy0xLTAuNS0xLjksMC44LTEuNCwxLjhjMi4yLDUsOC4xLDE0LDE4LjksOC41YzYuNy0zLjQsMTIuNC01LjYsMTIuNC01LjZjMi43LTEuNSw4LjYsMC41LDEyLjEsMS45YzEsMC40LDEuOS0wLjgsMS40LTEuOUM3MC4xLDUwLjMsNjQuMiw0MS4zLDUzLjUsNDYuOHoiLz48cGF0aCBkPSJNNDkuNSw2OC45Yy0xLjEsMC42LTMsMS44LTMsMS44QzQ0LjQsNzIsNDAsNzAuMywzNy40LDY5Yy0wLjctMC40LTEuNCwwLjgtMSwxLjhjMS42LDQuNSw2LDEyLjYsMTQuMSw3LjZjMy0xLjksMy0xLjgsMy0xLjhjMi4yLTEuMSw2LjUsMC40LDkuMSwxLjZjMC43LDAuNCwxLjQtMC44LDEtMS44QzYyLDcyLDU3LjksNjQuMyw0OS41LDY4Ljl6Ii8+PC9zdmc+';
	}

	/**
	 * Redirect to dashboard after plugin activation.
	 */
	public static function maybe_redirect_after_activation() {
		if ( ! get_transient( 'blockflow_activation_redirect' ) ) {
			return;
		}

		delete_transient( 'blockflow_activation_redirect' );

		// Don't redirect on multisite or bulk activation.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking existence only, no data processing.
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) );
		exit;
	}
}
