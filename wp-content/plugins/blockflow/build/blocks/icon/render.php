<?php
/**
 * Icon Block - Server-side Render
 *
 * @package BlockFlow
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- $attributes, $content, $block are provided by WordPress block API.

use ThemeRuby\BlockFlow\HTML_Helpers;
use ThemeRuby\BlockFlow\Icon_Renderer;

$block_id = $attributes['blockId'] ?? '';

// Extract icon properties.
$icon_attr       = $attributes['icon'] ?? [];
$icon_source     = $icon_attr['source'] ?? 'library';
$icon_name       = $icon_attr['name'] ?? '';
$custom_svg      = $icon_attr['customSvg'] ?? [];
$view            = $attributes['view'] ?? 'default';
$shape           = $attributes['shape'] ?? 'square';
$block_link      = $attributes['link'] ?? [];
$anchor          = $attributes['anchor'] ?? '';
$hover_animation = $attributes['hoverAnimation'] ?? '';

$is_styled_view = in_array( $view, [ 'stacked', 'framed' ], true );

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-icon',
	'blockflow-icon-' . esc_attr( $block_id ),
];

if ( 'default' !== $view ) {
	$classes[] = 'blockflow-icon--' . esc_attr( $view );
}

if ( $is_styled_view && 'square' !== $shape ) {
	$classes[] = 'blockflow-icon--' . esc_attr( $shape );
}

$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $classes ),
		'id'    => $anchor ? esc_attr( $anchor ) : null,
	]
);

// Build all data attributes.
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Build inner class with hover animation.
$inner_class = 'blockflow-icon__inner';
if ( $hover_animation ) {
	$inner_class .= ' blockflow-hover-' . esc_attr( $hover_animation );
}

// Render icon - Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
$icon_html = Icon_Renderer::render(
	[
		'name'          => $icon_name,
		'customSvg'     => 'upload' === $icon_source ? $custom_svg : [],
		'wrapper_class' => $inner_class,
	]
);

// Wrap with link if set.
if ( ! empty( $block_link['url'] ) ) {
	$link_url    = esc_url( $block_link['url'] );
	$link_target = $block_link['target'] ?? '';
	$link_rel    = $block_link['rel'] ?? '';

	if ( '_blank' === $link_target && empty( $link_rel ) ) {
		$link_rel = 'noopener';
	} elseif ( '_blank' === $link_target && false === strpos( $link_rel, 'noopener' ) ) {
		$link_rel = trim( $link_rel . ' noopener' );
	}

	$link_attrs = sprintf( 'href="%s"', $link_url );
	if ( $link_target ) {
		$link_attrs .= sprintf( ' target="%s"', esc_attr( $link_target ) );
	}
	if ( $link_rel ) {
		$link_attrs .= sprintf( ' rel="%s"', esc_attr( $link_rel ) );
	}

	$custom_attrs = $block_link['customAttributes'] ?? [];
	if ( is_array( $custom_attrs ) && ! empty( $custom_attrs ) ) {
		foreach ( $custom_attrs as $attr ) {
			$attr_name  = isset( $attr['key'] ) ? sanitize_key( $attr['key'] ) : '';
			$attr_value = isset( $attr['value'] ) ? esc_attr( $attr['value'] ) : '';
			if ( $attr_name && ! in_array( $attr_name, [ 'href', 'target', 'rel' ], true ) ) {
				$link_attrs .= sprintf( ' %s="%s"', $attr_name, $attr_value );
			}
		}
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $link_attrs built from esc_url() and esc_attr() escaped values, $icon_html from Icon_Renderer::render() uses wp_kses().
	$icon_html = sprintf( '<a %s>%s</a>', $link_attrs, $icon_html );
}

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $icon_html: Built from escaped components - Icon_Renderer::render() uses wp_kses(),
 *   esc_url()/esc_attr() for links via HTML_Helpers::build_link_attrs().
 */
printf(
	'<%1$s %2$s%3$s>%4$s</%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$icon_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
