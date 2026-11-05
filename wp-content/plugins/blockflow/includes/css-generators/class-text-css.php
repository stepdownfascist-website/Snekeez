<?php
/**
 * Text Block CSS Generator.
 *
 * Generates CSS for the Text block using CSS_Helpers.
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
 * Text CSS Generator class.
 */
class Text_CSS {

	/**
	 * Generate CSS for Text block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector         = '.blockflow-text.blockflow-text-' . $block_id;
		$content_selector = $selector . ' .blockflow-text__content';

		// Extract text-specific attributes.
		$alignment        = $attrs['alignment'] ?? [];
		$columns          = $attrs['columns'] ?? [];
		$columns_gap      = $attrs['columnsGap'] ?? [];
		$typography       = $attrs['typography'] ?? [];
		$text_color       = $attrs['textColor'] ?? [];
		$text_color_hover = $attrs['textColorHover'] ?? [];
		$text_stroke      = $attrs['textStroke'] ?? [];
		$text_shadow      = $attrs['textShadow'] ?? [];
		$blend_mode       = $attrs['blendMode'] ?? '';

		$stroke_enabled = $text_stroke['enabled'] ?? false;
		$shadow_enabled = $text_shadow['enabled'] ?? false;

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================

		// Wrapper: alignment, color variables, spacing, advanced layout.
		$css->set_selector( $selector );

		// Alignment (direct property).
		$desktop_align = $alignment['desktop'] ?? '';
		if ( $desktop_align ) {
			$css->add_property( 'text-align', $desktop_align );
		}

		// Color variables on wrapper for dark mode support.
		if ( ! empty( $text_color['light'] ) ) {
			$css->add_variable( 'text-color', $text_color['light'] );
		}
		if ( ! empty( $text_color_hover['light'] ) ) {
			$css->add_variable( 'text-color-hover', $text_color_hover['light'] );
		}

		// Stroke color as variable on wrapper (width on content selector).
		if ( $stroke_enabled && ! empty( $text_stroke['color']['light'] ) ) {
			$css->add_variable( 'text-stroke-c', $text_stroke['color']['light'] );
		}

		// Shadow color as variable on wrapper.
		if ( $shadow_enabled && ! empty( $text_shadow['color']['light'] ) ) {
			$css->add_variable( 'text-shadow-c', $text_shadow['color']['light'] );
		}

		// Common Advanced Tab CSS (spacing, advanced layout, animation).
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// Content element: typography, columns, stroke width, shadow, blend mode.
		$css->set_selector( $content_selector );

		// Typography (direct properties).
		CSS_Helpers::add_typography_css( $css, $typography, 'desktop' );

		// Columns (direct properties).
		$desktop_columns = $columns['desktop'] ?? '';
		if ( $desktop_columns ) {
			$css->add_property( 'column-count', $desktop_columns );
		}

		$desktop_gap = $columns_gap['desktop']['value'] ?? '';
		if ( $desktop_gap ) {
			$gap_unit = $columns_gap['desktop']['unit'] ?? 'px';
			$css->add_property( 'column-gap', $desktop_gap . $gap_unit );
		}

		// Stroke width (direct property - responsive).
		if ( $stroke_enabled ) {
			CSS_Helpers::add_text_stroke_width( $css, $text_stroke, 'desktop' );
		}

		// Text shadow (direct property with var for color).
		if ( $shadow_enabled ) {
			self::add_text_shadow_prop( $css, $text_shadow );
		}

		// Blend mode (direct property).
		if ( $blend_mode ) {
			$css->add_property( 'mix-blend-mode', $blend_mode );
		}

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );

		// Wrapper: alignment, advanced layout.
		$css->set_selector( $selector );

		$tablet_align = $alignment['tablet'] ?? '';
		if ( $tablet_align ) {
			$css->add_property( 'text-align', $tablet_align );
		}

		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		// Content element: typography, columns.
		$css->set_selector( $content_selector );

		CSS_Helpers::add_typography_css( $css, $typography, 'tablet' );

		$tablet_columns = $columns['tablet'] ?? '';
		if ( $tablet_columns ) {
			$css->add_property( 'column-count', $tablet_columns );
		}

		$tablet_gap = $columns_gap['tablet']['value'] ?? '';
		if ( $tablet_gap ) {
			$gap_unit = $columns_gap['tablet']['unit'] ?? $columns_gap['desktop']['unit'] ?? 'px';
			$css->add_property( 'column-gap', $tablet_gap . $gap_unit );
		}

		// Stroke width (responsive).
		if ( $stroke_enabled ) {
			CSS_Helpers::add_text_stroke_width( $css, $text_stroke, 'tablet' );
		}

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );

		// Wrapper: alignment, advanced layout.
		$css->set_selector( $selector );

		$mobile_align = $alignment['mobile'] ?? '';
		if ( $mobile_align ) {
			$css->add_property( 'text-align', $mobile_align );
		}

		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		// Content element: typography, columns.
		$css->set_selector( $content_selector );

		CSS_Helpers::add_typography_css( $css, $typography, 'mobile' );

		$mobile_columns = $columns['mobile'] ?? '';
		if ( $mobile_columns ) {
			$css->add_property( 'column-count', $mobile_columns );
		}

		$mobile_gap = $columns_gap['mobile']['value'] ?? '';
		if ( $mobile_gap ) {
			$gap_unit = $columns_gap['mobile']['unit'] ?? $columns_gap['desktop']['unit'] ?? 'px';
			$css->add_property( 'column-gap', $mobile_gap . $gap_unit );
		}

		// Stroke width (responsive).
		if ( $stroke_enabled ) {
			CSS_Helpers::add_text_stroke_width( $css, $text_stroke, 'mobile' );
		}

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		self::add_text_dark_mode( $css, $selector, $text_color, $text_color_hover, $text_stroke, $text_shadow, $stroke_enabled, $shadow_enabled );

		// Common Advanced Tab dark mode (background, border colors).
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES (border + shadow)
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}



	/**
	 * Add text shadow CSS property.
	 * Direct property with embedded var() for color (dark mode support).
	 *
	 * @param CSS_Builder $css    CSS generator instance.
	 * @param array                    $shadow Text shadow values.
	 */
	private static function add_text_shadow_prop( $css, $shadow ) {
		$h    = $shadow['horizontal'] ?? '0';
		$v    = $shadow['vertical'] ?? '2';
		$blur = $shadow['blur'] ?? '4';

		// Build shadow value with var() for color.
		$css->add_property( 'text-shadow', $h . 'px ' . $v . 'px ' . $blur . 'px var(--blockflow-text-shadow-c, transparent)' );
	}

