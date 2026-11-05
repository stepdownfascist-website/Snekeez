<?php
/**
 * Grid Block CSS Generator.
 *
 * Generates scoped CSS for the Grid block.
 *
 * Structure:
 * - Outer selector: size, margin, advanced positioning
 * - Inner selector (boxed) or outer (full-width): grid layout, padding, background, border, shadow
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
 * Grid CSS Generator class.
 */
class Grid_CSS {

	/**
	 * Generate CSS for Grid block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		// Container type determines selector structure.
		$container_type = $attrs['containerType'] ?? 'full-width';
		$is_boxed       = 'boxed' === $container_type;

		// Selectors.
		$outer_selector = 'body .blockflow-grid.blockflow-grid-' . $block_id;
		$inner_selector = $is_boxed ? $outer_selector . ' > .blockflow-grid__inner' : $outer_selector;

		// Extract attributes.
		$size            = $attrs['size'] ?? [];
		$width_boxed     = $attrs['widthBoxed'] ?? [];
		$width_fullwidth = $attrs['widthFullWidth'] ?? [];
		$layout          = $attrs['layout'] ?? [];
		$spacing         = $attrs['spacing'] ?? [];
		$border          = $attrs['border'] ?? [];
		$border_hover    = $attrs['borderHover'] ?? [];
		$shadow          = $attrs['boxShadow'] ?? [];
		$shadow_hover    = $attrs['boxShadowHover'] ?? [];
		$advanced        = $attrs['advancedLayout'] ?? [];
		$background      = $attrs['background'] ?? [];
		$animation       = $attrs['animation'] ?? [];

		// Get width from correct attribute based on container type.
		$width_attr = $is_boxed ? $width_boxed : $width_fullwidth;

		// Content target for boxed containers: 'outer' (full-width) or 'inner' (content-width).
		// Determines where decoration (background, border, padding, shadow) is applied.
		$content_target = $attrs['contentTarget'] ?? 'outer';

		// Determine where decoration styles go.
		// For boxed: depends on contentTarget. For full-width: always outer (which equals inner_selector).
		$styles_on_outer = ! $is_boxed || 'outer' === $content_target;
		$styled_selector = $styles_on_outer ? $outer_selector : $inner_selector;

		// Generate CSS for each breakpoint.
		self::generate_desktop( $css, $outer_selector, $inner_selector, $styled_selector, $is_boxed, $size, $width_attr, $layout, $spacing, $border, $shadow, $advanced, $background, $animation );
		self::generate_tablet( $css, $outer_selector, $inner_selector, $styled_selector, $is_boxed, $size, $width_attr, $layout, $spacing, $border, $advanced );
		self::generate_mobile( $css, $outer_selector, $inner_selector, $styled_selector, $is_boxed, $size, $width_attr, $layout, $spacing, $border, $advanced );

		// Generate hover states.
		CSS_Helpers::add_hover_states( $css, $styled_selector, $border_hover, $shadow_hover );

		// Generate dark mode styles.
		self::generate_dark_mode( $css, $styled_selector, $background, $border, $shadow );
	}

	/**
	 * Generate desktop CSS (base styles, no media query).
	 *
	 * @param CSS_Builder $css             CSS generator.
	 * @param string                   $outer_selector  Outer element selector.
	 * @param string                   $inner_selector  Inner element selector.
	 * @param string                   $styled_selector Styled element selector (decoration).
	 * @param bool                     $is_boxed        Whether container is boxed.
	 * @param array                    $size            Size attributes (minHeight only).
	 * @param array                    $width_attr      Width attribute (widthBoxed or widthFullWidth).
	 * @param array                    $layout          Layout attributes.
	 * @param array                    $spacing         Spacing attributes.
	 * @param array                    $border          Border attributes.
	 * @param array                    $shadow          Shadow attributes.
	 * @param array                    $advanced        Advanced attributes.
	 * @param array                    $background      Background attributes.
	 * @param array                    $animation       Animation attributes.
	 */
	private static function generate_desktop( $css, $outer_selector, $inner_selector, $styled_selector, $is_boxed, $size, $width_attr, $layout, $spacing, $border, $shadow, $advanced, $background, $animation = [] ) {
		// Outer: margin, advanced positioning, animation.
		$css->set_selector( $outer_selector );
		CSS_Helpers::add_margin( $css, $spacing['desktop']['margin'] ?? [] );
		CSS_Helpers::add_advanced_vars( $css, $advanced['desktop'] ?? [] );
		CSS_Helpers::add_animation_vars( $css, $animation );

		// Merge width from separate attribute into size for helper compatibility.
		$desktop_size = $size['desktop'] ?? [];
		if ( ! empty( $width_attr['desktop']['value'] ) ) {
			$desktop_size['width'] = $width_attr['desktop'];
		}

		// For boxed containers: size and grid layout always go to inner.
		if ( $is_boxed ) {
			$css->set_selector( $inner_selector );
			CSS_Helpers::add_size_vars( $css, $desktop_size );
			self::add_grid_layout_vars( $css, $layout['desktop'] ?? [] );
		} else {
			// Full-width: size and grid layout on outer.
			$css->set_selector( $outer_selector );
			CSS_Helpers::add_size_vars( $css, $desktop_size );
			self::add_grid_layout_vars( $css, $layout['desktop'] ?? [] );
		}

		// Decoration (padding, border, shadow, background, overflow): applied to styled_selector.
		$css->set_selector( $styled_selector );
		CSS_Helpers::add_padding( $css, $spacing['desktop']['padding'] ?? [] );
		CSS_Helpers::add_border_vars( $css, $border['desktop'] ?? [] );
		// Shadow and background use direct properties to avoid child inheritance.
		CSS_Helpers::add_shadow( $css, $shadow );
		CSS_Helpers::add_background( $css, $background );

		// Overflow: applied directly (not via variable) to prevent child inheritance.
		$overflow = $advanced['desktop']['overflow'] ?? '';
		if ( ! empty( $overflow ) && 'visible' !== $overflow ) {
			$css->add_property( 'overflow', $overflow );
		}
	}

