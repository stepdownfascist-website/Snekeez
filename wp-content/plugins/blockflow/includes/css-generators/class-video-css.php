<?php
/**
 * Video Block CSS Generator.
 *
 * Generates CSS for the Video block using CSS_Helpers.
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
 * Video CSS Generator class.
 */
class Video_CSS {

	/**
	 * Generate CSS for Video block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector = '.blockflow-video.blockflow-video-' . $block_id;

		// Extract video-specific attributes.
		$aspect_ratio          = $attrs['aspectRatio'] ?? '16-9';
		$custom_aspect_ratio   = $attrs['customAspectRatio'] ?? [];
		$overlay               = $attrs['overlay'] ?? [];
		$play_icon             = $attrs['playIcon'] ?? [];
		$play_icon_size        = $attrs['playIconSize'] ?? [];
		$play_icon_color       = $attrs['playIconColor'] ?? [];
		$play_icon_color_hover = $attrs['playIconColorHover'] ?? [];
		$play_icon_bg          = $attrs['playIconBg'] ?? [];
		$play_icon_bg_hover    = $attrs['playIconBgHover'] ?? [];
		$play_icon_padding     = $attrs['playIconPadding'] ?? [];
		$play_icon_radius      = $attrs['playIconBorderRadius'] ?? [];
		$border_radius         = $attrs['borderRadius'] ?? [];

		$overlay_enabled   = $overlay['enabled'] ?? false;
		$play_icon_enabled = $play_icon['enabled'] ?? true;

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================
		$css->set_selector( $selector );

		// Aspect ratio.
		self::add_aspect_ratio( $css, $aspect_ratio, $custom_aspect_ratio );

		// Video wrapper border radius (direct properties on inner element).
		self::add_video_radius( $css, $selector, $border_radius, 'desktop' );

		// Play icon styles (only if overlay and play icon enabled).
		if ( $overlay_enabled && $play_icon_enabled ) {
			$css->set_selector( $selector );
			self::add_play_icon_size( $css, $play_icon_size, 'desktop' );
			CSS_Helpers::add_padding_var( $css, $play_icon_padding['desktop'] ?? [], 'video-play' );
			self::add_play_icon_radius( $css, $selector, $play_icon_radius, 'desktop' );
			self::add_play_icon_colors( $css, $play_icon_color, $play_icon_color_hover, $play_icon_bg, $play_icon_bg_hover );
		}

		// Common Advanced Tab CSS (spacing, background, border, shadow, advanced, animation).
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );
		$css->set_selector( $selector );

		// Video wrapper border radius (direct properties on inner element).
		self::add_video_radius( $css, $selector, $border_radius, 'tablet' );

		// Play icon styles.
		if ( $overlay_enabled && $play_icon_enabled ) {
			$css->set_selector( $selector );
			self::add_play_icon_size( $css, $play_icon_size, 'tablet' );
			CSS_Helpers::add_padding_var( $css, $play_icon_padding['tablet'] ?? [], 'video-play' );
			self::add_play_icon_radius( $css, $selector, $play_icon_radius, 'tablet' );
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

		// Video wrapper border radius (direct properties on inner element).
		self::add_video_radius( $css, $selector, $border_radius, 'mobile' );

		// Play icon styles.
		if ( $overlay_enabled && $play_icon_enabled ) {
			$css->set_selector( $selector );
			self::add_play_icon_size( $css, $play_icon_size, 'mobile' );
			CSS_Helpers::add_padding_var( $css, $play_icon_padding['mobile'] ?? [], 'video-play' );
			self::add_play_icon_radius( $css, $selector, $play_icon_radius, 'mobile' );
		}

		// Common Advanced Tab CSS (responsive).
		$css->set_selector( $selector );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		if ( $overlay_enabled && $play_icon_enabled ) {
			self::add_play_icon_dark_mode( $css, $selector, $play_icon_color, $play_icon_color_hover, $play_icon_bg, $play_icon_bg_hover );
		}

		// Common Advanced Tab dark mode (background, border colors).
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES (border + shadow)
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add aspect ratio CSS variable.
	 *
	 * @param CSS_Builder $css                 CSS generator.
	 * @param string                   $aspect_ratio        Aspect ratio preset.
	 * @param array                    $custom_aspect_ratio Custom aspect ratio values.
	 */
	private static function add_aspect_ratio( $css, $aspect_ratio, $custom_aspect_ratio ) {
		$ratio_value = '';

		if ( 'custom' === $aspect_ratio ) {
			$width  = $custom_aspect_ratio['width'] ?? 16;
			$height = $custom_aspect_ratio['height'] ?? 9;
			if ( $width && $height ) {
				$ratio_value = $width . ' / ' . $height;
			}
		} else {
			// Convert preset to CSS value (e.g., '16-9' -> '16 / 9').
			$presets     = [
				'16-9' => '16 / 9',
				'4-3'  => '4 / 3',
				'1-1'  => '1 / 1',
				'21-9' => '21 / 9',
				'9-16' => '9 / 16',
			];
			$ratio_value = $presets[ $aspect_ratio ] ?? '16 / 9';
		}

		// Output aspect ratio if value is set.
		if ( $ratio_value ) {
			$css->add_variable( 'video-aspect-ratio', $ratio_value );
		}
	}

