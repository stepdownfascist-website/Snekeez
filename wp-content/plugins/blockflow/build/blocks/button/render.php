<?php
/**
 * Button Block - Server-side Render
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

$block_id             = $attributes['blockId'] ?? '';
$text                 = $attributes['text'] ?? '';
$block_link           = $attributes['link'] ?? [];
$icon                 = $attributes['icon'] ?? [];
$anchor               = $attributes['anchor'] ?? '';
$hover_animation      = $attributes['hoverAnimation'] ?? '';
$icon_hover_animation = $attributes['iconHoverAnimation'] ?? '';

// Icon settings.
$icon_enabled  = ! empty( $icon['enabled'] );
$icon_source   = $icon['source'] ?? 'library';
$icon_name     = $icon['name'] ?? '';
$icon_position = $icon['position'] ?? 'before';
$custom_svg    = $icon['customSvg'] ?? [];

$use_uploaded_svg = 'upload' === $icon_source && ! empty( $custom_svg['url'] );
$has_icon         = $icon_enabled && ( $icon_name || $use_uploaded_svg );

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-button',
	'blockflow-button-' . esc_attr( $block_id ),
];


$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $classes ),
		'id'    => $anchor ? esc_attr( $anchor ) : null,
	]
);

// Build all data attributes.
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Build inner class with hover animation and icon position.
$inner_class = 'blockflow-button__inner';
if ( $has_icon ) {
	$inner_class .= ' blockflow-button--icon-' . esc_attr( $icon_position );
}
if ( $hover_animation ) {
	$inner_class .= ' blockflow-hover-' . esc_attr( $hover_animation );
}

// Build icon class with hover animation.
$icon_class = 'blockflow-button__icon';
if ( $icon_hover_animation ) {
	$icon_class .= ' blockflow-icon-hover-' . esc_attr( $icon_hover_animation );
}

// Build icon HTML - Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
$icon_html = '';
if ( $has_icon ) {
	$icon_html = Icon_Renderer::render(
		[
			'name'          => $icon_name,
			'customSvg'     => $use_uploaded_svg ? $custom_svg : [],
			'wrapper_class' => $icon_class,
		]
	);
}

// Build text content.
$text_html = sprintf(
	'<span class="blockflow-button__text">%s</span>',
	wp_kses( $text, HTML_Helpers::get_allowed_inline_html() )
);

// Build button content (icon before/after text).
// $icon_html from Icon_Renderer::render() uses wp_kses() internally.
$button_content = '';
if ( $has_icon && 'before' === $icon_position ) {
	$button_content .= $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
$button_content .= $text_html;
if ( $has_icon && 'after' === $icon_position ) {
	$button_content .= $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

// Build inner button element.
$inner_html = sprintf(
	'<span class="%s">%s</span>',
	esc_attr( $inner_class ),
	$button_content
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

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $link_attrs built from esc_url() and esc_attr() escaped values.
	$inner_html = sprintf( '<a %s>%s</a>', $link_attrs, $inner_html );
}

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $inner_html: Built from escaped components - esc_url()/esc_attr() for links,
 *   wp_kses() for button text, Icon_Renderer::render() for icons (uses wp_kses).
 */
printf(
	'<%1$s %2$s%3$s>%4$s</%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$inner_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
