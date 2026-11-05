<?php
/**
 * Carousel Images Block CSS Generator.
 *
 * Generates CSS for the Carousel Images block using CSS_Helpers.
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
 * Carousel Images CSS Generator class.
 */
class Carousel_Images_CSS {

	/**
	 * Generate CSS for Carousel Images block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector                  = '.blockflow-carousel-images.blockflow-carousel-images-' . $block_id;
		$caption_selector          = $selector . ' .blockflow-carousel-images__caption';
		$lightbox_caption_selector = '.blockflow-lightbox--' . $block_id . ' .blockflow-lightbox__caption';

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================
		$css->set_selector( $selector );

		// Layout.
		self::add_slides_per_view( $css, $attrs, 'desktop' );
		self::add_gap( $css, $attrs, 'desktop' );
		self::add_aspect_ratio( $css, $attrs, 'desktop' );

		// Arrow styles.
		self::add_arrow_styles( $css, $attrs, 'desktop' );

		// Pagination styles.
		self::add_pagination_styles( $css, $attrs, 'desktop' );

		// Image styles.
		self::add_image_styles( $css, $attrs, 'desktop' );
		self::add_overlay_styles( $css, $attrs );

		// Caption styles.
		self::add_caption_styles( $css, $selector, $caption_selector, $lightbox_caption_selector, $attrs, 'desktop' );

		// Common Advanced Tab CSS.
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );
		$css->set_selector( $selector );

		// Layout.
		self::add_slides_per_view( $css, $attrs, 'tablet' );
		self::add_gap( $css, $attrs, 'tablet' );
		self::add_aspect_ratio( $css, $attrs, 'tablet' );

		// Arrow styles.
		self::add_arrow_styles( $css, $attrs, 'tablet' );

		// Pagination.
		self::add_pagination_styles( $css, $attrs, 'tablet' );

		// Image styles.
		self::add_image_styles( $css, $attrs, 'tablet' );

		// Caption styles.
		self::add_caption_styles( $css, $selector, $caption_selector, $lightbox_caption_selector, $attrs, 'tablet' );

		// Advanced Tab CSS.
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );
		$css->set_selector( $selector );

		// Layout.
		self::add_slides_per_view( $css, $attrs, 'mobile' );
		self::add_gap( $css, $attrs, 'mobile' );
		self::add_aspect_ratio( $css, $attrs, 'mobile' );

		// Arrow styles.
		self::add_arrow_styles( $css, $attrs, 'mobile' );

		// Pagination.
		self::add_pagination_styles( $css, $attrs, 'mobile' );

		// Image styles.
		self::add_image_styles( $css, $attrs, 'mobile' );

		// Caption styles.
		self::add_caption_styles( $css, $selector, $caption_selector, $lightbox_caption_selector, $attrs, 'mobile' );

		// Advanced Tab CSS.
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE
		// =====================================================================
		self::add_dark_mode( $css, $selector, $attrs );
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES
		// =====================================================================
		self::add_image_hover_styles( $css, $selector, $attrs );
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add slides per view CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $attrs  Attributes.
	 * @param string                   $device Device key.
	 */
	private static function add_slides_per_view( $css, $attrs, $device ) {
		$slides_per_view = $attrs['slidesPerView'] ?? [];
		$value           = $slides_per_view[ $device ]['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$css->add_variable( 'carousel-slides', $value );
	}

	/**
	 * Add gap CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $attrs  Attributes.
	 * @param string                   $device Device key.
	 */
	private static function add_gap( $css, $attrs, $device ) {
		$gap   = $attrs['gap'] ?? [];
		$value = $gap[ $device ]['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $gap[ $device ]['unit'] ?? 'px';
		$css->add_variable( 'carousel-gap', $value . $unit );
	}

	/**
	 * Add aspect ratio CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $attrs  Attributes.
	 * @param string                   $device Device key.
	 */
	private static function add_aspect_ratio( $css, $attrs, $device ) {
		$aspect_ratio = $attrs['aspectRatio'] ?? [];

		// Only output if aspect ratio is enabled.
		if ( empty( $aspect_ratio['enabled'] ) ) {
			return;
		}

		$is_desktop  = 'desktop' === $device;
		$device_data = $aspect_ratio[ $device ] ?? [];
		$ratio_value = $device_data['value'] ?? '';

		// Output ratio value if set.
		if ( '' !== $ratio_value ) {
			$css->add_variable( 'carousel-img-ratio', $ratio_value . '%' );
		}

		// Object-fit is a separate attribute, only output on desktop.
		if ( $is_desktop ) {
			$object_fit = $attrs['objectFit'] ?? '';
			if ( $object_fit ) {
				$css->add_variable( 'carousel-img-fit', $object_fit );
			}
		}
	}

	/**
	 * Add arrow styles (size, padding, offset, radius, colors).
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $attrs  Attributes.
	 * @param string                   $device Device key.
	 */
	private static function add_arrow_styles( $css, $attrs, $device ) {
		$is_desktop = 'desktop' === $device;

		// Arrow size.
		$arrow_size = $attrs['arrowSize'] ?? [];
		$size_value = $arrow_size[ $device ]['value'] ?? '';
		if ( '' !== $size_value ) {
			$size_unit = $arrow_size[ $device ]['unit'] ?? 'px';
			$css->add_variable( 'carousel-arrow-size', $size_value . $size_unit );
		}

		// Arrow padding (single value, not 4-sided).
		$arrow_padding = $attrs['arrowPadding'] ?? [];
		$padding_value = $arrow_padding[ $device ]['value'] ?? '';
		if ( '' !== $padding_value ) {
			$padding_unit = $arrow_padding[ $device ]['unit'] ?? 'px';
			$css->add_variable( 'carousel-arrow-padding', $padding_value . $padding_unit );
		}

		// Arrow offset.
		$arrow_offset = $attrs['arrowOffset'] ?? [];
		$offset_value = $arrow_offset[ $device ]['value'] ?? '';
		if ( '' !== $offset_value ) {
			$offset_unit = $arrow_offset[ $device ]['unit'] ?? 'px';
			$css->add_variable( 'carousel-arrow-offset', $offset_value . $offset_unit );
		}

		// Arrow border radius.
		$arrow_radius = $attrs['arrowBorderRadius'] ?? [];
		$radius       = $arrow_radius[ $device ] ?? [];
		$radius_value = CSS_Helpers::build_radius_shorthand( $radius );
		if ( $radius_value ) {
			$css->add_variable( 'carousel-arrow-radius', $radius_value );
		}

		// Arrow colors (desktop only, non-responsive).
		if ( $is_desktop ) {
			$arrow_color       = $attrs['arrowColor'] ?? [];
			$arrow_color_hover = $attrs['arrowColorHover'] ?? [];
			$arrow_background  = $attrs['arrowBackground'] ?? [];
			$arrow_bg_hover    = $attrs['arrowBackgroundHover'] ?? [];

			if ( ! empty( $arrow_color['light'] ) ) {
				$css->add_variable( 'carousel-arrow-color', $arrow_color['light'] );
			}
			if ( ! empty( $arrow_color_hover['light'] ) ) {
				$css->add_variable( 'carousel-arrow-color-hover', $arrow_color_hover['light'] );
			}
			if ( ! empty( $arrow_background['light'] ) ) {
				$css->add_variable( 'carousel-arrow-bg', $arrow_background['light'] );
			}
			if ( ! empty( $arrow_bg_hover['light'] ) ) {
				$css->add_variable( 'carousel-arrow-bg-hover', $arrow_bg_hover['light'] );
			}
		}
	}

	/**
	 * Add pagination styles (bullet size, gap, offset, colors).
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $attrs  Attributes.
	 * @param string                   $device Device key.
	 */
	private static function add_pagination_styles( $css, $attrs, $device ) {
		$is_desktop = 'desktop' === $device;

		// Bullet size.
		$bullet_size = $attrs['bulletSize'] ?? [];
		$size_value  = $bullet_size[ $device ]['value'] ?? '';
		if ( '' !== $size_value ) {
			$size_unit = $bullet_size[ $device ]['unit'] ?? 'px';
			$css->add_variable( 'carousel-bullet-size', $size_value . $size_unit );
		}

		// Bullet gap.
		$bullet_gap = $attrs['bulletGap'] ?? [];
		$gap_value  = $bullet_gap[ $device ]['value'] ?? '';
		if ( '' !== $gap_value ) {
			$gap_unit = $bullet_gap[ $device ]['unit'] ?? 'px';
			$css->add_variable( 'carousel-bullet-gap', $gap_value . $gap_unit );
		}

		// Pagination offset.
		$pagination_offset = $attrs['paginationOffset'] ?? [];
		$offset_value      = $pagination_offset[ $device ]['value'] ?? '';
		if ( '' !== $offset_value ) {
			$offset_unit = $pagination_offset[ $device ]['unit'] ?? 'px';
			$css->add_variable( 'carousel-pagination-offset', $offset_value . $offset_unit );
		}

		// Bullet colors (desktop only, non-responsive).
		if ( $is_desktop ) {
			$bullet_color  = $attrs['bulletColor'] ?? [];
			$bullet_active = $attrs['bulletColorActive'] ?? [];

			if ( ! empty( $bullet_color['light'] ) ) {
				$css->add_variable( 'carousel-bullet-color', $bullet_color['light'] );
			}
			if ( ! empty( $bullet_active['light'] ) ) {
				$css->add_variable( 'carousel-bullet-color-active', $bullet_active['light'] );
			}
		}
	}

	/**
	 * Add image styles (border, box shadow).
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $attrs  Attributes.
	 * @param string                   $device Device key.
	 */
	private static function add_image_styles( $css, $attrs, $device ) {
		$image_border = $attrs['imageBorder'] ?? [];
		$is_desktop   = 'desktop' === $device;
		$border       = $image_border[ $device ] ?? [];

		// Skip if no border data and not desktop.
		if ( empty( $border ) && ! $is_desktop ) {
			return;
		}

		// Use helper for image border variables.
		CSS_Helpers::add_image_border_vars( $css, $border, 'carousel' );

		// Box shadow (desktop only - non-responsive).
		if ( $is_desktop ) {
			$box_shadow   = $attrs['imageBoxShadow'] ?? [];
			$shadow_value = CSS_Helpers::build_box_shadow( $box_shadow );
			if ( $shadow_value ) {
				$css->add_variable( 'carousel-shadow', $shadow_value );
			}
		}
	}

	/**
	 * Add image hover styles (box shadow hover, border hover).
	 *
	 * @param CSS_Builder $css      CSS generator.
	 * @param string                   $selector Block selector.
	 * @param array                    $attrs    Attributes.
	 */
	private static function add_image_hover_styles( $css, $selector, $attrs ) {
		CSS_Helpers::add_image_hover_css(
			$css,
			$selector . ' .blockflow-carousel-images__media:hover',
			$attrs['imageBorderHover'] ?? [],
			$attrs['imageBoxShadowHover'] ?? []
		);
	}

	/**
	 * Add overlay styles.
	 *
	 * @param CSS_Builder $css   CSS generator.
	 * @param array                    $attrs Attributes.
	 */
	private static function add_overlay_styles( $css, $attrs ) {
		CSS_Helpers::add_overlay_vars(
			$css,
			$attrs['overlayColor'] ?? [],
			$attrs['overlayOpacity'] ?? 0.3,
			$attrs['overlayHoverOpacity'] ?? 0.5,
			'carousel'
		);
	}

	/**
	 * Add caption styles.
	 *
	 * @param CSS_Builder $css                      CSS generator.
	 * @param string                   $selector                 Block selector.
	 * @param string                   $caption_selector         Caption selector.
	 * @param string                   $lightbox_caption_selector Lightbox caption selector.
	 * @param array                    $attrs                    Attributes.
	 * @param string                   $device                   Device key.
	 */
	private static function add_caption_styles( $css, $selector, $caption_selector, $lightbox_caption_selector, $attrs, $device ) {
		$caption_settings      = $attrs['caption'] ?? [];
		$caption_display       = $caption_settings['display'] ?? 'none';
		$caption_typography    = $attrs['captionTypography'] ?? [];
		$caption_color         = $attrs['captionColor'] ?? [];
		$caption_background    = $attrs['captionBackground'] ?? [];
		$caption_padding       = $attrs['captionPadding'] ?? [];
		$caption_border_radius = $attrs['captionBorderRadius'] ?? [];
		$is_desktop            = 'desktop' === $device;

		// Only output caption styles if enabled.
		if ( 'none' === $caption_display ) {
			return;
		}

		// Color, background, and alignment (desktop only, non-responsive).
		if ( $is_desktop ) {
			$css->set_selector( $selector );

			$light_color = $caption_color['light'] ?? '';
			if ( $light_color ) {
				$css->add_variable( 'carousel-caption-color', $light_color );
			}

			$light_bg = $caption_background['light'] ?? '';
			if ( $light_bg ) {
				$css->add_variable( 'carousel-caption-bg', $light_bg );
			}

			// Caption alignment (only for 'below' mode).
			if ( 'below' === $caption_display ) {
				$alignment = $caption_settings['alignment'] ?? '';
				if ( $alignment ) {
					$css->add_variable( 'carousel-caption-align', $alignment );
				}
			}
		}

		// Typography (carousel caption and lightbox caption).
		$typography = $caption_typography[ $device ] ?? [];
		if ( ! empty( $typography ) || $is_desktop ) {
			$css->set_selector( $caption_selector . ', ' . $lightbox_caption_selector );
			CSS_Helpers::add_typography_css( $css, $caption_typography, $device );
		}

		// Padding.
		$padding = $caption_padding[ $device ] ?? [];
		if ( ! empty( $padding ) ) {
			$css->set_selector( $selector );
			CSS_Helpers::add_padding_var( $css, $padding, 'carousel-caption' );
		}

		// Border radius.
		$border_radius = $caption_border_radius[ $device ] ?? [];
		$radius_value  = CSS_Helpers::build_radius_shorthand( $border_radius );
		if ( $radius_value ) {
			$css->set_selector( $selector );
			$css->add_variable( 'carousel-caption-radius', $radius_value );
		}
	}

	/**
	 * Add dark mode styles.
	 *
	 * @param CSS_Builder $css      CSS generator.
	 * @param string                   $selector Block selector.
	 * @param array                    $attrs    Attributes.
	 */
	private static function add_dark_mode( $css, $selector, $attrs ) {
		$arrow_color        = $attrs['arrowColor'] ?? [];
		$arrow_color_hover  = $attrs['arrowColorHover'] ?? [];
		$arrow_background   = $attrs['arrowBackground'] ?? [];
		$arrow_bg_hover     = $attrs['arrowBackgroundHover'] ?? [];
		$bullet_color       = $attrs['bulletColor'] ?? [];
		$bullet_active      = $attrs['bulletColorActive'] ?? [];
		$overlay_color      = $attrs['overlayColor'] ?? [];
		$caption_color      = $attrs['captionColor'] ?? [];
		$caption_background = $attrs['captionBackground'] ?? [];
		$image_border       = $attrs['imageBorder'] ?? [];
		$border_color       = $image_border['desktop']['color'] ?? [];
		$box_shadow         = $attrs['imageBoxShadow'] ?? [];
		$box_shadow_hover   = $attrs['imageBoxShadowHover'] ?? [];

		// Collect dark mode values.
		$dark_arrow_color       = $arrow_color['dark'] ?? '';
		$dark_arrow_color_hover = $arrow_color_hover['dark'] ?? '';
		$dark_arrow_bg          = $arrow_background['dark'] ?? '';
		$dark_arrow_bg_hover    = $arrow_bg_hover['dark'] ?? '';
		$dark_bullet_color      = $bullet_color['dark'] ?? '';
		$dark_bullet_active     = $bullet_active['dark'] ?? '';
		$dark_overlay           = $overlay_color['dark'] ?? '';
		$dark_caption_color     = $caption_color['dark'] ?? '';
		$dark_caption_bg        = $caption_background['dark'] ?? '';
		$dark_border_color      = $border_color['dark'] ?? '';
		$dark_shadow            = $box_shadow['color']['dark'] ?? '';
		$dark_shadow_hover      = $box_shadow_hover['color']['dark'] ?? '';

		$has_dark_styles = $dark_arrow_color || $dark_arrow_color_hover || $dark_arrow_bg || $dark_arrow_bg_hover || $dark_bullet_color || $dark_bullet_active || $dark_overlay || $dark_caption_color || $dark_caption_bg || $dark_border_color || $dark_shadow || $dark_shadow_hover;

		if ( ! $has_dark_styles ) {
			return;
		}

		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_arrow_color, $dark_arrow_color_hover, $dark_arrow_bg, $dark_arrow_bg_hover, $dark_bullet_color, $dark_bullet_active, $dark_overlay, $dark_caption_color, $dark_caption_bg, $dark_border_color, $dark_shadow, $dark_shadow_hover, $box_shadow, $box_shadow_hover ) {
				if ( $dark_arrow_color ) {
					$css->add_variable( 'carousel-arrow-color', $dark_arrow_color );
				}
				if ( $dark_arrow_color_hover ) {
					$css->add_variable( 'carousel-arrow-color-hover', $dark_arrow_color_hover );
				}
				if ( $dark_arrow_bg ) {
					$css->add_variable( 'carousel-arrow-bg', $dark_arrow_bg );
				}
				if ( $dark_arrow_bg_hover ) {
					$css->add_variable( 'carousel-arrow-bg-hover', $dark_arrow_bg_hover );
				}
				if ( $dark_bullet_color ) {
					$css->add_variable( 'carousel-bullet-color', $dark_bullet_color );
				}
				if ( $dark_bullet_active ) {
					$css->add_variable( 'carousel-bullet-color-active', $dark_bullet_active );
				}
				if ( $dark_overlay ) {
					$css->add_variable( 'carousel-overlay-bg', $dark_overlay );
				}
				if ( $dark_caption_color ) {
					$css->add_variable( 'carousel-caption-color', $dark_caption_color );
				}
				if ( $dark_caption_bg ) {
					$css->add_variable( 'carousel-caption-bg', $dark_caption_bg );
				}
				if ( $dark_border_color ) {
					$css->add_variable( 'carousel-border-color', $dark_border_color );
				}
				if ( $dark_shadow && ! empty( $box_shadow['enabled'] ) ) {
					$shadow_value = CSS_Helpers::build_box_shadow( $box_shadow, $dark_shadow, false );
					$css->add_variable( 'carousel-shadow', $shadow_value );
				}
				if ( $dark_shadow_hover && ! empty( $box_shadow_hover['enabled'] ) ) {
					$shadow_value = CSS_Helpers::build_box_shadow( $box_shadow_hover, $dark_shadow_hover, false );
					$css->add_variable( 'carousel-shadow-hover', $shadow_value );
				}
			}
		);
	}
}
