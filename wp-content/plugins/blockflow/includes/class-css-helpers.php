<?php
/**
 * CSS Helpers for BlockFlow.
 *
 * Provides modular methods for adding CSS properties and variables.
 * Two categories:
 * - Variables: Use CSS custom properties (--blockflow-*) for values that need SCSS fallbacks
 * - Properties: Use direct CSS properties for values that should not inherit to nested elements
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSS Helpers class.
 */
class CSS_Helpers {

	// =========================================================================
	// SPACING (Direct Properties - prevents nested inheritance)
	// =========================================================================

	/**
	 * Add padding CSS properties (direct).
	 *
	 * Use this for wrapper blocks (Container, Grid) and Advanced tab
	 * where direct properties prevent nested inheritance issues.
	 * Use add_padding_var() for inner content elements (accordion header, note content, etc.).
	 *
	 * @param CSS_Builder $css     CSS generator instance.
	 * @param array                    $padding Padding values { top, right, bottom, left, unit }.
	 */
	public static function add_padding( $css, $padding ) {
		self::add_spacing_properties( $css, $padding, 'padding' );
	}

	/**
	 * Add padding CSS variable.
	 *
	 * Outputs one variable per element: --blockflow-{prefix}-padding
	 *
	 * Why CSS variable instead of direct properties?
	 * All CSS variables must be defined on the root block selector for the CSS generator
	 * architecture to work correctly. Inner elements (e.g., __icon, __button) consume
	 * these variables via var() in SCSS. This allows responsive overrides in media queries
	 * to work properly - we only need to override the variable value on the root selector,
	 * and all inner elements automatically inherit the new value.
	 *
	 * Use add_padding() for wrapper/outer elements where direct properties are preferred
	 * (e.g., Container, Grid) to avoid nested inheritance issues.
	 *
	 * Supports two structures:
	 * - 4-sided: { top, right, bottom, left, unit } → "15px 20px 15px 20px"
	 * - Single:  { value, unit } → "10px"
	 *
	 * @param CSS_Builder $css     CSS generator instance.
	 * @param array                    $padding Padding values.
	 * @param string                   $prefix  Element prefix (e.g., 'icon', 'button', 'accordion-header').
	 */
	public static function add_padding_var( $css, $padding, $prefix ) {
		if ( empty( $padding ) || ! is_array( $padding ) ) {
			return;
		}

		$unit = $padding['unit'] ?? 'px';

		// Single-value structure: { value, unit }.
		if ( isset( $padding['value'] ) ) {
			$value = $padding['value'];
			if ( '' === $value ) {
				return;
			}
			$css->add_variable( $prefix . '-padding', $value . $unit );
			return;
		}

		// 4-sided structure: { top, right, bottom, left, unit }.
		$top    = $padding['top'] ?? '';
		$right  = $padding['right'] ?? '';
		$bottom = $padding['bottom'] ?? '';
		$left   = $padding['left'] ?? '';

		if ( '' === $top && '' === $right && '' === $bottom && '' === $left ) {
			return;
		}

		// Build shorthand value (use 0 for empty values).
		$values = [
			'' !== $top ? $top . $unit : '0',
			'' !== $right ? $right . $unit : '0',
			'' !== $bottom ? $bottom . $unit : '0',
			'' !== $left ? $left . $unit : '0',
		];

		$css->add_variable( $prefix . '-padding', implode( ' ', $values ) );
	}

	/**
	 * Add margin CSS properties.
	 *
	 * @param CSS_Builder $css    CSS generator instance.
	 * @param array               $margin Margin values { top, right, bottom, left, unit }.
	 */
	public static function add_margin( $css, $margin ) {
		self::add_spacing_properties( $css, $margin, 'margin' );
	}

	/**
	 * Add spacing CSS properties (internal).
	 *
	 * @param CSS_Builder $css      CSS generator instance.
	 * @param array               $spacing  Spacing values { top, right, bottom, left, unit }.
	 * @param string              $property Property name: 'padding' or 'margin'.
	 */
	private static function add_spacing_properties( $css, $spacing, $property ) {
		if ( empty( $spacing ) || ! is_array( $spacing ) ) {
			return;
		}

		$unit  = $spacing['unit'] ?? 'px';
		$sides = [ 'top', 'right', 'bottom', 'left' ];

		foreach ( $sides as $side ) {
			if ( isset( $spacing[ $side ] ) && '' !== $spacing[ $side ] ) {
				$value = $spacing[ $side ];

				// Don't add unit for 'auto' or values with existing units.
				if ( 'auto' === $value || preg_match( '/[a-z%]$/i', $value ) ) {
					$final_value = $value;
				} else {
					$final_value = $value . $unit;
				}

				$css->add_property( $property . '-' . $side, $final_value );
			}
		}
	}

	// =========================================================================
	// BACKGROUND (Direct CSS Property - prevents nested inheritance)
	// =========================================================================

	/**
	 * Add background CSS property.
	 *
	 * Uses direct CSS property instead of CSS variable to prevent nested blocks
	 * from inheriting parent's background. Same approach as box-shadow.
	 * Image settings are passed as data attributes (see HTML_Helpers).
	 *
	 * @param CSS_Builder $css        CSS generator instance.
	 * @param array                    $background Background values.
	 */
	public static function add_background( $css, $background ) {
		if ( empty( $background ) || ! is_array( $background ) ) {
			return;
		}

		$type = $background['type'] ?? '';

		// Classic type: solid color.
		if ( 'classic' === $type ) {
			$color = self::get_color_value( $background['color'] ?? [] );
			if ( $color ) {
				$css->add_property( 'background', $color );
			}
		}

		// Gradient type.
		if ( 'gradient' === $type ) {
			$gradient = self::get_color_value( $background['gradient'] ?? [] );
			if ( $gradient ) {
				$css->add_property( 'background', $gradient );
			}
		}
	}

	/**
	 * Add background dark mode CSS property.
	 *
	 * @param CSS_Builder $css        CSS generator instance.
	 * @param string                   $selector   Block selector.
	 * @param array                    $background Background values.
	 */
	public static function add_background_dark_mode( $css, $selector, $background ) {
		if ( empty( $background ) || ! is_array( $background ) ) {
			return;
		}

		$type = $background['type'] ?? '';

		// Classic type: dark background color.
		if ( 'classic' === $type ) {
			$dark_color = self::get_dark_color( $background['color'] ?? [] );
			if ( $dark_color ) {
				self::add_dark_mode_css(
					$css,
					$selector,
					function ( $css ) use ( $dark_color ) {
						$css->add_property( 'background', $dark_color );
					}
				);
			}
		}

		// Gradient type: dark gradient.
		if ( 'gradient' === $type ) {
			$dark_gradient = self::get_dark_color( $background['gradient'] ?? [] );
			if ( $dark_gradient ) {
				self::add_dark_mode_css(
					$css,
					$selector,
					function ( $css ) use ( $dark_gradient ) {
						$css->add_property( 'background', $dark_gradient );
					}
				);
			}
		}
	}

