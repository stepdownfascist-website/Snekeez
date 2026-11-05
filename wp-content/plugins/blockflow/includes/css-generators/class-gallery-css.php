<?php
/**
 * Gallery Block CSS Generator.
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
 * Gallery CSS Generator class.
 */
class Gallery_CSS {

	/**
	 * Generate CSS for gallery block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';
		if ( ! $block_id ) {
			return;
		}

		$selector                  = '.blockflow-gallery.blockflow-gallery-' . $block_id;
		$caption_selector          = $selector . ' .blockflow-gallery__caption';
		$lightbox_caption_selector = '.blockflow-lightbox--' . $block_id . ' .blockflow-lightbox__caption';

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================
		$css->set_selector( $selector );
		self::add_layout_styles( $css, $attrs, 'desktop' );
		self::add_image_styles( $css, $attrs, 'desktop' );
		self::add_overlay_styles( $css, $attrs );
		self::add_caption_styles( $css, $selector, $caption_selector, $lightbox_caption_selector, $attrs, 'desktop' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );
		$css->set_selector( $selector );
		self::add_layout_styles( $css, $attrs, 'tablet' );
		self::add_image_styles( $css, $attrs, 'tablet' );
		self::add_caption_styles( $css, $selector, $caption_selector, $lightbox_caption_selector, $attrs, 'tablet' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );
		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );
		$css->set_selector( $selector );
		self::add_layout_styles( $css, $attrs, 'mobile' );
		self::add_image_styles( $css, $attrs, 'mobile' );
		self::add_caption_styles( $css, $selector, $caption_selector, $lightbox_caption_selector, $attrs, 'mobile' );
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
	 * Add layout styles (columns, gap, aspect ratio).
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $attrs  Attributes.
	 * @param string                   $device Device (desktop/tablet/mobile).
	 */
	private static function add_layout_styles( $css, $attrs, $device ) {
		$columns      = $attrs['columns'] ?? [];
		$gap          = $attrs['gap'] ?? [];
		$aspect_ratio = $attrs['aspectRatio'] ?? [];
		$is_desktop   = 'desktop' === $device;

		// Columns.
		$columns_value = $columns[ $device ]['value'] ?? '';
		if ( '' !== $columns_value ) {
			$css->add_variable( 'columns', $columns_value );
		}

		// Gap.
		$gap_value = $gap[ $device ]['value'] ?? '';
		if ( '' !== $gap_value ) {
			$gap_unit = $gap[ $device ]['unit'] ?? $gap['desktop']['unit'] ?? 'px';
			$css->add_variable( 'gap', $gap_value . $gap_unit );
		}

		// Aspect ratio (grid only).
		if ( ! empty( $aspect_ratio['enabled'] ) ) {
			$ratio_value = $aspect_ratio[ $device ]['value'] ?? '';
			if ( '' !== $ratio_value ) {
				$css->add_variable( 'gallery-ratio', $ratio_value . '%' );
			}
		}
	}

