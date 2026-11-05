<?php
/**
 * Text Block - Server-side Render
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

$block_id     = $attributes['blockId'] ?? '';
$text_content = $attributes['content'] ?? '';
$html_tag     = $attributes['htmlTag'] ?? 'p';
$anchor       = $attributes['anchor'] ?? '';

// Validate HTML tag.
$allowed_tags = [ 'p', 'div', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];
if ( ! in_array( $html_tag, $allowed_tags, true ) ) {
	$html_tag = 'p';
}

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-text',
	'blockflow-text-' . esc_attr( $block_id ),
];
$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $classes ),
		'id'    => $anchor ? esc_attr( $anchor ) : null,
	]
);

// Build all data attributes (animation, custom, background image).
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Build text content with allowed HTML.
$text_html = wp_kses( $text_content, HTML_Helpers::get_allowed_inline_html() );

/*
 * Output - Escaping:
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $text_html: Escaped via wp_kses() with HTML_Helpers::get_allowed_inline_html().
 */
printf(
	'<div %1$s%2$s><%3$s class="blockflow-text__content">%4$s</%3$s></div>',
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	tag_escape( $html_tag ),
	$text_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
