<?php
/**
 * Heading Block CSS Generator.
 *
 * Generates CSS for the Heading block using CSS_Helpers.
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
 * Heading CSS Generator class.
 */
class Heading_CSS {

	/**
	 * Generate CSS for Heading block.
	 *
	 * @param array              $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector            = '.blockflow-heading.blockflow-heading-' . $block_id;
		$text_selector       = $selector . ' .blockflow-heading__text';
		$subheading_selector = $selector . ' .blockflow-heading__subheading';
		$separator_selector  = $selector . ' .blockflow-heading__separator';

		// Extract heading-specific attributes.
		$alignment             = $attrs['alignment'] ?? [];
		$gap                   = $attrs['gap'] ?? [];
		$typography            = $attrs['typography'] ?? [];
		$text_color            = $attrs['textColor'] ?? [];
		$text_color_hover      = $attrs['textColorHover'] ?? [];
		$text_stroke           = $attrs['textStroke'] ?? [];
		$text_shadow           = $attrs['textShadow'] ?? [];
		$blend_mode            = $attrs['blendMode'] ?? '';
		$show_subheading       = $attrs['showSubheading'] ?? false;
		$subheading_typography = $attrs['subheadingTypography'] ?? [];
		$subheading_color      = $attrs['subheadingColor'] ?? [];

		// Separator attributes.
		$show_separator           = $attrs['showSeparator'] ?? false;
		$separator_width          = $attrs['separatorWidth'] ?? [];
		$separator_weight         = $attrs['separatorWeight'] ?? [];
		$separator_style          = $attrs['separatorStyle'] ?? 'solid';
		$separator_color          = $attrs['separatorColor'] ?? [];
		$separator_bottom_spacing = $attrs['separatorBottomSpacing'] ?? [];

		$stroke_enabled = $text_stroke['enabled'] ?? false;
		$shadow_enabled = $text_shadow['enabled'] ?? false;

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================

		// Wrapper: alignment, gap, color variables (all colors), spacing, advanced layout.
		$css->set_selector( $selector );
		$desktop_align = $alignment['desktop'] ?? '';
		if ( $desktop_align ) {
			$css->add_property( 'text-align', $desktop_align );
			$css->add_property( 'justify-content', CSS_Helpers::align_to_justify( $desktop_align ) );
		}
		// Gap (always output when value exists).
		CSS_Helpers::add_responsive_value_var( $css, $gap, 'desktop', 'block-gap', '', 'px' );
		if ( ! empty( $text_color['light'] ) ) {
			$css->add_variable( 'heading-color', $text_color['light'] );
		}
		if ( ! empty( $text_color_hover['light'] ) ) {
			$css->add_variable( 'heading-color-hover', $text_color_hover['light'] );
		}
		// Stroke color as variable (width output on inner text selector as direct property).
		if ( $stroke_enabled && ! empty( $text_stroke['color']['light'] ) ) {
			$css->add_variable( 'heading-stroke-color', $text_stroke['color']['light'] );
		}
		// Shadow color as variable.
		if ( $shadow_enabled && ! empty( $text_shadow['color']['light'] ) ) {
			$css->add_variable( 'heading-shadow-color', $text_shadow['color']['light'] );
		}
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// Inner text: typography, stroke width, shadow value (with var for color), blend, bottom spacing.
		$css->set_selector( $text_selector );
		CSS_Helpers::add_typography_css( $css, $typography, 'desktop' );
		// Stroke width as direct property (color via CSS variable on root).
		if ( $stroke_enabled ) {
			CSS_Helpers::add_text_stroke_width( $css, $text_stroke, 'desktop' );
		}
		if ( $shadow_enabled ) {
			self::add_text_shadow_with_var( $css, $text_shadow );
		}
		if ( $blend_mode ) {
			$css->add_property( 'mix-blend-mode', $blend_mode );
		}

		// Subheading styles.
		if ( $show_subheading ) {
			// Subheading color variable on root.
			if ( ! empty( $subheading_color['light'] ) ) {
				$css->set_selector( $selector );
				$css->add_variable( 'subheading-color', $subheading_color['light'] );
			}

			// Subheading typography.
			$css->set_selector( $subheading_selector );
			CSS_Helpers::add_typography_css( $css, $subheading_typography, 'desktop' );
		}

		// Separator styles.
		if ( $show_separator ) {
			// Separator color variable on root.
			if ( ! empty( $separator_color['light'] ) ) {
				$css->set_selector( $selector );
				$css->add_variable( 'separator-color', $separator_color['light'] );
			}

			// Separator properties on separator selector.
			$css->set_selector( $separator_selector );

			// Alignment (based on block alignment).
			self::add_separator_alignment( $css, $desktop_align );

			// Style.
			if ( $separator_style ) {
				$css->add_property( 'border-top-style', $separator_style );
			}

			// Width.
			$sep_width_desktop = $separator_width['desktop'] ?? [];
			if ( ! empty( $sep_width_desktop['value'] ) ) {
				$css->add_property( 'width', $sep_width_desktop['value'] . ( $sep_width_desktop['unit'] ?? 'px' ) );
			}

			// Weight.
			$sep_weight_desktop = $separator_weight['desktop'] ?? [];
			if ( ! empty( $sep_weight_desktop['value'] ) ) {
				$css->add_property( 'border-top-width', $sep_weight_desktop['value'] . ( $sep_weight_desktop['unit'] ?? 'px' ) );
			}

			// Bottom spacing.
			$sep_mb_desktop = $separator_bottom_spacing['desktop'] ?? [];
			if ( ! empty( $sep_mb_desktop['value'] ) ) {
				$css->add_property( 'margin-bottom', $sep_mb_desktop['value'] . ( $sep_mb_desktop['unit'] ?? 'px' ) );
			}
		}

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );

		// Wrapper: alignment, gap, advanced layout.
		$css->set_selector( $selector );
		$tablet_align = $alignment['tablet'] ?? '';
		if ( $tablet_align ) {
			$css->add_property( 'text-align', $tablet_align );
			$css->add_property( 'justify-content', CSS_Helpers::align_to_justify( $tablet_align ) );
		}
		CSS_Helpers::add_responsive_value_var( $css, $gap, 'tablet', 'block-gap', '', 'px' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		// Inner text: typography, stroke width.
		$css->set_selector( $text_selector );
		CSS_Helpers::add_typography_css( $css, $typography, 'tablet' );
		if ( $stroke_enabled ) {
			CSS_Helpers::add_text_stroke_width( $css, $text_stroke, 'tablet' );
		}

		// Subheading styles.
		if ( $show_subheading ) {
			$css->set_selector( $subheading_selector );
			CSS_Helpers::add_typography_css( $css, $subheading_typography, 'tablet' );
		}

		// Separator styles.
		if ( $show_separator ) {
			$css->set_selector( $separator_selector );

			// Alignment (based on block alignment).
			if ( $tablet_align ) {
				self::add_separator_alignment( $css, $tablet_align );
			}

			// Width.
			$sep_width_tablet = $separator_width['tablet'] ?? [];
			if ( ! empty( $sep_width_tablet['value'] ) ) {
				$css->add_property( 'width', $sep_width_tablet['value'] . ( $sep_width_tablet['unit'] ?? $separator_width['desktop']['unit'] ?? 'px' ) );
			}

			// Weight.
			$sep_weight_tablet = $separator_weight['tablet'] ?? [];
			if ( ! empty( $sep_weight_tablet['value'] ) ) {
				$css->add_property( 'border-top-width', $sep_weight_tablet['value'] . ( $sep_weight_tablet['unit'] ?? $separator_weight['desktop']['unit'] ?? 'px' ) );
			}

			// Bottom spacing.
			$sep_mb_tablet = $separator_bottom_spacing['tablet'] ?? [];
			if ( ! empty( $sep_mb_tablet['value'] ) ) {
				$css->add_property( 'margin-bottom', $sep_mb_tablet['value'] . ( $sep_mb_tablet['unit'] ?? $separator_bottom_spacing['desktop']['unit'] ?? 'px' ) );
			}
		}

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );

		// Wrapper: alignment, gap, advanced layout.
		$css->set_selector( $selector );
		$mobile_align = $alignment['mobile'] ?? '';
		if ( $mobile_align ) {
			$css->add_property( 'text-align', $mobile_align );
			$css->add_property( 'justify-content', CSS_Helpers::align_to_justify( $mobile_align ) );
		}
		CSS_Helpers::add_responsive_value_var( $css, $gap, 'mobile', 'block-gap', '', 'px' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		// Inner text: typography, stroke width.
		$css->set_selector( $text_selector );
		CSS_Helpers::add_typography_css( $css, $typography, 'mobile' );
		if ( $stroke_enabled ) {
			CSS_Helpers::add_text_stroke_width( $css, $text_stroke, 'mobile' );
		}

		// Subheading styles.
		if ( $show_subheading ) {
			$css->set_selector( $subheading_selector );
			CSS_Helpers::add_typography_css( $css, $subheading_typography, 'mobile' );
		}

		// Separator styles.
		if ( $show_separator ) {
			$css->set_selector( $separator_selector );

			// Alignment (based on block alignment).
			if ( $mobile_align ) {
				self::add_separator_alignment( $css, $mobile_align );
			}

			// Width.
			$sep_width_mobile = $separator_width['mobile'] ?? [];
			if ( ! empty( $sep_width_mobile['value'] ) ) {
				$css->add_property( 'width', $sep_width_mobile['value'] . ( $sep_width_mobile['unit'] ?? $separator_width['desktop']['unit'] ?? 'px' ) );
			}

			// Weight.
			$sep_weight_mobile = $separator_weight['mobile'] ?? [];
			if ( ! empty( $sep_weight_mobile['value'] ) ) {
				$css->add_property( 'border-top-width', $sep_weight_mobile['value'] . ( $sep_weight_mobile['unit'] ?? $separator_weight['desktop']['unit'] ?? 'px' ) );
			}

			// Bottom spacing.
			$sep_mb_mobile = $separator_bottom_spacing['mobile'] ?? [];
			if ( ! empty( $sep_mb_mobile['value'] ) ) {
				$css->add_property( 'margin-bottom', $sep_mb_mobile['value'] . ( $sep_mb_mobile['unit'] ?? $separator_bottom_spacing['desktop']['unit'] ?? 'px' ) );
			}
		}

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		self::add_heading_dark_mode( $css, $selector, $text_color, $text_color_hover, $text_stroke, $text_shadow, $stroke_enabled, $shadow_enabled, $show_subheading, $subheading_color, $show_separator, $separator_color );

		// Common Advanced Tab dark mode (background, border colors).
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES (border + shadow)
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add text shadow CSS property with var() for color.
	 *
	 * @param CSS_Builder $css    CSS generator instance.
	 * @param array               $shadow Text shadow values.
	 */
	private static function add_text_shadow_with_var( $css, $shadow ) {
		$h    = $shadow['horizontal'] ?? '0';
		$v    = $shadow['vertical'] ?? '0';
		$blur = $shadow['blur'] ?? '0';

		// Build shadow value with var() for color.
		$value = sprintf(
			'%spx %spx %spx var(--blockflow-heading-shadow-color, transparent)',
			$h,
			$v,
			$blur
		);

		$css->add_property( 'text-shadow', $value );
	}

