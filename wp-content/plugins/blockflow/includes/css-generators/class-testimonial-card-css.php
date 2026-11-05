<?php
/**
 * Testimonial Card Block CSS Generator.
 *
 * Generates CSS for the Testimonial Card block using CSS_Helpers.
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
 * Testimonial Card CSS Generator class.
 */
class Testimonial_Card_CSS {

	/**
	 * Generate CSS for Testimonial Card block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		// Selectors.
		$selector       = '.blockflow-testimonial-card.blockflow-testimonial-card-' . $block_id;
		$title_selector = $selector . ' .blockflow-testimonial-card__title';
		$quote_selector = $selector . ' .blockflow-testimonial-card__quote';
		$name_selector  = $selector . ' .blockflow-testimonial-card__name';
		$job_selector   = $selector . ' .blockflow-testimonial-card__job';

		// Extract attributes.
		$rating_color      = $attrs['ratingColor'] ?? [];
		$rating_size       = $attrs['ratingSize'] ?? [];
		$rating_spacing    = $attrs['ratingSpacing'] ?? [];
		$title_typography  = $attrs['titleTypography'] ?? [];
		$title_color       = $attrs['titleColor'] ?? [];
		$title_spacing     = $attrs['titleSpacing'] ?? [];
		$quote_typography  = $attrs['quoteTypography'] ?? [];
		$quote_color       = $attrs['quoteColor'] ?? [];
		$quote_spacing     = $attrs['quoteSpacing'] ?? [];
		$author_image_size = $attrs['authorImageSize'] ?? [];
		$name_typography   = $attrs['nameTypography'] ?? [];
		$name_color        = $attrs['nameColor'] ?? [];
		$job_typography    = $attrs['jobTypography'] ?? [];
		$job_color         = $attrs['jobColor'] ?? [];
		$author_gap        = $attrs['authorGap'] ?? [];

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================

		// Root selector: colors (variables), sizes, advanced tab.
		$css->set_selector( $selector );

		// Rating styles.
		self::add_rating_color( $css, $rating_color );
		self::add_responsive_value( $css, $rating_size, 'desktop', 'tc-rating-size' );
		self::add_responsive_value( $css, $rating_spacing, 'desktop', 'tc-rating-spacing' );

		// Title color.
		self::add_color_var( $css, $title_color, 'tc-title-color' );
		self::add_responsive_value( $css, $title_spacing, 'desktop', 'tc-title-spacing' );

		// Quote color.
		self::add_color_var( $css, $quote_color, 'tc-quote-color' );
		self::add_responsive_value( $css, $quote_spacing, 'desktop', 'tc-quote-spacing' );

		// Author styles.
		self::add_responsive_value( $css, $author_image_size, 'desktop', 'tc-avatar-size' );
		self::add_responsive_value( $css, $author_gap, 'desktop', 'tc-author-gap' );

		// Name color.
		self::add_color_var( $css, $name_color, 'tc-name-color' );

		// Job color.
		self::add_color_var( $css, $job_color, 'tc-job-color' );

		// Advanced tab CSS.
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// Title selector: typography (direct properties).
		$css->set_selector( $title_selector );
		CSS_Helpers::add_typography_css( $css, $title_typography, 'desktop' );

		// Quote selector: typography (direct properties).
		$css->set_selector( $quote_selector );
		CSS_Helpers::add_typography_css( $css, $quote_typography, 'desktop' );

		// Name selector: typography (direct properties).
		$css->set_selector( $name_selector );
		CSS_Helpers::add_typography_css( $css, $name_typography, 'desktop' );

		// Job selector: typography (direct properties).
		$css->set_selector( $job_selector );
		CSS_Helpers::add_typography_css( $css, $job_typography, 'desktop' );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );

		// Root selector.
		$css->set_selector( $selector );
		self::add_responsive_value( $css, $rating_size, 'tablet', 'tc-rating-size' );
		self::add_responsive_value( $css, $rating_spacing, 'tablet', 'tc-rating-spacing' );
		self::add_responsive_value( $css, $title_spacing, 'tablet', 'tc-title-spacing' );
		self::add_responsive_value( $css, $quote_spacing, 'tablet', 'tc-quote-spacing' );
		self::add_responsive_value( $css, $author_image_size, 'tablet', 'tc-avatar-size' );
		self::add_responsive_value( $css, $author_gap, 'tablet', 'tc-author-gap' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		// Title selector.
		$css->set_selector( $title_selector );
		CSS_Helpers::add_typography_css( $css, $title_typography, 'tablet' );

		// Quote selector.
		$css->set_selector( $quote_selector );
		CSS_Helpers::add_typography_css( $css, $quote_typography, 'tablet' );

		// Name selector.
		$css->set_selector( $name_selector );
		CSS_Helpers::add_typography_css( $css, $name_typography, 'tablet' );

		// Job selector.
		$css->set_selector( $job_selector );
		CSS_Helpers::add_typography_css( $css, $job_typography, 'tablet' );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );

		// Root selector.
		$css->set_selector( $selector );
		self::add_responsive_value( $css, $rating_size, 'mobile', 'tc-rating-size' );
		self::add_responsive_value( $css, $rating_spacing, 'mobile', 'tc-rating-spacing' );
		self::add_responsive_value( $css, $title_spacing, 'mobile', 'tc-title-spacing' );
		self::add_responsive_value( $css, $quote_spacing, 'mobile', 'tc-quote-spacing' );
		self::add_responsive_value( $css, $author_image_size, 'mobile', 'tc-avatar-size' );
		self::add_responsive_value( $css, $author_gap, 'mobile', 'tc-author-gap' );
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		// Title selector.
		$css->set_selector( $title_selector );
		CSS_Helpers::add_typography_css( $css, $title_typography, 'mobile' );

		// Quote selector.
		$css->set_selector( $quote_selector );
		CSS_Helpers::add_typography_css( $css, $quote_typography, 'mobile' );

		// Name selector.
		$css->set_selector( $name_selector );
		CSS_Helpers::add_typography_css( $css, $name_typography, 'mobile' );

		// Job selector.
		$css->set_selector( $job_selector );
		CSS_Helpers::add_typography_css( $css, $job_typography, 'mobile' );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		self::add_dark_mode(
			$css,
			$selector,
			$rating_color,
			$title_color,
			$quote_color,
			$name_color,
			$job_color
		);

		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add rating color CSS variable.
	 *
	 * @param CSS_Builder $css   CSS generator.
	 * @param array                    $color Color attribute.
	 */
	private static function add_rating_color( $css, $color ) {
		$light = $color['light'] ?? '';

		if ( '' !== $light ) {
			$css->add_variable( 'tc-rating-color', $light );
		}
	}

