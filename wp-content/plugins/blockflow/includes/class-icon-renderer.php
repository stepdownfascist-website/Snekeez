<?php
/**
 * Icon Renderer Class
 *
 * Centralized SVG icon rendering for all blocks.
 * Supports library icons, stroke icons, and custom SVG uploads.
 *
 * SECURITY: All output from this class is sanitized via wp_kses() using
 * HTML_Helpers::get_allowed_svg() which defines a strict whitelist of
 * allowed SVG elements and attributes. This ensures all rendered SVG
 * content is safe for output without additional escaping.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Icon_Renderer class.
 */
class Icon_Renderer {

	/**
	 * Cached allowed SVG tags.
	 *
	 * @var array|null
	 */
	private static $allowed_svg = null;

	/**
	 * Get allowed SVG tags for wp_kses.
	 *
	 * @return array Allowed SVG elements and attributes.
	 */
	private static function get_allowed_svg() {
		if ( null === self::$allowed_svg ) {
			self::$allowed_svg = HTML_Helpers::get_allowed_svg();
		}
		return self::$allowed_svg;
	}

	/**
	 * Render an icon from library or custom upload.
	 *
	 * All output is sanitized via wp_kses() - safe for direct output.
	 *
	 * @param array $args {
	 *     Icon rendering arguments.
	 *
	 *     @type string       $name          Icon name to resolve from library.
	 *     @type string|array $path          SVG path data (string or array of paths).
	 *     @type string       $viewBox       SVG viewBox attribute. Default '0 0 24 24'.
	 *     @type bool         $stroke        Whether to use stroke rendering. Default false.
	 *     @type array        $customSvg     Custom SVG data {id} for media library uploads.
	 *     @type string       $class         CSS class for the SVG element.
	 *     @type string       $wrapper_class CSS class for the wrapper span.
	 *     @type bool         $aria_hidden   Whether to add aria-hidden. Default true.
	 *     @type bool         $wrap          Whether to wrap in span. Default true.
	 * }
	 * @return string Sanitized HTML (safe for output) or empty string.
	 */
	public static function render( $args = [] ) {
		$defaults = [
			'name'          => '',
			'path'          => '',
			'viewBox'       => '0 0 24 24',
			'stroke'        => null,
			'customSvg'     => [],
			'class'         => '',
			'wrapper_class' => '',
			'aria_hidden'   => true,
			'wrap'          => true,
		];

		$args = wp_parse_args( $args, $defaults );

		// Resolve icon data from name if path not provided.
		if ( empty( $args['path'] ) && ! empty( $args['name'] ) ) {
			$icon_data = Icon_Data::find( $args['name'] );
			if ( $icon_data ) {
				// Handle both 'path' (string) and 'paths' (array).
				if ( ! empty( $icon_data['paths'] ) && is_array( $icon_data['paths'] ) ) {
					$args['path'] = $icon_data['paths'];
				} else {
					$args['path'] = $icon_data['path'] ?? '';
				}
				$args['viewBox'] = $icon_data['viewBox'] ?? '0 0 24 24';

				// Only use icon_data stroke if not explicitly set in args.
				if ( null === $args['stroke'] ) {
					$args['stroke'] = ! empty( $icon_data['stroke'] );
				}

				// Get fillRule for outline icons with cutouts.
				if ( ! empty( $icon_data['fillRule'] ) ) {
					$args['fillRule'] = $icon_data['fillRule'];
				}
			}
		}

		// Default stroke to false if still null.
		if ( null === $args['stroke'] ) {
			$args['stroke'] = false;
		}

		// Determine source: custom upload or library.
		$custom_svg = $args['customSvg'];
		$has_custom = is_array( $custom_svg ) && ! empty( $custom_svg['id'] );

		if ( $has_custom ) {
			return self::render_custom_svg( $args );
		}

		if ( ! empty( $args['path'] ) ) {
			return self::render_library_icon( $args );
		}

		return '';
	}

