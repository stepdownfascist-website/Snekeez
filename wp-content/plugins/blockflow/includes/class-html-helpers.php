<?php
/**
 * HTML Helpers for BlockFlow.
 *
 * Provides utility methods for building HTML attributes and elements.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML Helpers class.
 */
class HTML_Helpers {

	/**
	 * Get allowed SVG tags and attributes for wp_kses.
	 *
	 * @return array Allowed SVG elements and attributes.
	 */
	public static function get_allowed_svg() {
		return [
			'svg'            => [
				'xmlns'               => true,
				'xmlns:xlink'         => true,
				'viewbox'             => true,
				'fill'                => true,
				'stroke'              => true,
				'stroke-width'        => true,
				'stroke-linecap'      => true,
				'stroke-linejoin'     => true,
				'width'               => true,
				'height'              => true,
				'class'               => true,
				'aria-hidden'         => true,
				'focusable'           => true,
				'role'                => true,
				'preserveaspectratio' => true,
			],
			'g'              => [
				'fill'      => true,
				'transform' => true,
				'clip-path' => true,
				'mask'      => true,
				'id'        => true,
			],
			'path'           => [
				'd'               => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'fill-rule'       => true,
				'clip-rule'       => true,
				'transform'       => true,
			],
			'circle'         => [
				'cx'     => true,
				'cy'     => true,
				'r'      => true,
				'fill'   => true,
				'stroke' => true,
			],
			'ellipse'        => [
				'cx'     => true,
				'cy'     => true,
				'rx'     => true,
				'ry'     => true,
				'fill'   => true,
				'stroke' => true,
			],
			'rect'           => [
				'x'      => true,
				'y'      => true,
				'width'  => true,
				'height' => true,
				'rx'     => true,
				'ry'     => true,
				'fill'   => true,
				'stroke' => true,
			],
			'line'           => [
				'x1'           => true,
				'y1'           => true,
				'x2'           => true,
				'y2'           => true,
				'stroke'       => true,
				'stroke-width' => true,
			],
			'polyline'       => [
				'points' => true,
				'fill'   => true,
				'stroke' => true,
			],
			'polygon'        => [
				'points' => true,
				'fill'   => true,
				'stroke' => true,
			],
			'defs'           => [],
			'clippath'       => [
				'id' => true,
			],
			'mask'           => [
				'id' => true,
			],
			'use'            => [
				'x' => true,
				'y' => true,
			],
			'lineargradient' => [
				'id'                => true,
				'x1'                => true,
				'y1'                => true,
				'x2'                => true,
				'y2'                => true,
				'gradientunits'     => true,
				'gradienttransform' => true,
			],
			'radialgradient' => [
				'id'                => true,
				'cx'                => true,
				'cy'                => true,
				'r'                 => true,
				'fx'                => true,
				'fy'                => true,
				'gradientunits'     => true,
				'gradienttransform' => true,
			],
			'stop'           => [
				'offset'       => true,
				'stop-color'   => true,
				'stop-opacity' => true,
			],
			'text'           => [
				'x'         => true,
				'y'         => true,
				'fill'      => true,
				'transform' => true,
				'class'     => true,
			],
			'tspan'          => [
				'x'    => true,
				'y'    => true,
				'dx'   => true,
				'dy'   => true,
				'fill' => true,
			],
			'symbol'         => [
				'id'      => true,
				'viewbox' => true,
			],
		];
	}

	/**
	 * Get allowed SVG element names.
	 *
	 * @return array List of allowed SVG element tag names.
	 */
	public static function get_allowed_svg_elements() {
		return array_keys( self::get_allowed_svg() );
	}

	/**
	 * Get allowed inline HTML tags for text content.
	 *
	 * Used with wp_kses for RichText content (headings, text blocks, etc.).
	 *
	 * @return array Allowed HTML tags and attributes.
	 */
	public static function get_allowed_inline_html() {
		return [
			'a'      => [
				'href'   => true,
				'title'  => true,
				'target' => true,
				'rel'    => true,
			],
			'br'     => [],
			'em'     => [],
			'strong' => [],
			'b'      => [],
			'i'      => [],
			's'      => [],
			'del'    => [],
			'sub'    => [],
			'sup'    => [],
			'span'   => [
				'class' => true,
				'style' => true,
			],
		];
	}

