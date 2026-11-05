<?php
/**
 * Accordion Block CSS Generator.
 *
 * Generates ALL CSS for the Accordion block.
 * Uses CSS_Helpers for common patterns.
 *
 * Typography, padding are output as direct properties on child selectors.
 * Colors remain as CSS variables on root selector for dark mode support.
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
 * Accordion CSS Generator class.
 */
class Accordion_CSS {

	/**
	 * Generate CSS for Accordion block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector         = '.blockflow-accordion.blockflow-accordion-' . $block_id;
		$heading_selector = $selector . ' .blockflow-accordion-item__heading';

		// Extract attributes.
		$animation_duration   = $attrs['animationDuration'] ?? 300;
		$item_gap             = $attrs['itemGap'] ?? [];
		$item_background      = $attrs['itemBackground'] ?? [];
		$item_bg_hover        = $attrs['itemBackgroundHover'] ?? [];
		$item_bg_active       = $attrs['itemBackgroundActive'] ?? [];
		$item_border          = $attrs['itemBorder'] ?? [];
		$item_shadow          = $attrs['itemShadow'] ?? [];
		$header_padding       = $attrs['headerPadding'] ?? [];
		$content_padding      = $attrs['contentPadding'] ?? [];
		$content_background   = $attrs['contentBackground'] ?? [];
		$heading_typography   = $attrs['headingTypography'] ?? [];
		$heading_text_shadow  = $attrs['headingTextShadow'] ?? [];
		$heading_color        = $attrs['headingColor'] ?? [];
		$heading_color_hover  = $attrs['headingColorHover'] ?? [];
		$heading_color_active = $attrs['headingColorActive'] ?? [];
		$icon_size            = $attrs['iconSize'] ?? [];
		$icon_color           = $attrs['iconColor'] ?? [];
		$icon_color_hover     = $attrs['iconColorHover'] ?? [];
		$icon_color_active    = $attrs['iconColorActive'] ?? [];
		$title_icon_size      = $attrs['titleIconSize'] ?? [];
		$title_icon_color     = $attrs['titleIconColor'] ?? [];
		$header_align         = $attrs['headerAlign'] ?? [];

		$shadow_enabled = $heading_text_shadow['enabled'] ?? false;

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================

		// Wrapper: colors (as variables), animation, gap, item styles, advanced tab.
		$css->set_selector( $selector );

		// Animation duration.
		if ( '' !== $animation_duration && null !== $animation_duration ) {
			$css->add_variable( 'accordion-animation-duration', $animation_duration . 'ms' );
		}

		// Item gap.
		self::add_responsive_value( $css, $item_gap, 'desktop', 'accordion-gap', '0' );

		// Heading colors (variables for dark mode support).
		self::add_color( $css, $heading_color, 'accordion-heading-color' );
		self::add_color( $css, $heading_color_hover, 'accordion-heading-color-hover' );
		self::add_color( $css, $heading_color_active, 'accordion-heading-color-active' );

		// Heading text shadow color (variable for dark mode, value on inner selector).
		if ( $shadow_enabled && ! empty( $heading_text_shadow['color']['light'] ) ) {
			$css->add_variable( 'accordion-heading-shadow-color', $heading_text_shadow['color']['light'] );
		}

		// Icon size.
		self::add_responsive_value( $css, $icon_size, 'desktop', 'accordion-icon-size', '16', 'px' );

		// Icon colors.
		self::add_color( $css, $icon_color, 'accordion-icon-color' );
		self::add_color( $css, $icon_color_hover, 'accordion-icon-color-hover' );
		self::add_color( $css, $icon_color_active, 'accordion-icon-color-active' );

		// Title icon.
		self::add_responsive_value( $css, $title_icon_size, 'desktop', 'accordion-title-icon-size', '20', 'px' );
		self::add_color( $css, $title_icon_color, 'accordion-title-icon-color' );

		// Header alignment (responsive).
		$desktop_align = $header_align['desktop'] ?? '';
		if ( '' !== $desktop_align ) {
			$css->add_variable( 'accordion-header-align', $desktop_align );
			$css->add_variable( 'accordion-header-justify', self::align_to_justify( $desktop_align ) );
		}

		// Content background.
		self::add_color( $css, $content_background, 'accordion-content-bg' );

		// Common Advanced Tab CSS.
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// Item-level styles (all variables on wrapper for inheritance).
		self::add_color( $css, $item_background, 'accordion-item-bg' );
		CSS_Helpers::add_border_vars( $css, $item_border['desktop'] ?? [], 'accordion-item' );
		self::add_item_shadow( $css, $item_shadow );

		// Hover/active state variables.
		if ( ! empty( $item_bg_hover['light'] ) ) {
			$css->add_variable( 'accordion-item-bg-hover', $item_bg_hover['light'] );
		}
		if ( ! empty( $item_bg_active['light'] ) ) {
			$css->add_variable( 'accordion-item-bg-active', $item_bg_active['light'] );
		}

		// Padding as CSS variables (shorthand) on root selector.
		CSS_Helpers::add_padding_var( $css, $header_padding['desktop'] ?? [], 'accordion-header' );
		CSS_Helpers::add_padding_var( $css, $content_padding['desktop'] ?? [], 'accordion-content' );

		// Heading: typography as direct properties, text-shadow with var() for color.
		$css->set_selector( $heading_selector );
		CSS_Helpers::add_typography_css( $css, $heading_typography, 'desktop' );
		if ( $shadow_enabled ) {
			self::add_text_shadow_with_var( $css, $heading_text_shadow );
		}

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );

		// Wrapper.
		$css->set_selector( $selector );
		self::add_responsive_value( $css, $item_gap, 'tablet', 'accordion-gap' );
		self::add_responsive_value( $css, $icon_size, 'tablet', 'accordion-icon-size' );
		self::add_responsive_value( $css, $title_icon_size, 'tablet', 'accordion-title-icon-size' );
		$tablet_align = $header_align['tablet'] ?? '';
		if ( '' !== $tablet_align ) {
			$css->add_variable( 'accordion-header-align', $tablet_align );
			$css->add_variable( 'accordion-header-justify', self::align_to_justify( $tablet_align ) );
		}
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );
		CSS_Helpers::add_border_vars( $css, $item_border['tablet'] ?? [], 'accordion-item' );

		// Padding.
		CSS_Helpers::add_padding_var( $css, $header_padding['tablet'] ?? [], 'accordion-header' );
		CSS_Helpers::add_padding_var( $css, $content_padding['tablet'] ?? [], 'accordion-content' );

		// Heading.
		$css->set_selector( $heading_selector );
		CSS_Helpers::add_typography_css( $css, $heading_typography, 'tablet' );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );

		// Wrapper.
		$css->set_selector( $selector );
		self::add_responsive_value( $css, $item_gap, 'mobile', 'accordion-gap' );
		self::add_responsive_value( $css, $icon_size, 'mobile', 'accordion-icon-size' );
		self::add_responsive_value( $css, $title_icon_size, 'mobile', 'accordion-title-icon-size' );
		$mobile_align = $header_align['mobile'] ?? '';
		if ( '' !== $mobile_align ) {
			$css->add_variable( 'accordion-header-align', $mobile_align );
			$css->add_variable( 'accordion-header-justify', self::align_to_justify( $mobile_align ) );
		}
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );
		CSS_Helpers::add_border_vars( $css, $item_border['mobile'] ?? [], 'accordion-item' );

		// Padding.
		CSS_Helpers::add_padding_var( $css, $header_padding['mobile'] ?? [], 'accordion-header' );
		CSS_Helpers::add_padding_var( $css, $content_padding['mobile'] ?? [], 'accordion-content' );

		// Heading.
		$css->set_selector( $heading_selector );
		CSS_Helpers::add_typography_css( $css, $heading_typography, 'mobile' );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		self::add_dark_mode_styles(
			$css,
			$selector,
			$item_background,
			$item_bg_hover,
			$item_bg_active,
			$item_border,
			$heading_color,
			$heading_color_hover,
			$heading_color_active,
			$heading_text_shadow,
			$icon_color,
			$icon_color_hover,
			$icon_color_active,
			$content_background,
			$item_shadow,
			$title_icon_color
		);

		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add a responsive value CSS variable.
	 *
	 * @param CSS_Builder $css          CSS generator.
	 * @param array                    $attr         Attribute with device keys.
	 * @param string                   $device       Device key.
	 * @param string                   $var_name     CSS variable name.
	 * @param string                   $default      Default value to skip on desktop.
	 * @param string                   $default_unit Default unit.
	 */
	private static function add_responsive_value( $css, $attr, $device, $var_name, $default = '', $default_unit = 'px' ) {
		$device_val = $attr[ $device ] ?? [];
		$value      = $device_val['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_val['unit'] ?? $default_unit;

		$css->add_variable( $var_name, $value . $unit );
	}

	/**
	 * Add color CSS variable from { light, dark } structure.
	 *
	 * @param CSS_Builder $css      CSS generator.
	 * @param array                    $color    Color attribute { light, dark }.
	 * @param string                   $var_name CSS variable name.
	 */
	private static function add_color( $css, $color, $var_name ) {
		if ( ! empty( $color['light'] ) ) {
			$css->add_variable( $var_name, $color['light'] );
		}
	}

	/**
	 * Add text shadow CSS property with var() for color.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $shadow Text shadow values.
	 */
	private static function add_text_shadow_with_var( $css, $shadow ) {
		$h    = $shadow['horizontal'] ?? '0';
		$v    = $shadow['vertical'] ?? '0';
		$blur = $shadow['blur'] ?? '0';

		$value = sprintf(
			'%spx %spx %spx var(--blockflow-accordion-heading-shadow-color, transparent)',
			$h,
			$v,
			$blur
		);

		$css->add_property( 'text-shadow', $value );
	}

	/**
	 * Add item shadow CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $shadow Shadow attribute.
	 */
	private static function add_item_shadow( $css, $shadow ) {
		$value = CSS_Helpers::build_box_shadow( $shadow );
		if ( $value && 'none' !== $value ) {
			$css->add_variable( 'accordion-item-shadow', $value );
		}
	}

	/**
	 * Add dark mode CSS.
	 *
	 * All dark mode variables are set on the wrapper selector for inheritance.
	 *
	 * @param CSS_Builder $css                   CSS generator.
	 * @param string                   $selector              Main block selector.
	 * @param array                    $item_bg               Item background.
	 * @param array                    $item_bg_hover         Item background hover.
	 * @param array                    $item_bg_active        Item background active.
	 * @param array                    $item_border           Item border.
	 * @param array                    $heading_color         Heading color.
	 * @param array                    $heading_color_hover   Heading color hover.
	 * @param array                    $heading_color_active  Heading color active.
	 * @param array                    $heading_text_shadow   Heading text shadow.
	 * @param array                    $icon_color            Icon color.
	 * @param array                    $icon_color_hover      Icon color hover.
	 * @param array                    $icon_color_active     Icon color active.
	 * @param array                    $content_bg            Content background.
	 * @param array                    $item_shadow           Item shadow.
	 * @param array                    $title_icon_color      Title icon color.
	 */
	private static function add_dark_mode_styles(
		$css,
		$selector,
		$item_bg,
		$item_bg_hover,
		$item_bg_active,
		$item_border,
		$heading_color,
		$heading_color_hover,
		$heading_color_active,
		$heading_text_shadow,
		$icon_color,
		$icon_color_hover,
		$icon_color_active,
		$content_bg,
		$item_shadow,
		$title_icon_color
	) {
		$has_dark_styles = false;
		$dark_vars       = [];

		// Item backgrounds.
		if ( ! empty( $item_bg['dark'] ) ) {
			$dark_vars['accordion-item-bg-dark'] = $item_bg['dark'];
			$has_dark_styles                     = true;
		}
		if ( ! empty( $item_bg_hover['dark'] ) ) {
			$dark_vars['accordion-item-bg-hover-dark'] = $item_bg_hover['dark'];
			$has_dark_styles                           = true;
		}
		if ( ! empty( $item_bg_active['dark'] ) ) {
			$dark_vars['accordion-item-bg-active-dark'] = $item_bg_active['dark'];
			$has_dark_styles                            = true;
		}

		// Item border color.
		$border_color_dark = $item_border['desktop']['color']['dark'] ?? '';
		if ( '' !== $border_color_dark ) {
			$dark_vars['accordion-item-border-c-dark'] = $border_color_dark;
			$has_dark_styles                           = true;
		}

		// Heading colors.
		if ( ! empty( $heading_color['dark'] ) ) {
			$dark_vars['accordion-heading-color-dark'] = $heading_color['dark'];
			$has_dark_styles                           = true;
		}
		if ( ! empty( $heading_color_hover['dark'] ) ) {
			$dark_vars['accordion-heading-color-hover-dark'] = $heading_color_hover['dark'];
			$has_dark_styles                                 = true;
		}
		if ( ! empty( $heading_color_active['dark'] ) ) {
			$dark_vars['accordion-heading-color-active-dark'] = $heading_color_active['dark'];
			$has_dark_styles                                  = true;
		}

		// Icon colors.
		if ( ! empty( $icon_color['dark'] ) ) {
			$dark_vars['accordion-icon-color-dark'] = $icon_color['dark'];
			$has_dark_styles                        = true;
		}
		if ( ! empty( $icon_color_hover['dark'] ) ) {
			$dark_vars['accordion-icon-color-hover-dark'] = $icon_color_hover['dark'];
			$has_dark_styles                              = true;
		}
		if ( ! empty( $icon_color_active['dark'] ) ) {
			$dark_vars['accordion-icon-color-active-dark'] = $icon_color_active['dark'];
			$has_dark_styles                               = true;
		}

		// Content background.
		if ( ! empty( $content_bg['dark'] ) ) {
			$dark_vars['accordion-content-bg-dark'] = $content_bg['dark'];
			$has_dark_styles                        = true;
		}

		// Title icon color.
		if ( ! empty( $title_icon_color['dark'] ) ) {
			$dark_vars['accordion-title-icon-color-dark'] = $title_icon_color['dark'];
			$has_dark_styles                              = true;
		}

		// Heading text shadow color dark mode.
		$dark_shadow_color = $heading_text_shadow['color']['dark'] ?? '';
		if ( '' !== $dark_shadow_color ) {
			$dark_vars['accordion-heading-shadow-color'] = $dark_shadow_color;
			$has_dark_styles                             = true;
		}

		// Item shadow dark mode.
		$dark_shadow = '';
		if ( ! empty( $item_shadow['enabled'] ) && ! empty( $item_shadow['color']['dark'] ) ) {
			$dark_shadow     = CSS_Helpers::build_box_shadow( $item_shadow, $item_shadow['color']['dark'], false );
			$has_dark_styles = true;
		}

		if ( ! $has_dark_styles ) {
			return;
		}

		// Use helper to handle dark mode method.
		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_vars, $dark_shadow ) {
				foreach ( $dark_vars as $var => $value ) {
					$css->add_variable( $var, $value );
				}
				if ( $dark_shadow ) {
					$css->add_variable( 'accordion-item-shadow-dark', $dark_shadow );
				}
			}
		);
	}

	/**
	 * Convert text alignment value to flex justify-content value.
	 *
	 * @param string $align Alignment value (left, center, right).
	 * @return string Flex justify-content value.
	 */
	private static function align_to_justify( $align ) {
		$map = [
			'left'   => 'flex-start',
			'center' => 'center',
			'right'  => 'flex-end',
		];
		return $map[ $align ] ?? 'flex-start';
	}
}
