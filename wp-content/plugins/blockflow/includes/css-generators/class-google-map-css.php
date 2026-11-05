<?php
/**
 * Google Map Block CSS Generator.
 *
 * Generates CSS for the Google Map block using CSS_Helpers.
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
 * Google Map CSS Generator class.
 */
class Google_Map_CSS {

	/**
	 * Generate CSS for Google Map block.
	 *
	 * @param array                    $attrs Block attributes.
	 * @param CSS_Builder $css   CSS generator instance.
	 */
	public static function generate( $attrs, $css ) {
		$block_id = $attrs['blockId'] ?? '';

		if ( empty( $block_id ) ) {
			return;
		}

		$selector = '.blockflow-google-map.blockflow-google-map-' . $block_id;

		// Extract map-specific attributes.
		$height = $attrs['height'] ?? [];

		// =====================================================================
		// DESKTOP STYLES (base)
		// =====================================================================
		$css->set_selector( $selector );

		// Map height.
		self::add_height( $css, $height, 'desktop' );

		// Common Advanced Tab CSS (spacing, background, border, shadow, advanced, animation).
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'desktop', true );

		// =====================================================================
		// TABLET STYLES
		// =====================================================================
		$css->start_media_query( 'tablet' );
		$css->set_selector( $selector );

		// Map height.
		self::add_height( $css, $height, 'tablet' );

		// Common Advanced Tab CSS (responsive).
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'tablet', false );

		$css->end_media_query();

		// =====================================================================
		// MOBILE STYLES
		// =====================================================================
		$css->start_media_query( 'mobile' );
		$css->set_selector( $selector );

		// Map height.
		self::add_height( $css, $height, 'mobile' );

		// Common Advanced Tab CSS (responsive).
		CSS_Helpers::add_advanced_tab_css( $css, $attrs, 'mobile', false );

		$css->end_media_query();

		// =====================================================================
		// DARK MODE STYLES
		// =====================================================================
		CSS_Helpers::add_advanced_tab_dark_mode( $css, $selector, $attrs );

		// =====================================================================
		// HOVER STATES (border + shadow)
		// =====================================================================
		CSS_Helpers::add_advanced_tab_hover_css( $css, $selector, $attrs );
	}

	/**
	 * Add map height CSS variable.
	 *
	 * @param CSS_Builder $css    CSS generator.
	 * @param array                    $height Height attribute.
	 * @param string                   $device Device key.
	 */
	private static function add_height( $css, $height, $device ) {
		$device_height = $height[ $device ] ?? [];
		$value         = $device_height['value'] ?? '';

		if ( '' === $value ) {
			return;
		}

		$unit = $device_height['unit'] ?? 'px';

		$css->add_variable( 'map-height', $value . $unit );
	}
}