	/**
	 * Add text dark mode CSS (all variables on root selector).
	 *
	 * @param CSS_Builder $css              CSS generator.
	 * @param string                   $selector         Root block selector.
	 * @param array                    $text_color       Text color.
	 * @param array                    $text_color_hover Text color hover.
	 * @param array                    $text_stroke      Text stroke.
	 * @param array                    $text_shadow      Text shadow.
	 * @param bool                     $stroke_enabled   Whether stroke is enabled.
	 * @param bool                     $shadow_enabled   Whether shadow is enabled.
	 */
	private static function add_text_dark_mode( $css, $selector, $text_color, $text_color_hover, $text_stroke, $text_shadow, $stroke_enabled, $shadow_enabled ) {
		$dark_color       = $text_color['dark'] ?? '';
		$dark_color_hover = $text_color_hover['dark'] ?? '';
		$dark_stroke      = $stroke_enabled ? ( $text_stroke['color']['dark'] ?? '' ) : '';
		$dark_shadow      = $shadow_enabled ? ( $text_shadow['color']['dark'] ?? '' ) : '';

		if ( ! $dark_color && ! $dark_color_hover && ! $dark_stroke && ! $dark_shadow ) {
			return;
		}

		// All color variables on root selector.
		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_color, $dark_color_hover, $dark_stroke, $dark_shadow ) {
				if ( $dark_color ) {
					$css->add_variable( 'text-color', $dark_color );
				}
				if ( $dark_color_hover ) {
					$css->add_variable( 'text-color-hover', $dark_color_hover );
				}
				if ( $dark_stroke ) {
					$css->add_variable( 'text-stroke-c', $dark_stroke );
				}
				if ( $dark_shadow ) {
					$css->add_variable( 'text-shadow-c', $dark_shadow );
				}
			}
		);
	}
}