	/**
	 * Add video wrapper border radius using direct CSS properties.
	 *
	 * @param CSS_Builder $css      CSS generator.
	 * @param string                   $selector Block selector.
	 * @param array                    $radius   Border radius attribute.
	 * @param string                   $device   Device key.
	 */
	private static function add_video_radius( $css, $selector, $radius, $device ) {
		$device_radius = $radius[ $device ] ?? [];

		if ( empty( $device_radius ) ) {
			return;
		}

		// Check if any corner has value.
		$has_any = false;
		foreach ( [ 'topLeft', 'topRight', 'bottomRight', 'bottomLeft' ] as $key ) {
			if ( '' !== ( $device_radius[ $key ] ?? '' ) ) {
				$has_any = true;
				break;
			}
		}

		if ( ! $has_any ) {
			return;
		}

		// Set selector for wrapper element.
		$css->set_selector( $selector . ' .blockflow-video__wrapper' );

		// Use centralized helper for direct properties.
		CSS_Helpers::add_border_radius( $css, $device_radius );
	}

	/**
	 * Add play icon size CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array               $size   Play icon size attribute.
	 * @param string              $device Device key.
	 */
	private static function add_play_icon_size( $css, $size, $device ) {
		$device_size = $size[ $device ] ?? [];
		$value       = $device_size['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_size['unit'] ?? 'px';

		$css->add_variable( 'video-play-size', $value . $unit );
	}

	/**
	 * Add play icon border radius using direct CSS properties.
	 *
	 * @param CSS_Builder $css      CSS generator.
	 * @param string                   $selector Block selector.
	 * @param array                    $radius   Play icon border radius attribute.
	 * @param string                   $device   Device key.
	 */
	private static function add_play_icon_radius( $css, $selector, $radius, $device ) {
		$device_radius = $radius[ $device ] ?? [];

		if ( empty( $device_radius ) ) {
			return;
		}

		// Check if any corner has value.
		$has_any = false;

		foreach ( [ 'topLeft', 'topRight', 'bottomRight', 'bottomLeft' ] as $key ) {
			$value = $device_radius[ $key ] ?? '';
			if ( '' !== $value ) {
				$has_any = true;
				break;
			}
		}

		if ( ! $has_any ) {
			return;
		}

		// Set selector for play button element.
		$css->set_selector( $selector . ' .blockflow-video__play-btn' );

		// Use centralized helper for direct properties.
		CSS_Helpers::add_border_radius( $css, $device_radius );
	}

	/**
	 * Add play icon color CSS variables (light mode).
	 *
	 * @param CSS_Builder $css         CSS generator.
	 * @param array                    $color       Play icon color.
	 * @param array                    $color_hover Play icon color hover.
	 * @param array                    $bg          Play icon background.
	 * @param array                    $bg_hover    Play icon background hover.
	 */
	private static function add_play_icon_colors( $css, $color, $color_hover, $bg, $bg_hover ) {
		// Icon color.
		if ( ! empty( $color['light'] ) ) {
			$css->add_variable( 'video-play-color', $color['light'] );
		}

		// Icon color hover.
		if ( ! empty( $color_hover['light'] ) ) {
			$css->add_variable( 'video-play-color-hover', $color_hover['light'] );
		}

		// Background.
		if ( ! empty( $bg['light'] ) ) {
			$css->add_variable( 'video-play-bg', $bg['light'] );
		}

		// Background hover.
		if ( ! empty( $bg_hover['light'] ) ) {
			$css->add_variable( 'video-play-bg-hover', $bg_hover['light'] );
		}
	}

	/**
	 * Add play icon dark mode CSS.
	 *
	 * @param CSS_Builder $css         CSS generator.
	 * @param string                   $selector    Block selector.
	 * @param array                    $color       Play icon color.
	 * @param array                    $color_hover Play icon color hover.
	 * @param array                    $bg          Play icon background.
	 * @param array                    $bg_hover    Play icon background hover.
	 */
	private static function add_play_icon_dark_mode( $css, $selector, $color, $color_hover, $bg, $bg_hover ) {
		$dark_color       = $color['dark'] ?? '';
		$dark_color_hover = $color_hover['dark'] ?? '';
		$dark_bg          = $bg['dark'] ?? '';
		$dark_bg_hover    = $bg_hover['dark'] ?? '';

		$has_dark_styles = $dark_color || $dark_color_hover || $dark_bg || $dark_bg_hover;

		if ( ! $has_dark_styles ) {
			return;
		}

		// Use helper to handle dark mode method.
		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_color, $dark_color_hover, $dark_bg, $dark_bg_hover ) {
				if ( $dark_color ) {
					$css->add_variable( 'video-play-color', $dark_color );
				}
				if ( $dark_color_hover ) {
					$css->add_variable( 'video-play-color-hover', $dark_color_hover );
				}
				if ( $dark_bg ) {
					$css->add_variable( 'video-play-bg', $dark_bg );
				}
				if ( $dark_bg_hover ) {
					$css->add_variable( 'video-play-bg-hover', $dark_bg_hover );
				}
			}
		);
	}
}