	/**
	 * Add heading dark mode CSS (all variables on root selector).
	 *
	 * @param CSS_Builder $css               CSS generator.
	 * @param string                   $selector          Root block selector.
	 * @param array                    $text_color        Text color.
	 * @param array                    $text_color_hover  Text color hover.
	 * @param array                    $text_stroke       Text stroke.
	 * @param array                    $text_shadow       Text shadow.
	 * @param bool                     $stroke_enabled    Whether stroke is enabled.
	 * @param bool                     $shadow_enabled    Whether shadow is enabled.
	 * @param bool                     $show_subheading   Whether subheading is shown.
	 * @param array                    $subheading_color  Subheading color.
	 * @param bool                     $show_separator    Whether separator is shown.
	 * @param array                    $separator_color   Separator color.
	 */
	private static function add_heading_dark_mode( $css, $selector, $text_color, $text_color_hover, $text_stroke, $text_shadow, $stroke_enabled, $shadow_enabled, $show_subheading = false, $subheading_color = [], $show_separator = false, $separator_color = [] ) {
		$dark_color            = $text_color['dark'] ?? '';
		$dark_color_hover      = $text_color_hover['dark'] ?? '';
		$dark_stroke           = $stroke_enabled ? ( $text_stroke['color']['dark'] ?? '' ) : '';
		$dark_shadow           = $shadow_enabled ? ( $text_shadow['color']['dark'] ?? '' ) : '';
		$dark_subheading_color = $show_subheading ? ( $subheading_color['dark'] ?? '' ) : '';
		$dark_separator_color  = $show_separator ? ( $separator_color['dark'] ?? '' ) : '';

		if ( ! $dark_color && ! $dark_color_hover && ! $dark_stroke && ! $dark_shadow && ! $dark_subheading_color && ! $dark_separator_color ) {
			return;
		}

		// All color variables on root selector.
		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_color, $dark_color_hover, $dark_stroke, $dark_shadow, $dark_subheading_color, $dark_separator_color ) {
				if ( $dark_color ) {
					$css->add_variable( 'heading-color', $dark_color );
				}
				if ( $dark_color_hover ) {
					$css->add_variable( 'heading-color-hover', $dark_color_hover );
				}
				if ( $dark_stroke ) {
					$css->add_variable( 'heading-stroke-color', $dark_stroke );
				}
				if ( $dark_shadow ) {
					$css->add_variable( 'heading-shadow-color', $dark_shadow );
				}
				if ( $dark_subheading_color ) {
					$css->add_variable( 'subheading-color', $dark_subheading_color );
				}
				if ( $dark_separator_color ) {
					$css->add_variable( 'separator-color', $dark_separator_color );
				}
			}
		);
	}

	/**
	 * Add separator alignment CSS variables based on text alignment.
	 *
	 * @param CSS_Builder $css   CSS generator instance.
	 * @param string                   $align Alignment value (left, center, right, justify).
	 */
	private static function add_separator_alignment( $css, $align ) {
		if ( empty( $align ) ) {
			return;
		}

		// Map alignment to margin-left/margin-right values.
		$margins = [
			'left'    => [ '0', 'auto' ],
			'center'  => [ 'auto', 'auto' ],
			'right'   => [ 'auto', '0' ],
			'justify' => [ '0', 'auto' ],
		];

		$values = $margins[ $align ] ?? [ '0', 'auto' ];
		$css->add_variable( 'separator-ml', $values[0] );
		$css->add_variable( 'separator-mr', $values[1] );
	}
}
