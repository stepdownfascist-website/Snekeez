<?php
/**
 * Slider Block CSS Generator.
 *
 * Generates CSS for the Slider block using CSS_Helpers.
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
 * Slider CSS Generator class.
 */
class Slider_CSS {

	/**
	 * Generate CSS for Slider block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector       = '.blockflow-slider.blockflow-slider-' . $block_id;
		$arrow_selector = $selector . ' .blockflow-slider__arrow';
		$slide_selector = $selector . ' .swiper-slide';

		// Extract slider-specific attributes.
		$min_height           = $attrs['minHeight'] ?? [];
		$slides_border_radius = $attrs['slidesBorderRadius'] ?? [];
		$vertical_align       = $attrs['verticalAlign'] ?? 'center';
		$arrow_size           = $attrs['arrowSize'] ?? [];
		$arrow_color          = $attrs['arrowColor'] ?? [];
		$arrow_color_hover    = $attrs['arrowColorHover'] ?? [];
		$arrow_background     = $attrs['arrowBackground'] ?? [];
		$arrow_bg_hover       = $attrs['arrowBackgroundHover'] ?? [];
		$arrow_padding        = $attrs['arrowPadding'] ?? [];
		$arrow_radius         = $attrs['arrowBorderRadius'] ?? [];
		$arrow_offset         = $attrs['arrowOffset'] ?? [];
		$bullet_size          = $attrs['bulletSize'] ?? [];
		$bullet_color         = $attrs['bulletColor'] ?? [];
		$bullet_active        = $attrs['bulletColorActive'] ?? [];
		$bullet_gap           = $attrs['bulletGap'] ?? [];
		$pagination_offset    = $attrs['paginationOffset'] ?? [];
		$slide_background     = $attrs['slideBackground'] ?? [];
		$slide_padding        = $attrs['slidePadding'] ?? [];
		$slide_border         = $attrs['slideBorder'] ?? [];
		$slide_shadow         = $attrs['slideShadow'] ?? [];

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================
		$css->set_selector( $selector );

		// Slider-specific: min height.
		self::add_min_height( $css, $min_height, 'desktop' );

		// Slides border radius.
		self::add_slides_border_radius( $css, $slides_border_radius, 'desktop' );

		// Slider-specific: vertical align.
		if ( $vertical_align ) {
			$css->add_variable( 'slider-align', $vertical_align );
		}

		// Slider-specific: arrow styles.
		self::add_arrow_size( $css, $arrow_size, 'desktop' );
		CSS_Helpers::add_padding_var( $css, $arrow_padding['desktop'] ?? [], 'slider-arrow' );
		self::add_arrow_offset( $css, $arrow_offset, 'desktop' );

		// Arrow colors (light mode).
		self::add_arrow_colors( $css, $arrow_color, $arrow_color_hover, $arrow_background, $arrow_bg_hover );

		// Arrow border radius (direct property on arrow selector).
		$css->set_selector( $arrow_selector );
		CSS_Helpers::add_border_radius( $css, $arrow_radius['desktop'] ?? [] );

		// Pagination styles.
		$css->set_selector( $selector );
		self::add_bullet_size( $css, $bullet_size, 'desktop' );
		self::add_bullet_gap( $css, $bullet_gap, 'desktop' );
		self::add_pagination_offset( $css, $pagination_offset, 'desktop' );

		// Bullet colors (light mode).
		self::add_bullet_colors( $css, $bullet_color, $bullet_active );

		// Slide styles.
		self::add_slide_background( $css, $slide_background );

		// Slide padding as CSS variable (shorthand) on root selector.
		CSS_Helpers::add_padding_var( $css, $slide_padding['desktop'] ?? [], 'slide' );

		// Slide border (direct properties on slide selector).
		$css->set_selector( $slide_selector );
		$desktop_border = $slide_border['desktop'] ?? [];
		if ( ! empty( $desktop_border['style'] ) ) {
			$css->add_property( 'border-style', $desktop_border['style'] );
		}
		CSS_Helpers::add_border_width( $css, $desktop_border['width'] ?? [] );
		CSS_Helpers::add_border_radius( $css, $desktop_border['radius'] ?? [] );
		if ( ! empty( $desktop_border['color']['light'] ) ) {
			$css->add_property( 'border-color', $desktop_border['color']['light'] );
		}

		// Slide shadow.
		$css->set_selector( $selector );
		self::add_slide_shadow( $css, $slide_shadow );

		// Common Advanced Tab CSS (spacing, background, border, shadow, advanced, animation).
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );
		$css->set_selector( $selector );

		// Slider-specific.
		self::add_min_height( $css, $min_height, 'tablet' );
		self::add_slides_border_radius( $css, $slides_border_radius, 'tablet' );
		self::add_arrow_size( $css, $arrow_size, 'tablet' );
		CSS_Helpers::add_padding_var( $css, $arrow_padding['tablet'] ?? [], 'slider-arrow' );
		self::add_arrow_offset( $css, $arrow_offset, 'tablet' );

		// Arrow border radius.
		$css->set_selector( $arrow_selector );
		CSS_Helpers::add_border_radius( $css, $arrow_radius['tablet'] ?? [] );

		// Pagination.
		$css->set_selector( $selector );
		self::add_bullet_size( $css, $bullet_size, 'tablet' );
		self::add_bullet_gap( $css, $bullet_gap, 'tablet' );
		self::add_pagination_offset( $css, $pagination_offset, 'tablet' );

		// Slide padding as CSS variable.
		CSS_Helpers::add_padding_var( $css, $slide_padding['tablet'] ?? [], 'slide' );

		// Slide border.
		$css->set_selector( $slide_selector );
		$tablet_border = $slide_border['tablet'] ?? [];
		if ( ! empty( $tablet_border['style'] ) ) {
			$css->add_property( 'border-style', $tablet_border['style'] );
		}
		CSS_Helpers::add_border_width( $css, $tablet_border['width'] ?? [] );
		CSS_Helpers::add_border_radius( $css, $tablet_border['radius'] ?? [] );
		if ( ! empty( $tablet_border['color']['light'] ) ) {
			$css->add_property( 'border-color', $tablet_border['color']['light'] );
		}

		// Common Advanced Tab CSS (responsive).
		$css->set_selector( $selector );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );
		$css->set_selector( $selector );

		// Slider-specific.
		self::add_min_height( $css, $min_height, 'mobile' );
		self::add_slides_border_radius( $css, $slides_border_radius, 'mobile' );
		self::add_arrow_size( $css, $arrow_size, 'mobile' );
		CSS_Helpers::add_padding_var( $css, $arrow_padding['mobile'] ?? [], 'slider-arrow' );
		self::add_arrow_offset( $css, $arrow_offset, 'mobile' );

		// Arrow border radius.
		$css->set_selector( $arrow_selector );
		CSS_Helpers::add_border_radius( $css, $arrow_radius['mobile'] ?? [] );

		// Pagination.
		$css->set_selector( $selector );
		self::add_bullet_size( $css, $bullet_size, 'mobile' );
		self::add_bullet_gap( $css, $bullet_gap, 'mobile' );
		self::add_pagination_offset( $css, $pagination_offset, 'mobile' );

		// Slide padding as CSS variable.
		CSS_Helpers::add_padding_var( $css, $slide_padding['mobile'] ?? [], 'slide' );

		// Slide border.
		$css->set_selector( $slide_selector );
		$mobile_border = $slide_border['mobile'] ?? [];
		if ( ! empty( $mobile_border['style'] ) ) {
			$css->add_property( 'border-style', $mobile_border['style'] );
		}
		CSS_Helpers::add_border_width( $css, $mobile_border['width'] ?? [] );
		CSS_Helpers::add_border_radius( $css, $mobile_border['radius'] ?? [] );
		if ( ! empty( $mobile_border['color']['light'] ) ) {
			$css->add_property( 'border-color', $mobile_border['color']['light'] );
		}

		// Common Advanced Tab CSS (responsive).
		$css->set_selector( $selector );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		self::add_dark_mode( $css, $selector, $slide_selector, $arrow_color, $arrow_color_hover, $arrow_background, $arrow_bg_hover, $bullet_color, $bullet_active, $slide_background, $slide_border );

		// Common Advanced Tab dark mode (background, border colors).
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES (border + shadow)
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add min height CSS variable.
	 *
	 * @param CSS_Builder $css        CSS generator.
	 * @param array                    $min_height Min height attribute.
	 * @param string                   $device     Device key.
	 */
	private static function add_min_height( $css, $min_height, $device ) {
		$device_value = $min_height[ $device ] ?? [];
		$value        = $device_value['value'] ?? '';

		// Skip if empty or 0 (allows auto height).
		if ( '' === $value || '0' === $value || 0 === $value ) {
			return;
		}

		$unit = $device_value['unit'] ?? 'px';
		$css->add_variable( 'slider-min-h', $value . $unit );
	}

