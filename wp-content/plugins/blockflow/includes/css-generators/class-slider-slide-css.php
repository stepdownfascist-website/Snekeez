<?php
/**
 * Slider Slide Block CSS Generator.
 *
 * Generates CSS for individual slides using CSS_Helpers.
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
 * Slider Slide CSS Generator class.
 */
class Slider_Slide_CSS {

	/**
	 * Generate CSS for Slider Slide block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector = '.blockflow-slider-slide.blockflow-slider-slide-' . $block_id;

		// Extract attributes.
		$background = $attrs['background'] ?? [];
		$padding    = $attrs['padding'] ?? [];
		$max_width  = $attrs['maxWidth'] ?? [];
		$layout     = $attrs['layout'] ?? [];
		$gap        = $attrs['gap'] ?? [];

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================
		$css->set_selector( $selector );

		// Background (color/gradient) - direct property to avoid inheritance.
		CSS_Helpers::add_background( $css, $background );

		// Padding (CSS variable for consistency with button pattern).
		CSS_Helpers::add_padding_var( $css, $padding['desktop'] ?? [], 'slide' );

		// Max width for content.
		$desktop_max_width = $max_width['desktop'] ?? [];
		if ( ! empty( $desktop_max_width['value'] ) ) {
			$css->add_variable( 'slide-max-w', $desktop_max_width['value'] . ( $desktop_max_width['unit'] ?? 'px' ) );
		}

		// Layout (justify/align).
		$desktop_layout = $layout['desktop'] ?? [];
		$align_items    = $desktop_layout['alignItems'] ?? '';
		$justify        = $desktop_layout['justifyContent'] ?? '';

		if ( $align_items ) {
			$css->add_variable( 'slide-align', $align_items );
		}
		if ( $justify ) {
			$css->add_variable( 'slide-justify', $justify );
		}

		// Gap.
		self::add_gap( $css, $gap['desktop'] ?? [] );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );
		$css->set_selector( $selector );

		// Padding.
		CSS_Helpers::add_padding_var( $css, $padding['tablet'] ?? [], 'slide' );

		// Max width.
		$tablet_max_width = $max_width['tablet'] ?? [];
		if ( ! empty( $tablet_max_width['value'] ) ) {
			$css->add_variable( 'slide-max-w', $tablet_max_width['value'] . ( $tablet_max_width['unit'] ?? 'px' ) );
		}

		// Layout (justify/align).
		$tablet_layout = $layout['tablet'] ?? [];
		if ( ! empty( $tablet_layout['alignItems'] ) ) {
			$css->add_variable( 'slide-align', $tablet_layout['alignItems'] );
		}
		if ( ! empty( $tablet_layout['justifyContent'] ) ) {
			$css->add_variable( 'slide-justify', $tablet_layout['justifyContent'] );
		}

		// Gap.
		self::add_gap( $css, $gap['tablet'] ?? [] );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );
		$css->set_selector( $selector );

		// Padding.
		CSS_Helpers::add_padding_var( $css, $padding['mobile'] ?? [], 'slide' );

		// Max width.
		$mobile_max_width = $max_width['mobile'] ?? [];
		if ( ! empty( $mobile_max_width['value'] ) ) {
			$css->add_variable( 'slide-max-w', $mobile_max_width['value'] . ( $mobile_max_width['unit'] ?? 'px' ) );
		}

		// Layout (justify/align).
		$mobile_layout = $layout['mobile'] ?? [];
		if ( ! empty( $mobile_layout['alignItems'] ) ) {
			$css->add_variable( 'slide-align', $mobile_layout['alignItems'] );
		}
		if ( ! empty( $mobile_layout['justifyContent'] ) ) {
			$css->add_variable( 'slide-justify', $mobile_layout['justifyContent'] );
		}

		// Gap.
		self::add_gap( $css, $gap['mobile'] ?? [] );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		CSS_Helpers::add_background_dark_mode( $css, $selector, $background );
	}

	/**
	 * Add gap CSS variable.
	 *
	 * @param CSS_Builder $css CSS generator instance.
	 * @param array                    $gap Gap values (row, column, unit).
	 */
	private static function add_gap( $css, $gap ) {
		if ( empty( $gap ) ) {
			return;
		}

		$row    = $gap['row'] ?? '';
		$column = $gap['column'] ?? '';
		$unit   = $gap['unit'] ?? 'px';

		// Only output if at least one value is set.
		if ( '' === $row && '' === $column ) {
			return;
		}

		$row_value    = '' !== $row ? $row . $unit : '0';
		$column_value = '' !== $column ? $column . $unit : '0';

		$css->add_variable( 'slide-gap', $row_value . ' ' . $column_value );
	}
}