	/**
	 * Add image styles (border, box shadow).
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $attrs  Attributes.
	 * @param string                   $device Device (desktop/tablet/mobile).
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
		CSS_Helpers::add_image_border_vars( $css, $border, 'gallery' );

		// Box shadow (desktop only - non-responsive).
		if ( $is_desktop ) {
			$box_shadow   = $attrs['imageBoxShadow'] ?? [];
			$shadow_value = CSS_Helpers::build_box_shadow( $box_shadow );
			if ( $shadow_value ) {
				$css->add_variable( 'gallery-shadow', $shadow_value );
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
			$selector . ' .blockflow-gallery__media:hover',
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
			$attrs['overlayOpacity'] ?? 0,
			$attrs['overlayHoverOpacity'] ?? 0,
			'gallery'
		);
	}

	/**
	 * Add caption styles.
	 *
	 * @param CSS_Builder $css                       CSS generator.
	 * @param string                   $selector                  Block selector.
	 * @param string                   $caption_selector          Caption element selector.
	 * @param string                   $lightbox_caption_selector Lightbox caption selector.
	 * @param array                    $attrs                     Attributes.
	 * @param string                   $device                    Device (desktop/tablet/mobile).
	 */
	private static function add_caption_styles( $css, $selector, $caption_selector, $lightbox_caption_selector, $attrs, $device ) {
		$caption               = $attrs['caption'] ?? [];
		$caption_typography    = $attrs['captionTypography'] ?? [];
		$caption_color         = $attrs['captionColor'] ?? [];
		$caption_background    = $attrs['captionBackground'] ?? [];
		$caption_padding       = $attrs['captionPadding'] ?? [];
		$caption_border_radius = $attrs['captionBorderRadius'] ?? [];
		$is_desktop            = 'desktop' === $device;

		// Only output caption styles if enabled (display !== 'none').
		$caption_display = $caption['display'] ?? 'none';
		if ( 'none' === $caption_display ) {
			return;
		}

		// Caption color and background (desktop only, non-responsive).
		if ( $is_desktop ) {
			$css->set_selector( $selector );

			$light_color = $caption_color['light'] ?? '';
			if ( $light_color ) {
				$css->add_variable( 'gallery-caption-color', $light_color );
			}

			$light_bg = $caption_background['light'] ?? '';
			if ( $light_bg ) {
				$css->add_variable( 'gallery-caption-bg', $light_bg );
			}
		}

		// Caption typography (gallery caption and lightbox caption).
		$typography = $caption_typography[ $device ] ?? [];
		if ( ! empty( $typography ) || $is_desktop ) {
			$css->set_selector( $caption_selector . ', ' . $lightbox_caption_selector );
			CSS_Helpers::add_typography_css( $css, $caption_typography, $device );
		}

		// Caption padding.
		$padding = $caption_padding[ $device ] ?? [];
		if ( ! empty( $padding ) ) {
			$css->set_selector( $selector );
			CSS_Helpers::add_padding_var( $css, $padding, 'gallery-caption' );
		}

		// Caption border radius.
		$border_radius = $caption_border_radius[ $device ] ?? [];
		$radius_value  = CSS_Helpers::build_radius_shorthand( $border_radius );
		if ( $radius_value ) {
			$css->set_selector( $selector );
			$css->add_variable( 'gallery-caption-radius', $radius_value );
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
		$overlay_color      = $attrs['overlayColor'] ?? [];
		$caption_color      = $attrs['captionColor'] ?? [];
		$caption_background = $attrs['captionBackground'] ?? [];
		$image_border       = $attrs['imageBorder'] ?? [];
		$border_color       = $image_border['desktop']['color'] ?? [];
		$box_shadow         = $attrs['imageBoxShadow'] ?? [];
		$box_shadow_hover   = $attrs['imageBoxShadowHover'] ?? [];

		$dark_overlay      = $overlay_color['dark'] ?? '';
		$dark_caption      = $caption_color['dark'] ?? '';
		$dark_caption_bg   = $caption_background['dark'] ?? '';
		$dark_border_color = $border_color['dark'] ?? '';
		$dark_shadow       = $box_shadow['color']['dark'] ?? '';
		$dark_shadow_hover = $box_shadow_hover['color']['dark'] ?? '';

		$has_dark = $dark_overlay || $dark_caption || $dark_caption_bg || $dark_border_color || $dark_shadow || $dark_shadow_hover;
		if ( ! $has_dark ) {
			return;
		}

		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_overlay, $dark_caption, $dark_caption_bg, $dark_border_color, $dark_shadow, $dark_shadow_hover, $box_shadow, $box_shadow_hover ) {
				if ( $dark_overlay ) {
					$css->add_variable( 'gallery-overlay-bg', $dark_overlay );
				}
				if ( $dark_caption ) {
					$css->add_variable( 'gallery-caption-color', $dark_caption );
				}
				if ( $dark_caption_bg ) {
					$css->add_variable( 'gallery-caption-bg', $dark_caption_bg );
				}
				if ( $dark_border_color ) {
					$css->add_variable( 'gallery-border-color', $dark_border_color );
				}
				if ( $dark_shadow && ! empty( $box_shadow['enabled'] ) ) {
					$shadow_value = CSS_Helpers::build_box_shadow( $box_shadow, $dark_shadow, false );
					$css->add_variable( 'gallery-shadow', $shadow_value );
				}
				if ( $dark_shadow_hover && ! empty( $box_shadow_hover['enabled'] ) ) {
					$shadow_value = CSS_Helpers::build_box_shadow( $box_shadow_hover, $dark_shadow_hover, false );
					$css->add_variable( 'gallery-shadow-hover', $shadow_value );
				}
			}
		);
	}
}
