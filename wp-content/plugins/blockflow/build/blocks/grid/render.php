<?php
/**
 * Grid Block - Server-side Render
 *
 * @package BlockFlow
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content (InnerBlocks).
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- $attributes, $content, $block are provided by WordPress block API.

use ThemeRuby\BlockFlow\HTML_Helpers;

$block_id       = $attributes['blockId'] ?? '';
$html_tag       = $attributes['htmlTag'] ?? 'div';
$container_type = $attributes['containerType'] ?? 'full-width';
$content_target = $attributes['contentTarget'] ?? 'outer';
$anchor         = $attributes['anchor'] ?? '';
$align          = $attributes['align'] ?? '';
$sticky         = $attributes['sticky'] ?? [];

// Validate HTML tag.
$allowed_tags = [ 'div', 'section', 'article', 'aside', 'header', 'footer', 'main', 'nav' ];
$html_tag     = in_array( $html_tag, $allowed_tags, true ) ? $html_tag : 'div';

$is_boxed        = 'boxed' === $container_type;
$styles_on_outer = ! $is_boxed || 'outer' === $content_target;

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-grid',
	'blockflow-grid--' . esc_attr( $container_type ),
];

if ( ! empty( $block_id ) ) {
	$classes[] = 'blockflow-grid-' . esc_attr( $block_id );
}

if ( $align ) {
	$classes[] = 'align' . esc_attr( $align );
}

$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

if ( $styles_on_outer ) {
	$classes[] = 'blockflow-grid--styled';
}

if ( ! empty( $sticky['enabled'] ) ) {
	$classes[] = 'blockflow-grid--sticky';
}

$wrapper_attrs = [
	'class' => implode( ' ', $classes ),
];

if ( $anchor ) {
	$wrapper_attrs['id'] = esc_attr( $anchor );
}

$wrapper_attributes = get_block_wrapper_attributes( $wrapper_attrs );

// Build data attributes. Pass 'inner' when background targets inner element.
$bg_target       = ( $is_boxed && ! $styles_on_outer ) ? 'inner' : '';
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes, $bg_target );

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $content: WordPress InnerBlocks content, pre-rendered and escaped by block renderer.
 */
if ( $is_boxed ) {
	$inner_classes = [ 'blockflow-grid__inner' ];
	if ( ! $styles_on_outer ) {
		$inner_classes[] = 'blockflow-grid--styled';
	}

	printf(
		'<%1$s %2$s%3$s><div class="%4$s">%5$s</div></%1$s>',
		tag_escape( $html_tag ),
		$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
		$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
		esc_attr( implode( ' ', $inner_classes ) ),
		$content // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	);
} else {
	printf(
		'<%1$s %2$s%3$s>%4$s</%1$s>',
		tag_escape( $html_tag ),
		$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
		$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
		$content // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	);
}