	/**
	 * Generate tablet CSS.
	 *
	 * @param CSS_Builder $css             CSS generator.
	 * @param string                   $outer_selector  Outer element selector.
	 * @param string                   $inner_selector  Inner element selector.
	 * @param string                   $styled_selector Styled element selector (decoration).
	 * @param bool                     $is_boxed        Whether container is boxed.
	 * @param array                    $size            Size attributes (minHeight only).
	 * @param array                    $width_attr      Width attribute (widthBoxed or widthFullWidth).
	 * @param array                    $layout          Layout attributes.
	 * @param array                    $spacing         Spacing attributes.
	 * @param array                    $border          Border attributes.
	 * @param array                    $advanced        Advanced attributes.
	 */
	private static function generate_tablet( $css, $outer_selector, $inner_selector, $styled_selector, $is_boxed, $size, $width_attr, $layout, $spacing, $border, $advanced ) {
		$css->start_media_query( 'tablet' );

		// Outer: margin, advanced.
		$css->set_selector( $outer_selector );
		CSS_Helpers::add_margin( $css, $spacing['tablet']['margin'] ?? [] );
		CSS_Helpers::add_advanced_vars( $css, $advanced['tablet'] ?? [] );

		// Merge width from separate attribute into size for helper compatibility.
		$tablet_size = $size['tablet'] ?? [];
		if ( ! empty( $width_attr['tablet']['value'] ) ) {
			$tablet_size['width'] = $width_attr['tablet'];
		}

		// For boxed containers: size and grid layout always go to inner.
		if ( $is_boxed ) {
			$css->set_selector( $inner_selector );
			CSS_Helpers::add_size_vars( $css, $tablet_size );
			self::add_grid_layout_vars( $css, $layout['tablet'] ?? [] );
		} else {
			// Full-width: size and grid layout on outer.
			$css->set_selector( $outer_selector );
			CSS_Helpers::add_size_vars( $css, $tablet_size );
			self::add_grid_layout_vars( $css, $layout['tablet'] ?? [] );
		}

		// Decoration (padding, border, overflow): applied to styled_selector.
		$css->set_selector( $styled_selector );
		CSS_Helpers::add_padding( $css, $spacing['tablet']['padding'] ?? [] );
		CSS_Helpers::add_border_vars( $css, $border['tablet'] ?? [] );

		// Overflow: applied directly (not via variable) to prevent child inheritance.
		$overflow = $advanced['tablet']['overflow'] ?? '';
		if ( ! empty( $overflow ) && 'visible' !== $overflow ) {
			$css->add_property( 'overflow', $overflow );
		}

		$css->end_media_query();
	}

