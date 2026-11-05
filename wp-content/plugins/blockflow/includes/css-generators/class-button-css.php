<?php
/**
 * Button Block CSS Generator.
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
 * Button CSS Generator class.
 */
class Button_CSS {

	/**
	 * Generate CSS for Button block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector       = '.blockflow-button.blockflow-button-' . $block_id;
		$inner_selector = $selector . ' .blockflow-button__inner';
		$icon_selector  = $selector . ' .blockflow-button__icon';

		// Extract button-specific attributes.
		$alignment        = $attrs['alignment'] ?? [];
		$full_width       = $attrs['fullWidth'] ?? [];
		$typography       = $attrs['typography'] ?? [];
		$text_color       = $attrs['textColor'] ?? [];
		$bg_color         = $attrs['backgroundColor'] ?? [];
		$text_color_hover = $attrs['textColorHover'] ?? [];
		$bg_color_hover   = $attrs['backgroundColorHover'] ?? [];
		$button_padding   = $attrs['buttonPadding'] ?? [];
		$border           = $attrs['border'] ?? [];
		$border_hover     = $attrs['borderHover'] ?? [];
		$box_shadow       = $attrs['boxShadow'] ?? [];
		$box_shadow_hover = $attrs['boxShadowHover'] ?? [];
		$icon             = $attrs['icon'] ?? [];
		$icon_size        = $attrs['iconSize'] ?? [];
		$icon_spacing     = $attrs['iconSpacing'] ?? [];

		$icon_enabled = ! empty( $icon['enabled'] );

		// Create modified attrs for advanced tab CSS (exclude border/boxShadow - Button handles these).
		$advanced_attrs = $attrs;
		unset( $advanced_attrs['border'] );
		unset( $advanced_attrs['boxShadow'] );
		unset( $advanced_attrs['borderHover'] );
		unset( $advanced_attrs['boxShadowHover'] );

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================

		// Wrapper: alignment as direct properties, colors as CSS variables.
		$css->set_selector( $selector );
		self::add_alignment_props( $css, $alignment, 'desktop' );
		self::add_full_width_props( $css, $full_width, 'desktop' );
		self::add_colors( $css, $text_color, $bg_color, $text_color_hover, $bg_color_hover );
		self::add_box_shadow( $css, $box_shadow );

		// Icon spacing as CSS variable (used in gap).
		if ( $icon_enabled ) {
			self::add_icon_spacing_var( $css, $icon_spacing, 'desktop' );
		}

		// Border color as CSS variable.
		self::add_border_color_var( $css, $border, 'desktop' );

		// Padding as CSS variable (shorthand) on root selector.
		CSS_Helpers::add_padding_var( $css, $button_padding['desktop'] ?? [], 'button' );

		// Common Advanced Tab CSS.
		CSS_Helpers::add_advanced_tab_css( $css, $advanced_attrs, 'desktop', true );

		// Inner: typography, border style/width/radius as direct properties.
		$css->set_selector( $inner_selector );
		CSS_Helpers::add_typography_css( $css, $typography, 'desktop' );
		self::add_border_props( $css, $border, 'desktop' );

		// Icon: size as direct properties.
		if ( $icon_enabled ) {
			$css->set_selector( $icon_selector );
			self::add_icon_size_props( $css, $icon_size, 'desktop' );
		}

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );

		// Wrapper.
		$css->set_selector( $selector );
		self::add_alignment_props( $css, $alignment, 'tablet' );
		self::add_full_width_props( $css, $full_width, 'tablet' );
		if ( $icon_enabled ) {
			self::add_icon_spacing_var( $css, $icon_spacing, 'tablet' );
		}
		CSS_Helpers::add_padding_var( $css, $button_padding['tablet'] ?? [], 'button' );
		CSS_Helpers::add_advanced_tab_css( $css, $advanced_attrs, 'tablet', false );

		// Inner.
		$css->set_selector( $inner_selector );
		CSS_Helpers::add_typography_css( $css, $typography, 'tablet' );
		self::add_border_props( $css, $border, 'tablet' );

		// Icon.
		if ( $icon_enabled ) {
			$css->set_selector( $icon_selector );
			self::add_icon_size_props( $css, $icon_size, 'tablet' );
		}

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );

		// Wrapper.
		$css->set_selector( $selector );
		self::add_alignment_props( $css, $alignment, 'mobile' );
		self::add_full_width_props( $css, $full_width, 'mobile' );
		if ( $icon_enabled ) {
			self::add_icon_spacing_var( $css, $icon_spacing, 'mobile' );
		}
		CSS_Helpers::add_padding_var( $css, $button_padding['mobile'] ?? [], 'button' );
		CSS_Helpers::add_advanced_tab_css( $css, $advanced_attrs, 'mobile', false );

		// Inner.
		$css->set_selector( $inner_selector );
		CSS_Helpers::add_typography_css( $css, $typography, 'mobile' );
		self::add_border_props( $css, $border, 'mobile' );

		// Icon.
		if ( $icon_enabled ) {
			$css->set_selector( $icon_selector );
			self::add_icon_size_props( $css, $icon_size, 'mobile' );
		}

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		self::add_dark_mode( $css, $selector, $text_color, $bg_color, $text_color_hover, $bg_color_hover, $box_shadow, $box_shadow_hover, $border );

		// Common Advanced Tab dark mode.
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $advanced_attrs );

		// =====================================================================
		// HOVER STATES
		// =====================================================================
		self::add_hover_styles( $css, $selector, $inner_selector, $border_hover, $box_shadow_hover );
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $advanced_attrs );
	}

	/**
	 * Add alignment CSS properties.
	 *
	 * @param CSS_Builder $css       CSS generator.
	 * @param array                    $alignment Alignment attribute.
	 * @param string                   $device    Device key.
	 */
	private static function add_alignment_props( $css, $alignment, $device ) {
		$value = $alignment[ $device ] ?? '';

		if ( '' === $value ) {
			return;
		}

		// Use CSS variable for alignment (flex-start, center, flex-end).
		$css->add_variable( 'btn-align', CSS_Helpers::align_to_justify( $value ) );
	}

