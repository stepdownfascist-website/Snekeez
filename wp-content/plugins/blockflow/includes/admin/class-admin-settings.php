<?php
/**
 * Admin Settings Class
 *
 * Handles settings storage, retrieval, and sanitization for the admin dashboard.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow\Admin;

use ThemeRuby\BlockFlow\Block_Manager;
use ThemeRuby\BlockFlow\SVG_Upload;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Settings class.
 */
class Admin_Settings {

	/**
	 * Option name in wp_options table.
	 */
	const OPTION_NAME = 'blockflow_settings';

	/**
	 * Default settings.
	 */
	const DEFAULTS = [
		'disabled_blocks' => [],
		'performance'     => [
			'minify_css'            => true,
			'css_specificity_boost' => false,
		],
		'general'         => [
			'editor_width' => 'default',
		],
		'dark_mode'       => [
			'enabled'     => true,
			'colorScheme' => true,
			'dataTheme'   => false,
		],
		'assets'          => [
			'enable_svg_upload' => false,
		],
		'typography'      => [
			'globalEnabled' => true,
		],
	];

	/**
	 * Get all settings with defaults merged.
	 *
	 * @return array All settings.
	 */
	public static function get_all() {
		$saved    = get_option( self::OPTION_NAME, [] );
		$settings = self::merge_defaults( $saved );

		// Sync SVG upload setting from dedicated option.
		$settings['assets']['enable_svg_upload'] = SVG_Upload::is_enabled();

		return $settings;
	}

	/**
	 * Get a specific setting value.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value if not found.
	 * @return mixed Setting value.
	 */
	public static function get( $key, $default = null ) {
		$settings = self::get_all();

		if ( isset( $settings[ $key ] ) ) {
			return $settings[ $key ];
		}

		if ( null !== $default ) {
			return $default;
		}

		return self::DEFAULTS[ $key ] ?? null;
	}

	/**
	 * Save settings.
	 *
	 * @param array $settings Settings to save.
	 * @return bool True on success, false on failure.
	 */
	public static function save( $settings ) {
		$sanitized = self::sanitize( $settings );
		return update_option( self::OPTION_NAME, $sanitized );
	}

	/**
	 * Update specific settings (merge with existing).
	 *
	 * @param array $settings Settings to update.
	 * @return bool True on success, false on failure.
	 */
	public static function update( $settings ) {
		$current   = self::get_all();
		$merged    = self::merge_recursive( $current, $settings );
		$sanitized = self::sanitize( $merged );
		return update_option( self::OPTION_NAME, $sanitized );
	}

	/**
	 * Reset settings to defaults.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function reset() {
		// Reset SVG upload to default (disabled).
		update_option( SVG_Upload::OPTION_KEY, false );

		return update_option( self::OPTION_NAME, self::DEFAULTS );
	}

	/**
	 * Sanitize settings input.
	 *
	 * @param array $input Raw input data.
	 * @return array Sanitized settings.
	 */
	public static function sanitize( $input ) {
		$sanitized = [];

		// Sanitize disabled_blocks array (exclude core blocks).
		if ( isset( $input['disabled_blocks'] ) && is_array( $input['disabled_blocks'] ) ) {
			$sanitized['disabled_blocks'] = array_map( 'sanitize_key', $input['disabled_blocks'] );
			$sanitized['disabled_blocks'] = array_values( array_unique( $sanitized['disabled_blocks'] ) );
			// Remove core blocks from disabled list (use Block_Manager as single source).
			$sanitized['disabled_blocks'] = array_values(
				array_diff( $sanitized['disabled_blocks'], Block_Manager::get_core_slugs() )
			);
		} else {
			$sanitized['disabled_blocks'] = [];
		}

		// Sanitize performance settings.
		if ( isset( $input['performance'] ) && is_array( $input['performance'] ) ) {
			$sanitized['performance'] = [
				'minify_css'            => ! empty( $input['performance']['minify_css'] ),
				'css_specificity_boost' => ! empty( $input['performance']['css_specificity_boost'] ),
			];
		} else {
			$sanitized['performance'] = self::DEFAULTS['performance'];
		}

		// Sanitize general settings.
		if ( isset( $input['general'] ) && is_array( $input['general'] ) ) {
			$valid_widths         = [ 'default', 'wide', 'full' ];
			$editor_width         = sanitize_key( $input['general']['editor_width'] ?? 'default' );
			$sanitized['general'] = [
				'editor_width' => in_array( $editor_width, $valid_widths, true ) ? $editor_width : 'default',
			];
		} else {
			$sanitized['general'] = self::DEFAULTS['general'];
		}

		// Sanitize dark mode settings.
		if ( isset( $input['dark_mode'] ) && is_array( $input['dark_mode'] ) ) {
			$sanitized['dark_mode'] = [
				'enabled'     => ! empty( $input['dark_mode']['enabled'] ),
				'colorScheme' => ! empty( $input['dark_mode']['colorScheme'] ),
				'dataTheme'   => ! empty( $input['dark_mode']['dataTheme'] ),
			];
		} else {
			$sanitized['dark_mode'] = self::DEFAULTS['dark_mode'];
		}

		// Sanitize assets settings.
		if ( isset( $input['assets'] ) && is_array( $input['assets'] ) ) {
			$sanitized['assets'] = [
				'enable_svg_upload' => ! empty( $input['assets']['enable_svg_upload'] ),
			];

			// Sync SVG upload to dedicated option.
			update_option( SVG_Upload::OPTION_KEY, $sanitized['assets']['enable_svg_upload'] );
		} else {
			$sanitized['assets'] = self::DEFAULTS['assets'];
		}

		// Sanitize typography settings.
		if ( isset( $input['typography'] ) && is_array( $input['typography'] ) ) {
			$sanitized['typography'] = self::sanitize_typography( $input['typography'] );
		}

		return $sanitized;
	}

