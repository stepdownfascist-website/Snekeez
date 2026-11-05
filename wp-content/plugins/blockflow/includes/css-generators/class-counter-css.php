<?php
/**
 * Counter Block CSS Generator.
 *
 * Generates CSS for the Counter block using CSS_Helpers.
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
 * Counter CSS Generator class.
 */
class Counter_CSS {

	/**
	 * Generate CSS for the counter block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector        = '.blockflow-counter.blockflow-counter-' . $block_id;
		$number_selector = $selector . ' .blockflow-counter__number';
		$prefix_selector = $selector . ' .blockflow-counter__prefix';
		$suffix_selector = $selector . ' .blockflow-counter__suffix';
		$label_selector  = $selector . ' .blockflow-counter__label';
		$item_selector   = $selector . ' .blockflow-counter__item';

		// Extract attributes.
		$columns           = $attrs['columns'] ?? [];
		$gap               = $attrs['gap'] ?? [];
		$item_gap          = $attrs['itemGap'] ?? [];
		$suffix_gap        = $attrs['suffixGap'] ?? [];
		$alignment         = $attrs['alignment'] ?? [];
		$separator         = $attrs['separator'] ?? [];
		$separator_width   = $attrs['separatorWidth'] ?? [];
		$separator_weight  = $attrs['separatorWeight'] ?? [];
		$show_icon         = $attrs['showIcon'] ?? false;
		$icon_size         = $attrs['iconSize'] ?? [];
		$number_typography = $attrs['numberTypography'] ?? [];
		$label_typography  = $attrs['labelTypography'] ?? [];
		$number_color      = $attrs['numberColor'] ?? [];
		$label_color       = $attrs['labelColor'] ?? [];
		$icon_color        = $attrs['iconColor'] ?? [];
		$item_background   = $attrs['itemBackground'] ?? [];
		$item_padding      = $attrs['itemPadding'] ?? [];
		$item_border       = $attrs['itemBorder'] ?? [];
		$item_border_hover = $attrs['itemBorderHover'] ?? [];
		$item_shadow       = $attrs['itemShadow'] ?? [];
		$item_shadow_hover = $attrs['itemShadowHover'] ?? [];

		// =====================================================================
		// DESKTOP STYLES
		// =====================================================================
		$css->set_selector( $selector );

		// Columns - uses shared grid variable.
		$desktop_columns = $columns['desktop']['value'] ?? '';
		if ( '' !== $desktop_columns ) {
			$css->add_variable( 'columns', $desktop_columns );
		}

		// Gap - uses shared grid variable.
		$desktop_gap = $gap['desktop'] ?? [];
		if ( ! empty( $desktop_gap['value'] ) ) {
			$gap_unit = $desktop_gap['unit'] ?? 'px';
			$css->add_variable( 'gap', $desktop_gap['value'] . $gap_unit );
		}

		// Item gap.
		$desktop_item_gap = $item_gap['desktop'] ?? [];
		if ( ! empty( $desktop_item_gap['value'] ) ) {
			$item_gap_unit = $desktop_item_gap['unit'] ?? 'px';
			$css->add_variable( 'counter-item-gap', $desktop_item_gap['value'] . $item_gap_unit );
		}

		// Suffix gap (responsive).
		$desktop_suffix_gap = $suffix_gap['desktop'] ?? [];
		if ( ! empty( $desktop_suffix_gap['value'] ) && '0' !== $desktop_suffix_gap['value'] ) {
			$suffix_gap_unit = $desktop_suffix_gap['unit'] ?? 'px';
			$css->add_variable( 'counter-suffix-gap', $desktop_suffix_gap['value'] . $suffix_gap_unit );
		}

		// Alignment.
		$desktop_align = $alignment['desktop'] ?? '';
		if ( '' !== $desktop_align ) {
			$css->add_variable( 'counter-align', CSS_Helpers::align_to_justify( $desktop_align ) );
		}

		// Colors.
		if ( ! empty( $number_color['light'] ) ) {
			$css->add_variable( 'counter-number-color', $number_color['light'] );
		}
		if ( ! empty( $label_color['light'] ) ) {
			$css->add_variable( 'counter-label-color', $label_color['light'] );
		}
		if ( $show_icon && ! empty( $icon_color['light'] ) ) {
			$css->add_variable( 'counter-icon-color', $icon_color['light'] );
		}
		if ( ! empty( $item_background['light'] ) ) {
			$css->add_variable( 'counter-item-bg', $item_background['light'] );
		}

		// Icon size.
		if ( $show_icon ) {
			$desktop_icon_size = $icon_size['desktop'] ?? [];
			if ( ! empty( $desktop_icon_size['value'] ) ) {
				$icon_unit = $desktop_icon_size['unit'] ?? 'px';
				$css->add_variable( 'counter-icon-size', $desktop_icon_size['value'] . $icon_unit );
			}
		}

		// Separator.
		$sep_enabled = $separator['enabled'] ?? false;
		if ( $sep_enabled ) {
			$sep_style = $separator['style'] ?? '';
			if ( '' !== $sep_style ) {
				$css->add_variable( 'counter-sep-style', $sep_style );
			}

			// Separator width (responsive).
			$desktop_sep_width = $separator_width['desktop'] ?? [];
			if ( ! empty( $desktop_sep_width['value'] ) ) {
				$sep_width_unit = $desktop_sep_width['unit'] ?? 'px';
				$css->add_variable( 'counter-sep-width', $desktop_sep_width['value'] . $sep_width_unit );
			}

			// Separator weight (responsive).
			$desktop_sep_weight = $separator_weight['desktop'] ?? [];
			if ( ! empty( $desktop_sep_weight['value'] ) ) {
				$sep_weight_unit = $desktop_sep_weight['unit'] ?? 'px';
				$css->add_variable( 'counter-sep-weight', $desktop_sep_weight['value'] . $sep_weight_unit );
			}

			if ( ! empty( $separator['color']['light'] ) ) {
				$css->add_variable( 'counter-sep-color', $separator['color']['light'] );
			}
		}

		// Item padding as CSS variable (shorthand) on root selector.
		CSS_Helpers::add_padding_var( $css, $item_padding['desktop'] ?? [], 'counter-item' );

		// Advanced tab.
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// Number typography.
		$css->set_selector( $number_selector );
		CSS_Helpers::add_typography_css( $css, $number_typography, 'desktop' );

		// Prefix typography.
		$css->set_selector( $prefix_selector );
		CSS_Helpers::add_typography_css( $css, $number_typography, 'desktop' );

		// Suffix typography.
		$css->set_selector( $suffix_selector );
		CSS_Helpers::add_typography_css( $css, $number_typography, 'desktop' );

		// Label typography.
		$css->set_selector( $label_selector );
		CSS_Helpers::add_typography_css( $css, $label_typography, 'desktop' );

		// Item styles (border, shadow).
		$css->set_selector( $item_selector );
		self::add_item_border( $css, $item_border['desktop'] ?? [] );
		CSS_Helpers::add_shadow( $css, $item_shadow );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );

		$css->set_selector( $selector );

		// Columns - uses shared grid variable.
		$tablet_columns = $columns['tablet']['value'] ?? '';
		if ( '' !== $tablet_columns ) {
			$css->add_variable( 'columns', $tablet_columns );
		}

		// Gap - uses shared grid variable.
		$tablet_gap = $gap['tablet'] ?? [];
		if ( ! empty( $tablet_gap['value'] ) ) {
			$gap_unit = $tablet_gap['unit'] ?? $gap['desktop']['unit'] ?? 'px';
			$css->add_variable( 'gap', $tablet_gap['value'] . $gap_unit );
		}

		// Item gap.
		$tablet_item_gap = $item_gap['tablet'] ?? [];
		if ( ! empty( $tablet_item_gap['value'] ) ) {
			$item_gap_unit = $tablet_item_gap['unit'] ?? $item_gap['desktop']['unit'] ?? 'px';
			$css->add_variable( 'counter-item-gap', $tablet_item_gap['value'] . $item_gap_unit );
		}

		// Suffix gap.
		$tablet_suffix_gap = $suffix_gap['tablet'] ?? [];
		if ( ! empty( $tablet_suffix_gap['value'] ) ) {
			$suffix_gap_unit = $tablet_suffix_gap['unit'] ?? $suffix_gap['desktop']['unit'] ?? 'px';
			$css->add_variable( 'counter-suffix-gap', $tablet_suffix_gap['value'] . $suffix_gap_unit );
		}

		// Alignment.
		$tablet_align = $alignment['tablet'] ?? '';
		if ( '' !== $tablet_align ) {
			$css->add_variable( 'counter-align', CSS_Helpers::align_to_justify( $tablet_align ) );
		}

		// Icon size.
		if ( $show_icon ) {
			$tablet_icon_size = $icon_size['tablet'] ?? [];
			if ( ! empty( $tablet_icon_size['value'] ) ) {
				$icon_unit = $tablet_icon_size['unit'] ?? $icon_size['desktop']['unit'] ?? 'px';
				$css->add_variable( 'counter-icon-size', $tablet_icon_size['value'] . $icon_unit );
			}
		}

		// Separator responsive (tablet).
		if ( $sep_enabled ) {
			$tablet_sep_width = $separator_width['tablet'] ?? [];
			if ( ! empty( $tablet_sep_width['value'] ) ) {
				$sep_width_unit = $tablet_sep_width['unit'] ?? $separator_width['desktop']['unit'] ?? 'px';
				$css->add_variable( 'counter-sep-width', $tablet_sep_width['value'] . $sep_width_unit );
			}

			$tablet_sep_weight = $separator_weight['tablet'] ?? [];
			if ( ! empty( $tablet_sep_weight['value'] ) ) {
				$sep_weight_unit = $tablet_sep_weight['unit'] ?? $separator_weight['desktop']['unit'] ?? 'px';
				$css->add_variable( 'counter-sep-weight', $tablet_sep_weight['value'] . $sep_weight_unit );
			}
		}

		CSS_Helpers::add_padding_var( $css, $item_padding['tablet'] ?? [], 'counter-item' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		// Number typography.
		$css->set_selector( $number_selector );
		CSS_Helpers::add_typography_css( $css, $number_typography, 'tablet' );

		// Prefix typography.
		$css->set_selector( $prefix_selector );
		CSS_Helpers::add_typography_css( $css, $number_typography, 'tablet' );

		// Suffix typography.
		$css->set_selector( $suffix_selector );
		CSS_Helpers::add_typography_css( $css, $number_typography, 'tablet' );

		// Label typography.
		$css->set_selector( $label_selector );
		CSS_Helpers::add_typography_css( $css, $label_typography, 'tablet' );

		// Item styles.
		$css->set_selector( $item_selector );
		self::add_item_border( $css, $item_border['tablet'] ?? [] );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );

		$css->set_selector( $selector );

		// Columns - uses shared grid variable.
		$mobile_columns = $columns['mobile']['value'] ?? '';
		if ( '' !== $mobile_columns ) {
			$css->add_variable( 'columns', $mobile_columns );
		}

		// Gap - uses shared grid variable.
		$mobile_gap = $gap['mobile'] ?? [];
		if ( ! empty( $mobile_gap['value'] ) ) {
			$gap_unit = $mobile_gap['unit'] ?? $gap['tablet']['unit'] ?? $gap['desktop']['unit'] ?? 'px';
			$css->add_variable( 'gap', $mobile_gap['value'] . $gap_unit );
		}

		// Item gap.
		$mobile_item_gap = $item_gap['mobile'] ?? [];
		if ( ! empty( $mobile_item_gap['value'] ) ) {
			$item_gap_unit = $mobile_item_gap['unit'] ?? $item_gap['tablet']['unit'] ?? $item_gap['desktop']['unit'] ?? 'px';
			$css->add_variable( 'counter-item-gap', $mobile_item_gap['value'] . $item_gap_unit );
		}

		// Suffix gap.
		$mobile_suffix_gap = $suffix_gap['mobile'] ?? [];
		if ( ! empty( $mobile_suffix_gap['value'] ) ) {
			$suffix_gap_unit = $mobile_suffix_gap['unit'] ?? $suffix_gap['tablet']['unit'] ?? $suffix_gap['desktop']['unit'] ?? 'px';
			$css->add_variable( 'counter-suffix-gap', $mobile_suffix_gap['value'] . $suffix_gap_unit );
		}

		// Alignment.
		$mobile_align = $alignment['mobile'] ?? '';
		if ( '' !== $mobile_align ) {
			$css->add_variable( 'counter-align', CSS_Helpers::align_to_justify( $mobile_align ) );
		}

		// Icon size.
		if ( $show_icon ) {
			$mobile_icon_size = $icon_size['mobile'] ?? [];
			if ( ! empty( $mobile_icon_size['value'] ) ) {
				$icon_unit = $mobile_icon_size['unit'] ?? $icon_size['tablet']['unit'] ?? $icon_size['desktop']['unit'] ?? 'px';
				$css->add_variable( 'counter-icon-size', $mobile_icon_size['value'] . $icon_unit );
			}
		}

		// Separator responsive (mobile).
		if ( $sep_enabled ) {
			$mobile_sep_width = $separator_width['mobile'] ?? [];
			if ( ! empty( $mobile_sep_width['value'] ) ) {
				$sep_width_unit = $mobile_sep_width['unit'] ?? $separator_width['tablet']['unit'] ?? $separator_width['desktop']['unit'] ?? 'px';
				$css->add_variable( 'counter-sep-width', $mobile_sep_width['value'] . $sep_width_unit );
			}

			$mobile_sep_weight = $separator_weight['mobile'] ?? [];
			if ( ! empty( $mobile_sep_weight['value'] ) ) {
				$sep_weight_unit = $mobile_sep_weight['unit'] ?? $separator_weight['tablet']['unit'] ?? $separator_weight['desktop']['unit'] ?? 'px';
				$css->add_variable( 'counter-sep-weight', $mobile_sep_weight['value'] . $sep_weight_unit );
			}
		}

		CSS_Helpers::add_padding_var( $css, $item_padding['mobile'] ?? [], 'counter-item' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		// Number typography.
		$css->set_selector( $number_selector );
		CSS_Helpers::add_typography_css( $css, $number_typography, 'mobile' );

		// Prefix typography.
		$css->set_selector( $prefix_selector );
		CSS_Helpers::add_typography_css( $css, $number_typography, 'mobile' );

		// Suffix typography.
		$css->set_selector( $suffix_selector );
		CSS_Helpers::add_typography_css( $css, $number_typography, 'mobile' );

		// Label typography.
		$css->set_selector( $label_selector );
		CSS_Helpers::add_typography_css( $css, $label_typography, 'mobile' );

		// Item styles.
		$css->set_selector( $item_selector );
		self::add_item_border( $css, $item_border['mobile'] ?? [] );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE
		// =====================================================================
		self::add_dark_mode( $css, $selector, $item_selector, $attrs );
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
		self::add_item_hover( $css, $item_selector, $item_border_hover, $item_shadow_hover );
	}

	/**
	 * Add item border CSS (style, width, color, radius).
	 *
	 * @param CSS_Builder $css          CSS generator.
	 * @param array                    $device_border Border data for specific device.
	 */
	private static function add_item_border( $css, $device_border ) {
		if ( empty( $device_border ) ) {
			return;
		}

		// Border style.
		$style = $device_border['style'] ?? '';
		if ( $style ) {
			$css->add_property( 'border-style', $style );
		}

		// Border width (use helper).
		CSS_Helpers::add_border_width( $css, $device_border['width'] ?? [] );

		// Border color.
		$color = $device_border['color']['light'] ?? '';
		if ( $color ) {
			$css->add_property( 'border-color', $color );
		}

		// Border radius (use helper).
		CSS_Helpers::add_border_radius( $css, $device_border['radius'] ?? [] );
	}