	/**
	 * Generate mobile CSS.
	 *
	 * @param CSS_Builder $css             CSS generator.
	 * @param string                   $outer_selector  Outer element selector.
	 * @param string                   $inner_selector  Inner element selector.
	 * @param string                   $styled_selector Styled element selector (decoration).
	 * @param bool                     $is_boxed        Whether container is boxed.
	 * @param array                    $size            Size attributes (minHeight only).
	 * @param array                    $width_attr      Width attribute (widthBoxed or widthFullWidth).
	 * @param array                    $layout          Layout attributes.
	 * @param array                    $spacing         Spacing attributes.
	 * @param array                    $border          Border attributes.
	 * @param array                    $advanced        Advanced attributes.
	 */
	private static function generate_mobile( $css, $outer_selector, $inner_selector, $styled_selector, $is_boxed, $size, $width_attr, $layout, $spacing, $border, $advanced ) {
		$css->start_media_query( 'mobile' );

		// Outer: margin, advanced.
		$css->set_selector( $outer_selector );
		CSS_Helpers::add_margin( $css, $spacing['mobile']['margin'] ?? [] );
		CSS_Helpers::add_advanced_vars( $css, $advanced['mobile'] ?? [] );

		// Merge width from separate attribute into size for helper compatibility.
		$mobile_size = $size['mobile'] ?? [];
		if ( ! empty( $width_attr['mobile']['value'] ) ) {
			$mobile_size['width'] = $width_attr['mobile'];
		}

		// For boxed containers: size and grid layout always go to inner.
		if ( $is_boxed ) {
			$css->set_selector( $inner_selector );
			CSS_Helpers::add_size_vars( $css, $mobile_size );
			self::add_grid_layout_vars( $css, $layout['mobile'] ?? [] );
		} else {
			// Full-width: size and grid layout on outer.
			$css->set_selector( $outer_selector );
			CSS_Helpers::add_size_vars( $css, $mobile_size );
			self::add_grid_layout_vars( $css, $layout['mobile'] ?? [] );
		}

		// Decoration (padding, border, overflow): applied to styled_selector.
		$css->set_selector( $styled_selector );
		CSS_Helpers::add_padding( $css, $spacing['mobile']['padding'] ?? [] );
		CSS_Helpers::add_border_vars( $css, $border['mobile'] ?? [] );

		// Overflow: applied directly (not via variable) to prevent child inheritance.
		$overflow = $advanced['mobile']['overflow'] ?? '';
		if ( ! empty( $overflow ) && 'visible' !== $overflow ) {
			$css->add_property( 'overflow', $overflow );
		}

		$css->end_media_query();
	}

