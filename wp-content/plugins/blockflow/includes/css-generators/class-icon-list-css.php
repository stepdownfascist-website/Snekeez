<?php
/**
 * Icon List Block CSS Generator.
 *
 * Generates CSS for the Icon List block.
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
 * Icon List CSS Generator class.
 */
class Icon_List_CSS {

	/**
	 * Generate CSS for Icon List block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector = '.blockflow-icon-list.blockflow-icon-list-' . $block_id;

		// Extract attributes.
		$view          = $attrs['view'] ?? 'list';
		$icon_position = $attrs['iconPosition'] ?? 'before';
		$columns       = $attrs['columns'] ?? [];
		$gap           = $attrs['gap'] ?? [];
		$item_gap      = $attrs['itemGap'] ?? [];
		$alignment     = $attrs['alignment'] ?? [];
		$icon_size     = $attrs['iconSize'] ?? [];
		$icon_padding  = $attrs['iconPadding'] ?? [];
		$typography    = $attrs['typography'] ?? [];
		$icon_shape    = $attrs['iconShape'] ?? 'square';

		// Icon border attributes.
		$icon_border_width  = $attrs['iconBorderWidth'] ?? [];
		$icon_border_radius = $attrs['iconBorderRadius'] ?? [];

		// Build colors array for icon helper.
		$icon_colors = [
			'color'       => $attrs['iconColor'] ?? [],
			'colorHover'  => $attrs['iconColorHover'] ?? [],
			'bg'          => $attrs['iconBgColor'] ?? [],
			'bgHover'     => $attrs['iconBgColorHover'] ?? [],
			'border'      => $attrs['iconBorderColor'] ?? [],
			'borderHover' => $attrs['iconBorderColorHover'] ?? [],
		];

		// Text colors (separate from icon colors).
		$text_color       = $attrs['textColor'] ?? [];
		$text_color_hover = $attrs['textColorHover'] ?? [];

		$is_grid = 'grid' === $view;

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================
		$css->set_selector( $selector );

		// Layout (uses centralized helpers).
		CSS_Helpers::add_responsive_value_var( $css, $gap, 'desktop', 'gap', '', 'px' );
		CSS_Helpers::add_responsive_value_var( $css, $item_gap, 'desktop', 'icon-list-item-gap', '', 'px' );

		if ( $is_grid ) {
			self::add_columns( $css, $columns, 'desktop' );
		}

		// Alignment and icon position.
		self::add_alignment( $css, $alignment, 'desktop' );
		self::add_icon_position( $css, $icon_position );

		// Icon size and padding (uses centralized helper).
		CSS_Helpers::add_icon_size_vars( $css, $icon_size, 'desktop', 'icon-list-icon', '40' );
		CSS_Helpers::add_padding_var( $css, $icon_padding['desktop'] ?? [], 'icon-list-icon' );

		// Icon colors (uses centralized helper).
		CSS_Helpers::add_icon_color_vars( $css, $icon_colors, 'icon-list-icon' );

		// Text colors (separate variables).
		self::add_text_colors( $css, $text_color, $text_color_hover );

		// Icon border width and radius (CSS variables on root selector).
		CSS_Helpers::add_icon_border_width_var( $css, $icon_border_width['desktop'] ?? [], 'icon-list-icon' );
		if ( 'circle' !== $icon_shape ) {
			CSS_Helpers::add_icon_border_radius_var( $css, $icon_border_radius['desktop'] ?? [], 'icon-list-icon' );
		}

		// Typography.
		self::add_typography( $css, $typography, 'desktop' );

		// Common Advanced Tab CSS.
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );
		$css->set_selector( $selector );

		CSS_Helpers::add_responsive_value_var( $css, $gap, 'tablet', 'gap', '', 'px' );
		CSS_Helpers::add_responsive_value_var( $css, $item_gap, 'tablet', 'icon-list-item-gap', '', 'px' );

		if ( $is_grid ) {
			self::add_columns( $css, $columns, 'tablet' );
		}

		self::add_alignment( $css, $alignment, 'tablet' );
		CSS_Helpers::add_icon_size_vars( $css, $icon_size, 'tablet', 'icon-list-icon', '40' );
		CSS_Helpers::add_padding_var( $css, $icon_padding['tablet'] ?? [], 'icon-list-icon' );
		self::add_typography( $css, $typography, 'tablet' );

		// Icon border width and radius (CSS variables on root selector).
		CSS_Helpers::add_icon_border_width_var( $css, $icon_border_width['tablet'] ?? [], 'icon-list-icon' );
		if ( 'circle' !== $icon_shape ) {
			CSS_Helpers::add_icon_border_radius_var( $css, $icon_border_radius['tablet'] ?? [], 'icon-list-icon' );
		}

		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );
		$css->set_selector( $selector );

		CSS_Helpers::add_responsive_value_var( $css, $gap, 'mobile', 'gap', '', 'px' );
		CSS_Helpers::add_responsive_value_var( $css, $item_gap, 'mobile', 'icon-list-item-gap', '', 'px' );

		if ( $is_grid ) {
			self::add_columns( $css, $columns, 'mobile' );
		}

		self::add_alignment( $css, $alignment, 'mobile' );
		CSS_Helpers::add_icon_size_vars( $css, $icon_size, 'mobile', 'icon-list-icon', '40' );
		CSS_Helpers::add_padding_var( $css, $icon_padding['mobile'] ?? [], 'icon-list-icon' );
		self::add_typography( $css, $typography, 'mobile' );

		// Icon border width and radius (CSS variables on root selector).
		CSS_Helpers::add_icon_border_width_var( $css, $icon_border_width['mobile'] ?? [], 'icon-list-icon' );
		if ( 'circle' !== $icon_shape ) {
			CSS_Helpers::add_icon_border_radius_var( $css, $icon_border_radius['mobile'] ?? [], 'icon-list-icon' );
		}

		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES (uses centralized helper for icon colors)
		// =====================================================================
		CSS_Helpers::add_icon_color_dark_mode( $css, $selector, $icon_colors, 'icon-list-icon' );
		self::add_text_colors_dark_mode( $css, $selector, $text_color, $text_color_hover );
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES (border + shadow)
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add columns CSS variable.
	 *
	 * Uses shared 'columns' variable for grid class.
	 *
	 * @param CSS_Builder $css     CSS generator.
	 * @param array                    $columns Columns attribute.
	 * @param string                   $device  Device key.
	 */
	private static function add_columns( $css, $columns, $device ) {
		$device_columns = $columns[ $device ] ?? [];
		$value          = $device_columns['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$css->add_variable( 'columns', $value );
	}

	/**
	 * Add alignment CSS variable.
	 *
	 * Icon List uses start/center/end instead of flex-start/center/flex-end.
	 *
	 * @param CSS_Builder $css       CSS generator.
	 * @param array                    $alignment Alignment attribute.
	 * @param string                   $device    Device key.
	 */
	private static function add_alignment( $css, $alignment, $device ) {
		$value = $alignment[ $device ] ?? '';

		if ( '' === $value ) {
			return;
		}

		// Map alignment to CSS values (simpler for grid-column-start).
		$align_map = [
			'left'   => 'start',
			'center' => 'center',
			'right'  => 'end',
		];

		$css_value = $align_map[ $value ] ?? 'start';
		$css->add_variable( 'icon-list-align', $css_value );
	}

	/**
	 * Add icon position CSS variable.
	 *
	 * @param CSS_Builder $css           CSS generator.
	 * @param string                   $icon_position Icon position (before/after).
	 */
	private static function add_icon_position( $css, $icon_position ) {
		// Only output if not default.
		if ( 'after' === $icon_position ) {
			$css->add_variable( 'icon-list-direction', 'row-reverse' );
		}
	}

	/**
	 * Add text color CSS variables (light mode).
	 *
	 * @param CSS_Builder $css              CSS generator.
	 * @param array                    $text_color       Text color.
	 * @param array                    $text_color_hover Text color hover.
	 */
	private static function add_text_colors( $css, $text_color, $text_color_hover ) {
		if ( ! empty( $text_color['light'] ) ) {
			$css->add_variable( 'icon-list-text-color', $text_color['light'] );
		}
		if ( ! empty( $text_color_hover['light'] ) ) {
			$css->add_variable( 'icon-list-text-color-hover', $text_color_hover['light'] );
		}
	}

	/**
	 * Add text color dark mode CSS.
	 *
	 * @param CSS_Builder $css              CSS generator.
	 * @param string                   $selector         Block selector.
	 * @param array                    $text_color       Text color.
	 * @param array                    $text_color_hover Text color hover.
	 */
	private static function add_text_colors_dark_mode( $css, $selector, $text_color, $text_color_hover ) {
		$dark_text       = $text_color['dark'] ?? '';
		$dark_text_hover = $text_color_hover['dark'] ?? '';

		if ( ! $dark_text && ! $dark_text_hover ) {
			return;
		}

		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_text, $dark_text_hover ) {
				if ( $dark_text ) {
					$css->add_variable( 'icon-list-text-color', $dark_text );
				}
				if ( $dark_text_hover ) {
					$css->add_variable( 'icon-list-text-color-hover', $dark_text_hover );
				}
			}
		);
	}

	/**
	 * Add typography CSS variables.
	 *
	 * @param CSS_Builder $css        CSS generator.
	 * @param array                    $typography Typography attribute.
	 * @param string                   $device     Device key.
	 */
	private static function add_typography( $css, $typography, $device ) {
		if ( empty( $typography['enabled'] ) && false === $typography['enabled'] ) {
			return;
		}

		$device_typography = $typography[ $device ] ?? [];
		$desktop           = $typography['desktop'] ?? [];

		// Font family (non-responsive, only output on desktop).
		if ( 'desktop' === $device && ! empty( $desktop['fontFamily'] ) ) {
			$css->add_variable( 'icon-list-font-family', $desktop['fontFamily'] );
		}

		// Font size (responsive).
		$font_size = $device_typography['fontSize'] ?? [];
		if ( ! empty( $font_size['value'] ) ) {
			$unit = $font_size['unit'] ?? 'px';
			$css->add_variable( 'icon-list-font-size', $font_size['value'] . $unit );
		}

		// Font weight (non-responsive, only output on desktop).
		if ( 'desktop' === $device && ! empty( $desktop['fontWeight'] ) ) {
			$css->add_variable( 'icon-list-font-weight', $desktop['fontWeight'] );
		}

		// Line height (responsive).
		$line_height = $device_typography['lineHeight'] ?? [];
		if ( ! empty( $line_height['value'] ) ) {
			$unit = $line_height['unit'] ?? 'em';
			$css->add_variable( 'icon-list-line-height', $line_height['value'] . $unit );
		}

		// Letter spacing (responsive).
		$letter_spacing = $device_typography['letterSpacing'] ?? [];
		if ( ! empty( $letter_spacing['value'] ) ) {
			$unit = $letter_spacing['unit'] ?? 'px';
			$css->add_variable( 'icon-list-letter-spacing', $letter_spacing['value'] . $unit );
		}

		// Word spacing (responsive).
		$word_spacing = $device_typography['wordSpacing'] ?? [];
		if ( ! empty( $word_spacing['value'] ) ) {
			$unit = $word_spacing['unit'] ?? 'px';
			$css->add_variable( 'icon-list-word-spacing', $word_spacing['value'] . $unit );
		}

		// Text transform (non-responsive, only output on desktop).
		if ( 'desktop' === $device && ! empty( $desktop['textTransform'] ) ) {
			$css->add_variable( 'icon-list-text-transform', $desktop['textTransform'] );
		}

		// Text decoration (non-responsive, only output on desktop).
		if ( 'desktop' === $device && ! empty( $desktop['textDecoration'] ) ) {
			$css->add_variable( 'icon-list-text-decoration', $desktop['textDecoration'] );
		}

		// Font style (non-responsive, only output on desktop).
		if ( 'desktop' === $device && ! empty( $desktop['fontStyle'] ) ) {
			$css->add_variable( 'icon-list-font-style', $desktop['fontStyle'] );
		}
	}
}
