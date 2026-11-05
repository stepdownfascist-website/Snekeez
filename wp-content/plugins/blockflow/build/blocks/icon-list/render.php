<?php
/**
 * Icon List Block - Server Side Render
 *
 * @package BlockFlow
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- $attributes, $content, $block are provided by WordPress block API.

use ThemeRuby\BlockFlow\HTML_Helpers;
use ThemeRuby\BlockFlow\Icon_Renderer;

// Get block attributes.
$block_id        = $attributes['blockId'] ?? '';
$items           = $attributes['items'] ?? [];
$view            = $attributes['view'] ?? 'list';
$icon_view       = $attributes['iconView'] ?? 'default';
$icon_shape      = $attributes['iconShape'] ?? 'square';
$hover_animation = $attributes['hoverAnimation'] ?? '';
$item_animation  = $attributes['itemAnimation'] ?? [];
$anchor          = $attributes['anchor'] ?? '';

// Skip if no block ID.
if ( empty( $block_id ) ) {
	return;
}

// Determine if styled view (stacked, framed, or custom).
$is_styled_view = in_array( $icon_view, [ 'stacked', 'framed', 'custom' ], true );

// Build wrapper classes.
$wrapper_classes = [
	'blockflow-block',
	'blockflow-icon-list',
	'blockflow-icon-list-' . $block_id,
];

// View and shape classes (only for styled views).
if ( $is_styled_view ) {
	$wrapper_classes[] = 'blockflow-icon-list--view-' . $icon_view;
	$wrapper_classes[] = 'blockflow-icon-list--shape-' . $icon_shape;
}

// Items wrapper class (grid or list).
$items_classes = [ 'blockflow-icon-list__items' ];
if ( 'grid' === $view ) {
	$items_classes[] = 'blockflow-icon-list__items--grid';
	$items_classes[] = 'blockflow-grid'; // Shared grid class.
}
$item_anim_wrapper_class = HTML_Helpers::get_item_animation_wrapper_class( $item_animation );
if ( $item_anim_wrapper_class ) {
	$items_classes[] = $item_anim_wrapper_class;
}
$items_class = implode( ' ', $items_classes );

// Add standard classes (className, visibility, animation).
$wrapper_classes = HTML_Helpers::build_wrapper_classes( $wrapper_classes, $attributes );

// Build wrapper attributes.
$wrapper_attrs = [
	'class' => implode( ' ', $wrapper_classes ),
];

if ( $anchor ) {
	$wrapper_attrs['id'] = esc_attr( $anchor );
}

// Get wrapper attributes string.
$wrapper_attributes = get_block_wrapper_attributes( $wrapper_attrs );

// Build all data attributes.
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Build items HTML.
$items_html = '';
foreach ( $items as $item ) {
	// Extract icon properties from grouped object.
	$icon_data   = $item['icon'] ?? [];
	$icon_source = $icon_data['source'] ?? 'library';
	$icon_name   = $icon_data['name'] ?? '';
	$custom_svg  = $icon_data['customSvg'] ?? [];
	$item_text   = $item['text'] ?? '';

	// Per-item color overrides.
	$item_colors     = $item['colors'] ?? [];
	$item_icon_color = $item_colors['icon']['light'] ?? '';
	$item_icon_bg    = $item_colors['iconBg']['light'] ?? '';
	$item_text_color = $item_colors['text']['light'] ?? '';

	// Build inline style strings.
	// NOTE: Per-item colors must be inline styles (not in CSS Generator) because each item
	// in the items[] array can have different color values. CSS Generator only handles
	// block-level styles that apply uniformly to .blockflow-icon-list-{blockId}.
	$icon_style_parts = [];
	if ( $item_icon_color ) {
		$icon_style_parts[] = '--blockflow-icon-list-icon-color:' . esc_attr( $item_icon_color );
	}
	if ( $item_icon_bg ) {
		$icon_style_parts[] = '--blockflow-icon-list-icon-bg:' . esc_attr( $item_icon_bg );
	}
	$icon_style_attr = $icon_style_parts ? ' style="' . implode( ';', $icon_style_parts ) . '"' : '';

	$text_style_attr = $item_text_color
		? ' style="--blockflow-icon-list-text-color:' . esc_attr( $item_text_color ) . '"'
		: '';

	// Link data.
	$link_data    = $item['link'] ?? [];
	$link_url     = $link_data['url'] ?? '';
	$link_target  = $link_data['target'] ?? '';
	$link_rel     = $link_data['rel'] ?? '';
	$custom_attrs = $link_data['customAttributes'] ?? [];

	// Build item class.
	$item_classes = [ 'blockflow-icon-list__item' ];
	if ( $hover_animation ) {
		$item_classes[] = 'blockflow-hover-' . esc_attr( $hover_animation );
	}
	$item_anim_class = HTML_Helpers::get_item_animation_class( $item_animation );
	if ( $item_anim_class ) {
		$item_classes[] = $item_anim_class;
	}
	$item_class = implode( ' ', $item_classes );

	// Check if icon exists (library with name or upload with URL).
	$has_icon = ( 'library' === $icon_source && ! empty( $icon_name ) ) || ( 'upload' === $icon_source && ! empty( $custom_svg['url'] ) );

	// Build item content.
	$item_content = '';

	// Only render icon span if icon exists.
	if ( $has_icon ) {
		// Render icon using Icon_Renderer.
		// Icon_Renderer resolves path/viewBox from icons.json via Icon_Data::find().
		// Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
		$icon_html = Icon_Renderer::render(
			[
				'name'      => $icon_name,
				'customSvg' => 'upload' === $icon_source ? $custom_svg : [],
				'wrap'      => false,
			]
		);

		$item_content .= sprintf(
			'<span class="blockflow-icon-list__icon"%s>%s</span>',
			$icon_style_attr,
			$icon_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer::render() uses wp_kses() internally.
		);
	}

	$item_content .= sprintf(
		'<span class="blockflow-icon-list__text"%s>%s</span>',
		$text_style_attr,
		wp_kses( $item_text, HTML_Helpers::get_allowed_inline_html() )
	);

	// Build item HTML - use anchor if link URL exists.
	if ( ! empty( $link_url ) ) {
		$link_attrs = sprintf( 'href="%s"', esc_url( $link_url ) );
		if ( $link_target ) {
			$link_attrs .= sprintf( ' target="%s"', esc_attr( $link_target ) );
		}
		if ( $link_rel ) {
			$link_attrs .= sprintf( ' rel="%s"', esc_attr( $link_rel ) );
		} elseif ( '_blank' === $link_target ) {
			$link_attrs .= ' rel="noopener noreferrer"';
		}

		// Add custom attributes (only data-* for security).
		if ( ! empty( $custom_attrs ) && is_array( $custom_attrs ) ) {
			foreach ( $custom_attrs as $attr ) {
				$key   = $attr['key'] ?? '';
				$value = $attr['value'] ?? '';
				// Only allow data-* attributes for security.
				if ( $key && 0 === strpos( $key, 'data-' ) ) {
					$link_attrs .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
				}
			}
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $link_attrs from esc_url/esc_attr, $item_content contains Icon_Renderer output (wp_kses) and wp_kses text.
		$items_html .= sprintf(
			'<a class="%s" %s>%s</a>',
			esc_attr( $item_class ),
			$link_attrs,
			$item_content
		);
	} else {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $item_content contains Icon_Renderer output (wp_kses) and wp_kses text.
		$items_html .= sprintf(
			'<div class="%s">%s</div>',
			esc_attr( $item_class ),
			$item_content
		);
	}
}

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $items_html: Built from escaped components - esc_attr() for classes/styles,
 *   wp_kses() for text, Icon_Renderer::render() for SVGs (uses wp_kses),
 *   esc_url()/esc_attr() for links.
 */
printf(
	'<%1$s %2$s%3$s><div class="%4$s">%5$s</div></%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	esc_attr( $items_class ),
	$items_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