	// =========================================================================
	// LAYOUT (CSS Variables - consumed by SCSS with fallbacks)
	// =========================================================================

	/**
	 * Add layout CSS variables.
	 *
	 * @param CSS_Builder $css    CSS generator instance.
	 * @param array               $layout Layout values.
	 */
	public static function add_layout_vars( $css, $layout ) {
		if ( empty( $layout ) || ! is_array( $layout ) ) {
			return;
		}

		$mapping = [
			'display'        => 'display',
			'direction'      => 'direction',
			'wrap'           => 'wrap',
			'justifyContent' => 'justify',
			'alignItems'     => 'align',
			'alignContent'   => 'align-content',
		];

		foreach ( $mapping as $prop => $var_name ) {
			if ( ! empty( $layout[ $prop ] ) ) {
				$css->add_variable( $var_name, $layout[ $prop ] );
			}
		}

		// Gap: { column, row, unit }.
		if ( ! empty( $layout['gap'] ) ) {
			$gap  = $layout['gap'];
			$unit = $gap['unit'] ?? 'px';

			if ( '' !== ( $gap['column'] ?? '' ) ) {
				$css->add_variable( 'column-gap', $gap['column'] . $unit );
			}
			if ( '' !== ( $gap['row'] ?? '' ) ) {
				$css->add_variable( 'row-gap', $gap['row'] . $unit );
			}
		}
	}

	// =========================================================================
	// SIZE (CSS Variables - consumed by SCSS with fallbacks)
	// =========================================================================

	/**
	 * Add size CSS variables.
	 *
	 * @param CSS_Builder $css  CSS generator instance.
	 * @param array               $size Size values.
	 */
	public static function add_size_vars( $css, $size ) {
		if ( empty( $size ) || ! is_array( $size ) ) {
			return;
		}

		$mapping = [
			'width'     => [
				'var'  => 'w',
				'unit' => '%',
			],
			'maxWidth'  => [
				'var'  => 'max-w',
				'unit' => 'px',
			],
			'minWidth'  => [
				'var'  => 'min-w',
				'unit' => 'px',
			],
			'height'    => [
				'var'  => 'h',
				'unit' => 'px',
			],
			'minHeight' => [
				'var'  => 'min-h',
				'unit' => 'px',
			],
			'maxHeight' => [
				'var'  => 'max-h',
				'unit' => 'px',
			],
		];

		foreach ( $mapping as $prop => $config ) {
			if ( ! empty( $size[ $prop ]['value'] ) ) {
				$value = $size[ $prop ]['value'];
				$unit  = $size[ $prop ]['unit'] ?? $config['unit'];

				// Skip unit for special values.
				if ( in_array( $value, [ 'auto', 'none' ], true ) || preg_match( '/^calc\(|[a-z%]$/i', $value ) ) {
					$css->add_variable( $config['var'], $value );
				} else {
					$css->add_variable( $config['var'], $value . $unit );
				}
			}
		}
	}

	// =========================================================================
	// BORDER (CSS Variables - consumed by SCSS with fallbacks)
	// =========================================================================

	/**
	 * Add individual border width as direct CSS properties (4 sides).
	 *
	 * Use for inner elements where direct properties prevent inheritance issues.
	 * Outputs: border-top-width, border-right-width, border-bottom-width, border-left-width
	 *
	 * @param CSS_Builder $css   CSS generator instance.
	 * @param array                    $width Width values { top, right, bottom, left, unit }.
	 */
	public static function add_border_width( $css, $width ) {
		if ( empty( $width ) || ! is_array( $width ) ) {
			return;
		}

		$unit  = $width['unit'] ?? 'px';
		$sides = [
			'top'    => 'border-top-width',
			'right'  => 'border-right-width',
			'bottom' => 'border-bottom-width',
			'left'   => 'border-left-width',
		];

		foreach ( $sides as $key => $prop ) {
			$value = $width[ $key ] ?? '';
			if ( '' !== $value ) {
				$css->add_property( $prop, $value . $unit );
			}
		}
	}

	/**
	 * Add individual border radius as direct CSS properties (4 corners).
	 *
	 * Use for inner elements where direct properties prevent inheritance issues.
	 * Outputs: border-top-left-radius, border-top-right-radius, etc.
	 *
	 * @param CSS_Builder $css    CSS generator instance.
	 * @param array                    $radius Radius values { topLeft, topRight, bottomRight, bottomLeft, unit }.
	 */
	public static function add_border_radius( $css, $radius ) {
		if ( empty( $radius ) || ! is_array( $radius ) ) {
			return;
		}

		$unit    = $radius['unit'] ?? 'px';
		$corners = [
			'topLeft'     => 'border-top-left-radius',
			'topRight'    => 'border-top-right-radius',
			'bottomRight' => 'border-bottom-right-radius',
			'bottomLeft'  => 'border-bottom-left-radius',
		];

		foreach ( $corners as $key => $prop ) {
			$value = $radius[ $key ] ?? '';
			if ( '' !== $value ) {
				$css->add_property( $prop, $value . $unit );
			}
		}
	}

	/**
	 * Add border CSS variables.
	 *
	 * Variable naming:
	 * - Without prefix: --blockflow-border-s, --blockflow-border-w, etc. (for block wrapper)
	 * - With prefix 'accordion-item': --blockflow-accordion-item-border-s, etc. (for inner elements)
	 *
	 * @param CSS_Builder $css    CSS generator instance.
	 * @param array                    $border Border values.
	 * @param string                   $prefix Optional prefix for inner elements (e.g., 'accordion-item').
	 */
	public static function add_border_vars( $css, $border, $prefix = '' ) {
		if ( empty( $border ) || ! is_array( $border ) ) {
			return;
		}

		// Build variable name prefix.
		$var_prefix = $prefix ? $prefix . '-' : '';

		if ( ! empty( $border['style'] ) ) {
			$css->add_variable( $var_prefix . 'border-s', $border['style'] );
		}

		if ( ! empty( $border['color'] ) ) {
			$color = self::get_color_value( $border['color'] );
			if ( $color ) {
				$css->add_variable( $var_prefix . 'border-c', $color );
			}
		}

		if ( ! empty( $border['width'] ) ) {
			$width = self::build_spacing_shorthand( $border['width'] );
			if ( $width ) {
				$css->add_variable( $var_prefix . 'border-w', $width );
			}
		}

		if ( ! empty( $border['radius'] ) ) {
			$radius = self::build_radius_shorthand( $border['radius'] );
			if ( $radius ) {
				$css->add_variable( $var_prefix . 'radius', $radius );
			}
		}
	}