	/**
	 * Add full width CSS properties.
	 *
	 * @param CSS_Builder $css        CSS generator.
	 * @param array                    $full_width Full width attribute.
	 * @param string                   $device     Device key.
	 */
	private static function add_full_width_props( $css, $full_width, $device ) {
		$value = $full_width[ $device ] ?? '';

		// Skip if empty or inline (default).
		if ( '' === $value || 'inline' === $value ) {
			return;
		}

		if ( 'full' === $value ) {
			$css->add_property( 'width', '100%' );
			// Use CSS variable to stretch inner element.
			$css->add_variable( 'btn-inner-width', '100%' );
		}
	}

	/**
	 * Add color CSS variables (on root selector).
	 *
	 * @param CSS_Builder $css              CSS generator.
	 * @param array                    $text_color       Text color.
	 * @param array                    $bg_color         Background color.
	 * @param array                    $text_color_hover Text color hover.
	 * @param array                    $bg_color_hover   Background color hover.
	 */
	private static function add_colors( $css, $text_color, $bg_color, $text_color_hover, $bg_color_hover ) {
		if ( ! empty( $text_color['light'] ) ) {
			$css->add_variable( 'btn-color', $text_color['light'] );
		}
		if ( ! empty( $bg_color['light'] ) ) {
			$css->add_variable( 'btn-bg', $bg_color['light'] );
		}
		if ( ! empty( $text_color_hover['light'] ) ) {
			$css->add_variable( 'btn-color-hover', $text_color_hover['light'] );
		}
		if ( ! empty( $bg_color_hover['light'] ) ) {
			$css->add_variable( 'btn-bg-hover', $bg_color_hover['light'] );
		}
	}

	/**
	 * Add border CSS properties (direct properties on inner selector).
	 * Border color is handled separately as CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $border Border attribute.
	 * @param string                   $device Device key.
	 */
	private static function add_border_props( $css, $border, $device ) {
		$device_border = $border[ $device ] ?? [];
		if ( empty( $device_border ) ) {
			return;
		}

		// Border style.
		$border_style = $device_border['style'] ?? '';
		if ( '' !== $border_style ) {
			$css->add_property( 'border-style', $border_style );
		}

		// Border width.
		$width = $device_border['width'] ?? [];
		CSS_Helpers::add_border_width( $css, $width );

		// Border radius.
		$radius = $device_border['radius'] ?? [];
		CSS_Helpers::add_border_radius( $css, $radius );
	}