	/**
	 * Get available blocks metadata for admin.
	 *
	 * Uses Block_Manager's centralized registry.
	 *
	 * @return array Array of block metadata.
	 */
	public static function get_available_blocks() {
		return Block_Manager::get_blocks_for_admin();
	}

	/**
	 * Check if a block is enabled.
	 *
	 * Delegates to Block_Manager as single source of truth.
	 *
	 * @param string $slug Block slug.
	 * @return bool True if enabled.
	 */
	public static function is_block_enabled( $slug ) {
		return Block_Manager::is_block_enabled( $slug );
	}

	/**
	 * Merge defaults with saved settings.
	 *
	 * @param array $saved Saved settings.
	 * @return array Merged settings.
	 */
	private static function merge_defaults( $saved ) {
		return self::merge_recursive( self::DEFAULTS, $saved );
	}

	/**
	 * Recursively merge arrays.
	 *
	 * @param array $base    Base array.
	 * @param array $overlay Overlay array.
	 * @return array Merged array.
	 */
	private static function merge_recursive( $base, $overlay ) {
		foreach ( $overlay as $key => $value ) {
			if ( is_array( $value ) && isset( $base[ $key ] ) && is_array( $base[ $key ] ) ) {
				// For disabled_blocks, replace entirely instead of merging.
				if ( 'disabled_blocks' === $key ) {
					$base[ $key ] = $value;
				} else {
					$base[ $key ] = self::merge_recursive( $base[ $key ], $value );
				}
			} else {
				$base[ $key ] = $value;
			}
		}
		return $base;
	}

	/**
	 * Sanitize typography settings.
	 *
	 * @param array $typography Typography settings array.
	 * @return array Sanitized typography settings.
	 */
	private static function sanitize_typography( $typography ) {
		$sanitized = [];

		// Handle global enabled toggle.
		$sanitized['globalEnabled'] = isset( $typography['globalEnabled'] ) ? (bool) $typography['globalEnabled'] : true;

		$valid_elements = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'paragraph' ];