	/**
	 * Add slides border radius CSS variable.
	 *
	 * @param CSS_Builder $css                   CSS generator.
	 * @param array                    $slides_border_radius  Slides border radius attribute.
	 * @param string                   $device                Device key.
	 */
	private static function add_slides_border_radius( $css, $slides_border_radius, $device ) {
		$device_value = $slides_border_radius[ $device ] ?? [];
		$radius       = CSS_Helpers::build_radius_shorthand( $device_value );

		if ( $radius ) {
			$css->add_variable( 'slider-slide-radius', $radius );
		}
	}

	/**
	 * Add arrow size CSS variable.
	 *
	 * @param CSS_Builder $css        CSS generator.
	 * @param array                    $arrow_size Arrow size attribute.
	 * @param string                   $device     Device key.
	 */
	private static function add_arrow_size( $css, $arrow_size, $device ) {
		$device_value = $arrow_size[ $device ] ?? [];
		$value        = $device_value['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_value['unit'] ?? 'px';
		$css->add_variable( 'slider-arrow-size', $value . $unit );
	}

	/**
	 * Add arrow offset CSS variable.
	 *
	 * @param CSS_Builder $css          CSS generator.
	 * @param array                    $arrow_offset Arrow offset attribute.
	 * @param string                   $device       Device key.
	 */
	private static function add_arrow_offset( $css, $arrow_offset, $device ) {
		$device_value = $arrow_offset[ $device ] ?? [];
		$value        = $device_value['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_value['unit'] ?? 'px';
		$css->add_variable( 'slider-arrow-offset', $value . $unit );
	}

	/**
	 * Add arrow color CSS variables (light mode).
	 *
	 * @param CSS_Builder $css              CSS generator.
	 * @param array                    $arrow_color      Arrow color.
	 * @param array                    $arrow_color_hover Arrow hover color.
	 * @param array                    $arrow_background Arrow background.
	 * @param array                    $arrow_bg_hover   Arrow background hover.
	 */
	private static function add_arrow_colors( $css, $arrow_color, $arrow_color_hover, $arrow_background, $arrow_bg_hover ) {
		if ( ! empty( $arrow_color['light'] ) ) {
			$css->add_variable( 'slider-arrow-color', $arrow_color['light'] );
		}
		if ( ! empty( $arrow_color_hover['light'] ) ) {
			$css->add_variable( 'slider-arrow-color-hover', $arrow_color_hover['light'] );
		}
		if ( ! empty( $arrow_background['light'] ) ) {
			$css->add_variable( 'slider-arrow-bg', $arrow_background['light'] );
		}
		if ( ! empty( $arrow_bg_hover['light'] ) ) {
			$css->add_variable( 'slider-arrow-bg-hover', $arrow_bg_hover['light'] );
		}
	}

	/**
	 * Add bullet size CSS variable.
	 *
	 * @param CSS_Builder $css         CSS generator.
	 * @param array                    $bullet_size Bullet size attribute.
	 * @param string                   $device      Device key.
	 */
	private static function add_bullet_size( $css, $bullet_size, $device ) {
		$device_value = $bullet_size[ $device ] ?? [];
		$value        = $device_value['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_value['unit'] ?? 'px';
		$css->add_variable( 'slider-bullet-size', $value . $unit );
	}

	/**
	 * Add bullet gap CSS variable.
	 *
	 * @param CSS_Builder $css        CSS generator.
	 * @param array                    $bullet_gap Bullet gap attribute.
	 * @param string                   $device     Device key.
	 */
	private static function add_bullet_gap( $css, $bullet_gap, $device ) {
		$device_value = $bullet_gap[ $device ] ?? [];
		$value        = $device_value['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_value['unit'] ?? 'px';
		$css->add_variable( 'slider-bullet-gap', $value . $unit );
	}

	/**
	 * Add pagination offset CSS variable.
	 *
	 * @param CSS_Builder $css               CSS generator.
	 * @param array                    $pagination_offset Pagination offset attribute.
	 * @param string                   $device            Device key.
	 */
	private static function add_pagination_offset( $css, $pagination_offset, $device ) {
		$device_value = $pagination_offset[ $device ] ?? [];
		$value        = $device_value['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_value['unit'] ?? 'px';
		$css->add_variable( 'slider-pagination-offset', $value . $unit );
	}

	/**
	 * Add bullet color CSS variables (light mode).
	 *
	 * @param CSS_Builder $css           CSS generator.
	 * @param array                    $bullet_color  Bullet color.
	 * @param array                    $bullet_active Active bullet color.
	 */
	private static function add_bullet_colors( $css, $bullet_color, $bullet_active ) {
		if ( ! empty( $bullet_color['light'] ) ) {
			$css->add_variable( 'slider-bullet-color', $bullet_color['light'] );
		}
		if ( ! empty( $bullet_active['light'] ) ) {
			$css->add_variable( 'slider-bullet-color-active', $bullet_active['light'] );
		}
	}

	/**
	 * Add slide background CSS variable (light mode).
	 *
	 * @param CSS_Builder $css              CSS generator.
	 * @param array                    $slide_background Slide background.
	 */
	private static function add_slide_background( $css, $slide_background ) {
		if ( ! empty( $slide_background['light'] ) ) {
			$css->add_variable( 'slider-slide-bg', $slide_background['light'] );
		}
	}

	/**
	 * Add slide shadow CSS variable.
	 *
	 * @param CSS_Builder $css          CSS generator.
	 * @param array                    $slide_shadow Slide shadow.
	 */
	private static function add_slide_shadow( $css, $slide_shadow ) {
		$enabled = $slide_shadow['enabled'] ?? false;

		if ( ! $enabled ) {
			return;
		}

		$h      = $slide_shadow['horizontal'] ?? '0';
		$v      = $slide_shadow['vertical'] ?? '0';
		$blur   = $slide_shadow['blur'] ?? '0';
		$spread = $slide_shadow['spread'] ?? '0';
		$color  = $slide_shadow['color']['light'] ?? 'rgba(0,0,0,0.1)';
		$inset  = ! empty( $slide_shadow['inset'] ) ? 'inset ' : '';

		$shadow = "{$inset}{$h}px {$v}px {$blur}px {$spread}px {$color}";
		$css->add_variable( 'slider-slide-shadow', $shadow );
	}

	/**
	 * Add dark mode CSS.
	 *
	 * @param CSS_Builder $css              CSS generator.
	 * @param string                   $selector         Block selector.
	 * @param string                   $slide_selector   Slide selector.
	 * @param array                    $arrow_color      Arrow color.
	 * @param array                    $arrow_color_hover Arrow hover color.
	 * @param array                    $arrow_background Arrow background.
	 * @param array                    $arrow_bg_hover   Arrow background hover.
	 * @param array                    $bullet_color     Bullet color.
	 * @param array                    $bullet_active    Active bullet color.
	 * @param array                    $slide_background Slide background.
	 * @param array                    $slide_border     Slide border.
	 */
	private static function add_dark_mode( $css, $selector, $slide_selector, $arrow_color, $arrow_color_hover, $arrow_background, $arrow_bg_hover, $bullet_color, $bullet_active, $slide_background, $slide_border ) {
		// Collect dark mode values.
		$dark_arrow_color       = $arrow_color['dark'] ?? '';
		$dark_arrow_color_hover = $arrow_color_hover['dark'] ?? '';
		$dark_arrow_bg          = $arrow_background['dark'] ?? '';
		$dark_arrow_bg_hover    = $arrow_bg_hover['dark'] ?? '';
		$dark_bullet_color      = $bullet_color['dark'] ?? '';
		$dark_bullet_active     = $bullet_active['dark'] ?? '';
		$dark_slide_bg          = $slide_background['dark'] ?? '';
		$dark_slide_border      = $slide_border['desktop']['color']['dark'] ?? '';

		$has_dark_styles = $dark_arrow_color || $dark_arrow_color_hover || $dark_arrow_bg || $dark_arrow_bg_hover || $dark_bullet_color || $dark_bullet_active || $dark_slide_bg || $dark_slide_border;

		if ( ! $has_dark_styles ) {
			return;
		}

		// Use helper to handle dark mode method.
		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_arrow_color, $dark_arrow_color_hover, $dark_arrow_bg, $dark_arrow_bg_hover, $dark_bullet_color, $dark_bullet_active, $dark_slide_bg ) {
				if ( $dark_arrow_color ) {
					$css->add_variable( 'slider-arrow-color', $dark_arrow_color );
				}
				if ( $dark_arrow_color_hover ) {
					$css->add_variable( 'slider-arrow-color-hover', $dark_arrow_color_hover );
				}
				if ( $dark_arrow_bg ) {
					$css->add_variable( 'slider-arrow-bg', $dark_arrow_bg );
				}
				if ( $dark_arrow_bg_hover ) {
					$css->add_variable( 'slider-arrow-bg-hover', $dark_arrow_bg_hover );
				}
				if ( $dark_bullet_color ) {
					$css->add_variable( 'slider-bullet-color', $dark_bullet_color );
				}
				if ( $dark_bullet_active ) {
					$css->add_variable( 'slider-bullet-color-active', $dark_bullet_active );
				}
				if ( $dark_slide_bg ) {
					$css->add_variable( 'slider-slide-bg', $dark_slide_bg );
				}
			}
		);

		// Slide border dark mode (separate callback for slide selector).
		if ( $dark_slide_border ) {
			CSS_Helpers::add_dark_mode_css(
				$css,
				$slide_selector,
				function ( $css ) use ( $dark_slide_border ) {
					$css->add_property( 'border-color', $dark_slide_border );
				}
			);
		}
	}
}