	/**
	 * Add border color CSS variable (on root selector).
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $border Border attribute.
	 * @param string                   $device Device key.
	 */
	private static function add_border_color_var( $css, $border, $device ) {
		if ( 'desktop' !== $device ) {
			return;
		}

		$device_border = $border[ $device ] ?? [];
		$border_color  = $device_border['color']['light'] ?? '';

		if ( '' !== $border_color ) {
			$css->add_variable( 'btn-bc', $border_color );
		}
	}

	/**
	 * Add icon size CSS properties (direct properties on icon selector).
	 *
	 * @param CSS_Builder $css       CSS generator.
	 * @param array                    $icon_size Icon size attribute.
	 * @param string                   $device    Device key.
	 */
	private static function add_icon_size_props( $css, $icon_size, $device ) {
		$device_size = $icon_size[ $device ] ?? [];
		$size_value  = $device_size['value'] ?? '';
		$size_unit   = $device_size['unit'] ?? 'px';

		if ( '' === $size_value ) {
			return;
		}

		$css->add_property( 'width', $size_value . $size_unit );
		$css->add_property( 'height', $size_value . $size_unit );
	}

	/**
	 * Add icon spacing CSS variable (on root selector, used in gap).
	 *
	 * @param CSS_Builder $css          CSS generator.
	 * @param array                    $icon_spacing Icon spacing attribute.
	 * @param string                   $device       Device key.
	 */
	private static function add_icon_spacing_var( $css, $icon_spacing, $device ) {
		$device_spacing = $icon_spacing[ $device ] ?? [];
		$spacing_value  = $device_spacing['value'] ?? '';
		$spacing_unit   = $device_spacing['unit'] ?? 'px';

		if ( '' === $spacing_value ) {
			return;
		}

		$css->add_variable( 'btn-icon-gap', $spacing_value . $spacing_unit );
	}

	/**
	 * Add box shadow CSS variable.
	 *
	 * @param CSS_Builder $css        CSS generator.
	 * @param array                    $box_shadow Box shadow attribute.
	 */
	private static function add_box_shadow( $css, $box_shadow ) {
		if ( empty( $box_shadow ) || empty( $box_shadow['enabled'] ) ) {
			return;
		}

		$shadow = CSS_Helpers::build_box_shadow( $box_shadow );
		if ( $shadow && 'none' !== $shadow ) {
			$css->add_variable( 'btn-shadow', $shadow );
		}
	}

	/**
	 * Add hover state styles.
	 *
	 * @param CSS_Builder $css              CSS generator.
	 * @param string                   $selector         Block selector.
	 * @param string                   $inner_selector   Inner selector.
	 * @param array                    $border_hover     Border hover attribute.
	 * @param array                    $box_shadow_hover Box shadow hover attribute.
	 */
	private static function add_hover_styles( $css, $selector, $inner_selector, $border_hover, $box_shadow_hover ) {
		$desktop_hover = $border_hover['desktop'] ?? [];

		// Check if there's any hover value.
		$has_border_hover = ! empty( $desktop_hover['style'] ) ||
			! empty( $desktop_hover['color']['light'] ) ||
			! empty( $desktop_hover['width'] ) ||
			! empty( $desktop_hover['radius'] );
		$has_shadow_hover = ! empty( $box_shadow_hover ) && ! empty( $box_shadow_hover['enabled'] );

		if ( ! $has_border_hover && ! $has_shadow_hover ) {
			return;
		}

		// Border color hover as CSS variable on root.
		$border_color_hover = $desktop_hover['color']['light'] ?? '';
		if ( '' !== $border_color_hover ) {
			$css->set_selector( $selector );
			$css->add_variable( 'btn-bc-hover', $border_color_hover );
		}

		// Box shadow hover as CSS variable on root.
		if ( $has_shadow_hover ) {
			$shadow_hover = CSS_Helpers::build_box_shadow( $box_shadow_hover );
			if ( $shadow_hover && 'none' !== $shadow_hover ) {
				$css->set_selector( $selector );
				$css->add_variable( 'btn-shadow-hover', $shadow_hover );
			}
		}

		// Border style/width/radius hover as direct properties on inner:hover.
		$has_inner_hover = ! empty( $desktop_hover['style'] ) ||
			! empty( $desktop_hover['width'] ) ||
			! empty( $desktop_hover['radius'] );

		if ( ! $has_inner_hover ) {
			return;
		}

		$css->set_selector( $selector . ':hover .blockflow-button__inner' );

		// Border style.
		$border_style = $desktop_hover['style'] ?? '';
		if ( '' !== $border_style ) {
			$css->add_property( 'border-style', $border_style );
		}

		// Border width.
		$width = $desktop_hover['width'] ?? [];
		CSS_Helpers::add_border_width( $css, $width );

		// Border radius hover.
		$radius = $desktop_hover['radius'] ?? [];
		CSS_Helpers::add_border_radius( $css, $radius );
	}