		foreach ( $valid_elements as $element ) {
			if ( isset( $typography[ $element ] ) && is_array( $typography[ $element ] ) ) {
				$sanitized[ $element ] = self::sanitize_typography_element( $typography[ $element ] );
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize a single typography element (h1, h2, etc.).
	 *
	 * @param array $element Typography element data.
	 * @return array Sanitized element data.
	 */
	private static function sanitize_typography_element( $element ) {
		$sanitized = [];
		$devices   = [ 'desktop', 'tablet', 'mobile' ];

		foreach ( $devices as $device ) {
			if ( isset( $element[ $device ] ) && is_array( $element[ $device ] ) ) {
				$sanitized[ $device ] = self::sanitize_typography_device( $element[ $device ] );
			} else {
				$sanitized[ $device ] = [];
			}
		}

		// Sanitize enabled flag.
		$sanitized['enabled'] = isset( $element['enabled'] ) ? (bool) $element['enabled'] : true;

		return $sanitized;
	}

	/**
	 * Valid CSS units for typography.
	 */
	const VALID_UNITS = [ 'px', 'em', 'rem', '%', 'vw', 'vh', 'pt', 'ch', 'ex', '' ];

	/**
	 * Sanitize a CSS unit value.
	 *
	 * @param string $unit    Unit to sanitize.
	 * @param string $default Default unit if invalid.
	 * @return string Sanitized unit.
	 */
	private static function sanitize_unit( $unit, $default = 'px' ) {
		$unit = sanitize_key( $unit );
		return in_array( $unit, self::VALID_UNITS, true ) ? $unit : $default;
	}

	/**
	 * Sanitize typography settings for a specific device.
	 *
	 * @param array $device_settings Device typography settings.
	 * @return array Sanitized device settings.
	 */
	private static function sanitize_typography_device( $device_settings ) {
		$sanitized = [];

		// Font family.
		if ( ! empty( $device_settings['fontFamily'] ) ) {
			$sanitized['fontFamily'] = sanitize_text_field( $device_settings['fontFamily'] );
		}

		// Font size.
		if ( isset( $device_settings['fontSize'] ) && is_array( $device_settings['fontSize'] ) ) {
			$sanitized['fontSize'] = [
				'value' => isset( $device_settings['fontSize']['value'] ) ? floatval( $device_settings['fontSize']['value'] ) : null,
				'unit'  => isset( $device_settings['fontSize']['unit'] ) ? self::sanitize_unit( $device_settings['fontSize']['unit'], 'px' ) : 'px',
			];
		}

		// Font weight.
		if ( isset( $device_settings['fontWeight'] ) ) {
			$sanitized['fontWeight'] = sanitize_text_field( $device_settings['fontWeight'] );
		}

		// Font style (empty string means no custom style/normal).
		if ( isset( $device_settings['fontStyle'] ) ) {
			$valid_styles = [ '', 'normal', 'italic' ];
			$style        = sanitize_key( $device_settings['fontStyle'] );
			if ( in_array( $style, $valid_styles, true ) ) {
				$sanitized['fontStyle'] = $style;
			}
		}

		// Text transform.
		if ( isset( $device_settings['textTransform'] ) ) {
			$valid_transforms = [ 'none', 'uppercase', 'lowercase', 'capitalize' ];
			$transform        = sanitize_key( $device_settings['textTransform'] );
			if ( in_array( $transform, $valid_transforms, true ) ) {
				$sanitized['textTransform'] = $transform;
			}
		}

		// Text decoration.
		if ( isset( $device_settings['textDecoration'] ) ) {
			$valid_decorations = [ 'none', 'underline', 'line-through', 'overline' ];
			$decoration        = sanitize_key( $device_settings['textDecoration'] );
			if ( in_array( $decoration, $valid_decorations, true ) ) {
				$sanitized['textDecoration'] = $decoration;
			}
		}

		// Line height.
		if ( isset( $device_settings['lineHeight'] ) && is_array( $device_settings['lineHeight'] ) ) {
			$sanitized['lineHeight'] = [
				'value' => isset( $device_settings['lineHeight']['value'] ) ? floatval( $device_settings['lineHeight']['value'] ) : null,
				'unit'  => isset( $device_settings['lineHeight']['unit'] ) ? self::sanitize_unit( $device_settings['lineHeight']['unit'], '' ) : '',
			];
		}

		// Letter spacing.
		if ( isset( $device_settings['letterSpacing'] ) && is_array( $device_settings['letterSpacing'] ) ) {
			$sanitized['letterSpacing'] = [
				'value' => isset( $device_settings['letterSpacing']['value'] ) ? floatval( $device_settings['letterSpacing']['value'] ) : null,
				'unit'  => isset( $device_settings['letterSpacing']['unit'] ) ? self::sanitize_unit( $device_settings['letterSpacing']['unit'], 'px' ) : 'px',
			];
		}

		// Word spacing.
		if ( isset( $device_settings['wordSpacing'] ) && is_array( $device_settings['wordSpacing'] ) ) {
			$sanitized['wordSpacing'] = [
				'value' => isset( $device_settings['wordSpacing']['value'] ) ? floatval( $device_settings['wordSpacing']['value'] ) : null,
				'unit'  => isset( $device_settings['wordSpacing']['unit'] ) ? self::sanitize_unit( $device_settings['wordSpacing']['unit'], 'px' ) : 'px',
			];
		}

		return $sanitized;
	}
}
