<?php
/**
 * Countdown Block CSS Generator.
 *
 * Generates CSS for the Countdown block using CSS_Helpers.
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
 * Countdown CSS Generator class.
 */
class Countdown_CSS {

	/**
	 * Generate CSS for the countdown block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector         = '.blockflow-countdown.blockflow-countdown-' . $block_id;
		$digit_selector   = $selector . ' .blockflow-countdown__digit';
		$label_selector   = $selector . ' .blockflow-countdown__label';
		$item_selector    = $selector . ' .blockflow-countdown__item';
		$sep_selector     = $selector . ' .blockflow-countdown__separator';
		$expired_selector = $selector . ' .blockflow-countdown__expired';

		// Extract countdown-specific attributes.
		$alignment          = $attrs['alignment'] ?? [];
		$item_gap           = $attrs['itemGap'] ?? [];
		$digit_typography   = $attrs['digitTypography'] ?? [];
		$label_typography   = $attrs['labelTypography'] ?? [];
		$digit_color        = $attrs['digitColor'] ?? [];
		$label_color        = $attrs['labelColor'] ?? [];
		$separator_color    = $attrs['separatorColor'] ?? [];
		$item_background    = $attrs['itemBackground'] ?? [];
		$item_padding       = $attrs['itemPadding'] ?? [];
		$item_border_radius = $attrs['itemBorderRadius'] ?? [];

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================

		// Wrapper: alignment, gap, color variables.
		$css->set_selector( $selector );

		$desktop_align = $alignment['desktop'] ?? '';
		if ( $desktop_align ) {
			$css->add_variable( 'cd-align', CSS_Helpers::align_to_justify( $desktop_align ) );
		}

		$desktop_gap = $item_gap['desktop'] ?? [];
		if ( ! empty( $desktop_gap['value'] ) ) {
			$gap_unit = $desktop_gap['unit'] ?? 'px';
			$css->add_variable( 'cd-gap', $desktop_gap['value'] . $gap_unit );
		}

		// Colors as CSS variables on root selector.
		if ( ! empty( $digit_color['light'] ) ) {
			$css->add_variable( 'cd-digit-color', $digit_color['light'] );
		}
		if ( ! empty( $label_color['light'] ) ) {
			$css->add_variable( 'cd-label-color', $label_color['light'] );
		}
		if ( ! empty( $separator_color['light'] ) ) {
			$css->add_variable( 'cd-sep-color', $separator_color['light'] );
		}
		if ( ! empty( $item_background['light'] ) ) {
			$css->add_variable( 'cd-item-bg', $item_background['light'] );
		}

		// Item padding as CSS variable (shorthand) on root selector.
		CSS_Helpers::add_padding_var( $css, $item_padding['desktop'] ?? [], 'cd-item' );

		// Common Advanced Tab CSS (spacing, advanced layout, animation).
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// Digit: typography as direct properties.
		$css->set_selector( $digit_selector );
		CSS_Helpers::add_typography_css( $css, $digit_typography, 'desktop' );

		// Separator: same typography as digit.
		$css->set_selector( $sep_selector );
		CSS_Helpers::add_typography_css( $css, $digit_typography, 'desktop' );

		// Label: typography as direct properties.
		$css->set_selector( $label_selector );
		CSS_Helpers::add_typography_css( $css, $label_typography, 'desktop' );

		// Expired: same typography as label.
		$css->set_selector( $expired_selector );
		CSS_Helpers::add_typography_css( $css, $label_typography, 'desktop' );

		// Item: border-radius as direct properties.
		$css->set_selector( $item_selector );
		self::add_item_border_radius_prop( $css, $item_border_radius, 'desktop' );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );

		// Wrapper: alignment, gap.
		$css->set_selector( $selector );

		$tablet_align = $alignment['tablet'] ?? '';
		if ( $tablet_align ) {
			$css->add_variable( 'cd-align', CSS_Helpers::align_to_justify( $tablet_align ) );
		}

		$tablet_gap = $item_gap['tablet'] ?? [];
		if ( ! empty( $tablet_gap['value'] ) ) {
			$gap_unit = $tablet_gap['unit'] ?? $item_gap['desktop']['unit'] ?? 'px';
			$css->add_variable( 'cd-gap', $tablet_gap['value'] . $gap_unit );
		}

		CSS_Helpers::add_padding_var( $css, $item_padding['tablet'] ?? [], 'cd-item' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		// Digit: typography.
		$css->set_selector( $digit_selector );
		CSS_Helpers::add_typography_css( $css, $digit_typography, 'tablet' );

		// Separator: same typography as digit.
		$css->set_selector( $sep_selector );
		CSS_Helpers::add_typography_css( $css, $digit_typography, 'tablet' );

		// Label: typography.
		$css->set_selector( $label_selector );
		CSS_Helpers::add_typography_css( $css, $label_typography, 'tablet' );

		// Expired: same typography as label.
		$css->set_selector( $expired_selector );
		CSS_Helpers::add_typography_css( $css, $label_typography, 'tablet' );

		// Item: border-radius.
		$css->set_selector( $item_selector );
		self::add_item_border_radius_prop( $css, $item_border_radius, 'tablet' );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );

		// Wrapper: alignment, gap, padding.
		$css->set_selector( $selector );

		$mobile_align = $alignment['mobile'] ?? '';
		if ( $mobile_align ) {
			$css->add_variable( 'cd-align', CSS_Helpers::align_to_justify( $mobile_align ) );
		}

		$mobile_gap = $item_gap['mobile'] ?? [];
		if ( ! empty( $mobile_gap['value'] ) ) {
			$gap_unit = $mobile_gap['unit'] ?? $item_gap['tablet']['unit'] ?? $item_gap['desktop']['unit'] ?? 'px';
			$css->add_variable( 'cd-gap', $mobile_gap['value'] . $gap_unit );
		}

		CSS_Helpers::add_padding_var( $css, $item_padding['mobile'] ?? [], 'cd-item' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		// Digit: typography.
		$css->set_selector( $digit_selector );
		CSS_Helpers::add_typography_css( $css, $digit_typography, 'mobile' );

		// Separator: same typography as digit.
		$css->set_selector( $sep_selector );
		CSS_Helpers::add_typography_css( $css, $digit_typography, 'mobile' );

		// Label: typography.
		$css->set_selector( $label_selector );
		CSS_Helpers::add_typography_css( $css, $label_typography, 'mobile' );

		// Expired: same typography as label.
		$css->set_selector( $expired_selector );
		CSS_Helpers::add_typography_css( $css, $label_typography, 'mobile' );

		// Item: border-radius.
		$css->set_selector( $item_selector );
		self::add_item_border_radius_prop( $css, $item_border_radius, 'mobile' );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		self::add_countdown_dark_mode( $css, $selector, $digit_color, $label_color, $separator_color, $item_background );

		// Common Advanced Tab dark mode (background, border colors).
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES (border + shadow)
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add item border radius CSS property (direct property, not variable).
	 *
	 * @param CSS_Builder $css         CSS generator.
	 * @param array                    $radius_data Border radius data.
	 * @param string                   $device      Device key.
	 */
	private static function add_item_border_radius_prop( $css, $radius_data, $device = 'desktop' ) {
		$device_radius = $radius_data[ $device ] ?? [];

		if ( ! empty( $device_radius['value'] ) ) {
			$unit = $device_radius['unit'] ?? 'px';
			$css->add_property( 'border-radius', $device_radius['value'] . $unit );
		}
	}

