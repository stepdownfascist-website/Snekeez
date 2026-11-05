<?php
/**
 * Icon Set Block - Server Side Render
 *
 * CSS is generated during save-time and stored in post meta.
 * This file only handles HTML output.
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
$link_mode       = $attributes['linkMode'] ?? 'all';
$shape           = $attributes['shape'] ?? 'square';
$block_link      = $attributes['link'] ?? [];
$hover_animation = $attributes['hoverAnimation'] ?? '';
$item_animation  = $attributes['itemAnimation'] ?? [];
$anchor          = $attributes['anchor'] ?? '';

// Link URL for "all" mode check.
$link_url = $block_link['url'] ?? '';

// Check if in individual mode.
$is_individual_mode = 'individual' === $link_mode;

// Build wrapper classes.
$wrapper_classes = [
	'blockflow-block',
	'blockflow-icon-set',
	'blockflow-icon-set-' . $block_id,
];

// Shape class (only circle needs special CSS).
if ( 'circle' === $shape ) {
	$wrapper_classes[] = 'blockflow-icon-set--circle';
}

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

// Item animation class for each item.
$item_anim_class = HTML_Helpers::get_item_animation_class( $item_animation );

// Build icons HTML.
$icons_html = '';
foreach ( $items as $item ) {
	// Extract icon properties from grouped object.
	$icon_data   = $item['icon'] ?? [];
	$icon_source = $icon_data['source'] ?? 'library';
	$icon_name   = $icon_data['name'] ?? '';
	$custom_svg  = $icon_data['customSvg'] ?? [];
	$item_link   = $item['link'] ?? [];

	// Build item class.
	$item_classes = [ 'blockflow-icon-set__item' ];

	// In individual mode, apply hover animation to items with links.
	if ( $is_individual_mode && $hover_animation && ! empty( $item_link['url'] ) ) {
		$item_classes[] = 'blockflow-hover-' . esc_attr( $hover_animation );
	}

	// Add animation class.
	if ( $item_anim_class ) {
		$item_classes[] = $item_anim_class;
	}

	$item_class = implode( ' ', $item_classes );

	// Render icon using Icon_Renderer (without wrapper).
	// Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
	$icon_svg = Icon_Renderer::render(
		[
			'name'      => $icon_name,
			'customSvg' => 'upload' === $icon_source ? $custom_svg : [],
			'wrap'      => false,
		]
	);

	if ( empty( $icon_svg ) ) {
		continue;
	}

	// Build item HTML.
	$icon_html = sprintf(
		'<span class="%s">%s</span>',
		esc_attr( $item_class ),
		$icon_svg // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer::render() uses wp_kses() internally.
	);

	// Wrap with individual link if in individual mode.
	// $icon_html contains Icon_Renderer output (wp_kses sanitized).
	if ( $is_individual_mode && ! empty( $item_link['url'] ) ) {
		$link_attrs  = HTML_Helpers::build_link_attrs( $item_link );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $link_attrs from HTML_Helpers, $icon_html from Icon_Renderer (wp_kses).
		$icons_html .= '<a ' . $link_attrs . '>' . $icon_html . '</a>';
	} else {
		$icons_html .= $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer uses wp_kses.
	}
}

// Build inner wrapper class for hover animation (only in "all" mode).
$inner_classes = [ 'blockflow-icon-set__inner' ];
if ( ! $is_individual_mode && $hover_animation ) {
	$inner_classes[] = 'blockflow-hover-' . esc_attr( $hover_animation );
}
$item_anim_wrapper_class = HTML_Helpers::get_item_animation_wrapper_class( $item_animation );
if ( $item_anim_wrapper_class ) {
	$inner_classes[] = $item_anim_wrapper_class;
}
$inner_class = implode( ' ', $inner_classes );

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

// Build inner content - in "all" mode, wrap all icons with single link.
$inner_content = $icons_html;
if ( ! $is_individual_mode && $link_url ) {
	$link_attrs    = HTML_Helpers::build_link_attrs( $block_link );
	$inner_content = sprintf( '<a %s>%s</a>', $link_attrs, $icons_html );
}

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $inner_content: Built from escaped components - Icon_Renderer::render() uses wp_kses(),
 *   HTML_Helpers::build_link_attrs() for links.
 */
printf(
	'<%1$s %2$s%3$s><span class="%4$s">%5$s</span></%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	esc_attr( $inner_class ),
	$inner_content // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
