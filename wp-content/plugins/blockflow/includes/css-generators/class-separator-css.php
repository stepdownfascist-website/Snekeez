<?php
/**
 * Separator Block CSS Generator.
 *
 * Generates CSS for the Separator block.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow\CSS_Generators;

use ThemeRuby\BlockFlow\CSS_Builder;
use ThemeRuby\BlockFlow\CSS_Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Separator CSS Generator class.
 */
class Separator_CSS {

	/**
	 * Generate CSS for Separator block.
	 *
	 * @param array              $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector = '.blockflow-separator.blockflow-separator-' . $block_id;

		// Extract separator-specific attributes.
		$width     = $attrs['width'] ?? [];
		$weight    = $attrs['weight'] ?? [];
		$style     = $attrs['style'] ?? 'solid';
		$padding   = $attrs['padding'] ?? [];
		$color     = $attrs['color'] ?? [];
		$alignment = $attrs['alignment'] ?? [];

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================
		$css->set_selector( $selector );

		// Separator-specific: width, weight, style, padding, color, alignment.
		self::add_separator_vars( $css, $width, $weight, $style, $padding, $color, $alignment, 'desktop' );

		// Common Advanced Tab CSS (spacing, advanced layout, animation).
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );
		$css->set_selector( $selector );

		// Separator-specific.
		self::add_separator_vars( $css, $width, $weight, $style, $padding, $color, $alignment, 'tablet' );

		// Common Advanced Tab CSS (responsive).
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );
		$css->set_selector( $selector );

		// Separator-specific.
		self::add_separator_vars( $css, $width, $weight, $style, $padding, $color, $alignment, 'mobile' );

		// Common Advanced Tab CSS (responsive).
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		$dark_color = $color['dark'] ?? '';
		if ( $dark_color ) {
			CSS_Helpers::add_dark_mode_css(
				$css,
				$selector,
				function ( $css ) use ( $dark_color ) {
					$css->add_variable( 'sep-color', $dark_color );
				}
			);
		}

		// Common Advanced Tab dark mode (background, border colors).
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES (border + shadow)
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add separator-specific CSS variables.
	 *
	 * @param CSS_Builder $css       CSS generator.
	 * @param array                    $width     Width attribute.
	 * @param array                    $weight    Weight attribute.
	 * @param string                   $style     Style (solid, dashed, dotted, double).
	 * @param array                    $padding   Padding attribute.
	 * @param array                    $color     Color attribute.
	 * @param array                    $alignment Alignment attribute.
	 * @param string                   $device    Device key.
	 */
	private static function add_separator_vars( $css, $width, $weight, $style, $padding, $color, $alignment, $device ) {
		$is_desktop = 'desktop' === $device;

		// Width.
		$device_width = $width[ $device ] ?? [];
		$width_value  = $device_width['value'] ?? '';
		$width_unit   = $device_width['unit'] ?? '%';

		if ( '' !== $width_value ) {
			$css->add_variable( 'sep-width', $width_value . $width_unit );
		}

		// Weight.
		$device_weight = $weight[ $device ] ?? [];
		$weight_value  = $device_weight['value'] ?? '';
		$weight_unit   = $device_weight['unit'] ?? 'px';

		if ( '' !== $weight_value ) {
			$css->add_variable( 'sep-weight', $weight_value . $weight_unit );
		}

		// Style (desktop only, non-responsive).
		if ( $is_desktop && $style ) {
			$css->add_variable( 'sep-style', $style );
		}

		// Padding (4-sided).
		$device_padding = $padding[ $device ] ?? [];
		$padding_unit   = $device_padding['unit'] ?? 'px';

		if ( ! empty( $device_padding['top'] ) ) {
			$css->add_variable( 'sep-pt', $device_padding['top'] . $padding_unit );
		}
		if ( ! empty( $device_padding['right'] ) ) {
			$css->add_variable( 'sep-pr', $device_padding['right'] . $padding_unit );
		}
		if ( ! empty( $device_padding['bottom'] ) ) {
			$css->add_variable( 'sep-pb', $device_padding['bottom'] . $padding_unit );
		}
		if ( ! empty( $device_padding['left'] ) ) {
			$css->add_variable( 'sep-pl', $device_padding['left'] . $padding_unit );
		}

		// Color (desktop only, light mode).
		if ( $is_desktop && ! empty( $color['light'] ) ) {
			$css->add_variable( 'sep-color', $color['light'] );
		}

		// Alignment.
		$align_value = $alignment[ $device ] ?? '';
		if ( $align_value ) {
			$css->add_variable( 'sep-align', CSS_Helpers::align_to_justify( $align_value ) );
		}
	}
}
