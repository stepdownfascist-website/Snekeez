<?php
/**
 * Brand Logos Block - Server-side Render
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

use ThemeRuby\BlockFlow\Asset_Loader;
use ThemeRuby\BlockFlow\HTML_Helpers;

$block_id              = $attributes['blockId'] ?? '';
$anchor                = $attributes['anchor'] ?? '';
$class_name            = $attributes['className'] ?? '';
$logos                 = $attributes['logos'] ?? [];
$display_mode          = $attributes['displayMode'] ?? 'slider';
$direction             = $attributes['direction'] ?? 'left';
$speed                 = $attributes['speed'] ?? 8000;
$pause_on_hover        = $attributes['pauseOnHover'] ?? true;
$hover_animation       = $attributes['hoverAnimation'] ?? '';
$animation             = $attributes['animation'] ?? [];
$item_animation        = $attributes['itemAnimation'] ?? [];
$responsive_visibility = $attributes['responsiveVisibility'] ?? [];
$background            = $attributes['background'] ?? [];

// Don't render if no logos.
if ( empty( $logos ) ) {
	return;
}

$is_slider = 'slider' === $display_mode;
$is_grid   = 'grid' === $display_mode;

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-brand-logos',
	'blockflow-brand-logos-' . esc_attr( $block_id ),
];

if ( $is_slider ) {
	$classes[] = 'blockflow-brand-logos--slider';
	$classes[] = 'blockflow-brand-logos--direction-' . esc_attr( $direction );
	if ( $pause_on_hover ) {
		$classes[] = 'blockflow-brand-logos--pause-hover';
	}
} else {
	$classes[] = 'blockflow-brand-logos--grid';
}

$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $classes ),
		'id'    => $anchor ? esc_attr( $anchor ) : null,
	]
);

// Build data attributes.
$data_attrs_html = '';

// Slider-specific data attributes.
if ( $is_slider ) {
	$data_attrs_html .= sprintf( ' data-speed="%s"', esc_attr( $speed ) );
	$data_attrs_html .= sprintf( ' data-direction="%s"', esc_attr( $direction ) );
	$data_attrs_html .= sprintf( ' data-pause-hover="%s"', $pause_on_hover ? 'true' : 'false' );

	// For slider mode, temporarily clear item animation to prevent output.
	$attrs_for_data                  = $attributes;
	$attrs_for_data['itemAnimation'] = [];
	$common_data                     = HTML_Helpers::build_data_attrs( $attrs_for_data );
} else {
	// Grid mode: include all data attributes.
	$common_data = HTML_Helpers::build_data_attrs( $attributes );
}

if ( $common_data ) {
	$data_attrs_html .= ' ' . $common_data;
}

// Hover animation class for items.
$hover_class = $hover_animation ? 'blockflow-hover-' . esc_attr( $hover_animation ) : '';

// Item animation classes (grid mode only).
$item_anim_wrapper_class = '';
$item_anim_class         = '';
if ( $is_grid && ! empty( $item_animation['type'] ) ) {
	$item_anim_wrapper_class = HTML_Helpers::get_item_animation_wrapper_class( $item_animation );
	$item_anim_class         = HTML_Helpers::get_item_animation_class( $item_animation );
}

// Build logos HTML.
$logos_html = '';
foreach ( $logos as $logo ) {
	$url  = $logo['url'] ?? '';
	$alt  = $logo['alt'] ?? '';
	$link = $logo['link'] ?? [];

	if ( ! $url ) {
		continue;
	}

	$item_classes = [ 'blockflow-brand-logos__item' ];
	if ( $hover_class ) {
		$item_classes[] = $hover_class;
	}
	if ( $is_grid && $item_anim_class ) {
		$item_classes[] = $item_anim_class;
	}
	$item_class = implode( ' ', $item_classes );

	$img_html = sprintf(
		'<img src="%s" alt="%s" loading="lazy" />',
		esc_url( $url ),
		esc_attr( $alt )
	);

	// Wrap image in logo div.
	$link_url = $link['url'] ?? '';
	if ( $link_url ) {
		$target    = ! empty( $link['target'] ) ? sprintf( ' target="%s"', esc_attr( $link['target'] ) ) : '';
		$rel       = ! empty( $link['rel'] ) ? sprintf( ' rel="%s"', esc_attr( $link['rel'] ) ) : '';
		$logo_html = sprintf(
			'<div class="blockflow-brand-logos__logo"><a href="%s"%s%s>%s</a></div>',
			esc_url( $link_url ),
			$target,
			$rel,
			$img_html
		);
	} else {
		$logo_html = sprintf(
			'<div class="blockflow-brand-logos__logo">%s</div>',
			$img_html
		);
	}

	$logos_html .= sprintf(
		'<div class="%s">%s</div>',
		esc_attr( $item_class ),
		$logo_html
	);
}

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $logos_html: Built from escaped components - esc_url()/esc_attr() for images,
 *   HTML_Helpers::build_link_attrs() for links.
 */
if ( $is_slider ) {
	// Slider mode - logos in track div with duplicated content for seamless loop.
	printf(
		'<%1$s %2$s%3$s>
			<div class="blockflow-brand-logos__track">
				<div class="blockflow-brand-logos__group">%4$s</div>
				<div class="blockflow-brand-logos__group" aria-hidden="true">%4$s</div>
			</div>
		</%1$s>',
		tag_escape( $html_tag ),
		$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
		$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
		$logos_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	);
} else {
	// Grid mode - logos in grid div.
	$grid_classes = [ 'blockflow-brand-logos__grid', 'blockflow-grid' ];
	if ( $item_anim_wrapper_class ) {
		$grid_classes[] = $item_anim_wrapper_class;
	}
	$grid_class = implode( ' ', $grid_classes );

	printf(
		'<%1$s %2$s%3$s><div class="%4$s">%5$s</div></%1$s>',
		tag_escape( $html_tag ),
		$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
		$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
		esc_attr( $grid_class ),
		$logos_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	);
}