	/**
	 * Add color CSS variable.
	 *
	 * @param CSS_Builder $css      CSS generator.
	 * @param array                    $color    Color attribute.
	 * @param string                   $var_name Variable name suffix.
	 */
	private static function add_color_var( $css, $color, $var_name ) {
		$light = $color['light'] ?? '';

		if ( '' !== $light ) {
			$css->add_variable( $var_name, $light );
		}
	}

	/**
	 * Add responsive value CSS variable.
	 *
	 * @param CSS_Builder $css          CSS generator.
	 * @param array                    $attr         Responsive attribute.
	 * @param string                   $device       Device key.
	 * @param string                   $var_name     Variable name suffix.
	 * @param string                   $default_unit Default unit.
	 */
	private static function add_responsive_value( $css, $attr, $device, $var_name, $default_unit = 'px' ) {
		$device_attr = $attr[ $device ] ?? [];
		$value       = $device_attr['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_attr['unit'] ?? $attr['desktop']['unit'] ?? $default_unit;

		$css->add_variable( $var_name, $value . $unit );
	}

	/**
	 * Add dark mode CSS.
	 *
	 * @param CSS_Builder $css          CSS generator.
	 * @param string                   $selector     Block selector.
	 * @param array                    $rating_color Rating color.
	 * @param array                    $title_color  Title color.
	 * @param array                    $quote_color  Quote color.
	 * @param array                    $name_color   Name color.
	 * @param array                    $job_color    Job color.
	 */
	private static function add_dark_mode( $css, $selector, $rating_color, $title_color, $quote_color, $name_color, $job_color ) {
		// Collect dark mode values.
		$dark_rating = $rating_color['dark'] ?? '';
		$dark_title  = $title_color['dark'] ?? '';
		$dark_quote  = $quote_color['dark'] ?? '';
		$dark_name   = $name_color['dark'] ?? '';
		$dark_job    = $job_color['dark'] ?? '';

		$has_dark = $dark_rating || $dark_title || $dark_quote || $dark_name || $dark_job;

		if ( ! $has_dark ) {
			return;
		}

		CSS_Helpers::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_rating, $dark_title, $dark_quote, $dark_name, $dark_job ) {
				if ( $dark_rating ) {
					$css->add_variable( 'tc-rating-color', $dark_rating );
				}
				if ( $dark_title ) {
					$css->add_variable( 'tc-title-color', $dark_title );
				}
				if ( $dark_quote ) {
					$css->add_variable( 'tc-quote-color', $dark_quote );
				}
				if ( $dark_name ) {
					$css->add_variable( 'tc-name-color', $dark_name );
				}
				if ( $dark_job ) {
					$css->add_variable( 'tc-job-color', $dark_job );
				}
			}
		);
	}
}