	/**
	 * Generate dark mode CSS.
	 *
	 * @param CSS_Builder $css             CSS generator.
	 * @param string                   $styled_selector Styled element selector.
	 * @param array                    $background      Background attributes.
	 * @param array                    $border          Border attributes.
	 * @param array                    $shadow          Shadow attributes.
	 */
	private static function generate_dark_mode( $css, $styled_selector, $background, $border, $shadow ) {
		// Collect dark mode values.
		$dark_bg_color = CSS_Helpers::get_dark_color( $background['color'] ?? '' );
		$dark_border   = CSS_Helpers::get_dark_color( $border['desktop']['color'] ?? '' );
		$dark_shadow   = CSS_Helpers::get_dark_color( $shadow['color'] ?? '' );

		$has_dark = $dark_bg_color || $dark_border || $dark_shadow;

		if ( ! $has_dark ) {
			return;
		}

		// Use helper to handle dark mode method.
		CSS_Helpers::add_dark_mode_css(
			$css,
			$styled_selector,
			function ( $css ) use ( $dark_bg_color, $dark_border, $dark_shadow, $shadow ) {
				if ( $dark_bg_color ) {
					$css->add_property( 'background', $dark_bg_color );
				}
				if ( $dark_border ) {
					$css->add_variable( 'border-c', $dark_border );
				}
				if ( $dark_shadow ) {
					$dark_shadow_value = CSS_Helpers::build_box_shadow(
						array_merge(
							$shadow,
							[ 'color' => [ 'light' => $dark_shadow ] ]
						)
					);
					if ( $dark_shadow_value ) {
						$css->add_property( 'box-shadow', $dark_shadow_value );
					}
				}
			}
		);
	}

	/**
	 * Add grid-specific layout CSS variables.
	 *
	 * @param CSS_Builder $css    CSS generator instance.
	 * @param array                    $layout Grid layout values.
	 */
	private static function add_grid_layout_vars( $css, $layout ) {
		if ( empty( $layout ) || ! is_array( $layout ) ) {
			return;
		}

		// Grid columns.
		if ( ! empty( $layout['columns'] ) ) {
			$cols_value = self::build_grid_template( $layout['columns'] );
			if ( $cols_value ) {
				$css->add_variable( 'grid-cols', $cols_value );
			}
		}

		// Grid rows.
		if ( ! empty( $layout['rows'] ) ) {
			$rows_value = self::build_grid_template( $layout['rows'] );
			if ( $rows_value ) {
				$css->add_variable( 'grid-rows', $rows_value );
			}
		}

		// Gaps: { column, row, unit }.
		if ( ! empty( $layout['gaps'] ) ) {
			$gaps = $layout['gaps'];
			$unit = $gaps['unit'] ?? 'px';

			if ( '' !== ( $gaps['column'] ?? '' ) ) {
				$css->add_variable( 'column-gap', $gaps['column'] . $unit );
			}
			if ( '' !== ( $gaps['row'] ?? '' ) ) {
				$css->add_variable( 'row-gap', $gaps['row'] . $unit );
			}
		}

		// Auto flow.
		if ( ! empty( $layout['autoFlow'] ) ) {
			$css->add_variable( 'auto-flow', $layout['autoFlow'] );
		}

		// Justify items.
		if ( ! empty( $layout['justifyItems'] ) ) {
			$css->add_variable( 'justify-items', $layout['justifyItems'] );
		}

		// Align items.
		if ( ! empty( $layout['alignItems'] ) ) {
			$css->add_variable( 'align-items', $layout['alignItems'] );
		}

		// Justify content.
		if ( ! empty( $layout['justifyContent'] ) ) {
			$css->add_variable( 'justify-content', $layout['justifyContent'] );
		}

		// Align content.
		if ( ! empty( $layout['alignContent'] ) ) {
			$css->add_variable( 'align-content', $layout['alignContent'] );
		}
	}

	/**
	 * Build grid-template-columns or grid-template-rows value.
	 *
	 * @param array $config Config { value, unit }.
	 * @return string CSS grid-template value.
	 */
	private static function build_grid_template( $config ) {
		if ( empty( $config['value'] ) ) {
			return '';
		}

		$value = $config['value'];
		$unit  = $config['unit'] ?? 'fr';

		// If unit is 'custom', value is already the full CSS value (e.g., "1fr 2fr").
		if ( 'custom' === $unit ) {
			return $value;
		}

		// If unit is 'fr' and value is a number, build repeat(n, 1fr).
		$num_value = intval( $value );
		if ( $num_value > 0 ) {
			return 'repeat(' . $num_value . ', 1fr)';
		}

		return $value;
	}
}
