<?php
/**
 * Progress Bar Block CSS Generator.
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
 * Progress Bar CSS Generator class.
 */
class Progress_Bar_CSS {

	/**
	 * Generate CSS for the progress bar block.
	 *
	 * @param array                               $attrs Block attributes.
	 * @param \ThemeRuby\BlockFlow\CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';
		if ( ! $block_id ) {
			return;
		}

		$selector         = '.blockflow-progress-bar.blockflow-progress-bar-' . $block_id;
		$title_selector   = $selector . ' .blockflow-progress-bar__title';
		$counter_selector = $selector . ' .blockflow-progress-bar__counter';

		// Extract attributes.
		$title_typography   = $attrs['titleTypography'] ?? [];
		$counter_typography = $attrs['counterTypography'] ?? [];
		$border_radius      = $attrs['borderRadius'] ?? [];

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================
		$css->set_selector( $selector );
		self::add_progress_styles( $css, $attrs, 'desktop' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// Border radius (direct properties on inner elements).
		self::add_border_radius( $css, $selector, $border_radius, 'desktop' );

		// Title typography.
		$css->set_selector( $title_selector );
		CSS_Helpers::add_typography_css( $css, $title_typography, 'desktop' );

		// Counter typography.
		$css->set_selector( $counter_selector );
		CSS_Helpers::add_typography_css( $css, $counter_typography, 'desktop' );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );

		$css->set_selector( $selector );
		self::add_progress_styles( $css, $attrs, 'tablet' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		// Border radius (direct properties on inner elements).
		self::add_border_radius( $css, $selector, $border_radius, 'tablet' );

		// Title typography.
		$css->set_selector( $title_selector );
		CSS_Helpers::add_typography_css( $css, $title_typography, 'tablet' );

		// Counter typography.
		$css->set_selector( $counter_selector );
		CSS_Helpers::add_typography_css( $css, $counter_typography, 'tablet' );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );

		$css->set_selector( $selector );
		self::add_progress_styles( $css, $attrs, 'mobile' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		// Border radius (direct properties on inner elements).
		self::add_border_radius( $css, $selector, $border_radius, 'mobile' );

		// Title typography.
		$css->set_selector( $title_selector );
		CSS_Helpers::add_typography_css( $css, $title_typography, 'mobile' );

		// Counter typography.
		$css->set_selector( $counter_selector );
		CSS_Helpers::add_typography_css( $css, $counter_typography, 'mobile' );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		self::add_dark_mode( $css, $selector, $attrs );
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add progress bar specific styles.
	 *
	 * @param \ThemeRuby\BlockFlow\CSS_Builder $css    CSS generator instance.
	 * @param array                          $attrs  Block attributes.
	 * @param string                         $device Device type.
	 */
	private static function add_progress_styles( $css, $attrs, $device ) {
		$is_desktop       = 'desktop' === $device;
		$layout_type      = $attrs['layoutType'] ?? 'line';
		$is_circle_layout = in_array( $layout_type, [ 'semi-circle', 'circle' ], true );

		// Calculate percentage.
		$counter_value = $attrs['counterValue'] ?? 75;
		$total_range   = $attrs['totalRange'] ?? 100;
		$percentage    = 0;
		if ( $total_range > 0 ) {
			$percentage = min( 100, max( 0, ( $counter_value / $total_range ) * 100 ) );
		}

		// Height (for line layout).
		$height      = $attrs['height'] ?? [];
		$height_val  = $height[ $device ]['value'] ?? '';
		$height_unit = $height[ $device ]['unit'] ?? $height['desktop']['unit'] ?? 'px';

		if ( $is_desktop ) {
			// Always output percentage.
			$css->add_variable( 'progress-value', $percentage . '%' );

			// Output height if value is set.
			if ( '' !== $height_val ) {
				$css->add_variable( 'progress-h', $height_val . $height_unit );
			}
		} elseif ( '' !== $height_val && null !== $height_val ) {
			$css->add_variable( 'progress-h', $height_val . $height_unit );
		}

		// Box size (for box layout - separate width/height).
		$box_size   = $attrs['boxSize'] ?? [];
		$box_width  = $box_size[ $device ]['width'] ?? '';
		$box_height = $box_size[ $device ]['height'] ?? '';
		$box_unit   = $box_size[ $device ]['unit'] ?? $box_size['desktop']['unit'] ?? 'px';

		if ( $is_desktop ) {
			// Output width if value is set.
			if ( '' !== $box_width ) {
				$css->add_variable( 'progress-box-w', $box_width . $box_unit );
			}
			// Output height if value is set.
			if ( '' !== $box_height ) {
				$css->add_variable( 'progress-box-h', $box_height . $box_unit );
			}
		} else {
			if ( '' !== $box_width && null !== $box_width ) {
				$css->add_variable( 'progress-box-w', $box_width . $box_unit );
			}
			if ( '' !== $box_height && null !== $box_height ) {
				$css->add_variable( 'progress-box-h', $box_height . $box_unit );
			}
		}

		// Circle size (for semi-circle and circle layouts).
		$size      = $attrs['size'] ?? [];
		$size_val  = $size[ $device ]['value'] ?? '';
		$size_unit = $size[ $device ]['unit'] ?? $size['desktop']['unit'] ?? 'px';

		if ( $is_desktop ) {
			// Output size if value is set.
			if ( '' !== $size_val ) {
				$css->add_variable( 'progress-size', $size_val . $size_unit );
			}
		} elseif ( '' !== $size_val && null !== $size_val ) {
			$css->add_variable( 'progress-size', $size_val . $size_unit );
		}

		// Stroke width (for circle layouts - SVG uses unitless values).
		$stroke     = $attrs['strokeWidth'] ?? [];
		$stroke_val = $stroke[ $device ]['value'] ?? '';

		if ( $is_desktop ) {
			// Output stroke width if value is set.
			if ( '' !== $stroke_val ) {
				$css->add_variable( 'progress-stroke', $stroke_val );
			}
		} elseif ( '' !== $stroke_val && null !== $stroke_val ) {
			$css->add_variable( 'progress-stroke', $stroke_val );
		}

		// Content spacing (for all layouts).
		$content_spacing      = $attrs['contentSpacing'] ?? [];
		$content_spacing_val  = $content_spacing[ $device ]['value'] ?? '';
		$content_spacing_unit = $content_spacing[ $device ]['unit'] ?? $content_spacing['desktop']['unit'] ?? 'px';

		if ( $is_desktop ) {
			// Output spacing if value is set.
			if ( '' !== $content_spacing_val ) {
				$css->add_variable( 'progress-spacing', $content_spacing_val . $content_spacing_unit );
			}
		} elseif ( '' !== $content_spacing_val && null !== $content_spacing_val ) {
			$css->add_variable( 'progress-spacing', $content_spacing_val . $content_spacing_unit );
		}

		// Bar background (desktop only, non-responsive).
		if ( $is_desktop ) {
			$bar_bg       = $attrs['barBackground'] ?? [];
			$bar_bg_light = $bar_bg['light'] ?? '';
			if ( $bar_bg_light ) {
				$css->add_variable( 'progress-bar-bg', $bar_bg_light );
			}

			// Track background.
			$track_bg       = $attrs['trackBackground'] ?? [];
			$track_bg_light = $track_bg['light'] ?? '';
			if ( $track_bg_light ) {
				$css->add_variable( 'progress-track-bg', $track_bg_light );
			}

			// Title color.
			$title_color = $attrs['titleColor']['light'] ?? '';
			if ( $title_color ) {
				$css->add_variable( 'progress-title-color', $title_color );
			}

			// Counter color.
			$counter_color = $attrs['counterColor']['light'] ?? '';
			if ( $counter_color ) {
				$css->add_variable( 'progress-counter-color', $counter_color );
			}

			// Animation duration.
			$progress_animation = $attrs['progressAnimation'] ?? [];
			$duration           = $progress_animation['duration'] ?? '';
			if ( '' !== $duration && null !== $duration ) {
				$css->add_variable( 'progress-duration', $duration . 'ms' );
			}
		}
	}