	// =========================================================================
	// BOX SHADOW (Direct Property - prevents child inheritance)
	// =========================================================================

	/**
	 * Add box shadow CSS property directly.
	 *
	 * Uses direct CSS property instead of variable to prevent
	 * nested elements from inheriting the shadow.
	 *
	 * @param CSS_Builder $css        CSS generator instance.
	 * @param array               $box_shadow Box shadow values.
	 */
	public static function add_shadow( $css, $box_shadow ) {
		$value = self::build_box_shadow( $box_shadow );
		if ( $value ) {
			$css->add_property( 'box-shadow', $value );
		}
	}

	// =========================================================================
	// ADVANCED LAYOUT (CSS Variables)
	// =========================================================================

	/**
	 * Add advanced layout CSS variables.
	 *
	 * Always outputs position when set to prevent CSS variable inheritance
	 * from parent blocks.
	 *
	 * @param CSS_Builder $css      CSS generator instance.
	 * @param array                    $advanced Advanced layout values.
	 */
	public static function add_advanced_vars( $css, $advanced ) {
		if ( empty( $advanced ) || ! is_array( $advanced ) ) {
			return;
		}

		if ( ! empty( $advanced['alignSelf'] ) ) {
			$css->add_variable( 'align-self', $advanced['alignSelf'] );
		}

		if ( '' !== ( $advanced['order'] ?? '' ) ) {
			$css->add_variable( 'order', intval( $advanced['order'] ) );
		}

		// Always output position when set (prevents inheritance from parent).
		if ( ! empty( $advanced['position'] ) ) {
			$css->add_variable( 'position', $advanced['position'] );
		}

		if ( '' !== ( $advanced['zIndex'] ?? '' ) ) {
			$css->add_variable( 'z', intval( $advanced['zIndex'] ) );
		}

		if ( ! empty( $advanced['overflow'] ) ) {
			$css->add_variable( 'overflow', $advanced['overflow'] );
		}

		// Sizing: width type.
		self::add_sizing_vars( $css, $advanced );
	}

	/**
	 * Add sizing CSS variables (width and flex size).
	 *
	 * @param CSS_Builder $css      CSS generator instance.
	 * @param array               $advanced Advanced layout values.
	 */
	public static function add_sizing_vars( $css, $advanced ) {
		if ( empty( $advanced ) || ! is_array( $advanced ) ) {
			return;
		}

		// Width type handling (sets both width and max-width for stronger specificity).
		$width_type = $advanced['widthType'] ?? '';
		switch ( $width_type ) {
			case 'full':
				$css->add_variable( 'block-w', '100%' );
				$css->add_variable( 'block-maxw', '100%' );
				break;
			case 'inline':
				$css->add_variable( 'block-w', 'auto' );
				$css->add_variable( 'display', 'inline-flex' );
				break;
			case 'custom':
				$custom_width = $advanced['customWidth'] ?? [];
				if ( ! empty( $custom_width['value'] ) ) {
					$unit  = $custom_width['unit'] ?? '%';
					$value = $custom_width['value'] . $unit;
					$css->add_variable( 'block-w', $value );
					$css->add_variable( 'block-maxw', $value );
				}
				break;
		}

		// Flex size handling.
		$flex_size = $advanced['flexSize'] ?? '';
		switch ( $flex_size ) {
			case 'grow':
				$css->add_variable( 'flex-grow', '1' );
				$css->add_variable( 'flex-shrink', '0' );
				break;
			case 'shrink':
				$css->add_variable( 'flex-grow', '0' );
				$css->add_variable( 'flex-shrink', '1' );
				break;
			case 'none':
				$css->add_variable( 'flex-grow', '0' );
				$css->add_variable( 'flex-shrink', '0' );
				break;
			case 'custom':
				$grow   = $advanced['flexGrow'] ?? '';
				$shrink = $advanced['flexShrink'] ?? '';
				if ( '' !== $grow ) {
					$css->add_variable( 'flex-grow', $grow );
				}
				if ( '' !== $shrink ) {
					$css->add_variable( 'flex-shrink', $shrink );
				}
				break;
		}
	}

	// =========================================================================
	// ADVANCED TAB - COMPLETE CSS GENERATION
	// =========================================================================

	/**
	 * Generate complete CSS for Advanced tab settings (common across all blocks).
	 *
	 * This is a convenience method that calls all the individual helpers
	 * for the Advanced tab panels in the correct order.
	 *
	 * Handles:
	 * - Spacing (margin, padding) from `spacing` attribute
	 * - Advanced layout (alignSelf, order, zIndex, position) from `advancedLayout`
	 * - Sizing (widthType, customWidth, flexSize) from `advancedLayout`
	 * - Background from `background` attribute
	 * - Border from `border` attribute
	 * - Box shadow from `boxShadow` attribute
	 * - Animation from `animation` attribute (desktop only)
	 *
	 * @param CSS_Builder $css         CSS generator instance.
	 * @param array               $attrs       Block attributes.
	 * @param string              $device      Device key: 'desktop', 'tablet', or 'mobile'.
	 * @param bool                $is_desktop  Whether this is desktop (base) styles.
	 */
	public static function add_advanced_tab_css( $css, $attrs, $device = 'desktop', $is_desktop = true ) {
		$spacing    = $attrs['spacing'] ?? [];
		$advanced   = $attrs['advancedLayout'] ?? [];
		$background = $attrs['background'] ?? [];
		$border     = $attrs['border'] ?? [];
		$shadow     = $attrs['boxShadow'] ?? [];
		$animation  = $attrs['animation'] ?? [];

		// Get device-specific values.
		$device_spacing  = $spacing[ $device ] ?? [];
		$device_advanced = $advanced[ $device ] ?? [];
		$device_border   = $border[ $device ] ?? [];

		// 1. Spacing (margin, padding).
		self::add_padding( $css, $device_spacing['padding'] ?? [] );
		self::add_margin( $css, $device_spacing['margin'] ?? [] );

		// 2. Advanced layout (alignSelf, order, zIndex, position).
		self::add_advanced_vars( $css, $device_advanced );

		// 3. Border.
		self::add_border_vars( $css, $device_border );

		// Desktop-only styles (non-responsive).
		if ( $is_desktop ) {
			// 4. Background (non-responsive, direct property).
			// Image settings are via data attributes (see HTML_Helpers).
			self::add_background( $css, $background );

			// 5. Box shadow (non-responsive, uses direct property).
			self::add_shadow( $css, $shadow );

			// 6. Animation (non-responsive).
			self::add_animation_vars( $css, $animation );
		}
	}

