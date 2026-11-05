<?php
/**
 * Image Block CSS Generator.
 *
 * Generates CSS for the Image block.
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
 * Image CSS Generator class.
 *
 * Built-in mask shapes are defined in style.scss using CSS classes.
 * This generator only handles custom masks and other CSS properties.
 */
class Image_CSS {

	/**
	 * Generate CSS for Image block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector         = '.blockflow-image.blockflow-image-' . $block_id;
		$caption_selector = $selector . ' .blockflow-image__caption';

		// Extract attributes.
		$image_width           = $attrs['imageWidth'] ?? [];
		$image_alignment       = $attrs['imageAlignment'] ?? [];
		$object_fit            = $attrs['objectFit'] ?? 'cover';
		$aspect_ratio          = $attrs['aspectRatio'] ?? [];
		$caption               = $attrs['caption'] ?? [];
		$caption_typography    = $attrs['captionTypography'] ?? [];
		$caption_color         = $attrs['captionColor'] ?? [];
		$caption_background    = $attrs['captionBackground'] ?? [];
		$caption_padding       = $attrs['captionPadding'] ?? [];
		$caption_border_radius = $attrs['captionBorderRadius'] ?? [];
		$image_mask            = $attrs['imageMask'] ?? [];
		$image_overlay         = $attrs['imageOverlay'] ?? [];
		$border_radius         = $attrs['borderRadius'] ?? [];

		// Caption display mode.
		$caption_display = $caption['display'] ?? 'none';
		$caption_enabled = 'none' !== $caption_display;

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================
		$css->set_selector( $selector );

		// Object fit.
		if ( $object_fit ) {
			$css->add_variable( 'img-object-fit', $object_fit );
		}

		// Image width and alignment (on wrapper element).
		$css->set_selector( $selector . ' .blockflow-image__wrapper' );
		self::add_image_width( $css, $image_width, 'desktop' );
		self::add_image_alignment( $css, $image_alignment, 'desktop' );

		// Aspect ratio (desktop value is in inline style, only output if non-default).
		$css->set_selector( $selector );
		self::add_aspect_ratio( $css, $aspect_ratio, 'desktop' );

		// Border radius (direct properties on inner elements).
		self::add_border_radius( $css, $selector, $border_radius, 'desktop' );

		// Mask.
		self::add_mask( $css, $image_mask );

		// Overlay (light mode).
		self::add_overlay( $css, $image_overlay );

		// Caption styles (if enabled).
		if ( $caption_enabled ) {
			// Caption padding (CSS variable on root selector).
			self::add_caption_padding( $css, $selector, $caption_padding, 'desktop' );

			// Caption border radius (CSS variable on root selector).
			self::add_caption_border_radius( $css, $selector, $caption_border_radius, 'desktop' );

			// Caption typography (direct properties on caption selector).
			$css->set_selector( $caption_selector );
			CSS_Helpers::add_typography_css( $css, $caption_typography, 'desktop' );

			// Caption alignment for below mode.
			if ( 'below' === $caption_display ) {
				$alignment = $caption['alignment'] ?? '';
				if ( $alignment ) {
					$css->add_property( 'text-align', $alignment );
				}
			}

			// Caption color (CSS variables on root selector).
			$css->set_selector( $selector );
			self::add_caption_color( $css, $caption_color, $caption_background );
		}

		// Common Advanced Tab CSS.
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );

		// Image width and alignment (on wrapper element).
		$css->set_selector( $selector . ' .blockflow-image__wrapper' );
		self::add_image_width( $css, $image_width, 'tablet' );
		self::add_image_alignment( $css, $image_alignment, 'tablet' );

		// Aspect ratio.
		$css->set_selector( $selector );
		self::add_aspect_ratio( $css, $aspect_ratio, 'tablet' );

		// Border radius (direct properties on inner elements).
		self::add_border_radius( $css, $selector, $border_radius, 'tablet' );

		// Caption styles (if enabled).
		if ( $caption_enabled ) {
			// Caption padding (CSS variable on root selector).
			self::add_caption_padding( $css, $selector, $caption_padding, 'tablet' );

			// Caption border radius (CSS variable on root selector).
			self::add_caption_border_radius( $css, $selector, $caption_border_radius, 'tablet' );

			// Caption typography (direct properties).
			$css->set_selector( $caption_selector );
			CSS_Helpers::add_typography_css( $css, $caption_typography, 'tablet' );
		}

		// Common Advanced Tab CSS.
		$css->set_selector( $selector );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );

		// Image width and alignment (on wrapper element).
		$css->set_selector( $selector . ' .blockflow-image__wrapper' );
		self::add_image_width( $css, $image_width, 'mobile' );
		self::add_image_alignment( $css, $image_alignment, 'mobile' );

		// Aspect ratio.
		$css->set_selector( $selector );
		self::add_aspect_ratio( $css, $aspect_ratio, 'mobile' );

		// Border radius (direct properties on inner elements).
		self::add_border_radius( $css, $selector, $border_radius, 'mobile' );

		// Caption styles (if enabled).
		if ( $caption_enabled ) {
			// Caption padding (CSS variable on root selector).
			self::add_caption_padding( $css, $selector, $caption_padding, 'mobile' );

			// Caption border radius (CSS variable on root selector).
			self::add_caption_border_radius( $css, $selector, $caption_border_radius, 'mobile' );

			// Caption typography (direct properties).
			$css->set_selector( $caption_selector );
			CSS_Helpers::add_typography_css( $css, $caption_typography, 'mobile' );
		}

		// Common Advanced Tab CSS.
		$css->set_selector( $selector );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		self::add_overlay_dark_mode( $css, $selector, $image_overlay );

		// Caption dark mode.
		if ( $caption_enabled ) {
			self::add_caption_dark_mode( $css, $selector, $caption_color, $caption_background );
		}

		// Common Advanced Tab dark mode.
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add aspect ratio CSS variable.
	 *
	 * Desktop value is set via inline style on the block element.
	 * This method handles responsive overrides for tablet/mobile.
	 *
	 * @param CSS_Builder $css          CSS generator.
	 * @param array                    $aspect_ratio Aspect ratio attribute.
	 * @param string                   $device       Device key.
	 */
	private static function add_aspect_ratio( $css, $aspect_ratio, $device ) {
		$enabled = $aspect_ratio['enabled'] ?? false;

		if ( ! $enabled ) {
			return;
		}

		$device_data = $aspect_ratio[ $device ] ?? [];
		$value       = $device_data['value'] ?? '';

		// Skip if no value for this device.
		if ( '' === $value ) {
			return;
		}

		$css->add_variable( 'img-ratio', $value . '%' );
	}

