<?php
/**
 * Marquee Text Block CSS Generator.
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
 * Marquee Text CSS Generator class.
 */
class Marquee_Text_CSS {

	/**
	 * Generate CSS for the block.
	 *
	 * @param array                       $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 * @return void
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';
		if ( ! $block_id ) {
			return;
		}

		$selector      = '.blockflow-marquee-text.blockflow-marquee-text-' . $block_id;
		$item_selector = $selector . ' .blockflow-marquee-text__item';

		// Extract text style attributes.
		$typography     = $attrs['typography'] ?? [];
		$text_shadow    = $attrs['textShadow'] ?? [];
		$blend_mode     = $attrs['blendMode'] ?? '';
		$shadow_enabled = ! empty( $text_shadow['enabled'] );

		// Desktop styles - root selector.
		$css->set_selector( $selector );
		self::add_layout_styles( $css, $attrs, 'desktop' );
		self::add_divider_styles( $css, $attrs, 'desktop' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// Shadow color as variable on root.
		if ( $shadow_enabled && ! empty( $text_shadow['color']['light'] ) ) {
			$css->add_variable( 'marquee-shadow-color', $text_shadow['color']['light'] );
		}

		// Typography and text effects on item selector.
		$css->set_selector( $item_selector );
		CSS_Helpers::add_typography_css( $css, $typography, 'desktop' );

		// Text shadow.
		if ( $shadow_enabled ) {
			self::add_text_shadow_with_var( $css, $text_shadow );
		}

		// Blend mode.
		if ( $blend_mode ) {
			$css->add_property( 'mix-blend-mode', $blend_mode );
		}

		// Tablet styles.
		$css->start_media_query( 'tablet' );
		$css->set_selector( $selector );
		self::add_layout_styles( $css, $attrs, 'tablet' );
		self::add_divider_styles( $css, $attrs, 'tablet' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		$css->set_selector( $item_selector );
		CSS_Helpers::add_typography_css( $css, $typography, 'tablet' );
		$css->end_media_query();

		// Mobile styles.
		$css->start_media_query( 'mobile' );
		$css->set_selector( $selector );
		self::add_layout_styles( $css, $attrs, 'mobile' );
		self::add_divider_styles( $css, $attrs, 'mobile' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		$css->set_selector( $item_selector );
		CSS_Helpers::add_typography_css( $css, $typography, 'mobile' );
		$css->end_media_query();

		// Dark mode.
		self::add_dark_mode( $css, $selector, $attrs );
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// Hover states.
		self::add_hover_styles( $css, $selector, $attrs );
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add layout styles.
	 *
	 * @param CSS_Builder $css        CSS generator instance.
	 * @param array                      $attrs      Block attributes.
	 * @param string                     $device     Device type.
	 * @return void
	 */
	private static function add_layout_styles( $css, $attrs, $device ) {
		$gap        = $attrs['gap'] ?? [];
		$is_desktop = 'desktop' === $device;

		// Gap.
		$gap_value = $gap[ $device ]['value'] ?? '';
		$gap_unit  = $gap[ $device ]['unit'] ?? $gap['desktop']['unit'] ?? 'px';

		if ( '' !== $gap_value ) {
			$css->add_variable( 'marquee-gap', $gap_value . $gap_unit );
		}

		// Text color (desktop only, non-responsive).
		if ( $is_desktop ) {
			$text_color = $attrs['textColor'] ?? [];
			$light      = $text_color['light'] ?? '';
			if ( $light ) {
				$css->add_variable( 'marquee-text-color', $light );
			}
		}

		// Text stroke.
		$text_stroke = $attrs['textStroke'] ?? [];
		$is_enabled  = ! empty( $text_stroke['enabled'] );

		if ( $is_enabled ) {
			$stroke_width = $text_stroke['width'] ?? [];
			$width_value  = $stroke_width[ $device ]['value'] ?? '';
			$width_unit   = $stroke_width[ $device ]['unit'] ?? $stroke_width['desktop']['unit'] ?? 'px';

			if ( '' !== $width_value ) {
				$css->add_variable( 'marquee-text-stroke-width', $width_value . $width_unit );
			}

			// Stroke color (desktop only).
			if ( $is_desktop ) {
				$stroke_color = $text_stroke['color'] ?? [];
				$stroke_light = $stroke_color['light'] ?? '';
				if ( $stroke_light ) {
					$css->add_variable( 'marquee-text-stroke-color', $stroke_light );
				}
			}
		}
	}

