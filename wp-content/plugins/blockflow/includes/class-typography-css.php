<?php
/**
 * Typography CSS Generator
 *
 * Generates CSS variables from global typography settings.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Typography CSS class.
 */
class Typography_CSS {

	/**
	 * Typography elements and their variable prefixes.
	 */
	const ELEMENTS = [
		'h1'        => 'h1',
		'h2'        => 'h2',
		'h3'        => 'h3',
		'h4'        => 'h4',
		'h5'        => 'h5',
		'h6'        => 'h6',
		'paragraph' => 'p',
	];

	/**
	 * Cached typography settings.
	 *
	 * @var array|null
	 */
	private static $settings = null;

	/**
	 * Cached CSS output.
	 *
	 * @var string|null
	 */
	private static $cached_css = null;

	/**
	 * Initialize typography CSS.
	 */
	public static function init() {
		// Register typography base CSS.
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'register_base_css' ], 5 );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_base_css' ], 10 );

		// Output dynamic typography CSS variables.
		// Must run during wp_enqueue_scripts (after enqueue at 10) before styles are printed.
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'output_typography_css' ], 20 );

		// Also load in editor.
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'register_base_css' ], 5 );
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_base_css' ], 10 );
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'output_editor_typography_css' ], 20 );
	}

	/**
	 * Check if global typography is enabled.
	 *
	 * @return bool True if enabled.
	 */
	public static function is_enabled() {
		$typography = self::get_settings();
		return ! isset( $typography['globalEnabled'] ) || true === $typography['globalEnabled'];
	}

	/**
	 * Register base typography CSS.
	 */
	public static function register_base_css() {
		$css_file = BLOCKFLOW_DIR . 'build/typography.css';
		if ( file_exists( $css_file ) ) {
			wp_register_style(
				'blockflow-typography',
				BLOCKFLOW_URL . 'build/typography.css',
				[],
				BLOCKFLOW_VER
			);
		}
	}

	/**
	 * Enqueue base typography CSS.
	 */
	public static function enqueue_base_css() {
		// Skip if global typography is disabled.
		if ( ! self::is_enabled() ) {
			return;
		}

		if ( wp_style_is( 'blockflow-typography', 'registered' ) ) {
			wp_enqueue_style( 'blockflow-typography' );
		}
	}

	/**
	 * Output typography CSS as inline style.
	 *
	 * Runs during wp_enqueue_scripts after base CSS is enqueued.
	 */
	public static function output_typography_css() {
		// Skip if global typography is disabled.
		if ( ! self::is_enabled() ) {
			return;
		}

		$css = self::generate_css();

		if ( empty( $css ) ) {
			return;
		}

		// wp_strip_all_tags() is the correct sanitization for wp_add_inline_style() per WordPress core pattern.
		// CSS values are pre-sanitized in get_typography_variables() using controlled property names and validated units.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_strip_all_tags() is appropriate for CSS context.
		wp_add_inline_style( 'blockflow-typography', wp_strip_all_tags( $css ) );
	}

	/**
	 * Output typography CSS in the editor.
	 */
	public static function output_editor_typography_css() {
		// Skip if global typography is disabled.
		if ( ! self::is_enabled() ) {
			return;
		}

		$css = self::generate_css( true );

		if ( empty( $css ) ) {
			return;
		}

		// wp_strip_all_tags() is the correct sanitization for wp_add_inline_style() per WordPress core pattern.
		// CSS values are pre-sanitized in get_typography_variables() using controlled property names and validated units.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_strip_all_tags() is appropriate for CSS context.
		wp_add_inline_style( 'blockflow-typography', wp_strip_all_tags( $css ) );
	}

	/**
	 * Get typography settings.
	 *
	 * @return array Typography settings.
	 */
	private static function get_settings() {
		if ( null === self::$settings ) {
			self::$settings = Admin\Admin_Settings::get( 'typography', [] );
		}
		return self::$settings;
	}

	/**
	 * Generate CSS from typography settings.
	 *
	 * @param bool $for_editor Whether generating for editor context.
	 * @return string Generated CSS.
	 */
	public static function generate_css( $for_editor = false ) {
		// Use cache for frontend.
		if ( ! $for_editor && null !== self::$cached_css ) {
			return self::$cached_css;
		}

		$typography = self::get_settings();

		if ( empty( $typography ) ) {
			return '';
		}

		$css = new CSS_Builder();

		// Root selector for CSS variables.
		$root_selector = $for_editor ? '.editor-styles-wrapper' : ':root';

		// Desktop variables (base).
		$desktop_vars = [];
		foreach ( self::ELEMENTS as $element_key => $var_prefix ) {
			$element_settings = $typography[ $element_key ] ?? [];

			// Skip disabled elements.
			if ( isset( $element_settings['enabled'] ) && false === $element_settings['enabled'] ) {
				continue;
			}

			$desktop = $element_settings['desktop'] ?? [];
			if ( ! empty( $desktop ) ) {
				$desktop_vars = array_merge( $desktop_vars, self::get_typography_variables( $var_prefix, $desktop ) );
			}
		}

		if ( ! empty( $desktop_vars ) ) {
			$css->set_selector( $root_selector );
			foreach ( $desktop_vars as $name => $value ) {
				$css->add_property( $name, $value );
			}
		}

		// Tablet variables.
		$tablet_vars = [];
		foreach ( self::ELEMENTS as $element_key => $var_prefix ) {
			$element_settings = $typography[ $element_key ] ?? [];

			if ( isset( $element_settings['enabled'] ) && false === $element_settings['enabled'] ) {
				continue;
			}

			$tablet = $element_settings['tablet'] ?? [];
			if ( ! empty( $tablet ) ) {
				$tablet_vars = array_merge( $tablet_vars, self::get_typography_variables( $var_prefix, $tablet ) );
			}
		}

		if ( ! empty( $tablet_vars ) ) {
			$css->start_media_query( 'tablet' );
			$css->set_selector( $root_selector );
			foreach ( $tablet_vars as $name => $value ) {
				$css->add_property( $name, $value );
			}
			$css->end_media_query();
		}

		// Mobile variables.
		$mobile_vars = [];
		foreach ( self::ELEMENTS as $element_key => $var_prefix ) {
			$element_settings = $typography[ $element_key ] ?? [];

			if ( isset( $element_settings['enabled'] ) && false === $element_settings['enabled'] ) {
				continue;
			}

			$mobile = $element_settings['mobile'] ?? [];
			if ( ! empty( $mobile ) ) {
				$mobile_vars = array_merge( $mobile_vars, self::get_typography_variables( $var_prefix, $mobile ) );
			}
		}

		if ( ! empty( $mobile_vars ) ) {
			$css->start_media_query( 'mobile' );
			$css->set_selector( $root_selector );
			foreach ( $mobile_vars as $name => $value ) {
				$css->add_property( $name, $value );
			}
			$css->end_media_query();
		}

		$output = $css->get_output();

		// Cache for frontend.
		if ( ! $for_editor ) {
			self::$cached_css = $output;
		}

		return $output;
	}

	/**
	 * Get typography CSS variables for an element.
	 *
	 * @param string $prefix   Variable prefix (h1, h2, p, etc.).
	 * @param array  $settings Typography settings for a device.
	 * @return array Associative array of variable name => value.
	 */
	private static function get_typography_variables( $prefix, $settings ) {
		$vars = [];

		// Font family - escape quotes for CSS safety.
		if ( ! empty( $settings['fontFamily'] ) ) {
			$family = str_replace( [ '"', "'" ], '', $settings['fontFamily'] );
			$vars[ '--blockflow-' . $prefix . '-font-family' ] = '"' . $family . '", sans-serif';
		}

		// Font size.
		if ( ! empty( $settings['fontSize']['value'] ) ) {
			$unit = $settings['fontSize']['unit'] ?? 'px';
			$vars[ '--blockflow-' . $prefix . '-font-size' ] = $settings['fontSize']['value'] . $unit;
		}

		// Font weight.
		if ( ! empty( $settings['fontWeight'] ) ) {
			$vars[ '--blockflow-' . $prefix . '-font-weight' ] = $settings['fontWeight'];
		}

		// Font style.
		if ( ! empty( $settings['fontStyle'] ) ) {
			$vars[ '--blockflow-' . $prefix . '-font-style' ] = $settings['fontStyle'];
		}

		// Text transform.
		if ( ! empty( $settings['textTransform'] ) ) {
			$vars[ '--blockflow-' . $prefix . '-text-transform' ] = $settings['textTransform'];
		}

		// Text decoration.
		if ( ! empty( $settings['textDecoration'] ) ) {
			$vars[ '--blockflow-' . $prefix . '-text-decoration' ] = $settings['textDecoration'];
		}

		// Line height.
		if ( ! empty( $settings['lineHeight']['value'] ) ) {
			$unit  = $settings['lineHeight']['unit'] ?? '';
			$value = $settings['lineHeight']['value'];
			$vars[ '--blockflow-' . $prefix . '-line-height' ] = $value . $unit;
		}

		// Letter spacing.
		if ( isset( $settings['letterSpacing']['value'] ) && '' !== $settings['letterSpacing']['value'] ) {
			$unit = $settings['letterSpacing']['unit'] ?? 'px';
			$vars[ '--blockflow-' . $prefix . '-letter-spacing' ] = $settings['letterSpacing']['value'] . $unit;
		}

		// Word spacing.
		if ( isset( $settings['wordSpacing']['value'] ) && '' !== $settings['wordSpacing']['value'] ) {
			$unit = $settings['wordSpacing']['unit'] ?? 'px';
			$vars[ '--blockflow-' . $prefix . '-word-spacing' ] = $settings['wordSpacing']['value'] . $unit;
		}

		return $vars;
	}

	/**
	 * Get fonts used in typography settings.
	 *
	 * Returns fonts with italic info using format: "ital,weight" (e.g., "0,400", "1,400").
	 * This format is compatible with Google Fonts API v2.
	 *
	 * @return array Array of font family => variants.
	 */
	public static function get_fonts() {
		$typography = self::get_settings();
		$fonts      = [];

		if ( empty( $typography ) ) {
			return $fonts;
		}

		foreach ( self::ELEMENTS as $element_key => $var_prefix ) {
			$element_settings = $typography[ $element_key ] ?? [];

			// Skip disabled elements.
			if ( isset( $element_settings['enabled'] ) && false === $element_settings['enabled'] ) {
				continue;
			}

			$desktop = $element_settings['desktop'] ?? [];

			if ( ! empty( $desktop['fontFamily'] ) ) {
				$family     = $desktop['fontFamily'];
				$weight     = ! empty( $desktop['fontWeight'] ) ? $desktop['fontWeight'] : '400';
				$font_style = $desktop['fontStyle'] ?? '';

				if ( ! isset( $fonts[ $family ] ) ) {
					$fonts[ $family ] = [];
				}

				// Build variant string: "ital,weight" format (e.g., "0,400" or "1,400").
				$is_italic   = 'italic' === $font_style;
				$variant_str = ( $is_italic ? '1' : '0' ) . ',' . $weight;

				if ( ! in_array( $variant_str, $fonts[ $family ], true ) ) {
					$fonts[ $family ][] = $variant_str;
				}
			}
		}

		return $fonts;
	}

	/**
	 * Clear cached CSS (call when settings change).
	 */
	public static function clear_cache() {
		self::$settings   = null;
		self::$cached_css = null;
	}
}