	/**
	 * Generate hover state CSS for Advanced tab (border hover + shadow hover).
	 *
	 * Call this after the main CSS generation for each block.
	 *
	 * @param CSS_Builder $css           CSS generator instance.
	 * @param string              $selector      Block selector.
	 * @param array               $attrs         Block attributes.
	 */
	public static function add_advanced_tab_hover_css( $css, $selector, $attrs ) {
		$border_hover = $attrs['borderHover'] ?? [];
		$shadow_hover = $attrs['boxShadowHover'] ?? [];

		self::add_hover_states( $css, $selector, $border_hover, $shadow_hover );
	}

	// =========================================================================
	// DARK MODE HELPER
	// =========================================================================

	/**
	 * Apply dark mode CSS with the enabled method(s).
	 *
	 * This helper eliminates duplicate logic across CSS generators.
	 * It outputs CSS for whichever methods are enabled (can be both).
	 *
	 * @param CSS_Builder $css      CSS generator instance.
	 * @param string                   $selector Block selector.
	 * @param callable                 $callback Callback that adds CSS variables/properties.
	 *                                           Receives ($css) as the argument.
	 */
	public static function add_dark_mode_css( $css, $selector, $callback ) {
		// Early return if dark mode is disabled.
		if ( ! Dark_Mode_Settings::is_enabled() ) {
			return;
		}

		// Output for data-theme method if enabled.
		if ( Dark_Mode_Settings::use_data_theme() ) {
			$css->set_selector( '[data-theme="dark"] ' . $selector );
			$callback( $css );
		}

		// Output for color-scheme method if enabled.
		if ( Dark_Mode_Settings::use_color_scheme() ) {
			$css->start_media_query( '@media (prefers-color-scheme: dark)' );
			$css->set_selector( $selector );
			$callback( $css );
			$css->end_media_query();
		}
	}

	/**
	 * Generate dark mode CSS for Advanced tab settings.
	 *
	 * Handles dark mode colors for:
	 * - Background color
	 * - Border color
	 * - Box shadow color
	 *
	 * @param CSS_Builder $css        CSS generator instance.
	 * @param string              $selector   Block selector.
	 * @param array               $attrs      Block attributes.
	 */
	public static function add_advanced_tab_dark_mode( $css, $selector, $attrs ) {
		$background = $attrs['background'] ?? [];
		$border     = $attrs['border'] ?? [];
		$shadow     = $attrs['boxShadow'] ?? [];

		// Collect dark mode values.
		$dark_bg_color     = self::get_dark_color( $background['color'] ?? '' );
		$dark_border_color = self::get_dark_color( $border['desktop']['color'] ?? '' );
		$dark_shadow_color = self::get_dark_color( $shadow['color'] ?? '' );

		$has_dark_styles = $dark_bg_color || $dark_border_color || $dark_shadow_color;

		if ( $has_dark_styles ) {
			// Use the helper to handle dark mode method.
			self::add_dark_mode_css(
				$css,
				$selector,
				function ( $css ) use ( $dark_bg_color, $dark_border_color, $dark_shadow_color, $shadow ) {
					if ( $dark_bg_color ) {
						$css->add_property( 'background', $dark_bg_color );
					}
					if ( $dark_border_color ) {
						$css->add_variable( 'border-c', $dark_border_color );
					}
					if ( $dark_shadow_color ) {
						$dark_shadow          = $shadow;
						$dark_shadow['color'] = $dark_shadow_color;
						$shadow_value         = self::build_box_shadow( $dark_shadow );
						if ( $shadow_value ) {
							$css->add_property( 'box-shadow', $shadow_value );
						}
					}
				}
			);
		}

		// Background dark mode (color or gradient).
		self::add_background_dark_mode( $css, $selector, $background );
	}

	// =========================================================================
	// ANIMATION (CSS Variables)
	// =========================================================================

	/**
	 * Add animation CSS variables.
	 *
	 * Always outputs duration and delay when animation is enabled to prevent
	 * CSS variable inheritance from parent blocks.
	 *
	 * @param CSS_Builder $css       CSS generator instance.
	 * @param array                    $animation Animation values { type, duration, delay, easing, once }.
	 */
	public static function add_animation_vars( $css, $animation ) {
		if ( empty( $animation ) || ! is_array( $animation ) ) {
			return;
		}

		$type = $animation['type'] ?? '';
		if ( empty( $type ) ) {
			return;
		}

		// Always output duration when animation is enabled (prevents inheritance).
		$duration = $animation['duration'] ?? 400;
		if ( $duration > 0 ) {
			$css->add_variable( 'anim-duration', ( $duration / 1000 ) . 's' );
		}

		// Always output delay when animation is enabled (prevents inheritance).
		$delay = $animation['delay'] ?? 0;
		$css->add_variable( 'anim-delay', ( $delay / 1000 ) . 's' );

		// Always output easing when animation is enabled (prevents inheritance).
		$easing = $animation['easing'] ?? 'ease';
		if ( ! empty( $easing ) ) {
			$css->add_variable( 'anim-easing', $easing );
		}
	}

	// =========================================================================
	// VALUE BUILDERS (Static Utilities)
	// =========================================================================

	/**
	 * Extract light color value from { light, dark } object.
	 *
	 * @param array $color Color object { light, dark }.
	 * @return string Light color string or empty.
	 */
	public static function get_color_value( $color ) {
		if ( ! is_array( $color ) || empty( $color['light'] ) ) {
			return '';
		}
		return $color['light'];
	}

	/**
	 * Get dark mode color from { light, dark } object.
	 *
	 * @param array $color Color object { light, dark }.
	 * @return string Dark color or empty.
	 */
	public static function get_dark_color( $color ) {
		if ( ! is_array( $color ) || empty( $color['dark'] ) ) {
			return '';
		}
		return $color['dark'];
	}

	/**
	 * Build spacing shorthand value.
	 *
	 * @param array $spacing Spacing { top, right, bottom, left, unit }.
	 * @return string CSS shorthand or empty.
	 */
	public static function build_spacing_shorthand( $spacing ) {
		if ( empty( $spacing ) || ! is_array( $spacing ) ) {
			return '';
		}

		$top    = $spacing['top'] ?? '';
		$right  = $spacing['right'] ?? '';
		$bottom = $spacing['bottom'] ?? '';
		$left   = $spacing['left'] ?? '';
		$unit   = $spacing['unit'] ?? 'px';

		if ( '' === $top && '' === $right && '' === $bottom && '' === $left ) {
			return '';
		}

		$values = [];
		foreach ( [ $top, $right, $bottom, $left ] as $value ) {
			$values[] = ( '' === $value || null === $value ) ? '0' : $value . $unit;
		}

		// Optimize shorthand.
		if ( $values[0] === $values[1] && $values[1] === $values[2] && $values[2] === $values[3] ) {
			return $values[0];
		}
		if ( $values[0] === $values[2] && $values[1] === $values[3] ) {
			return $values[0] . ' ' . $values[1];
		}
		if ( $values[1] === $values[3] ) {
			return $values[0] . ' ' . $values[1] . ' ' . $values[2];
		}

		return implode( ' ', $values );
	}