	/**
	 * Add dark mode CSS.
	 *
	 * @param CSS_Builder $css              CSS generator.
	 * @param string                   $selector         Block selector.
	 * @param array                    $text_color       Text color.
	 * @param array                    $bg_color         Background color.
	 * @param array                    $text_color_hover Text color hover.
	 * @param array                    $bg_color_hover   Background color hover.
	 * @param array                    $box_shadow       Box shadow.
	 * @param array                    $box_shadow_hover Box shadow hover.
	 * @param array                    $border           Border.
	 */
	private static function add_dark_mode( $css, $selector, $text_color, $bg_color, $text_color_hover, $bg_color_hover, $box_shadow = [], $box_shadow_hover = [], $border = [] ) {
		$dark_text_color       = $text_color['dark'] ?? '';
		$dark_bg_color         = $bg_color['dark'] ?? '';
		$dark_text_color_hover = $text_color_hover['dark'] ?? '';
		$dark_bg_color_hover   = $bg_color_hover['dark'] ?? '';
		$dark_shadow           = $box_shadow['color']['dark'] ?? '';
		$dark_shadow_hover     = $box_shadow_hover['color']['dark'] ?? '';
		$dark_border           = $border['desktop']['color']['dark'] ?? '';

		$has_dark = $dark_text_color || $dark_bg_color || $dark_text_color_hover || $dark_bg_color_hover || $dark_shadow || $dark_shadow_hover || $dark_border;

		if ( ! $has_dark ) {
			return;
		}

		// Use helper to handle dark mode method.
		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_text_color, $dark_bg_color, $dark_text_color_hover, $dark_bg_color_hover, $dark_border, $dark_shadow, $dark_shadow_hover, $box_shadow, $box_shadow_hover ) {
				if ( $dark_text_color ) {
					$css->add_variable( 'btn-color', $dark_text_color );
				}
				if ( $dark_bg_color ) {
					$css->add_variable( 'btn-bg', $dark_bg_color );
				}
				if ( $dark_text_color_hover ) {
					$css->add_variable( 'btn-color-hover', $dark_text_color_hover );
				}
				if ( $dark_bg_color_hover ) {
					$css->add_variable( 'btn-bg-hover', $dark_bg_color_hover );
				}
				if ( $dark_border ) {
					$css->add_variable( 'btn-bc', $dark_border );
				}
				if ( $dark_shadow && ! empty( $box_shadow['enabled'] ) ) {
					$shadow_value = CSS_Helpers::build_box_shadow( $box_shadow, $dark_shadow, false );
					$css->add_variable( 'btn-shadow', $shadow_value );
				}
				if ( $dark_shadow_hover && ! empty( $box_shadow_hover['enabled'] ) ) {
					$shadow_value = CSS_Helpers::build_box_shadow( $box_shadow_hover, $dark_shadow_hover, false );
					$css->add_variable( 'btn-shadow-hover', $shadow_value );
				}
			}
		);
	}
}