	/**
	 * Add divider styles.
	 *
	 * @param CSS_Builder $css        CSS generator instance.
	 * @param array                      $attrs      Block attributes.
	 * @param string                     $device     Device type.
	 * @return void
	 */
	private static function add_divider_styles( $css, $attrs, $device ) {
		$divider_size   = $attrs['dividerSize'] ?? [];
		$divider_color  = $attrs['dividerColor'] ?? [];
		$divider_rotate = $attrs['dividerRotate'] ?? 0;
		$is_desktop     = 'desktop' === $device;

		// Divider size.
		$size_value = $divider_size[ $device ]['value'] ?? '';
		$size_unit  = $divider_size[ $device ]['unit'] ?? $divider_size['desktop']['unit'] ?? 'px';

		if ( '' !== $size_value ) {
			$css->add_variable( 'marquee-divider-size', $size_value . $size_unit );
		}

		// Divider color and rotation (desktop only, non-responsive).
		if ( $is_desktop ) {
			$light = $divider_color['light'] ?? '';
			if ( $light ) {
				$css->add_variable( 'marquee-divider-color', $light );
			}

			if ( '' !== $divider_rotate && null !== $divider_rotate ) {
				$css->add_variable( 'marquee-divider-rotate', $divider_rotate . 'deg' );
			}
		}
	}

	/**
	 * Add dark mode styles.
	 *
	 * @param CSS_Builder $css      CSS generator instance.
	 * @param string                     $selector Block selector.
	 * @param array                      $attrs    Block attributes.
	 * @return void
	 */
	private static function add_dark_mode( $css, $selector, $attrs ) {
		$text_color       = $attrs['textColor'] ?? [];
		$text_color_hover = $attrs['textColorHover'] ?? [];
		$divider_color    = $attrs['dividerColor'] ?? [];
		$text_stroke      = $attrs['textStroke'] ?? [];
		$text_shadow      = $attrs['textShadow'] ?? [];

		$dark_text         = $text_color['dark'] ?? '';
		$dark_text_hover   = $text_color_hover['dark'] ?? '';
		$dark_divider      = $divider_color['dark'] ?? '';
		$dark_stroke       = '';
		$dark_shadow       = '';
		$stroke_is_enabled = ! empty( $text_stroke['enabled'] );
		$shadow_is_enabled = ! empty( $text_shadow['enabled'] );

		if ( $stroke_is_enabled ) {
			$dark_stroke = $text_stroke['color']['dark'] ?? '';
		}

		if ( $shadow_is_enabled ) {
			$dark_shadow = $text_shadow['color']['dark'] ?? '';
		}

		$has_dark = $dark_text || $dark_text_hover || $dark_divider || $dark_stroke || $dark_shadow;
		if ( ! $has_dark ) {
			return;
		}

		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_text, $dark_text_hover, $dark_divider, $dark_stroke, $dark_shadow ) {
				if ( $dark_text ) {
					$css->add_variable( 'marquee-text-color', $dark_text );
				}
				if ( $dark_text_hover ) {
					$css->add_variable( 'marquee-text-color-hover', $dark_text_hover );
				}
				if ( $dark_divider ) {
					$css->add_variable( 'marquee-divider-color', $dark_divider );
				}
				if ( $dark_stroke ) {
					$css->add_variable( 'marquee-text-stroke-color', $dark_stroke );
				}
				if ( $dark_shadow ) {
					$css->add_variable( 'marquee-shadow-color', $dark_shadow );
				}
			}
		);
	}

	/**
	 * Add text shadow CSS property with var() for color.
	 *
	 * @param CSS_Builder $css    CSS generator instance.
	 * @param array                      $shadow Text shadow values.
	 * @return void
	 */
	private static function add_text_shadow_with_var( $css, $shadow ) {
		$h    = $shadow['horizontal'] ?? '0';
		$v    = $shadow['vertical'] ?? '0';
		$blur = $shadow['blur'] ?? '0';

		// Build shadow value with var() for color.
		$value = sprintf(
			'%spx %spx %spx var(--blockflow-marquee-shadow-color, transparent)',
			$h,
			$v,
			$blur
		);

		$css->add_property( 'text-shadow', $value );
	}

	/**
	 * Add hover styles.
	 *
	 * @param CSS_Builder $css      CSS generator instance.
	 * @param string                     $selector Block selector.
	 * @param array                      $attrs    Block attributes.
	 * @return void
	 */
	private static function add_hover_styles( $css, $selector, $attrs ) {
		$text_color_hover = $attrs['textColorHover'] ?? [];
		$light            = $text_color_hover['light'] ?? '';

		if ( $light ) {
			$css->set_selector( $selector );
			$css->add_variable( 'marquee-text-color-hover', $light );
		}
	}
}