	/**
	 * Add countdown dark mode CSS.
	 *
	 * @param CSS_Builder $css             CSS generator.
	 * @param string                   $selector        Block selector.
	 * @param array                    $digit_color     Digit color.
	 * @param array                    $label_color     Label color.
	 * @param array                    $separator_color Separator color.
	 * @param array                    $item_background Item background.
	 */
	private static function add_countdown_dark_mode( $css, $selector, $digit_color, $label_color, $separator_color, $item_background ) {
		$dark_digit   = $digit_color['dark'] ?? '';
		$dark_label   = $label_color['dark'] ?? '';
		$dark_sep     = $separator_color['dark'] ?? '';
		$dark_item_bg = $item_background['dark'] ?? '';

		if ( ! $dark_digit && ! $dark_label && ! $dark_sep && ! $dark_item_bg ) {
			return;
		}

		// Use helper to handle dark mode method.
		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_digit, $dark_label, $dark_sep, $dark_item_bg ) {
				if ( $dark_digit ) {
					$css->add_variable( 'cd-digit-color', $dark_digit );
				}
				if ( $dark_label ) {
					$css->add_variable( 'cd-label-color', $dark_label );
				}
				if ( $dark_sep ) {
					$css->add_variable( 'cd-sep-color', $dark_sep );
				}
				if ( $dark_item_bg ) {
					$css->add_variable( 'cd-item-bg', $dark_item_bg );
				}
			}
		);
	}
}
