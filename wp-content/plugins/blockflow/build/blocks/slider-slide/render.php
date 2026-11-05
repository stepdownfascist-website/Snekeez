<?php
/**
 * Slider Slide Block - Server-side Render
 *
 * @package BlockFlow
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content (inner blocks).
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- $attributes, $content, $block are provided by WordPress block API.

use ThemeRuby\BlockFlow\HTML_Helpers;

$block_id   = $attributes['blockId'] ?? '';
$background = $attributes['background'] ?? [];

// Build class names.
$classes = [ 'swiper-slide', 'blockflow-slider-slide' ];
if ( $block_id ) {
	$classes[] = 'blockflow-slider-slide-' . esc_attr( $block_id );
}

// Check for background image (adds --styled class for placeholder).
$bg_type      = $background['type'] ?? 'none';
$bg_image_url = $background['image']['url'] ?? '';
if ( 'image' === $bg_type && $bg_image_url ) {
	$classes[] = 'blockflow-slider-slide--styled';
}

// Build data attributes (handles background image lazy loading).
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

/*
 * Output - Escaping:
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $content: WordPress InnerBlocks content, pre-rendered and escaped by block renderer.
 */
printf(
	'<div class="%s"%s><div class="blockflow-slider-slide__content">%s</div></div>',
	esc_attr( implode( ' ', $classes ) ),
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$content // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
