<?php
/**
 * Note Block CSS Generator.
 *
 * Generates ALL CSS for the Note block.
 * Uses CSS_Helpers for common patterns.
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
 * Note CSS Generator class.
 */
class Note_CSS {

	/**
	 * Generate CSS for Note block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector       = '.blockflow-note.blockflow-note-' . $block_id;
		$title_selector = $selector . ' .blockflow-note__title';

		// Extract attributes.
		$header_color       = $attrs['headerColor'] ?? [];
		$icon_color         = $attrs['iconColor'] ?? [];
		$header_color_hover = $attrs['headerColorHover'] ?? [];
		$header_background  = $attrs['headerBackground'] ?? [];
		$header_bg_hover    = $attrs['headerBackgroundHover'] ?? [];
		$content_background = $attrs['contentBackground'] ?? [];
		$header_padding     = $attrs['headerPadding'] ?? [];
		$content_padding    = $attrs['contentPadding'] ?? [];
		$content_gap        = $attrs['contentGap'] ?? [];
		$title_typography   = $attrs['titleTypography'] ?? [];
		$alignment          = $attrs['alignment'] ?? [];
		$control_icon_size  = $attrs['controlIconSize'] ?? [];

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================

		// Wrapper: colors (as variables), alignment (as direct property).
		$css->set_selector( $selector );

		// Alignment as direct property.
		$desktop_align = $alignment['desktop'] ?? '';
		if ( $desktop_align ) {
			$css->add_property( 'text-align', $desktop_align );
		}

		// Colors as CSS variables (on root only).
		self::add_color( $css, $header_color, 'note-header-color' );
		self::add_color( $css, $icon_color, 'note-icon-color' );
		self::add_color( $css, $header_background, 'note-header-bg' );
		self::add_color( $css, $content_background, 'note-content-bg' );

		// Padding as CSS variables (shorthand) on root selector.
		CSS_Helpers::add_padding_var( $css, $header_padding['desktop'] ?? [], 'note-header' );
		CSS_Helpers::add_padding_var( $css, $content_padding['desktop'] ?? [], 'note-content' );

		// Content gap.
		$desktop_gap = $content_gap['desktop'] ?? [];
		if ( ! empty( $desktop_gap['value'] ) ) {
			$css->add_variable( 'note-content-gap', $desktop_gap['value'] . ( $desktop_gap['unit'] ?? 'px' ) );
		}

		// Control icon size (toggle/dismiss icons).
		$desktop_icon_size = $control_icon_size['desktop'] ?? [];
		if ( ! empty( $desktop_icon_size['value'] ) && '20' !== $desktop_icon_size['value'] ) {
			$css->add_variable( 'note-control-icon-size', $desktop_icon_size['value'] . ( $desktop_icon_size['unit'] ?? 'px' ) );
		}

		// Common Advanced Tab CSS.
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// Title: typography as direct properties.
		$css->set_selector( $title_selector );
		CSS_Helpers::add_typography_css( $css, $title_typography, 'desktop' );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );

		// Wrapper: alignment, padding, advanced layout.
		$css->set_selector( $selector );
		$tablet_align = $alignment['tablet'] ?? '';
		if ( $tablet_align ) {
			$css->add_property( 'text-align', $tablet_align );
		}
		CSS_Helpers::add_padding_var( $css, $header_padding['tablet'] ?? [], 'note-header' );
		CSS_Helpers::add_padding_var( $css, $content_padding['tablet'] ?? [], 'note-content' );
		$tablet_gap = $content_gap['tablet'] ?? [];
		if ( ! empty( $tablet_gap['value'] ) ) {
			$css->add_variable( 'note-content-gap', $tablet_gap['value'] . ( $tablet_gap['unit'] ?? 'px' ) );
		}
		$tablet_icon_size = $control_icon_size['tablet'] ?? [];
		if ( ! empty( $tablet_icon_size['value'] ) ) {
			$css->add_variable( 'note-control-icon-size', $tablet_icon_size['value'] . ( $tablet_icon_size['unit'] ?? 'px' ) );
		}
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		// Title: typography.
		$css->set_selector( $title_selector );
		CSS_Helpers::add_typography_css( $css, $title_typography, 'tablet' );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );

		// Wrapper: alignment, padding, advanced layout.
		$css->set_selector( $selector );
		$mobile_align = $alignment['mobile'] ?? '';
		if ( $mobile_align ) {
			$css->add_property( 'text-align', $mobile_align );
		}
		CSS_Helpers::add_padding_var( $css, $header_padding['mobile'] ?? [], 'note-header' );
		CSS_Helpers::add_padding_var( $css, $content_padding['mobile'] ?? [], 'note-content' );
		$mobile_gap = $content_gap['mobile'] ?? [];
		if ( ! empty( $mobile_gap['value'] ) ) {
			$css->add_variable( 'note-content-gap', $mobile_gap['value'] . ( $mobile_gap['unit'] ?? 'px' ) );
		}
		$mobile_icon_size = $control_icon_size['mobile'] ?? [];
		if ( ! empty( $mobile_icon_size['value'] ) ) {
			$css->add_variable( 'note-control-icon-size', $mobile_icon_size['value'] . ( $mobile_icon_size['unit'] ?? 'px' ) );
		}
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		// Title: typography.
		$css->set_selector( $title_selector );
		CSS_Helpers::add_typography_css( $css, $title_typography, 'mobile' );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		self::add_dark_mode_styles(
			$css,
			$selector,
			$header_color,
			$icon_color,
			$header_color_hover,
			$header_background,
			$header_bg_hover,
			$content_background
		);

		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATE VARIABLES
		// =====================================================================
		$css->set_selector( $selector );
		if ( ! empty( $header_color_hover['light'] ) ) {
			$css->add_variable( 'note-header-color-hover', $header_color_hover['light'] );
		}
		if ( ! empty( $header_bg_hover['light'] ) ) {
			$css->add_variable( 'note-header-bg-hover', $header_bg_hover['light'] );
		}

		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
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
	 * Add dark mode CSS.
	 *
	 * @param CSS_Builder $css               CSS generator.
	 * @param string                   $selector          Main block selector.
	 * @param array                    $header_color      Header text color.
	 * @param array                    $icon_color        Icon color.
	 * @param array                    $header_color_hover Header text color hover.
	 * @param array                    $header_bg         Header background.
	 * @param array                    $header_bg_hover   Header background hover.
	 * @param array                    $content_bg        Content background.
	 */
	private static function add_dark_mode_styles(
		$css,
		$selector,
		$header_color,
		$icon_color,
		$header_color_hover,
		$header_bg,
		$header_bg_hover,
		$content_bg
	) {
		$has_dark_styles = false;
		$dark_vars       = [];

		// Header colors.
		if ( ! empty( $header_color['dark'] ) ) {
			$dark_vars['note-header-color'] = $header_color['dark'];
			$has_dark_styles                = true;
		}
		if ( ! empty( $icon_color['dark'] ) ) {
			$dark_vars['note-icon-color'] = $icon_color['dark'];
			$has_dark_styles              = true;
		}
		if ( ! empty( $header_color_hover['dark'] ) ) {
			$dark_vars['note-header-color-hover'] = $header_color_hover['dark'];
			$has_dark_styles                      = true;
		}
		if ( ! empty( $header_bg['dark'] ) ) {
			$dark_vars['note-header-bg'] = $header_bg['dark'];
			$has_dark_styles             = true;
		}
		if ( ! empty( $header_bg_hover['dark'] ) ) {
			$dark_vars['note-header-bg-hover'] = $header_bg_hover['dark'];
			$has_dark_styles                   = true;
		}

		// Content background.
		if ( ! empty( $content_bg['dark'] ) ) {
			$dark_vars['note-content-bg'] = $content_bg['dark'];
			$has_dark_styles              = true;
		}

		if ( ! $has_dark_styles ) {
			return;
		}

		// Use helper to handle dark mode method.
		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_vars ) {
				foreach ( $dark_vars as $var => $value ) {
					$css->add_variable( $var, $value );
				}
			}
		);
	}
}