	/**
	 * Add dark mode CSS.
	 *
	 * @param CSS_Builder $css           CSS generator.
	 * @param string                   $selector      Root selector.
	 * @param string                   $item_selector Item selector.
	 * @param array                    $attrs         Block attributes.
	 */
	private static function add_dark_mode( $css, $selector, $item_selector, $attrs ) {
		$number_color    = $attrs['numberColor'] ?? [];
		$label_color     = $attrs['labelColor'] ?? [];
		$icon_color      = $attrs['iconColor'] ?? [];
		$item_background = $attrs['itemBackground'] ?? [];
		$separator       = $attrs['separator'] ?? [];
		$show_icon       = $attrs['showIcon'] ?? false;
		$item_border     = $attrs['itemBorder'] ?? [];

		// Counter colors dark mode.
		$dark_number  = $number_color['dark'] ?? '';
		$dark_label   = $label_color['dark'] ?? '';
		$dark_icon    = $show_icon ? ( $icon_color['dark'] ?? '' ) : '';
		$dark_item_bg = $item_background['dark'] ?? '';
		$dark_sep     = ( $separator['enabled'] ?? false ) ? ( $separator['color']['dark'] ?? '' ) : '';

		$has_dark = $dark_number || $dark_label || $dark_icon || $dark_item_bg || $dark_sep;

		if ( $has_dark ) {
			CSS_Helpers::add_dark_mode_css(
				$css,
				$selector,
				function ( $css ) use ( $dark_number, $dark_label, $dark_icon, $dark_item_bg, $dark_sep ) {
					if ( $dark_number ) {
						$css->add_variable( 'counter-number-color', $dark_number );
					}
					if ( $dark_label ) {
						$css->add_variable( 'counter-label-color', $dark_label );
					}
					if ( $dark_icon ) {
						$css->add_variable( 'counter-icon-color', $dark_icon );
					}
					if ( $dark_item_bg ) {
						$css->add_variable( 'counter-item-bg', $dark_item_bg );
					}
					if ( $dark_sep ) {
						$css->add_variable( 'counter-sep-color', $dark_sep );
					}
				}
			);
		}

		// Item border dark mode.
		$dark_border_color = $item_border['desktop']['color']['dark'] ?? '';
		if ( $dark_border_color ) {
			CSS_Helpers::add_dark_mode_css(
				$css,
				$item_selector,
				function ( $css ) use ( $dark_border_color ) {
					$css->add_property( 'border-color', $dark_border_color );
				}
			);
		}
	}

	/**
	 * Add item hover CSS.
	 *
	 * @param CSS_Builder $css           CSS generator.
	 * @param string                   $item_selector Item selector.
	 * @param array                    $border_hover  Border hover data.
	 * @param array                    $shadow_hover  Shadow hover data.
	 */
	private static function add_item_hover( $css, $item_selector, $border_hover, $shadow_hover ) {
		$desktop_border   = $border_hover['desktop'] ?? [];
		$has_border_hover = ! empty( $desktop_border['style'] ) ||
			! empty( $desktop_border['width'] ) ||
			! empty( $desktop_border['color']['light'] ) ||
			! empty( $desktop_border['radius'] );

		$has_shadow_hover = ! empty( $shadow_hover['enabled'] );

		if ( ! $has_border_hover && ! $has_shadow_hover ) {
			return;
		}

		$css->set_selector( $item_selector . ':hover' );

		if ( $has_border_hover ) {
			self::add_item_border( $css, $desktop_border );
		}

		if ( $has_shadow_hover ) {
			CSS_Helpers::add_shadow( $css, $shadow_hover );
		}
	}
}
