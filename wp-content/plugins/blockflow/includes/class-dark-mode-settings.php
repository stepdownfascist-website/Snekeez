<?php
/**
 * Dark Mode Settings Helper
 *
 * Provides static methods to check dark mode configuration.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dark Mode Settings class.
 */
class Dark_Mode_Settings {

	/**
	 * Cached settings.
	 *
	 * @var array|null
	 */
	private static $cache = null;

	/**
	 * Check if dark mode CSS output is enabled (at least one method active).
	 *
	 * @return bool True if dark mode is enabled.
	 */
	public static function is_enabled(): bool {
		$settings = self::get();
		if ( empty( $settings['enabled'] ) ) {
			return false;
		}
		// At least one method must be enabled.
		return self::use_color_scheme() || self::use_data_theme();
	}

	/**
	 * Check if color-scheme method is enabled.
	 *
	 * @return bool True if color-scheme method is enabled.
	 */
	public static function use_color_scheme(): bool {
		$settings = self::get();
		return ! empty( $settings['enabled'] ) && ! empty( $settings['colorScheme'] );
	}

	/**
	 * Check if data-theme method is enabled.
	 *
	 * @return bool True if data-theme method is enabled.
	 */
	public static function use_data_theme(): bool {
		$settings = self::get();
		return ! empty( $settings['enabled'] ) && ! empty( $settings['dataTheme'] );
	}

	/**
	 * Get dark mode settings from options.
	 *
	 * @return array Dark mode settings.
	 */
	private static function get(): array {
		if ( null === self::$cache ) {
			$all_settings = get_option( 'blockflow_settings', [] );
			self::$cache  = $all_settings['dark_mode'] ?? [
				'enabled'     => true,
				'colorScheme' => true,
				'dataTheme'   => false,
			];
		}
		return self::$cache;
	}

	/**
	 * Clear cached settings (useful for testing or after settings update).
	 */
	public static function clear_cache(): void {
		self::$cache = null;
	}
}