	/**
	 * Build wrapper classes with standard class appends.
	 *
	 * Appends className, responsive visibility classes, and animation class
	 * to the provided base classes array.
	 *
	 * @param array $base_classes Initial classes array.
	 * @param array $attributes   Block attributes.
	 * @return array Classes array with standard classes appended.
	 */
	public static function build_wrapper_classes( array $base_classes, array $attributes ): array {
		$class_name            = $attributes['className'] ?? '';
		$responsive_visibility = $attributes['responsiveVisibility'] ?? [];
		$animation             = $attributes['animation'] ?? [];

		if ( $class_name ) {
			$base_classes[] = esc_attr( $class_name );
		}

		// Responsive visibility classes.
		if ( ! empty( $responsive_visibility['hideOnDesktop'] ) ) {
			$base_classes[] = 'blockflow-hide-desktop';
		}
		if ( ! empty( $responsive_visibility['hideOnTablet'] ) ) {
			$base_classes[] = 'blockflow-hide-tablet';
		}
		if ( ! empty( $responsive_visibility['hideOnMobile'] ) ) {
			$base_classes[] = 'blockflow-hide-mobile';
		}

		// Animation class.
		if ( ! empty( $animation['type'] ) ) {
			$base_classes[] = 'blockflow-anim';
		}

		return $base_classes;
	}

	/**
	 * Build all data attributes HTML string.
	 *
	 * Combines animation, item animation, custom data attributes, and background image
	 * into a single HTML string. Always outputs on root element.
	 *
	 * For container/grid with inner target, pass $bg_target = 'inner'
	 * and JS will create placeholder in the correct element.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $bg_target  Where background applies: '' (default/outer) or 'inner'.
	 * @return string HTML attributes string.
	 */
	public static function build_data_attrs( array $attributes, string $bg_target = '' ): string {
		$attrs_html = '';

		// Block animation attributes.
		$animation      = $attributes['animation'] ?? [];
		$animation_type = $animation['type'] ?? '';
		if ( $animation_type ) {
			$attrs_html .= sprintf( ' data-animation="%s"', esc_attr( $animation_type ) );
			$attrs_html .= sprintf( ' data-animation-once="%s"', ! empty( $animation['once'] ) ? 'true' : 'false' );
			Asset_Loader::track_animation_usage();
		}

		// Item animation attributes (for repeater blocks).
		// JS finds items via .blockflow-animate-item class and applies delay by index.
		$item_animation = $attributes['itemAnimation'] ?? [];
		$item_anim_type = $item_animation['type'] ?? '';
		if ( $item_anim_type ) {
			$item_duration = $item_animation['duration'] ?? 600;
			$item_stagger  = $item_animation['stagger'] ?? 100;
			$attrs_html   .= sprintf( ' data-item-animation="%s"', esc_attr( $item_anim_type ) );
			$attrs_html   .= sprintf( ' data-item-duration="%s"', esc_attr( $item_duration ) );
			$attrs_html   .= sprintf( ' data-item-stagger="%s"', esc_attr( $item_stagger ) );
			Asset_Loader::track_animation_usage();
		}

		// Custom data-* attributes.
		$html_attributes   = $attributes['htmlAttributes'] ?? [];
		$custom_attributes = $html_attributes['customAttributes'] ?? [];
		if ( ! empty( $custom_attributes ) && is_array( $custom_attributes ) ) {
			foreach ( $custom_attributes as $attr ) {
				if ( ! empty( $attr['key'] ) ) {
					$attr_name  = sanitize_key( $attr['key'] );
					$attr_value = esc_attr( $attr['value'] ?? '' );
					if ( 0 === strpos( $attr_name, 'data-' ) ) {
						$attrs_html .= sprintf( ' %s="%s"', $attr_name, $attr_value );
					}
				}
			}
		}

		// Background image attributes for lazy loading.
		// All settings passed as data attributes to avoid CSS variable inheritance.
		$background   = $attributes['background'] ?? [];
		$bg_image     = $background['image'] ?? [];
		$bg_image_url = $bg_image['url'] ?? '';
		if ( $bg_image_url ) {
			$attrs_html .= sprintf( ' data-bg-image="%s"', esc_url( $bg_image_url ) );
			Asset_Loader::track_lazy_bg_usage();

			// Image settings (only output if different from defaults).
			$position = $bg_image['position'] ?? 'center center';
			if ( 'center center' !== $position ) {
				$attrs_html .= sprintf( ' data-bg-position="%s"', esc_attr( $position ) );
			}

			$size = $bg_image['size'] ?? 'cover';
			if ( 'cover' !== $size ) {
				$attrs_html .= sprintf( ' data-bg-size="%s"', esc_attr( $size ) );
			}

			$repeat = $bg_image['repeat'] ?? 'no-repeat';
			if ( 'no-repeat' !== $repeat ) {
				$attrs_html .= sprintf( ' data-bg-repeat="%s"', esc_attr( $repeat ) );
			}

			$attachment = $bg_image['attachment'] ?? 'scroll';
			if ( 'scroll' !== $attachment ) {
				$attrs_html .= sprintf( ' data-bg-attachment="%s"', esc_attr( $attachment ) );
			}

			$opacity = $bg_image['opacity'] ?? 1;
			if ( 1 !== (float) $opacity ) {
				$attrs_html .= sprintf( ' data-bg-opacity="%s"', esc_attr( $opacity ) );
			}

			// Target for placeholder (JS handles insertion).
			if ( 'inner' === $bg_target ) {
				$attrs_html .= ' data-bg-target="inner"';
			}
		}

		return $attrs_html;
	}

