<?php
/**
 * Icon Block CSS Generator.
 *
 * Generates CSS for the Icon block using CSS_Helpers.
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
 * Icon CSS Generator class.
 */
class Icon_CSS {

	/**
	 * Generate CSS for Icon block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector = '.blockflow-icon.blockflow-icon-' . $block_id;

		// Extract icon-specific attributes.
		$size               = $attrs['size'] ?? [];
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
		// DESKTOP STYLES (base)
		// =====================================================================
		$css->set_selector( $selector );

		// Alignment (text-align + justify-content for flex).
		self::add_alignment( $css, $alignment, 'desktop' );

		// Size (uses centralized helper).
		CSS_Helpers::add_icon_size_vars( $css, $size, 'desktop', 'icon', '24' );

		// Padding, border width, and border radius.
		CSS_Helpers::add_padding_var( $css, $icon_padding['desktop'] ?? [], 'icon' );
		CSS_Helpers::add_border_width( $css, $icon_border_width['desktop'] ?? [] );
		if ( 'circle' !== $shape ) {
			CSS_Helpers::add_border_radius( $css, $icon_border_radius['desktop'] ?? [] );
		}

		// Colors (light mode - uses centralized helper).
		CSS_Helpers::add_icon_color_vars( $css, $colors, 'icon' );

		// Common Advanced Tab CSS.
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );
		$css->set_selector( $selector );

		self::add_alignment( $css, $alignment, 'tablet' );
		CSS_Helpers::add_icon_size_vars( $css, $size, 'tablet', 'icon', '24' );
		CSS_Helpers::add_padding_var( $css, $icon_padding['tablet'] ?? [], 'icon' );
		CSS_Helpers::add_border_width( $css, $icon_border_width['tablet'] ?? [] );
		if ( 'circle' !== $shape ) {
			CSS_Helpers::add_border_radius( $css, $icon_border_radius['tablet'] ?? [] );
		}

		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );
		$css->set_selector( $selector );

		self::add_alignment( $css, $alignment, 'mobile' );
		CSS_Helpers::add_icon_size_vars( $css, $size, 'mobile', 'icon', '24' );
		CSS_Helpers::add_padding_var( $css, $icon_padding['mobile'] ?? [], 'icon' );
		CSS_Helpers::add_border_width( $css, $icon_border_width['mobile'] ?? [] );
		if ( 'circle' !== $shape ) {
			CSS_Helpers::add_border_radius( $css, $icon_border_radius['mobile'] ?? [] );
		}

		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES (uses centralized helper)
		// =====================================================================
		CSS_Helpers::add_icon_color_dark_mode( $css, $selector, $colors, 'icon' );
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES (border + shadow)
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add alignment CSS variable (align-items for flex).
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

		// Use CSS variable for alignment (flex-start, center, flex-end).
		$css->add_variable( 'icon-align', CSS_Helpers::align_to_justify( $value ) );
	}
}