	/**
	 * Build border-radius shorthand value.
	 *
	 * @param array $radius Radius { topLeft, topRight, bottomRight, bottomLeft, unit }.
	 * @return string CSS shorthand or empty.
	 */
	public static function build_radius_shorthand( $radius ) {
		if ( empty( $radius ) || ! is_array( $radius ) ) {
			return '';
		}

		$tl   = $radius['topLeft'] ?? '';
		$tr   = $radius['topRight'] ?? '';
		$br   = $radius['bottomRight'] ?? '';
		$bl   = $radius['bottomLeft'] ?? '';
		$unit = $radius['unit'] ?? 'px';

		if ( '' === $tl && '' === $tr && '' === $br && '' === $bl ) {
			return '';
		}

		$values = [];
		foreach ( [ $tl, $tr, $br, $bl ] as $value ) {
			$values[] = ( '' === $value || null === $value ) ? '0' : $value . $unit;
		}

		// Optimize shorthand.
		if ( $values[0] === $values[1] && $values[1] === $values[2] && $values[2] === $values[3] ) {
			return $values[0];
		}
		if ( $values[0] === $values[2] && $values[1] === $values[3] ) {
			return $values[0] . ' ' . $values[1];
		}

		return implode( ' ', $values );
	}

	/**
	 * Build box-shadow CSS value.
	 *
	 * @param array  $shadow         Box shadow values.
	 * @param string $color_override Optional color to use instead of shadow['color'].
	 * @param bool   $check_enabled  Whether to check enabled flag (default true).
	 * @return string CSS box-shadow value or empty.
	 */
	public static function build_box_shadow( $shadow, $color_override = '', $check_enabled = true ) {
		if ( empty( $shadow ) || ! is_array( $shadow ) ) {
			return '';
		}

		// Check enabled flag if requested.
		if ( $check_enabled ) {
			if ( isset( $shadow['enabled'] ) && false === $shadow['enabled'] ) {
				return 'none';
			}
			if ( ! isset( $shadow['enabled'] ) || ! $shadow['enabled'] ) {
				return '';
			}
		}

		$h      = $shadow['horizontal'] ?? '0';
		$v      = $shadow['vertical'] ?? '0';
		$blur   = $shadow['blur'] ?? '0';
		$spread = $shadow['spread'] ?? '0';
		$inset  = ! empty( $shadow['inset'] );

		// Use override color or extract from shadow.
		if ( $color_override ) {
			$color = $color_override;
		} else {
			$color = self::get_color_value( $shadow['color'] ?? '' );
		}

		if ( ! $color ) {
			$color = 'rgba(0, 0, 0, 0.1)';
		}

		$parts = [];
		if ( $inset ) {
			$parts[] = 'inset';
		}
		$parts[] = $h . 'px';
		$parts[] = $v . 'px';
		$parts[] = $blur . 'px';
		$parts[] = $spread . 'px';
		$parts[] = $color;

		return implode( ' ', $parts );
	}

	// =========================================================================
	// HOVER STATES (Reusable across all blocks)
	// =========================================================================

	/**
	 * Add hover state CSS for border and box-shadow.
	 *
	 * @param CSS_Builder $css           CSS generator instance.
	 * @param string              $selector      Block selector.
	 * @param array               $border_hover  Border hover attributes.
	 * @param array               $shadow_hover  Shadow hover attributes.
	 */
	public static function add_hover_states( $css, $selector, $border_hover, $shadow_hover ) {
		$has_border_hover = self::has_border_hover_value( $border_hover );
		$has_shadow_hover = self::has_shadow_hover_value( $shadow_hover );

		if ( ! $has_border_hover && ! $has_shadow_hover ) {
			return;
		}

		// Hover selector.
		$hover_selector = $selector . ':hover';
		$css->set_selector( $hover_selector );

		// Border hover (desktop values only - hover is non-responsive).
		if ( $has_border_hover ) {
			$desktop_hover = $border_hover['desktop'] ?? [];

			if ( ! empty( $desktop_hover['style'] ) ) {
				$css->add_variable( 'border-s', $desktop_hover['style'] );
			}

			if ( ! empty( $desktop_hover['color'] ) ) {
				$color = self::get_color_value( $desktop_hover['color'] );
				if ( $color ) {
					$css->add_variable( 'border-c', $color );
				}
			}

			if ( ! empty( $desktop_hover['width'] ) ) {
				$width = self::build_spacing_shorthand( $desktop_hover['width'] );
				if ( $width ) {
					$css->add_variable( 'border-w', $width );
				}
			}

			if ( ! empty( $desktop_hover['radius'] ) ) {
				$radius = self::build_radius_shorthand( $desktop_hover['radius'] );
				if ( $radius ) {
					$css->add_variable( 'radius', $radius );
				}
			}
		}

		// Shadow hover.
		if ( $has_shadow_hover ) {
			$shadow_value = self::build_box_shadow( $shadow_hover );
			if ( $shadow_value ) {
				$css->add_property( 'box-shadow', $shadow_value );
			}
		}
	}