	/**
	 * Add border radius as CSS variable (shared with lightbox).
	 *
	 * Uses the shared --blockflow-img-radius variable that both
	 * the block and lightbox can reference.
	 *
	 * @param CSS_Builder $css      CSS generator.
	 * @param string                   $selector Block selector.
	 * @param array                    $radius   Border radius attribute.
	 * @param string                   $device   Device key.
	 */
	private static function add_border_radius( $css, $selector, $radius, $device ) {
		$device_radius = $radius[ $device ] ?? [];

		if ( empty( $device_radius ) ) {
			return;
		}

		// Build radius shorthand value.
		$radius_value = CSS_Helpers::build_radius_shorthand( $device_radius );

		if ( ! $radius_value ) {
			return;
		}

		// Set selector to root block element (CSS variable on root).
		$css->set_selector( $selector );

		// Output as CSS variable (shared with lightbox).
		$css->add_variable( 'img-radius', $radius_value );
	}

	/**
	 * Add mask CSS variables.
	 *
	 * Built-in shapes use CSS classes (e.g., .blockflow-image--mask-blob1).
	 * Only custom masks need the img-mask-image variable.
	 *
	 * @param CSS_Builder $css  CSS generator.
	 * @param array                    $mask Mask attribute.
	 */
	private static function add_mask( $css, $mask ) {
		$shape = $mask['shape'] ?? 'none';

		if ( 'none' === $shape ) {
			return;
		}

		// Only output mask-image variable for custom masks.
		// Built-in shapes use CSS classes defined in style.scss.
		if ( 'custom' === $shape ) {
			$custom_url = $mask['customImage']['url'] ?? '';
			if ( $custom_url ) {
				$css->add_variable( 'img-mask-image', 'url(' . esc_url( $custom_url ) . ')' );
			}
		}

		// Mask size.
		$size = $mask['size'] ?? 'contain';
		$css->add_variable( 'img-mask-size', $size );

		// Mask position.
		$position = $mask['position'] ?? 'center center';
		$css->add_variable( 'img-mask-position', $position );

		// Mask repeat.
		$repeat = $mask['repeat'] ?? 'no-repeat';
		$css->add_variable( 'img-mask-repeat', $repeat );
	}

	/**
	 * Add overlay CSS variables (light mode).
	 *
	 * @param CSS_Builder $css     CSS generator.
	 * @param array                    $overlay Overlay attribute.
	 */
	private static function add_overlay( $css, $overlay ) {
		$gradient = $overlay['gradient']['light'] ?? '';

		if ( ! $gradient ) {
			return;
		}

		$css->add_variable( 'img-overlay-gradient', $gradient );

		$opacity = $overlay['opacity'] ?? '';
		if ( '' !== $opacity ) {
			$css->add_variable( 'img-overlay-opacity', $opacity );
		}
	}

