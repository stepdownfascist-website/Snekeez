<?php
/**
 * SVG Parser Class
 *
 * Parses SVG files from media library and returns sanitized inline SVG
 * with color support (converts fill to currentColor).
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SVG_Parser class.
 */
class SVG_Parser {

	/**
	 * Cache for parsed SVGs.
	 *
	 * @var array
	 */
	private static $cache = [];

	/**
	 * Allowed SVG elements (cached).
	 *
	 * @var array|null
	 */
	private static $allowed_elements = null;

	/**
	 * Get allowed SVG elements.
	 *
	 * @return array List of allowed element names.
	 */
	private static function get_allowed_elements() {
		if ( null === self::$allowed_elements ) {
			self::$allowed_elements = HTML_Helpers::get_allowed_svg_elements();
		}
		return self::$allowed_elements;
	}

	/**
	 * Allowed SVG attributes.
	 *
	 * Security: 'style' removed (CSS injection risk).
	 * Security: 'xlink:href', 'href' removed (external resource loading risk).
	 *
	 * @var array
	 */
	private static $allowed_attributes = [
		// Core attributes.
		'id',
		'class',
		'xmlns',
		'viewBox',
		'viewbox',
		'width',
		'height',
		'preserveAspectRatio',
		'fill',
		'stroke',
		'stroke-width',
		'stroke-linecap',
		'stroke-linejoin',
		'stroke-dasharray',
		'stroke-dashoffset',
		'stroke-miterlimit',
		'stroke-opacity',
		'fill-opacity',
		'fill-rule',
		'clip-rule',
		'opacity',
		'transform',
		'transform-origin',
		// Path/shape attributes.
		'd',
		'cx',
		'cy',
		'r',
		'rx',
		'ry',
		'x',
		'y',
		'x1',
		'y1',
		'x2',
		'y2',
		'points',
		// Gradient attributes.
		'offset',
		'stop-color',
		'stop-opacity',
		'gradientUnits',
		'gradientTransform',
		'spreadMethod',
		'fx',
		'fy',
		// Internal reference attributes (no external URLs).
		'clip-path',
		'mask',
		// Accessibility.
		'role',
		'aria-hidden',
		'aria-label',
		'aria-labelledby',
		'focusable',
	];

	/**
	 * Get SVG content from attachment ID.
	 *
	 * @param int  $attachment_id Attachment ID.
	 * @param bool $use_current_color Whether to replace fill colors with currentColor.
	 * @return string|false SVG content or false on failure.
	 */
	public static function get_svg_from_attachment( $attachment_id, $use_current_color = true ) {
		// Ensure attachment_id is integer.
		$attachment_id = absint( $attachment_id );

		if ( empty( $attachment_id ) ) {
			return false;
		}

		// Check cache.
		$cache_key = $attachment_id . '_' . ( $use_current_color ? '1' : '0' );
		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}

		// Check if it's an SVG.
		$mime_type = get_post_mime_type( $attachment_id );
		if ( 'image/svg+xml' !== $mime_type ) {
			return false;
		}

		// Get attachment file path.
		$file_path = get_attached_file( $attachment_id );
		if ( ! $file_path ) {
			return false;
		}

		// Read SVG content.
		$svg_content = self::read_svg_file( $file_path );
		if ( ! $svg_content ) {
			return false;
		}

		// Parse and sanitize.
		$result = self::parse_svg( $svg_content, $use_current_color );

		// Cache result.
		self::$cache[ $cache_key ] = $result;

