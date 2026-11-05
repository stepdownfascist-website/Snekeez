<?php
/**
 * Icon Data Class
 *
 * Provides access to the icon library data for frontend rendering.
 * Reads from pre-generated PHP file in includes/data/.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Icon_Data class.
 */
class Icon_Data {

	/**
	 * Cached icons array.
	 *
	 * @var array|null
	 */
	private static $icons = null;

	/**
	 * Icons indexed by name for O(1) lookup.
	 *
	 * @var array|null
	 */
	private static $icons_index = null;

	/**
	 * Load icons data from PHP file.
	 *
	 * @return void
	 */
	private static function load(): void {
		if ( null !== self::$icons ) {
			return;
		}

		self::$icons       = [];
		self::$icons_index = [];

		// Use plugin constant for path if available, fallback to __DIR__.
		$base_path = defined( 'BLOCKFLOW_DIR' ) ? BLOCKFLOW_DIR . 'includes/' : __DIR__ . '/';
		$php_path  = $base_path . 'data/icons-data.php';

		if ( ! file_exists( $php_path ) ) {
			return;
		}

		// Include PHP file that returns JSON string via heredoc.
		$json_string = include $php_path;

		if ( ! is_string( $json_string ) || '' === $json_string ) {
			return;
		}

		$decoded = json_decode( $json_string, true );

		if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
			return;
		}

		$icons = $decoded['icons'] ?? [];

		if ( ! is_array( $icons ) ) {
			return;
		}

		self::$icons = $icons;

		// Build index for O(1) lookups.
		foreach ( $icons as $icon ) {
			if ( isset( $icon['name'] ) && is_string( $icon['name'] ) ) {
				self::$icons_index[ $icon['name'] ] = $icon;
			}
		}
	}

	/**
	 * Get all icons from the library.
	 *
	 * @return array Array of icon objects.
	 */
	public static function get_all(): array {
		self::load();
		return self::$icons;
	}

	/**
	 * Find an icon by name.
	 *
	 * Uses indexed lookup for O(1) performance.
	 *
	 * @param string $name Icon name.
	 * @return array|null Icon data or null if not found.
	 */
	public static function find( string $name ): ?array {
		if ( '' === $name ) {
			return null;
		}

		self::load();

		return self::$icons_index[ $name ] ?? null;
	}
}
