<?php
/**
 * Separator Block - Server-side Render
 *
 * @package BlockFlow
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- $attributes, $content, $block are provided by WordPress block API.

use ThemeRuby\BlockFlow\HTML_Helpers;

$block_id = $attributes['blockId'] ?? '';

// Build wrapper classes.
$wrapper_classes = [ 'blockflow-block', 'blockflow-separator' ];

if ( $block_id ) {
	$wrapper_classes[] = 'blockflow-separator-' . $block_id;
}

$wrapper_classes = HTML_Helpers::build_wrapper_classes( $wrapper_classes, $attributes );

$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $wrapper_classes ),
	]
);

// Build all data attributes.
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 */
printf(
	'<%1$s %2$s%3$s><span class="blockflow-separator__line"></span></%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