		return $result;
	}

	/**
	 * Parse and sanitize SVG content.
	 *
	 * @param string $svg_content Raw SVG content.
	 * @param bool   $use_current_color Whether to replace fill colors with currentColor.
	 * @return string|false Sanitized SVG content or false on failure.
	 */
	private static function parse_svg( $svg_content, $use_current_color = true ) {
		if ( empty( $svg_content ) ) {
			return false;
		}

		// Remove XML declaration and doctype.
		$svg_content = preg_replace( '/<\?xml[^>]*\?>/i', '', $svg_content );
		$svg_content = preg_replace( '/<!DOCTYPE[^>]*>/i', '', $svg_content );

		// Remove comments.
		$svg_content = preg_replace( '/<!--.*?-->/s', '', $svg_content );

		// Extract SVG element.
		if ( ! preg_match( '/<svg[^>]*>.*?<\/svg>/is', $svg_content, $matches ) ) {
			return false;
		}

		$svg_content = $matches[0];

		// Load into DOMDocument for parsing.
		$dom                     = new \DOMDocument();
		$dom->formatOutput       = false; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$dom->preserveWhiteSpace = false; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		// Suppress errors for malformed SVG.
		libxml_use_internal_errors( true );
		$loaded = $dom->loadXML( $svg_content );
		libxml_clear_errors();

		if ( ! $loaded ) {
			return false;
		}

		// Get the SVG element.
		$svg_elements = $dom->getElementsByTagName( 'svg' );
		if ( 0 === $svg_elements->length ) {
			return false;
		}

		$svg = $svg_elements->item( 0 );

		// Sanitize the SVG.
		self::sanitize_svg_element( $svg, $use_current_color );

		// Add accessibility attributes.
		$svg->setAttribute( 'aria-hidden', 'true' );
		$svg->setAttribute( 'focusable', 'false' );

		// Ensure fill is currentColor if not set.
		if ( $use_current_color && ! $svg->hasAttribute( 'fill' ) ) {
			$svg->setAttribute( 'fill', 'currentColor' );
		}

		// Get the sanitized SVG.
		$result = $dom->saveXML( $svg );

		// Clean up the output.
		$result = trim( $result );

		return $result;
	}

	/**
	 * Sanitize a DOM element recursively.
	 *
	 * @param \DOMElement $element Element to sanitize.
	 * @param bool        $use_current_color Whether to replace fill colors with currentColor.
	 */
	private static function sanitize_svg_element( $element, $use_current_color = true ) {
		// Get all child nodes first (to avoid modification issues during iteration).
		$children = [];
		foreach ( $element->childNodes as $child ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$children[] = $child;
		}

		// Process child elements.
		foreach ( $children as $child ) {
			if ( $child instanceof \DOMElement ) {
				$child_tag = strtolower( $child->tagName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				if ( in_array( $child_tag, self::get_allowed_elements(), true ) ) {
					self::sanitize_svg_element( $child, $use_current_color );
				} else {
					// Remove disallowed elements.
					$element->removeChild( $child );
				}
			}
		}

		// Sanitize attributes.
		$attributes_to_remove = [];
		foreach ( $element->attributes as $attr ) {
			$attr_name = strtolower( $attr->name );

			// Check if attribute is allowed.
			if ( ! in_array( $attr_name, self::$allowed_attributes, true ) ) {
				$attributes_to_remove[] = $attr->name;
				continue;
			}

			// Remove javascript: URLs.
			if ( preg_match( '/javascript:/i', $attr->value ) ) {
				$attributes_to_remove[] = $attr->name;
				continue;
			}

			// Remove on* event handlers.
			if ( preg_match( '/^on/i', $attr_name ) ) {
				$attributes_to_remove[] = $attr->name;
				continue;
			}

			// Replace fill colors with currentColor.
			if ( $use_current_color && 'fill' === $attr_name ) {
				$fill_value = strtolower( trim( $attr->value ) );
				// Keep 'none' and 'url(...)' references (for gradients).
				if ( 'none' !== $fill_value && 0 !== strpos( $fill_value, 'url(' ) ) {
					$element->setAttribute( 'fill', 'currentColor' );
				}
			}

			// Replace stroke colors with currentColor.
			if ( $use_current_color && 'stroke' === $attr_name ) {
				$stroke_value = strtolower( trim( $attr->value ) );
				// Keep 'none' and 'url(...)' references.
				if ( 'none' !== $stroke_value && 0 !== strpos( $stroke_value, 'url(' ) ) {
					$element->setAttribute( 'stroke', 'currentColor' );
				}
			}
		}

		// Remove disallowed attributes.
		foreach ( $attributes_to_remove as $attr_name ) {
			$element->removeAttribute( $attr_name );
		}
	}

	/**
	 * Read SVG file content using WP_Filesystem.
	 *
	 * @param string $file_path File path.
	 * @return string|false File content or false on failure.
	 */
	private static function read_svg_file( $file_path ) {
		global $wp_filesystem;

		// Initialize WP_Filesystem if needed.
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		// Verify filesystem is available.
		if ( empty( $wp_filesystem ) ) {
			return false;
		}

		// Check if file exists and read it.
		if ( ! $wp_filesystem->exists( $file_path ) ) {
			return false;
		}

		return $wp_filesystem->get_contents( $file_path );
	}

	/**
	 * Clear the SVG cache.
	 */
	public static function clear_cache() {
		self::$cache = [];
	}
}