	/**
	 * Add border radius direct properties to inner elements.
	 *
	 * Uses combined selector for track, fill, and box-wrapper elements.
	 *
	 * @param \ThemeRuby\BlockFlow\CSS_Builder $css      CSS generator instance.
	 * @param string                               $selector Block selector.
	 * @param array                                $radius   Border radius attribute.
	 * @param string                               $device   Device key.
	 */
	private static function add_border_radius( $css, $selector, $radius, $device ) {
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

		// Combined selector for all elements needing border-radius.
		$combined_selector = $selector . ' .blockflow-progress-bar__track, '
			. $selector . ' .blockflow-progress-bar__fill, '
			. $selector . ' .blockflow-progress-bar__box-wrapper';
		$css->set_selector( $combined_selector );

		// Use centralized helper for direct properties.
		CSS_Helpers::add_border_radius( $css, $device_radius );
	}

	/**
	 * Add dark mode styles.
	 *
	 * @param \ThemeRuby\BlockFlow\CSS_Builder $css      CSS generator instance.
	 * @param string                         $selector Block selector.
	 * @param array                          $attrs    Block attributes.
	 */
	private static function add_dark_mode( $css, $selector, $attrs ) {
		$title_color   = $attrs['titleColor'] ?? [];
		$counter_color = $attrs['counterColor'] ?? [];
		$bar_bg        = $attrs['barBackground'] ?? [];
		$track_bg      = $attrs['trackBackground'] ?? [];

		$dark_title    = $title_color['dark'] ?? '';
		$dark_counter  = $counter_color['dark'] ?? '';
		$dark_bar_bg   = $bar_bg['dark'] ?? '';
		$dark_track_bg = $track_bg['dark'] ?? '';

		$has_dark = $dark_title || $dark_counter || $dark_bar_bg || $dark_track_bg;

		if ( ! $has_dark ) {
			return;
		}

		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_title, $dark_counter, $dark_bar_bg, $dark_track_bg ) {
				if ( $dark_title ) {
					$css->add_variable( 'progress-title-color', $dark_title );
				}
				if ( $dark_counter ) {
					$css->add_variable( 'progress-counter-color', $dark_counter );
				}
				if ( $dark_bar_bg ) {
					$css->add_variable( 'progress-bar-bg', $dark_bar_bg );
				}
				if ( $dark_track_bg ) {
					$css->add_variable( 'progress-track-bg', $dark_track_bg );
				}
			}
		);
	}
}