	/**
	 * Add overlay dark mode CSS.
	 *
	 * @param CSS_Builder $css      CSS generator.
	 * @param string                   $selector Block selector.
	 * @param array                    $overlay  Overlay attribute.
	 */
	private static function add_overlay_dark_mode( $css, $selector, $overlay ) {
		$dark_gradient = $overlay['gradient']['dark'] ?? '';

		if ( ! $dark_gradient ) {
			return;
		}

		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_gradient ) {
				$css->add_variable( 'img-overlay-gradient', $dark_gradient );
			}
		);
	}

	/**
	 * Add caption padding CSS variable (on root selector.
	 *
	 * @param CSS_Builder $css      CSS generator.
	 * @param string                   $selector Block selector.
	 * @param array                    $padding  Caption padding attribute.
	 * @param string                   $device   Device key.
	 */
	private static function add_caption_padding( $css, $selector, $padding, $device ) {
		$device_padding = $padding[ $device ] ?? [];

		if ( empty( $device_padding ) ) {
			return;
		}

		$css->set_selector( $selector );
		CSS_Helpers::add_padding_var( $css, $device_padding, 'image-caption' );
	}

	/**
	 * Add caption border radius CSS variable (on root selector like carousel-images).
	 *
	 * @param CSS_Builder $css      CSS generator.
	 * @param string                   $selector Block selector.
	 * @param array                    $radius   Caption border radius attribute.
	 * @param string                   $device   Device key.
	 */
	private static function add_caption_border_radius( $css, $selector, $radius, $device ) {
		$device_radius = $radius[ $device ] ?? [];

		if ( empty( $device_radius ) ) {
			return;
		}

		$radius_value = CSS_Helpers::build_radius_shorthand( $device_radius );
		if ( $radius_value ) {
			$css->set_selector( $selector );
			$css->add_variable( 'image-caption-radius', $radius_value );
		}
	}

	/**
	 * Add caption color CSS variables (light mode).
	 *
	 * @param CSS_Builder $css        CSS generator.
	 * @param array                    $color      Caption color attribute.
	 * @param array                    $background Caption background attribute.
	 */
	private static function add_caption_color( $css, $color, $background ) {
		// Text color.
		$text_color = $color['light'] ?? '';
		if ( $text_color ) {
			$css->add_variable( 'image-caption-color', $text_color );
		}

		// Background color.
		$bg_color = $background['light'] ?? '';
		if ( $bg_color ) {
			$css->add_variable( 'image-caption-bg', $bg_color );
		}
	}

	/**
	 * Add caption dark mode CSS.
	 *
	 * @param CSS_Builder $css        CSS generator.
	 * @param string                   $selector   Block selector.
	 * @param array                    $color      Caption color attribute.
	 * @param array                    $background Caption background attribute.
	 */
	private static function add_caption_dark_mode( $css, $selector, $color, $background ) {
		$dark_color = $color['dark'] ?? '';
		$dark_bg    = $background['dark'] ?? '';

		if ( ! $dark_color && ! $dark_bg ) {
			return;
		}

		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_color, $dark_bg ) {
				if ( $dark_color ) {
					$css->add_variable( 'image-caption-color', $dark_color );
				}
				if ( $dark_bg ) {
					$css->add_variable( 'image-caption-bg', $dark_bg );
				}
			}
		);
	}

	/**
	 * Add image width CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $width  Image width attribute.
	 * @param string                   $device Device key.
	 */
	private static function add_image_width( $css, $width, $device ) {
		$device_width = $width[ $device ] ?? [];

		if ( empty( $device_width ) ) {
			return;
		}

		$value = $device_width['value'] ?? '';
		$unit  = $device_width['unit'] ?? '%';

		if ( '' === $value ) {
			return;
		}

		$css->add_variable( 'img-w', $value . $unit );
	}

	/**
	 * Add image alignment CSS variables.
	 *
	 * @param CSS_Builder $css       CSS generator.
	 * @param array                    $alignment Image alignment attribute.
	 * @param string                   $device    Device key.
	 */
	private static function add_image_alignment( $css, $alignment, $device ) {
		$value = $alignment[ $device ] ?? '';

		if ( '' === $value ) {
			return;
		}

		switch ( $value ) {
			case 'left':
				$css->add_variable( 'img-ml', '0' );
				$css->add_variable( 'img-mr', 'auto' );
				break;
			case 'center':
				$css->add_variable( 'img-ml', 'auto' );
				$css->add_variable( 'img-mr', 'auto' );
				break;
			case 'right':
				$css->add_variable( 'img-ml', 'auto' );
				$css->add_variable( 'img-mr', '0' );
				break;
		}
	}
}
