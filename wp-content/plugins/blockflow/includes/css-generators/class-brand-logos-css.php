<?php
/**
 * Brand Logos Block CSS Generator.
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
 * Brand Logos CSS Generator class.
 */
class Brand_Logos_CSS {

	/**
	 * Generate CSS for the Brand Logos block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector = '.blockflow-brand-logos.blockflow-brand-logos-' . $block_id;

		// Extract block-specific attributes.
		$display_mode  = $attrs['displayMode'] ?? 'slider';
		$columns       = $attrs['columns'] ?? [];
		$slider_gap    = $attrs['sliderGap'] ?? [];
		$grid_gap      = $attrs['gridGap'] ?? [];
		$logo_size     = $attrs['logoSize'] ?? [];
		$opacity       = $attrs['opacity'] ?? 0.6;
		$opacity_hover = $attrs['opacityHover'] ?? 1;
		$grayscale     = $attrs['grayscale'] ?? false;
		$grayscale_h   = $attrs['grayscaleHover'] ?? true;

		$is_grid = 'grid' === $display_mode;

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================
		$css->set_selector( $selector );
		self::add_layout_vars( $css, $columns, $slider_gap, $grid_gap, $logo_size, 'desktop', $is_grid );
		self::add_logo_vars( $css, $opacity, $opacity_hover, $grayscale, $grayscale_h );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );
		$css->set_selector( $selector );
		self::add_layout_vars( $css, $columns, $slider_gap, $grid_gap, $logo_size, 'tablet', $is_grid );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );
		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );
		$css->set_selector( $selector );
		self::add_layout_vars( $css, $columns, $slider_gap, $grid_gap, $logo_size, 'mobile', $is_grid );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );
		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add layout CSS variables (on root selector).
	 *
	 * @param CSS_Builder $css        CSS generator.
	 * @param array                    $columns    Columns attribute.
	 * @param array                    $slider_gap Slider gap attribute (single value).
	 * @param array                    $grid_gap   Grid gap attribute (column/row).
	 * @param array                    $logo_size  Logo size attribute.
	 * @param string                   $device     Device key.
	 * @param bool                     $is_grid    Whether grid mode is enabled.
	 */
	private static function add_layout_vars( $css, $columns, $slider_gap, $grid_gap, $logo_size, $device, $is_grid ) {
		$is_desktop = 'desktop' === $device;

		if ( $is_grid ) {
			// Grid mode: columns and column/row gap.
			$columns_value = $columns[ $device ]['value'] ?? '';
			if ( '' !== $columns_value ) {
				$css->add_variable( 'columns', $columns_value );
			}

			// Column gap.
			$column_gap = $grid_gap[ $device ]['column'] ?? '';
			if ( '' !== $column_gap ) {
				$gap_unit = $grid_gap[ $device ]['unit'] ?? $grid_gap['desktop']['unit'] ?? 'px';
				$css->add_variable( 'column-gap', $column_gap . $gap_unit );
			}

			// Row gap.
			$row_gap = $grid_gap[ $device ]['row'] ?? '';
			if ( '' !== $row_gap ) {
				$gap_unit = $grid_gap[ $device ]['unit'] ?? $grid_gap['desktop']['unit'] ?? 'px';
				$css->add_variable( 'row-gap', $row_gap . $gap_unit );
			}
		} else {
			// Slider mode: single gap value.
			$gap_value = $slider_gap[ $device ]['value'] ?? '';
			if ( '' !== $gap_value ) {
				$gap_unit = $slider_gap[ $device ]['unit'] ?? $slider_gap['desktop']['unit'] ?? 'px';
				$css->add_variable( 'gap', $gap_value . $gap_unit );
			}

			// Logo size (height and max-width) - slider mode only.
			$size_unit    = $logo_size[ $device ]['unit'] ?? $logo_size['desktop']['unit'] ?? 'px';
			$height_value = $logo_size[ $device ]['height'] ?? '';
			$width_value  = $logo_size[ $device ]['width'] ?? '';

			// Height.
			if ( '' !== $height_value ) {
				$css->add_variable( 'brand-h', $height_value . $size_unit );
			}

			// Max-width.
			if ( '' !== $width_value ) {
				$css->add_variable( 'brand-max-w', $width_value . $size_unit );
			}
		}
	}

	/**
	 * Add logo appearance CSS variables (non-responsive, desktop only).
	 *
	 * @param CSS_Builder $css           CSS generator.
	 * @param float                    $opacity       Normal opacity.
	 * @param float                    $opacity_hover Hover opacity.
	 * @param bool                     $grayscale     Normal grayscale.
	 * @param bool                     $grayscale_h   Hover grayscale.
	 */
	private static function add_logo_vars( $css, $opacity, $opacity_hover, $grayscale, $grayscale_h ) {
		// Opacity.
		$css->add_variable( 'brand-opacity', $opacity );

		// Hover opacity.
		$css->add_variable( 'brand-opacity-hover', $opacity_hover );

		// Grayscale.
		$css->add_variable( 'brand-grayscale', $grayscale ? '1' : '0' );

		// Grayscale hover.
		$css->add_variable( 'brand-grayscale-hover', $grayscale_h ? '1' : '0' );
	}
}