	/**
	 * Render icon from library (path + viewBox).
	 *
	 * @param array $args Rendering arguments.
	 * @return string Rendered HTML.
	 */
	public static function render_library_icon( $args ) {
		$path          = $args['path'] ?? '';
		$viewbox       = $args['viewBox'] ?? '0 0 24 24';
		$stroke        = ! empty( $args['stroke'] );
		$svg_class     = $args['class'] ?? '';
		$wrapper_class = $args['wrapper_class'] ?? '';
		$aria_hidden   = $args['aria_hidden'] ?? true;
		$wrap          = $args['wrap'] ?? true;

		if ( empty( $path ) ) {
			return '';
		}

		// Build SVG attributes.
		$svg_attrs  = 'xmlns="http://www.w3.org/2000/svg"';
		$svg_attrs .= sprintf( ' viewBox="%s"', esc_attr( $viewbox ) );

		if ( $svg_class ) {
			$svg_attrs .= sprintf( ' class="%s"', esc_attr( $svg_class ) );
		}

		// Smart fill/stroke based on icon type.
		if ( $stroke ) {
			$svg_attrs .= ' fill="none"';
			$svg_attrs .= ' stroke="currentColor"';
			$svg_attrs .= ' stroke-width="1.5"';
			$svg_attrs .= ' stroke-linecap="round"';
			$svg_attrs .= ' stroke-linejoin="round"';
		} else {
			$svg_attrs .= ' fill="currentColor"';
		}

		if ( $aria_hidden ) {
			$svg_attrs .= ' aria-hidden="true"';
		}

		// Build path attributes - include fillRule if set.
		$fill_rule  = $args['fillRule'] ?? '';
		$path_attrs = '';
		if ( $fill_rule ) {
			$path_attrs = sprintf( ' fill-rule="%s" clip-rule="%s"', esc_attr( $fill_rule ), esc_attr( $fill_rule ) );
		}

		// Build path elements - handle both string and array.
		$paths_html = '';
		if ( is_array( $path ) ) {
			foreach ( $path as $p ) {
				$paths_html .= sprintf( '<path d="%s"%s/>', esc_attr( $p ), $path_attrs );
			}
		} else {
			$paths_html = sprintf( '<path d="%s"%s/>', esc_attr( $path ), $path_attrs );
		}

		// Build SVG.
		$svg = sprintf( '<svg %s>%s</svg>', $svg_attrs, $paths_html );

		// Sanitize.
		$svg = wp_kses( $svg, self::get_allowed_svg() );

		// Wrap if needed.
		if ( $wrap && $wrapper_class ) {
			return sprintf(
				'<span class="%s">%s</span>',
				esc_attr( $wrapper_class ),
				$svg
			);
		}

		return $svg;
	}

	/**
	 * Render custom uploaded SVG.
	 *
	 * Only renders SVGs uploaded via media library (by attachment ID).
	 *
	 * @param array $args Rendering arguments.
	 * @return string Rendered HTML.
	 */
	public static function render_custom_svg( $args ) {
		$custom_svg    = $args['customSvg'] ?? [];
		$wrapper_class = $args['wrapper_class'] ?? '';
		$wrap          = $args['wrap'] ?? true;

		$svg_id = absint( $custom_svg['id'] ?? 0 );

		// Only allow SVGs uploaded via media library.
		if ( empty( $svg_id ) ) {
			return '';
		}

		// Parse SVG for inline rendering with currentColor support.
		$parsed_svg = SVG_Parser::get_svg_from_attachment( $svg_id, true );

		if ( ! $parsed_svg ) {
			return '';
		}

		// Sanitize and return.
		$svg = wp_kses( $parsed_svg, self::get_allowed_svg() );

		if ( $wrap && $wrapper_class ) {
			return sprintf(
				'<span class="%s">%s</span>',
				esc_attr( $wrapper_class ),
				$svg
			);
		}

		return $svg;
	}
}