	/**
	 * Check if border hover has any value set.
	 *
	 * @param array $border_hover Border hover attributes.
	 * @return bool True if has any hover value.
	 */
	public static function has_border_hover_value( $border_hover ) {
		if ( empty( $border_hover ) || ! is_array( $border_hover ) ) {
			return false;
		}

		$desktop = $border_hover['desktop'] ?? [];
		if ( empty( $desktop ) ) {
			return false;
		}

		// Check style.
		if ( ! empty( $desktop['style'] ) ) {
			return true;
		}

		// Check color.
		if ( ! empty( $desktop['color'] ) ) {
			if ( ! empty( $desktop['color']['light'] ) || ! empty( $desktop['color']['dark'] ) ) {
				return true;
			}
		}

		// Check width.
		if ( ! empty( $desktop['width'] ) ) {
			$w = $desktop['width'];
			if ( ! empty( $w['top'] ) || ! empty( $w['right'] ) || ! empty( $w['bottom'] ) || ! empty( $w['left'] ) ) {
				return true;
			}
		}

		// Check radius.
		if ( ! empty( $desktop['radius'] ) ) {
			$r = $desktop['radius'];
			if ( ! empty( $r['topLeft'] ) || ! empty( $r['topRight'] ) || ! empty( $r['bottomRight'] ) || ! empty( $r['bottomLeft'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if shadow hover has any value set.
	 *
	 * @param array $shadow_hover Shadow hover attributes.
	 * @return bool True if has any hover value.
	 */
	public static function has_shadow_hover_value( $shadow_hover ) {
		if ( empty( $shadow_hover ) || ! is_array( $shadow_hover ) ) {
			return false;
		}

		// Check if explicitly enabled/disabled.
		if ( isset( $shadow_hover['enabled'] ) ) {
			return true;
		}

		// Check if any non-empty values.
		$h      = $shadow_hover['horizontal'] ?? '';
		$v      = $shadow_hover['vertical'] ?? '';
		$blur   = $shadow_hover['blur'] ?? '';
		$spread = $shadow_hover['spread'] ?? '';
		$color  = $shadow_hover['color'] ?? '';

		if ( '' !== $h || '' !== $v || '' !== $blur || '' !== $spread ) {
			return true;
		}

		// Check color.
		if ( ! empty( $color ) ) {
			if ( ! empty( $color['light'] ) || ! empty( $color['dark'] ) ) {
				return true;
			}
		}

		return false;
	}

	// =========================================================================
	// IMAGE STYLES (Shared by Gallery, Carousel, etc.)
	// =========================================================================

	/**
	 * Add image border CSS variables.
	 *
	 * Handles border radius, style, width, and color for image elements.
	 * Used by Gallery, Carousel Images, and similar blocks.
	 *
	 * @param CSS_Builder $css    CSS generator instance.
	 * @param array                    $border Border values { radius, style, width, color }.
	 * @param string                   $prefix Variable prefix (e.g., 'gallery', 'carousel').
	 */
	public static function add_image_border_vars( $css, $border, $prefix = 'img' ) {
		if ( empty( $border ) || ! is_array( $border ) ) {
			return;
		}

		// Border radius - shared variable name.
		$radius       = $border['radius'] ?? [];
		$radius_value = self::build_radius_shorthand( $radius );
		if ( $radius_value ) {
			$css->add_variable( 'img-radius', $radius_value );
		}

		// Border style/width/color.
		$style       = $border['style'] ?? '';
		$color       = $border['color'] ?? [];
		$light_color = $color['light'] ?? '';

		if ( $style && $light_color ) {
			$css->add_variable( $prefix . '-border-style', $style );
			$css->add_variable( $prefix . '-border-color', $light_color );

			$width       = $border['width'] ?? [];
			$width_value = self::build_spacing_shorthand( $width );
			if ( $width_value ) {
				$css->add_variable( $prefix . '-border-width', $width_value );
			}
		}
	}

	/**
	 * Add image hover CSS (border + shadow).
	 *
	 * Outputs direct CSS properties for hover states on image elements.
	 * Used by Gallery, Carousel Images, and similar blocks.
	 *
	 * @param CSS_Builder $css           CSS generator instance.
	 * @param string                   $hover_selector Full hover selector (e.g., '.blockflow-gallery-123 .blockflow-gallery__media:hover').
	 * @param array                    $border_hover  Border hover values { desktop: { style, color, width } }.
	 * @param array                    $shadow_hover  Shadow hover values.
	 */
	public static function add_image_hover_css( $css, $hover_selector, $border_hover, $shadow_hover ) {
		$hover_border = $border_hover['desktop'] ?? [];
		$shadow_value = self::build_box_shadow( $shadow_hover );

		$hover_style = $hover_border['style'] ?? '';
		$hover_color = $hover_border['color']['light'] ?? '';
		$hover_width = $hover_border['width'] ?? [];

		$has_hover = ! empty( $shadow_value ) || $hover_style || $hover_color || ! empty( $hover_width );
		if ( ! $has_hover ) {
			return;
		}

		$css->set_selector( $hover_selector );

		if ( $shadow_value ) {
			$css->add_property( 'box-shadow', $shadow_value );
		}

		if ( $hover_style ) {
			$css->add_property( 'border-style', $hover_style );
		}
		if ( $hover_color ) {
			$css->add_property( 'border-color', $hover_color );
		}

		$width_value = self::build_spacing_shorthand( $hover_width );
		if ( $width_value ) {
			$css->add_property( 'border-width', $width_value );
		}
	}

	/**
	 * Add overlay CSS variables.
	 *
	 * Handles overlay color and opacity for image/video elements.
	 * Used by Gallery, Carousel Images, Video, and similar blocks.
	 *
	 * @param CSS_Builder $css           CSS generator instance.
	 * @param array                    $overlay_color Overlay color { light, dark }.
	 * @param float                    $opacity       Normal state opacity.
	 * @param float                    $hover_opacity Hover state opacity.
	 * @param string                   $prefix        Variable prefix (e.g., 'gallery', 'carousel').
	 */
	public static function add_overlay_vars( $css, $overlay_color, $opacity, $hover_opacity, $prefix ) {
		$light_bg = $overlay_color['light'] ?? '';
		if ( $light_bg ) {
			$css->add_variable( $prefix . '-overlay-bg', $light_bg );
		}

		if ( $opacity > 0 ) {
			$css->add_variable( $prefix . '-overlay-opacity', $opacity );
		}
		if ( $hover_opacity > 0 ) {
			$css->add_variable( $prefix . '-overlay-hover-opacity', $hover_opacity );
		}
	}

	// =========================================================================
	// TEXT STROKE (Direct Property - width is responsive)
	// =========================================================================

	/**
	 * Add text stroke width CSS property.
	 *
	 * Outputs -webkit-text-stroke-width and stroke-width as direct properties.
	 * Color is handled separately via CSS variable for dark mode support.
	 *
	 * @param CSS_Builder $css    CSS generator instance.
	 * @param array                    $stroke Text stroke attribute { width, color, enabled }.
	 * @param string                   $device Device key (desktop, tablet, mobile).
	 */
	public static function add_text_stroke_width( $css, $stroke, $device = 'desktop' ) {
		$width_data  = $stroke['width'] ?? [];
		$device_data = $width_data[ $device ] ?? [];

		$stroke_width = $device_data['value'] ?? '';
		$stroke_unit  = $device_data['unit'] ?? 'px';

		if ( $stroke_width ) {
			$css->add_property( '-webkit-text-stroke-width', $stroke_width . $stroke_unit );
			$css->add_property( 'stroke-width', $stroke_width . $stroke_unit );
		}
	}

	// =========================================================================
	// ALIGNMENT UTILITIES
	// =========================================================================

	/**
	 * Convert text alignment value to CSS justify-content value.
	 *
	 * @param string $align Alignment value (left, center, right, justify).
	 * @return string CSS justify-content value.
	 */
	public static function align_to_justify( $align ) {
		$map = [
			'left'    => 'flex-start',
			'center'  => 'center',
			'right'   => 'flex-end',
			'justify' => 'space-between',
		];
		return $map[ $align ] ?? $align;
	}

	/**
	 * Add alignment CSS variable with justify conversion.
	 *
	 * Converts text alignment (left/center/right) to CSS justify-content value.
	 * Skips default value on desktop, outputs only if has value on tablet/mobile.
	 *
	 * @param CSS_Builder $css       CSS generator instance.
	 * @param array                    $alignment Alignment attribute { desktop, tablet, mobile }.
	 * @param string                   $device    Device key: 'desktop', 'tablet', 'mobile'.
	 * @param string                   $var_name  CSS variable name suffix (default: 'justify').
	 * @param string                   $default   Default value to skip on desktop (default: 'left').
	 */
	public static function add_alignment_var( $css, $alignment, $device, $var_name = 'justify', $default = '' ) {
		$value = $alignment[ $device ] ?? '';

		// Skip empty only.
		if ( '' === $value ) {
			return;
		}

		$css->add_variable( $var_name, self::align_to_justify( $value ) );
	}

	// =========================================================================
	// ICON SIZE (Shared by Icon, Icon List, Icon Set, Social Icons)
	// =========================================================================

	/**
	 * Add icon size CSS variables.
	 *
	 * Handles width/height with support for old 'value' format fallback.
	 * Skips default values on desktop, outputs only if has value on tablet/mobile.
	 *
	 * @param CSS_Builder $css     CSS generator instance.
	 * @param array                    $size    Size attribute { desktop: { width, height, unit }, tablet: {}, mobile: {} }.
	 * @param string                   $device  Device key: 'desktop', 'tablet', 'mobile'.
	 * @param string                   $prefix  CSS variable prefix (e.g., 'icon', 'social', 'iconset').
	 * @param string                   $default Default size value to skip on desktop (e.g., '24', '40').
	 */
	public static function add_icon_size_vars( $css, $size, $device, $prefix, $default = '' ) {
		$device_size = $size[ $device ] ?? [];
		$width       = $device_size['width'] ?? '';
		$height      = $device_size['height'] ?? '';
		$unit        = $device_size['unit'] ?? 'px';

		// Fallback: support old 'value' format.
		if ( '' === $width && '' === $height && isset( $device_size['value'] ) ) {
			$width  = $device_size['value'];
			$height = $device_size['value'];
		}

		// Output only if has value (skip empty only).
		if ( '' !== $width ) {
			$css->add_variable( $prefix . '-w', $width . $unit );
		}
		if ( '' !== $height ) {
			$css->add_variable( $prefix . '-h', $height . $unit );
		}
	}

	/**
	 * Add icon border width CSS variable.
	 *
	 * Outputs 4-sided border-width as shorthand: --blockflow-{prefix}-border-w
	 *
	 * @param CSS_Builder $css    CSS generator instance.
	 * @param array                    $width  Width values { top, right, bottom, left, unit }.
	 * @param string                   $prefix CSS variable prefix (e.g., 'icon-list-icon').
	 */
	public static function add_icon_border_width_var( $css, $width, $prefix ) {
		if ( empty( $width ) || ! is_array( $width ) ) {
			return;
		}

		$unit   = $width['unit'] ?? 'px';
		$top    = $width['top'] ?? '';
		$right  = $width['right'] ?? '';
		$bottom = $width['bottom'] ?? '';
		$left   = $width['left'] ?? '';

		// Check if any value is set.
		if ( '' === $top && '' === $right && '' === $bottom && '' === $left ) {
			return;
		}

		// Build shorthand (use 0 for empty values).
		$value = sprintf(
			'%s%s %s%s %s%s %s%s',
			'' !== $top ? $top : '0',
			$unit,
			'' !== $right ? $right : '0',
			$unit,
			'' !== $bottom ? $bottom : '0',
			$unit,
			'' !== $left ? $left : '0',
			$unit
		);

		$css->add_variable( $prefix . '-border-w', $value );
	}

	/**
	 * Add icon border radius CSS variable.
	 *
	 * Outputs 4-corner border-radius as shorthand: --blockflow-{prefix}-radius
	 *
	 * @param CSS_Builder $css    CSS generator instance.
	 * @param array                    $radius Radius values { topLeft, topRight, bottomRight, bottomLeft, unit }.
	 * @param string                   $prefix CSS variable prefix (e.g., 'icon-list-icon').
	 */
	public static function add_icon_border_radius_var( $css, $radius, $prefix ) {
		if ( empty( $radius ) || ! is_array( $radius ) ) {
			return;
		}

		$unit         = $radius['unit'] ?? 'px';
		$top_left     = $radius['topLeft'] ?? '';
		$top_right    = $radius['topRight'] ?? '';
		$bottom_right = $radius['bottomRight'] ?? '';
		$bottom_left  = $radius['bottomLeft'] ?? '';

		// Check if any value is set.
		if ( '' === $top_left && '' === $top_right && '' === $bottom_right && '' === $bottom_left ) {
			return;
		}

		// Build shorthand (use 0 for empty values).
		$value = sprintf(
			'%s%s %s%s %s%s %s%s',
			'' !== $top_left ? $top_left : '0',
			$unit,
			'' !== $top_right ? $top_right : '0',
			$unit,
			'' !== $bottom_right ? $bottom_right : '0',
			$unit,
			'' !== $bottom_left ? $bottom_left : '0',
			$unit
		);

		$css->add_variable( $prefix . '-radius', $value );
	}

	// =========================================================================
	// ICON COLORS (Shared by Icon, Icon List, Icon Set, Social Icons)
	// =========================================================================

	/**
	 * Add icon color CSS variables (light mode).
	 *
	 * Outputs CSS variables for icon colors with consistent naming.
	 * Each color in the array should have { light, dark } structure.
	 *
	 * @param CSS_Builder $css    CSS generator instance.
	 * @param array                    $colors Array of color definitions.
	 *                                         Keys: 'color', 'colorHover', 'bg', 'bgHover', 'border', 'borderHover'.
	 *                                         Values: { light: '', dark: '' }.
	 * @param string                   $prefix CSS variable prefix (e.g., 'icon', 'social', 'iconset').
	 */
	public static function add_icon_color_vars( $css, $colors, $prefix ) {
		// Icon color.
		if ( ! empty( $colors['color']['light'] ) ) {
			$css->add_variable( $prefix . '-color', $colors['color']['light'] );
		}
		if ( ! empty( $colors['colorHover']['light'] ) ) {
			$css->add_variable( $prefix . '-color-hover', $colors['colorHover']['light'] );
		}

		// Background color.
		if ( ! empty( $colors['bg']['light'] ) ) {
			$css->add_variable( $prefix . '-bg', $colors['bg']['light'] );
		}
		if ( ! empty( $colors['bgHover']['light'] ) ) {
			$css->add_variable( $prefix . '-bg-hover', $colors['bgHover']['light'] );
		}

		// Border color.
		if ( ! empty( $colors['border']['light'] ) ) {
			$css->add_variable( $prefix . '-border-color', $colors['border']['light'] );
		}
		if ( ! empty( $colors['borderHover']['light'] ) ) {
			$css->add_variable( $prefix . '-border-color-hover', $colors['borderHover']['light'] );
		}
	}

	/**
	 * Add icon color dark mode CSS.
	 *
	 * Outputs dark mode CSS variables for icon colors.
	 * Guards against empty dark values before calling add_dark_mode_css.
	 *
	 * @param CSS_Builder $css      CSS generator instance.
	 * @param string                   $selector Block selector.
	 * @param array                    $colors   Array of color definitions (same as add_icon_color_vars).
	 * @param string                   $prefix   CSS variable prefix (e.g., 'icon', 'social', 'iconset').
	 */
	public static function add_icon_color_dark_mode( $css, $selector, $colors, $prefix ) {
		// Collect dark mode values.
		$dark_color        = $colors['color']['dark'] ?? '';
		$dark_color_hover  = $colors['colorHover']['dark'] ?? '';
		$dark_bg           = $colors['bg']['dark'] ?? '';
		$dark_bg_hover     = $colors['bgHover']['dark'] ?? '';
		$dark_border       = $colors['border']['dark'] ?? '';
		$dark_border_hover = $colors['borderHover']['dark'] ?? '';

		$has_dark_styles = $dark_color || $dark_color_hover || $dark_bg || $dark_bg_hover || $dark_border || $dark_border_hover;

		if ( ! $has_dark_styles ) {
			return;
		}

		// Use helper to handle dark mode method.
		self::add_dark_mode_css(
			$css,
			$selector,
			function ( $css ) use ( $dark_color, $dark_color_hover, $dark_bg, $dark_bg_hover, $dark_border, $dark_border_hover, $prefix ) {
				if ( $dark_color ) {
					$css->add_variable( $prefix . '-color', $dark_color );
				}
				if ( $dark_color_hover ) {
					$css->add_variable( $prefix . '-color-hover', $dark_color_hover );
				}
				if ( $dark_bg ) {
					$css->add_variable( $prefix . '-bg', $dark_bg );
				}
				if ( $dark_bg_hover ) {
					$css->add_variable( $prefix . '-bg-hover', $dark_bg_hover );
				}
				if ( $dark_border ) {
					$css->add_variable( $prefix . '-border-color', $dark_border );
				}
				if ( $dark_border_hover ) {
					$css->add_variable( $prefix . '-border-color-hover', $dark_border_hover );
				}
			}
		);
	}

	// =========================================================================
	// RESPONSIVE VALUE (Shared pattern for single value + unit)
	// =========================================================================

	/**
	 * Add responsive value CSS variable.
	 *
	 * Handles { value, unit } structure with default skip on desktop.
	 * Outputs only if has value for tablet/mobile.
	 *
	 * @param CSS_Builder $css          CSS generator instance.
	 * @param array                    $attr         Attribute { desktop: { value, unit }, tablet: {}, mobile: {} }.
	 * @param string                   $device       Device key: 'desktop', 'tablet', 'mobile'.
	 * @param string                   $var_name     CSS variable name suffix.
	 * @param string                   $default      Deprecated - not used. Pass empty string.
	 * @param string                   $default_unit Default unit if not specified.
	 */
	public static function add_responsive_value_var( $css, $attr, $device, $var_name, $default = '', $default_unit = 'px' ) {
		$device_data = $attr[ $device ] ?? [];
		$value       = $device_data['value'] ?? '';
		$unit        = $device_data['unit'] ?? $default_unit;

		// Skip empty only.
		if ( '' === $value ) {
			return;
		}

		$css->add_variable( $var_name, $value . $unit );
	}

	// =========================================================================
	// TYPOGRAPHY (Direct Properties - single source of truth)
	// =========================================================================

	/**
	 * Add typography CSS properties (direct properties, not variables).
	 *
	 * Non-responsive props (fontFamily, fontWeight, fontStyle, textTransform, textDecoration)
	 * are only output on desktop. Responsive props (fontSize, lineHeight, letterSpacing, wordSpacing)
	 * are output for the specified device.
	 *
	 * @param CSS_Builder $css        CSS generator instance.
	 * @param array                    $typography Full typography attribute.
	 * @param string                   $device     Device key: 'desktop', 'tablet', 'mobile'.
	 */
	public static function add_typography_css( $css, $typography, $device = 'desktop' ) {
		// Return early if typography is disabled.
		if ( isset( $typography['enabled'] ) && false === $typography['enabled'] ) {
			return;
		}

		$typo = $typography[ $device ] ?? [];
		if ( empty( $typo ) ) {
			return;
		}

		$is_desktop = 'desktop' === $device;

		// Non-responsive props only on desktop (from desktop values).
		if ( $is_desktop ) {
			$desktop = $typography['desktop'] ?? [];

			// Font family.
			$font_family = $desktop['fontFamily'] ?? '';
			if ( $font_family ) {
				if ( false !== strpos( $font_family, ',' ) ) {
					$css->add_property( 'font-family', $font_family );
				} else {
					$css->add_property( 'font-family', '"' . $font_family . '", sans-serif' );
				}
			}

			// Font weight.
			if ( ! empty( $desktop['fontWeight'] ) ) {
				$css->add_property( 'font-weight', $desktop['fontWeight'] );
			}

			// Font style.
			if ( ! empty( $desktop['fontStyle'] ) ) {
				$css->add_property( 'font-style', $desktop['fontStyle'] );
			}

			// Text transform.
			if ( ! empty( $desktop['textTransform'] ) ) {
				$css->add_property( 'text-transform', $desktop['textTransform'] );
			}

			// Text decoration.
			if ( ! empty( $desktop['textDecoration'] ) ) {
				$css->add_property( 'text-decoration', $desktop['textDecoration'] );
			}
		}

		// Responsive props on all devices.

		// Font size.
		$font_size = $typo['fontSize']['value'] ?? '';
		if ( '' !== $font_size ) {
			$unit = $typo['fontSize']['unit'] ?? 'px';
			$css->add_property( 'font-size', $font_size . $unit );
		}

		// Line height.
		$line_height = $typo['lineHeight']['value'] ?? '';
		if ( '' !== $line_height ) {
			$unit = $typo['lineHeight']['unit'] ?? 'em';
			$css->add_property( 'line-height', $line_height . $unit );
		}

		// Letter spacing.
		$letter_spacing = $typo['letterSpacing']['value'] ?? '';
		if ( '' !== $letter_spacing ) {
			$unit = $typo['letterSpacing']['unit'] ?? 'px';
			$css->add_property( 'letter-spacing', $letter_spacing . $unit );
		}

		// Word spacing.
		$word_spacing = $typo['wordSpacing']['value'] ?? '';
		if ( '' !== $word_spacing ) {
			$unit = $typo['wordSpacing']['unit'] ?? 'px';
			$css->add_property( 'word-spacing', $word_spacing . $unit );
		}
	}
}
