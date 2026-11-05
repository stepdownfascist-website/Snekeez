<?php
/**
 * CSS Generator for BlockFlow.
 *
 * Generates minified CSS with responsive media queries.
 * Used by all BlockFlow for server-side CSS generation.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow;

use ThemeRuby\BlockFlow\Admin\Admin_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CSS_Builder {

	/**
	 * Cached CSS specificity boost setting.
	 *
	 * @var bool|null
	 */
	private static $specificity_boost = null;

	/**
	 * CSS rules storage grouped by media query.
	 *
	 * @var array
	 */
	private $rules = [];

	/**
	 * Current CSS selector.
	 *
	 * @var string
	 */
	private $current_selector = '';

	/**
	 * Current media query context.
	 *
	 * @var string|null
	 */
	private $current_media = null;

	/**
	 * Breakpoint definitions.
	 *
	 * @var array
	 */
	private $breakpoints = [
		'tablet' => 1024,
		'mobile' => 767,
	];

	/**
	 * Constructor.
	 *
	 * @param array $breakpoints Optional custom breakpoints.
	 */
	public function __construct( $breakpoints = [] ) {
		if ( ! empty( $breakpoints ) ) {
			$this->breakpoints = array_merge( $this->breakpoints, $breakpoints );
		}
	}

	/**
	 * Set the current CSS selector.
	 *
	 * Optionally prepends 'body ' for specificity boost when enabled in settings.
	 *
	 * @param string $selector CSS selector.
	 * @return self Fluent interface.
	 */
	public function set_selector( $selector ) {
		// Cache setting on first call.
		if ( null === self::$specificity_boost ) {
			$performance             = Admin_Settings::get( 'performance', [] );
			self::$specificity_boost = ! empty( $performance['css_specificity_boost'] );
		}

		// Prepend body if enabled and not already present.
		if ( self::$specificity_boost && ! preg_match( '/^body[\s.\[]/', $selector ) ) {
			$selector = 'body ' . $selector;
		}

		$this->current_selector = $selector;
		return $this;
	}

	/**
	 * Add a CSS property to current selector.
	 *
	 * Empty values are automatically skipped.
	 *
	 * @param string $property CSS property name.
	 * @param mixed  $value    CSS value.
	 * @return self Fluent interface.
	 */
	public function add_property( $property, $value ) {
		// Skip empty values but allow 0.
		if ( '' === $value || null === $value ) {
			return $this;
		}

		$media = $this->current_media ?? 'base';

		if ( ! isset( $this->rules[ $media ] ) ) {
			$this->rules[ $media ] = [];
		}

		if ( ! isset( $this->rules[ $media ][ $this->current_selector ] ) ) {
			$this->rules[ $media ][ $this->current_selector ] = [];
		}

		$this->rules[ $media ][ $this->current_selector ][ $property ] = $value;

		return $this;
	}

	/**
	 * Start a media query block.
	 *
	 * @param string $device Device name ('desktop', 'tablet', 'mobile') or custom media query.
	 * @return self Fluent interface.
	 */
	public function start_media_query( $device ) {
		if ( 'desktop' === $device ) {
			// Desktop uses min-width > tablet breakpoint.
			$this->current_media = '@media (min-width: ' . ( $this->breakpoints['tablet'] + 1 ) . 'px)';
		} elseif ( isset( $this->breakpoints[ $device ] ) ) {
			$this->current_media = '@media (max-width: ' . $this->breakpoints[ $device ] . 'px)';
		} else {
			// Allow custom media query string.
			$this->current_media = $device;
		}

		return $this;
	}

	/**
	 * End the current media query block.
	 *
	 * @return self Fluent interface.
	 */
	public function end_media_query() {
		$this->current_media = null;
		return $this;
	}

	/**
	 * Get the generated CSS output.
	 *
	 * @param bool $minify Whether to minify output. Default true.
	 * @return string Generated CSS.
	 */
	public function get_output( $minify = true ) {
		$css = '';

		// Base styles (no media query).
		if ( isset( $this->rules['base'] ) ) {
			$css .= $this->render_rules( $this->rules['base'], $minify );
		}

		// Media query styles.
		foreach ( $this->rules as $media => $selectors ) {
			if ( 'base' === $media ) {
				continue;
			}

			$media_css = $this->render_rules( $selectors, $minify );
			if ( ! empty( $media_css ) ) {
				if ( $minify ) {
					$css .= $media . '{' . $media_css . '}';
				} else {
					$css .= "\n" . $media . " {\n" . $media_css . "}\n";
				}
			}
		}

		return $css;
	}

	/**
	 * Render CSS rules for a set of selectors.
	 *
	 * @param array $selectors Selectors with their properties.
	 * @param bool  $minify    Whether to minify output.
	 * @return string Rendered CSS.
	 */
	private function render_rules( $selectors, $minify ) {
		$css = '';

		foreach ( $selectors as $selector => $properties ) {
			if ( empty( $properties ) ) {
				continue;
			}

			$declarations = '';
			foreach ( $properties as $property => $value ) {
				if ( $minify ) {
					$declarations .= $property . ':' . $value . ';';
				} else {
					$declarations .= "\t" . $property . ': ' . $value . ";\n";
				}
			}

			if ( $minify ) {
				$css .= $selector . '{' . $declarations . '}';
			} else {
				$css .= $selector . " {\n" . $declarations . "}\n";
			}
		}

		return $css;
	}

	/**
	 * Reset the generator state.
	 *
	 * @return self Fluent interface.
	 */
	public function reset() {
		$this->rules            = [];
		$this->current_selector = '';
		$this->current_media    = null;
		return $this;
	}

	/**
	 * Check if generator has any rules.
	 *
	 * @return bool True if rules exist.
	 */
	public function has_rules() {
		return ! empty( $this->rules );
	}

	/**
	 * Add a CSS variable declaration to current selector.
	 *
	 * @param string $name   Variable name (without --blockflow- prefix).
	 * @param mixed  $value  Variable value.
	 * @param string $prefix Variable prefix. Default '--blockflow-'.
	 * @return self Fluent interface.
	 */
	public function add_variable( $name, $value, $prefix = '--blockflow-' ) {
		return $this->add_property( $prefix . $name, $value );
	}
}
