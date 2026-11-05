<?php
/**
 * Icon Set Block CSS Generator.
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
 * Icon Set CSS Generator class.
 */
class Icon_Set_CSS {

	/**
	 * Generate CSS for Icon Set block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector = '.blockflow-icon-set.blockflow-icon-set-' . $block_id;

		// Extract attributes.
		$size               = $attrs['size'] ?? [];
		$gap                = $attrs['gap'] ?? [];
		$alignment          = $attrs['alignment'] ?? [];
		$shape              = $attrs['shape'] ?? 'square';
		$icon_padding       = $attrs['iconPadding'] ?? [];
		$icon_border_radius = $attrs['iconBorderRadius'] ?? [];
		$icon_border_width  = $attrs['iconBorderWidth'] ?? [];

		// Build colors array for helper.
		$colors = [
			'color'       => $attrs['iconColor'] ?? [],
			'colorHover'  => $attrs['iconColorHover'] ?? [],
			'bg'          => $attrs['iconBgColor'] ?? [],
			'bgHover'     => $attrs['iconBgColorHover'] ?? [],
			'border'      => $attrs['iconBorderColor'] ?? [],
			'borderHover' => $attrs['iconBorderColorHover'] ?? [],
		];

		// =====================================================================
		// DESKTOP STYLES
		// =====================================================================
		$css->set_selector( $selector );

		// Gap (uses centralized helper).
		CSS_Helpers::add_responsive_value_var( $css, $gap, 'desktop', 'iconset-gap', '', 'px' );

		// Alignment (uses centralized helper).
		CSS_Helpers::add_alignment_var( $css, $alignment, 'desktop', 'iconset-justify', '' );

		// Size (uses centralized helper).
		CSS_Helpers::add_icon_size_vars( $css, $size, 'desktop', 'iconset', '' );

		// Padding and border width.
		CSS_Helpers::add_padding_var( $css, $icon_padding['desktop'] ?? [], 'iconset' );
		self::add_border_width( $css, $icon_border_width, 'desktop' );
		self::add_radius( $css, $selector, $icon_border_radius, 'desktop', $shape );

		// Colors (light mode - uses centralized helper).
		CSS_Helpers::add_icon_color_vars( $css, $colors, 'iconset' );

		// Common Advanced Tab CSS.
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );
		$css->set_selector( $selector );

		CSS_Helpers::add_responsive_value_var( $css, $gap, 'tablet', 'iconset-gap', '', 'px' );
		CSS_Helpers::add_alignment_var( $css, $alignment, 'tablet', 'iconset-justify', '' );
		CSS_Helpers::add_icon_size_vars( $css, $size, 'tablet', 'iconset', '24' );
		CSS_Helpers::add_padding_var( $css, $icon_padding['tablet'] ?? [], 'iconset' );
		self::add_border_width( $css, $icon_border_width, 'tablet' );
		self::add_radius( $css, $selector, $icon_border_radius, 'tablet', $shape );

		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );
		$css->set_selector( $selector );

		CSS_Helpers::add_responsive_value_var( $css, $gap, 'mobile', 'iconset-gap', '', 'px' );
		CSS_Helpers::add_alignment_var( $css, $alignment, 'mobile', 'iconset-justify', '' );
		CSS_Helpers::add_icon_size_vars( $css, $size, 'mobile', 'iconset', '24' );
		CSS_Helpers::add_padding_var( $css, $icon_padding['mobile'] ?? [], 'iconset' );
		self::add_border_width( $css, $icon_border_width, 'mobile' );
		self::add_radius( $css, $selector, $icon_border_radius, 'mobile', $shape );

		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE (uses centralized helper)
		// =====================================================================
		CSS_Helpers::add_icon_color_dark_mode( $css, $selector, $colors, 'iconset' );
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add border width CSS variables.
	 *
	 * @param CSS_Builder $css          CSS generator.
	 * @param array                    $border_width Border width attribute.
	 * @param string                   $device       Device key.
	 */
	private static function add_border_width( $css, $border_width, $device ) {
		$device_width = $border_width[ $device ] ?? [];
		$unit         = $device_width['unit'] ?? 'px';

		$sides = [
			'top'    => 'iconset-border-top',
			'right'  => 'iconset-border-right',
			'bottom' => 'iconset-border-bottom',
			'left'   => 'iconset-border-left',
		];

		foreach ( $sides as $key => $var ) {
			$value = $device_width[ $key ] ?? '';
			if ( '' !== $value ) {
				$css->add_variable( $var, $value . $unit );
			}
		}
	}

	/**
	 * Add border radius using direct CSS properties on inner element.
	 *
	 * @param CSS_Builder $css      CSS generator.
	 * @param string                   $selector Block selector.
	 * @param array                    $radius   Icon border radius attribute.
	 * @param string                   $device   Device key.
	 * @param string                   $shape    Shape (square, rounded, circle).
	 */
	private static function add_radius( $css, $selector, $radius, $device, $shape ) {
		// Circle shape uses 50% via CSS class, skip.
		if ( 'circle' === $shape ) {
			return;
		}

		$device_radius = $radius[ $device ] ?? [];

		if ( empty( $device_radius ) ) {
			return;
		}

		// Check if any corner has value.
		$has_any = false;
		foreach ( [ 'topLeft', 'topRight', 'bottomRight', 'bottomLeft' ] as $key ) {
			if ( '' !== ( $device_radius[ $key ] ?? '' ) ) {
				$has_any = true;
				break;
			}
		}

		if ( ! $has_any ) {
			return;
		}

		// Set selector for inner item element.
		$css->set_selector( $selector . ' .blockflow-icon-set__item' );

		// Use centralized helper for direct properties.
		CSS_Helpers::add_border_radius( $css, $device_radius );
	}
}
