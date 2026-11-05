<?php
/**
 * Admin REST API Class
 *
 * Handles REST API endpoints for the admin dashboard.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin REST API class.
 */
class Admin_REST_API {

	/**
	 * REST API namespace.
	 */
	const NAMESPACE = 'blockflow/v1';

	/**
	 * Initialize the REST API.
	 */
	public static function init() {
		add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
	}

	/**
	 * Register REST API routes.
	 */
	public static function register_routes() {
		// Get all settings.
		register_rest_route(
			self::NAMESPACE,
			'/settings',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_settings' ],
				'permission_callback' => [ __CLASS__, 'check_admin_permission' ],
			]
		);

		// Update settings.
		register_rest_route(
			self::NAMESPACE,
			'/settings',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'update_settings' ],
				'permission_callback' => [ __CLASS__, 'check_admin_permission' ],
			]
		);

		// Reset settings.
		register_rest_route(
			self::NAMESPACE,
			'/settings/reset',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'reset_settings' ],
				'permission_callback' => [ __CLASS__, 'check_admin_permission' ],
			]
		);

		// Get system info.
		register_rest_route(
			self::NAMESPACE,
			'/system-info',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_system_info' ],
				'permission_callback' => [ __CLASS__, 'check_admin_permission' ],
			]
		);

		// Get recommended plugins.
		register_rest_route(
			self::NAMESPACE,
			'/plugins/recommended',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_recommended_plugins' ],
				'permission_callback' => [ __CLASS__, 'check_admin_permission' ],
			]
		);

		// Install a plugin.
		register_rest_route(
			self::NAMESPACE,
			'/plugins/install',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'install_plugin' ],
				'permission_callback' => [ __CLASS__, 'check_install_permission' ],
				'args'                => [
					'slug' => [
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
						'validate_callback' => [ __CLASS__, 'validate_plugin_slug' ],
					],
				],
			]
		);

		// Activate a plugin.
		register_rest_route(
			self::NAMESPACE,
			'/plugins/activate',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'activate_plugin' ],
				'permission_callback' => [ __CLASS__, 'check_activate_permission' ],
				'args'                => [
					'slug' => [
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
						'validate_callback' => [ __CLASS__, 'validate_plugin_slug' ],
					],
				],
			]
		);
	}

	/**
	 * Check if user has admin permission.
	 *
	 * @return bool|\WP_Error True if permission granted.
	 */
	public static function check_admin_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				esc_html__( 'You do not have permission to access this resource.', 'blockflow' ),
				[ 'status' => 403 ]
			);
		}
		return true;
	}

	/**
	 * Get all settings.
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_settings() {
		$settings = Admin_Settings::get_all();
		return rest_ensure_response( $settings );
	}

	/**
	 * Update settings.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function update_settings( $request ) {
		$params = $request->get_json_params();

		if ( empty( $params ) ) {
			return new \WP_Error(
				'invalid_params',
				esc_html__( 'No settings provided.', 'blockflow' ),
				[ 'status' => 400 ]
			);
		}

		$result = Admin_Settings::update( $params );

		if ( ! $result ) {
			return new \WP_Error(
				'save_failed',
				esc_html__( 'Failed to save settings.', 'blockflow' ),
				[ 'status' => 500 ]
			);
		}

		return rest_ensure_response(
			[
				'success'  => true,
				'message'  => esc_html__( 'Settings saved successfully.', 'blockflow' ),
				'settings' => Admin_Settings::get_all(),
			]
		);
	}

	/**
	 * Reset settings to defaults.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function reset_settings() {
		$result = Admin_Settings::reset();

		if ( ! $result ) {
			return new \WP_Error(
				'reset_failed',
				esc_html__( 'Failed to reset settings.', 'blockflow' ),
				[ 'status' => 500 ]
			);
		}

		return rest_ensure_response(
			[
				'success'  => true,
				'message'  => esc_html__( 'Settings reset to defaults.', 'blockflow' ),
				'settings' => Admin_Settings::get_all(),
			]
		);
	}

	/**
	 * Get system information.
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_system_info() {
		global $wpdb;

		$theme = wp_get_theme();

		$info = [
			'wordpress' => [
				'version'      => get_bloginfo( 'version' ),
				'multisite'    => is_multisite(),
				'site_url'     => get_site_url(),
				'home_url'     => get_home_url(),
				'memory_limit' => WP_MEMORY_LIMIT,
				'debug_mode'   => defined( 'WP_DEBUG' ) && WP_DEBUG,
			],
			'server'    => [
				'php_version'    => phpversion(),
				'mysql_version'  => $wpdb->db_version(),
				'server_info'    => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : 'Unknown',
				'max_upload'     => size_format( wp_max_upload_size() ),
				'php_time_limit' => ini_get( 'max_execution_time' ),
			],
			'theme'     => [
				'name'     => $theme->get( 'Name' ),
				'version'  => $theme->get( 'Version' ),
				'author'   => $theme->get( 'Author' ),
				'is_child' => is_child_theme(),
				'parent'   => is_child_theme() ? $theme->parent()->get( 'Name' ) : null,
			],
			'blockflow' => [
				'version'         => BLOCKFLOW_VER,
				'is_pro'          => blockflow_is_pro(),
				'blocks_dir'      => BLOCKFLOW_DIR,
				'active_blocks'   => self::count_active_blocks(),
				'disabled_blocks' => self::count_disabled_blocks(),
			],
		];

		return rest_ensure_response( $info );
	}

	/**
	 * Count active blocks.
	 *
	 * @return int Number of active blocks.
	 */
	private static function count_active_blocks() {
		$blocks   = Admin_Settings::get_available_blocks();
		$disabled = Admin_Settings::get( 'disabled_blocks', [] );
		return count( $blocks ) - count( $disabled );
	}

	/**
	 * Count disabled blocks.
	 *
	 * @return int Number of disabled blocks.
	 */
	private static function count_disabled_blocks() {
		$disabled = Admin_Settings::get( 'disabled_blocks', [] );
		return count( $disabled );
	}

	/**
	 * Check if user has plugin install permission.
	 *
	 * @return bool|\WP_Error True if permission granted.
	 */
	public static function check_install_permission() {
		// Check nonce from REST API header.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_WP_NONCE'] ?? '' ) ), 'wp_rest' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				esc_html__( 'Invalid security token.', 'blockflow' ),
				[ 'status' => 403 ]
			);
		}

		// Check install_plugins capability.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				esc_html__( 'You do not have permission to install plugins.', 'blockflow' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Check if user has plugin activate permission.
	 *
	 * @return bool|\WP_Error True if permission granted.
	 */
	public static function check_activate_permission() {
		// Check nonce from REST API header.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_WP_NONCE'] ?? '' ) ), 'wp_rest' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				esc_html__( 'Invalid security token.', 'blockflow' ),
				[ 'status' => 403 ]
			);
		}

		// Check activate_plugins capability.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				esc_html__( 'You do not have permission to activate plugins.', 'blockflow' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Hardcoded list of recommended ThemeRuby plugins.
	 * Only these plugins can be installed/activated via REST API.
	 */
	const RECOMMENDED_PLUGINS = [
		'easy-post-submission'    => [
			'name'            => 'Easy Post Submission',
			'description'     => 'Frontend Posting, Guest Publishing & Submit Content for WordPress.',
			'icon'            => 'https://ps.w.org/easy-post-submission/assets/icon-256x256.gif?rev=3247494',
			'active_installs' => '3,000+',
			'rating'          => 5,
		],
		'light-views-counter'     => [
			'name'            => 'Light Views Counter',
			'description'     => 'Fast, Scalable View Counter for High-Traffic Sites.',
			'icon'            => 'https://ps.w.org/light-views-counter/assets/icon-256x256.gif?rev=3388219',
			'active_installs' => '2,000+',
			'rating'          => 0,
		],
		'local-fonts-uploader'    => [
			'name'            => 'Local Fonts Uploader',
			'description'     => 'Upload & Host Any Font Locally for GDPR Compliance.',
			'icon'            => 'https://ps.w.org/local-fonts-uploader/assets/icon-256x256.gif?rev=3260505',
			'active_installs' => '3,000+',
			'rating'          => 5,
		],
		'surveyx-builder'         => [
			'name'            => 'SurveyX Builder',
			'description'     => 'Easy Feedback, Poll, Quiz & Survey Builder.',
			'icon'            => 'https://ps.w.org/surveyx-builder/assets/icon-256x256.png?rev=3448366',
			'active_installs' => '1,000+',
			'rating'          => 5,
		],
		'themeruby-multi-authors' => [
			'name'            => 'ThemeRuby Multi Authors',
			'description'     => 'Assign Multiple Writers to Posts.',
			'icon'            => 'https://ps.w.org/themeruby-multi-authors/assets/icon-256x256.gif?rev=3422809',
			'active_installs' => '1,000+',
			'rating'          => 0,
		],
	];

	/**
	 * Get recommended plugins list.
	 * Returns hardcoded plugin data with current installation status.
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_recommended_plugins() {
		$installed_plugins = get_plugins();
		$active_plugins    = get_option( 'active_plugins', [] );

		$plugins = [];

		foreach ( self::RECOMMENDED_PLUGINS as $slug => $data ) {
			// Determine plugin status.
			$status = 'not-installed';

			foreach ( $installed_plugins as $path => $plugin_data ) {
				if ( strpos( $path, $slug . '/' ) === 0 ) {
					$status = in_array( $path, $active_plugins, true ) ? 'active' : 'installed';
					break;
				}
			}

			$plugins[] = [
				'slug'            => $slug,
				'name'            => $data['name'],
				'description'     => $data['description'],
				'icon'            => $data['icon'],
				'active_installs' => $data['active_installs'],
				'rating'          => $data['rating'],
				'status'          => $status,
			];
		}

		return rest_ensure_response(
			[
				'success' => true,
				'plugins' => $plugins,
			]
		);
	}

	/**
	 * Validate plugin slug is in our whitelist.
	 * Used as REST API validate_callback.
	 *
	 * @param string           $slug    Plugin slug.
	 * @param \WP_REST_Request $request Request object.
	 * @param string           $key     Parameter key.
	 * @return bool|\WP_Error True if valid, WP_Error otherwise.
	 */
	public static function validate_plugin_slug( $slug, $request, $key ) {
		$slug = sanitize_key( $slug );

		if ( empty( $slug ) ) {
			return new \WP_Error(
				'invalid_slug',
				esc_html__( 'Plugin slug is required.', 'blockflow' ),
				[ 'status' => 400 ]
			);
		}

		if ( ! array_key_exists( $slug, self::RECOMMENDED_PLUGINS ) ) {
			return new \WP_Error(
				'invalid_plugin',
				esc_html__( 'This plugin is not in the allowed list.', 'blockflow' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Check if plugin slug is in our whitelist.
	 *
	 * @param string $slug Plugin slug.
	 * @return bool True if valid.
	 */
	private static function is_allowed_plugin( $slug ) {
		return array_key_exists( sanitize_key( $slug ), self::RECOMMENDED_PLUGINS );
	}

	/**
	 * Install a plugin from WordPress.org.
	 * Only allows installation of whitelisted plugins.
	 *
	 * Security checks:
	 * - Nonce verification (in permission_callback)
	 * - Capability check: install_plugins (in permission_callback)
	 * - Whitelist validation (in validate_callback)
	 * - Download source verification (WordPress.org only)
	 * - File system access check
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function install_plugin( $request ) {
		$slug = sanitize_key( $request->get_param( 'slug' ) );

		// Double-check whitelist (already validated, but defense in depth).
		if ( ! self::is_allowed_plugin( $slug ) ) {
			return new \WP_Error(
				'invalid_plugin',
				esc_html__( 'This plugin is not in the allowed list.', 'blockflow' ),
				[ 'status' => 403 ]
			);
		}

		// Include required WordPress files.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		// Check if plugin is already installed.
		$installed_plugins = get_plugins();
		foreach ( $installed_plugins as $path => $data ) {
			if ( strpos( $path, $slug . '/' ) === 0 ) {
				return new \WP_Error(
					'already_installed',
					esc_html__( 'Plugin is already installed.', 'blockflow' ),
					[ 'status' => 400 ]
				);
			}
		}

		// Check file system access.
		$filesystem_method = get_filesystem_method();
		if ( 'direct' !== $filesystem_method ) {
			return new \WP_Error(
				'fs_unavailable',
				esc_html__( 'Could not access filesystem. Please check file permissions.', 'blockflow' ),
				[ 'status' => 500 ]
			);
		}

		// Initialize WP_Filesystem.
		if ( ! WP_Filesystem() ) {
			return new \WP_Error(
				'fs_error',
				esc_html__( 'Could not initialize filesystem.', 'blockflow' ),
				[ 'status' => 500 ]
			);
		}

		// Get plugin info from WordPress.org API.
		$api = plugins_api(
			'plugin_information',
			[
				'slug'   => $slug,
				'fields' => [
					'sections'      => false,
					'compatibility' => false,
					'download_link' => true,
				],
			]
		);

		if ( is_wp_error( $api ) ) {
			return new \WP_Error(
				'api_error',
				esc_html__( 'Failed to get plugin information from WordPress.org.', 'blockflow' ),
				[ 'status' => 500 ]
			);
		}

		// Verify download link exists and is from WordPress.org.
		if ( empty( $api->download_link ) ) {
			return new \WP_Error(
				'no_download_link',
				esc_html__( 'No download link available for this plugin.', 'blockflow' ),
				[ 'status' => 500 ]
			);
		}

		// Strict URL validation - must be from downloads.wordpress.org.
		$parsed_url = wp_parse_url( $api->download_link );
		if (
			empty( $parsed_url['host'] ) ||
			'downloads.wordpress.org' !== $parsed_url['host'] ||
			empty( $parsed_url['scheme'] ) ||
			'https' !== $parsed_url['scheme']
		) {
			return new \WP_Error(
				'invalid_source',
				esc_html__( 'Plugin must be downloaded from WordPress.org.', 'blockflow' ),
				[ 'status' => 403 ]
			);
		}

		// Use WordPress upgrader to install the plugin.
		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );

		// Perform the installation.
		$result = $upgrader->install( $api->download_link );

		// Check for errors.
		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				'install_error',
				$result->get_error_message(),
				[ 'status' => 500 ]
			);
		}

		if ( is_wp_error( $skin->result ) ) {
			return new \WP_Error(
				'install_error',
				$skin->result->get_error_message(),
				[ 'status' => 500 ]
			);
		}

		if ( ! $result ) {
			return new \WP_Error(
				'install_failed',
				esc_html__( 'Plugin installation failed.', 'blockflow' ),
				[ 'status' => 500 ]
			);
		}

		// Clear plugin cache after successful installation.
		wp_clean_plugins_cache();

		return rest_ensure_response(
			[
				'success' => true,
				'message' => esc_html__( 'Plugin installed successfully.', 'blockflow' ),
			]
		);
	}

	/**
	 * Activate an installed plugin.
	 * Only allows activation of whitelisted plugins.
	 *
	 * Security checks:
	 * - Nonce verification (in permission_callback)
	 * - Capability check: activate_plugins (in permission_callback)
	 * - Whitelist validation (in validate_callback)
	 * - Plugin file path validation
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function activate_plugin( $request ) {
		$slug = sanitize_key( $request->get_param( 'slug' ) );

		// Double-check whitelist (already validated, but defense in depth).
		if ( ! self::is_allowed_plugin( $slug ) ) {
			return new \WP_Error(
				'invalid_plugin',
				esc_html__( 'This plugin is not in the allowed list.', 'blockflow' ),
				[ 'status' => 403 ]
			);
		}

		// Include required WordPress files.
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		// Get all installed plugins and find matching plugin file.
		$installed_plugins = get_plugins();
		$plugin_file       = null;

		foreach ( $installed_plugins as $path => $data ) {
			// Match plugin directory with slug.
			if ( strpos( $path, $slug . '/' ) === 0 ) {
				$plugin_file = $path;
				break;
			}
		}

		if ( ! $plugin_file ) {
			return new \WP_Error(
				'not_found',
				esc_html__( 'Plugin is not installed.', 'blockflow' ),
				[ 'status' => 404 ]
			);
		}

		// Validate plugin file path (security: prevent directory traversal).
		if ( validate_file( $plugin_file ) !== 0 ) {
			return new \WP_Error(
				'invalid_plugin_path',
				esc_html__( 'Invalid plugin file path.', 'blockflow' ),
				[ 'status' => 403 ]
			);
		}

		// Check if plugin file exists.
		$plugin_path = trailingslashit( dirname( BLOCKFLOW_DIR ) ) . $plugin_file;
		if ( ! file_exists( $plugin_path ) ) {
			return new \WP_Error(
				'plugin_not_found',
				esc_html__( 'Plugin file does not exist.', 'blockflow' ),
				[ 'status' => 404 ]
			);
		}

		// Check if already active.
		if ( is_plugin_active( $plugin_file ) ) {
			return rest_ensure_response(
				[
					'success' => true,
					'message' => esc_html__( 'Plugin is already active.', 'blockflow' ),
				]
			);
		}

		// Activate the plugin using WordPress function.
		$result = activate_plugin( $plugin_file, '', false, false );

		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				'activate_error',
				$result->get_error_message(),
				[ 'status' => 500 ]
			);
		}

		return rest_ensure_response(
			[
				'success' => true,
				'message' => esc_html__( 'Plugin activated successfully.', 'blockflow' ),
			]
		);
	}
}