	/**
	 * Get item animation wrapper class.
	 *
	 * @param array $item_animation Item animation settings { type }.
	 * @return string Animation wrapper class or empty string.
	 */
	public static function get_item_animation_wrapper_class( array $item_animation ): string {
		$type = $item_animation['type'] ?? '';
		return $type ? 'blockflow-animate-items' : '';
	}

	/**
	 * Get item animation class for a single item.
	 *
	 * @param array $item_animation Item animation settings { type }.
	 * @return string Animation class or empty string.
	 */
	public static function get_item_animation_class( array $item_animation ): string {
		$type = $item_animation['type'] ?? '';
		return $type ? 'blockflow-animate-item' : '';
	}

	/**
	 * Get validated HTML tag for block wrapper.
	 *
	 * Returns a valid semantic HTML tag from attributes, falling back to 'div'.
	 * Allowed tags: div, section, article, aside, header, footer, main, nav.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $default    Default tag if not set (default: 'div').
	 * @return string Valid HTML tag.
	 */
	public static function get_html_tag( array $attributes, string $default = 'div' ): string {
		$allowed_tags = [ 'div', 'section', 'article', 'aside', 'header', 'footer', 'main', 'nav' ];
		$html_tag     = $attributes['htmlTag'] ?? $default;

		return in_array( $html_tag, $allowed_tags, true ) ? $html_tag : $default;
	}

	/**
	 * Build link attributes HTML string.
	 *
	 * @param array $link Link data { url, target, rel }.
	 * @return string HTML attributes string or empty.
	 */
	public static function build_link_attrs( $link ) {
		if ( empty( $link ) || ! is_array( $link ) ) {
			return '';
		}

		$url    = $link['url'] ?? '';
		$target = $link['target'] ?? '';
		$rel    = $link['rel'] ?? '';

		if ( empty( $url ) ) {
			return '';
		}

		$attrs = 'href="' . esc_url( $url ) . '"';

		if ( $target ) {
			$attrs .= ' target="' . esc_attr( $target ) . '"';
		}

		// Add noopener for _blank target.
		if ( '_blank' === $target ) {
			if ( empty( $rel ) ) {
				$rel = 'noopener noreferrer';
			} elseif ( false === strpos( $rel, 'noopener' ) ) {
				$rel = trim( $rel . ' noopener noreferrer' );
			}
		}

		if ( $rel ) {
			$attrs .= ' rel="' . esc_attr( $rel ) . '"';
		}

		return $attrs;
	}

