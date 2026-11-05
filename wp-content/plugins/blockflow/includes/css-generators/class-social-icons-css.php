<?php
/**
 * Social Icons Block CSS Generator.
 *
 * Generates CSS for the Social Icons block using CSS_Helpers.
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
 * Social Icons CSS Generator class.
 */
class Social_Icons_CSS {

	/**
	 * Generate CSS for Social Icons block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector = '.blockflow-social-icons.blockflow-social-icons-' . $block_id;

		// Extract attributes.
		$size               = $attrs['size'] ?? [];
		$gap                = $attrs['gap'] ?? [];
		$alignment          = $attrs['alignment'] ?? [];
		$view               = $attrs['view'] ?? 'default';
		$shape              = $attrs['shape'] ?? 'rounded';
		$icon_padding       = $attrs['iconPadding'] ?? [];
		$icon_border_radius = $attrs['iconBorderRadius'] ?? [];

		// Global colors.
		$primary_color         = $attrs['primaryColor'] ?? [];
		$secondary_color       = $attrs['secondaryColor'] ?? [];
		$primary_color_hover   = $attrs['primaryColorHover'] ?? [];
		$secondary_color_hover = $attrs['secondaryColorHover'] ?? [];

		// Check if styled view (stacked or framed).
		$is_styled_view = in_array( $view, [ 'stacked', 'framed' ], true );

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================
		$css->set_selector( $selector );

		// Gap (uses centralized helper).
		CSS_Helpers::add_responsive_value_var( $css, $gap, 'desktop', 'social-gap', '', 'px' );

		// Alignment (uses centralized helper).
		CSS_Helpers::add_alignment_var( $css, $alignment, 'desktop', 'social-justify', '' );

		// Size (uses centralized helper).
		CSS_Helpers::add_icon_size_vars( $css, $size, 'desktop', 'social', '' );

		// Padding and border radius.
		if ( $is_styled_view ) {
			CSS_Helpers::add_padding_var( $css, $icon_padding['desktop'] ?? [], 'social' );
		}
		self::add_radius( $css, $selector, $icon_border_radius, 'desktop', $is_styled_view, $shape );

		// Global colors (light mode) - social icons has custom color logic.
		self::add_global_colors( $css, $is_styled_view, $view, $primary_color, $secondary_color, $primary_color_hover, $secondary_color_hover );

		// Common Advanced Tab CSS.
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );
		$css->set_selector( $selector );

		CSS_Helpers::add_responsive_value_var( $css, $gap, 'tablet', 'social-gap', '', 'px' );
		CSS_Helpers::add_alignment_var( $css, $alignment, 'tablet', 'social-justify', '' );
		CSS_Helpers::add_icon_size_vars( $css, $size, 'tablet', 'social', '24' );
		if ( $is_styled_view ) {
			CSS_Helpers::add_padding_var( $css, $icon_padding['tablet'] ?? [], 'social' );
		}
		self::add_radius( $css, $selector, $icon_border_radius, 'tablet', $is_styled_view, $shape );

		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );
		$css->set_selector( $selector );

		CSS_Helpers::add_responsive_value_var( $css, $gap, 'mobile', 'social-gap', '', 'px' );
		CSS_Helpers::add_alignment_var( $css, $alignment, 'mobile', 'social-justify', '' );
		CSS_Helpers::add_icon_size_vars( $css, $size, 'mobile', 'social', '24' );
		if ( $is_styled_view ) {
			CSS_Helpers::add_padding_var( $css, $icon_padding['mobile'] ?? [], 'social' );
		}
		self::add_radius( $css, $selector, $icon_border_radius, 'mobile', $is_styled_view, $shape );

		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES - Social icons has custom color logic
		// =====================================================================
		self::add_dark_mode( $css, $selector, $view, $primary_color, $secondary_color, $primary_color_hover, $secondary_color_hover );
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES (border + shadow)
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add border radius using direct CSS properties on inner element.
	 *
	 * @param CSS_Builder $css            CSS generator.
	 * @param string                   $selector       Block selector.
	 * @param array                    $radius         Icon border radius attribute.
	 * @param string                   $device         Device key.
	 * @param bool                     $is_styled_view Whether styled view.
	 * @param string                   $shape          Shape (square, rounded, circle).
	 */
	private static function add_radius( $css, $selector, $radius, $device, $is_styled_view, $shape ) {
		// Only for styled views, not circle.
		if ( ! $is_styled_view || 'circle' === $shape ) {
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
		$css->set_selector( $selector . ' .blockflow-social-icons__item' );

		// Use centralized helper for direct properties.
		CSS_Helpers::add_border_radius( $css, $device_radius );
	}

	/**
	 * Add global color CSS variables (light mode).
	 *
	 * Social Icons has custom color logic: primary/secondary for different view types.
	 *
	 * @param CSS_Builder $css                   CSS generator.
	 * @param bool                     $is_styled_view        Whether styled view.
	 * @param string                   $view                  View type.
	 * @param array                    $primary_color         Primary color.
	 * @param array                    $secondary_color       Secondary color.
	 * @param array                    $primary_color_hover   Primary hover color.
	 * @param array                    $secondary_color_hover Secondary hover color.
	 */
	private static function add_global_colors( $css, $is_styled_view, $view, $primary_color, $secondary_color, $primary_color_hover, $secondary_color_hover ) {
		if ( $is_styled_view ) {
			// Styled views: primary for icon color, secondary for background.
			if ( ! empty( $primary_color['light'] ) ) {
				$css->add_variable( 'social-color', $primary_color['light'] );
			}
			if ( ! empty( $secondary_color['light'] ) ) {
				$css->add_variable( 'social-bg', $secondary_color['light'] );
			}
			if ( 'framed' === $view && ! empty( $primary_color['light'] ) ) {
				$css->add_variable( 'social-border-color', $primary_color['light'] );
			}
			// Hover.
			if ( ! empty( $primary_color_hover['light'] ) ) {
				$css->add_variable( 'social-color-hover', $primary_color_hover['light'] );
			}
			if ( ! empty( $secondary_color_hover['light'] ) ) {
				$css->add_variable( 'social-bg-hover', $secondary_color_hover['light'] );
			}
			if ( 'framed' === $view && ! empty( $primary_color_hover['light'] ) ) {
				$css->add_variable( 'social-border-color-hover', $primary_color_hover['light'] );
			}
		} else {
			// Default view: primary for icon color only.
			if ( ! empty( $primary_color['light'] ) ) {
				$css->add_variable( 'social-color', $primary_color['light'] );
			}
			if ( ! empty( $primary_color_hover['light'] ) ) {
				$css->add_variable( 'social-color-hover', $primary_color_hover['light'] );
			}
		}
	}

	/**
	 * Add dark mode CSS.
	 *
	 * Social Icons has custom color logic: primary/secondary for different view types.
	 *
	 * @param CSS_Builder $css                   CSS generator.
	 * @param string                   $selector              Block selector.
	 * @param string                   $view                  View type.
	 * @param array                    $primary_color         Primary color.
	 * @param array                    $secondary_color       Secondary color.
	 * @param array                    $primary_color_hover   Primary hover color.
	 * @param array                    $secondary_color_hover Secondary hover color.
	 */
	private static function add_dark_mode( $css, $selector, $view, $primary_color, $secondary_color, $primary_color_hover, $secondary_color_hover ) {
		// Collect dark mode values.
		$dark_color        = $primary_color['dark'] ?? '';
		$dark_color_hover  = $primary_color_hover['dark'] ?? '';
		$dark_bg           = $secondary_color['dark'] ?? '';
		$dark_bg_hover     = $secondary_color_hover['dark'] ?? '';
		$dark_border       = '';
		$dark_border_hover = '';

		if ( 'framed' === $view ) {
			$dark_border       = $primary_color['dark'] ?? '';
			$dark_border_hover = $primary_color_hover['dark'] ?? '';
		}

		$has_dark_styles = $dark_color || $dark_color_hover || $dark_bg || $dark_bg_hover || $dark_border || $dark_border_hover;

		if ( ! $has_dark_styles ) {
			return;
		}

		// Use helper to handle dark mode method.
		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_color, $dark_color_hover, $dark_bg, $dark_bg_hover, $dark_border, $dark_border_hover ) {
				if ( $dark_color ) {
					$css->add_variable( 'social-color', $dark_color );
				}
				if ( $dark_color_hover ) {
					$css->add_variable( 'social-color-hover', $dark_color_hover );
				}
				if ( $dark_bg ) {
					$css->add_variable( 'social-bg', $dark_bg );
				}
				if ( $dark_bg_hover ) {
					$css->add_variable( 'social-bg-hover', $dark_bg_hover );
				}
				if ( $dark_border ) {
					$css->add_variable( 'social-border-color', $dark_border );
				}
				if ( $dark_border_hover ) {
					$css->add_variable( 'social-border-color-hover', $dark_border_hover );
				}
			}
		);
	}
}
