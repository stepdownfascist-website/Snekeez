<?php
/**
 * Google Map Block - Server-side Render
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

$block_id = $attributes['blockId'] ?? '';
$location = $attributes['location'] ?? '';
$zoom     = $attributes['zoom'] ?? 14;
$api_key  = $attributes['apiKey'] ?? [];
$height   = $attributes['height'] ?? [];
$anchor   = $attributes['anchor'] ?? '';

// Don't render if no location.
if ( empty( $location ) ) {
	return;
}

// Build embed URL.
$encoded_location = rawurlencode( $location );
$api_key_enabled  = $api_key['enabled'] ?? false;
$api_key_value    = $api_key['key'] ?? '';

if ( $api_key_enabled && ! empty( $api_key_value ) ) {
	$embed_url = sprintf(
		'https://www.google.com/maps/embed/v1/place?key=%s&q=%s&zoom=%d',
		esc_attr( $api_key_value ),
		$encoded_location,
		absint( $zoom )
	);
} else {
	$embed_url = sprintf(
		'https://maps.google.com/maps?q=%s&z=%d&output=embed',
		$encoded_location,
		absint( $zoom )
	);
}

// Build all data attributes.
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-google-map',
	'blockflow-google-map-' . esc_attr( $block_id ),
];

// Add standard classes (className, visibility, animation).
$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $classes ),
		'id'    => $anchor ? esc_attr( $anchor ) : null,
	]
);

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

// Build inner HTML.
$inner_html = sprintf(
	'<div class="blockflow-google-map__wrapper"><iframe class="blockflow-google-map__iframe" src="%s" title="%s" frameborder="0" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>',
	esc_url( $embed_url ),
	esc_attr__( 'Google Map', 'blockflow' )
);

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $inner_html: Built from escaped components - esc_url() for iframe src,
 *   esc_attr() for iframe attributes.
 */
printf(
	'<%1$s %2$s%3$s>%4$s</%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$inner_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