	/**
	 * Build lightbox data attributes HTML string.
	 *
	 * @param array $lightbox       Lightbox settings from block attributes.
	 * @param bool  $include_arrows Whether to include arrow icon attributes (for gallery/carousel).
	 * @return string HTML attributes string.
	 */
	public static function build_lightbox_attrs( array $lightbox, bool $include_arrows = false ): string {
		$attrs_html = ' data-lightbox="true"';

		// Background color.
		$bg_color    = $lightbox['backgroundColor']['light'] ?? '';
		$attrs_html .= sprintf( ' data-lightbox-bg="%s"', esc_attr( $bg_color ? $bg_color : 'rgba(0, 0, 0, 0.9)' ) );

		// Icon color.
		$icon_color = $lightbox['iconColor']['light'] ?? '';
		if ( $icon_color ) {
			$attrs_html .= sprintf( ' data-lightbox-icon-color="%s"', esc_attr( $icon_color ) );
		}

		// Icon size.
		$icon_size_value = $lightbox['iconSize']['desktop']['value'] ?? '24';
		$icon_size_unit  = $lightbox['iconSize']['desktop']['unit'] ?? 'px';
		$attrs_html     .= sprintf( ' data-lightbox-icon-size="%s"', esc_attr( $icon_size_value . $icon_size_unit ) );

		// Icon border radius.
		$icon_radius      = $lightbox['iconBorderRadius']['desktop'] ?? [];
		$icon_radius_tl   = $icon_radius['topLeft'] ?? '';
		$icon_radius_tr   = $icon_radius['topRight'] ?? '';
		$icon_radius_br   = $icon_radius['bottomRight'] ?? '';
		$icon_radius_bl   = $icon_radius['bottomLeft'] ?? '';
		$icon_radius_unit = $icon_radius['unit'] ?? 'px';
		if ( $icon_radius_tl || $icon_radius_tr || $icon_radius_br || $icon_radius_bl ) {
			$icon_radius_value = ( $icon_radius_tl ? $icon_radius_tl : '0' ) . $icon_radius_unit . ' '
				. ( $icon_radius_tr ? $icon_radius_tr : '0' ) . $icon_radius_unit . ' '
				. ( $icon_radius_br ? $icon_radius_br : '0' ) . $icon_radius_unit . ' '
				. ( $icon_radius_bl ? $icon_radius_bl : '0' ) . $icon_radius_unit;
			$attrs_html       .= sprintf( ' data-lightbox-icon-radius="%s"', esc_attr( $icon_radius_value ) );
		}

		// Arrow icons (for gallery/carousel blocks).
		if ( $include_arrows ) {
			$arrow_prev_name = $lightbox['arrowIconPrev']['name'] ?? 'chevron-left';
			$arrow_next_name = $lightbox['arrowIconNext']['name'] ?? 'chevron-right';
			$arrow_prev_svg  = Icon_Renderer::render(
				[
					'name' => $arrow_prev_name,
					'wrap' => false,
				]
			);
			$arrow_next_svg  = Icon_Renderer::render(
				[
					'name' => $arrow_next_name,
					'wrap' => false,
				]
			);
			$attrs_html     .= sprintf( ' data-lightbox-arrow-prev="%s"', esc_attr( $arrow_prev_svg ) );
			$attrs_html     .= sprintf( ' data-lightbox-arrow-next="%s"', esc_attr( $arrow_next_svg ) );
		}

		// Show caption.
		$attrs_html .= sprintf( ' data-lightbox-show-caption="%s"', ! empty( $lightbox['showCaption'] ) ? 'true' : 'false' );

		return $attrs_html;
	}
}
