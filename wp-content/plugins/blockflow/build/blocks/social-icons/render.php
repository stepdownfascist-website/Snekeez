<?php
/**
 * Social Icons Block - Server-side Render
 *
 * CSS is generated via CSS_Generator_Service (save-time) using Social_Icons_CSS class.
 * This file only handles HTML output. Per-item colors use inline styles as they vary per item.
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
use ThemeRuby\BlockFlow\Icon_Data;
use ThemeRuby\BlockFlow\Icon_Renderer;

$block_id        = $attributes['blockId'] ?? '';
$items           = $attributes['items'] ?? [];
$view            = $attributes['view'] ?? 'default';
$shape           = $attributes['shape'] ?? 'rounded';
$hover_animation = $attributes['hoverAnimation'] ?? '';
$item_animation  = $attributes['itemAnimation'] ?? [];
$anchor          = $attributes['anchor'] ?? '';

// Check if styled view (stacked or framed).
$is_styled_view = in_array( $view, [ 'stacked', 'framed' ], true );

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-social-icons',
	'blockflow-social-icons-' . esc_attr( $block_id ),
];

// Add view class (stacked, framed).
if ( 'default' !== $view ) {
	$classes[] = 'blockflow-social-icons--' . esc_attr( $view );
}

// Add shape class (rounded, circle) - only for styled views.
if ( $is_styled_view && 'square' !== $shape ) {
	$classes[] = 'blockflow-social-icons--' . esc_attr( $shape );
}

// Add standard classes (className, visibility, animation).
// Note: Hover animation is applied to each item, not wrapper.
$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $classes ),
		'id'    => $anchor ? esc_attr( $anchor ) : null,
	]
);

// Build all data attributes.
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Global colors.
$global_primary_color   = $attributes['primaryColor']['light'] ?? '';
$global_secondary_color = $attributes['secondaryColor']['light'] ?? '';

// Item animation classes.
$item_anim_wrapper_class = HTML_Helpers::get_item_animation_wrapper_class( $item_animation );
$item_anim_class         = HTML_Helpers::get_item_animation_class( $item_animation );

// Build items output.
$items_html = '';

foreach ( $items as $item ) {
	$platform_id    = $item['platform'] ?? '';
	$item_link      = $item['link'] ?? [];
	$url            = $item_link['url'] ?? ( $item['url'] ?? '' ); // Fallback for old data.
	$target         = $item_link['target'] ?? '_blank';
	$rel            = $item_link['rel'] ?? 'noopener noreferrer';
	$color_mode     = $item['colorMode'] ?? 'official';
	$item_primary   = $item['primaryColor']['light'] ?? '';
	$item_secondary = $item['secondaryColor']['light'] ?? '';

	// Resolve icon data from icons.json at runtime.
	$icon_data   = Icon_Data::find( $platform_id );
	$brand_color = $icon_data['color'] ?? '#000000';

	// Skip items without platform icon data.
	if ( empty( $icon_data ) ) {
		continue;
	}

	// Determine primary color (icon color).
	// Priority: item custom > global > brand color.
	if ( 'custom' === $color_mode && $item_primary ) {
		$primary_color = $item_primary;
	} elseif ( $global_primary_color ) {
		$primary_color = $global_primary_color;
	} else {
		$primary_color = $brand_color;
	}

	// Determine secondary color (background color).
	// Priority: item custom > global > brand color.
	if ( 'custom' === $color_mode && $item_secondary ) {
		$secondary_color = $item_secondary;
	} elseif ( $global_secondary_color ) {
		$secondary_color = $global_secondary_color;
	} else {
		$secondary_color = $brand_color;
	}

	// Build SVG using Icon_Renderer.
	// Icon_Renderer resolves path/viewBox from icons.json via Icon_Data::find().
	// Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
	$svg = Icon_Renderer::render(
		[
			'name'        => $platform_id,
			'wrap'        => false,
			'aria_hidden' => true,
		]
	);

	// Build item styles - only per-item colors (which vary per item).
	// NOTE: Per-item colors must be inline styles (not in CSS Generator) because each social
	// icon can have different colors based on platform brand color, colorMode, and custom overrides.
	// CSS Generator handles block-level styles (size, padding, radius) that apply uniformly.
	$item_style = '';

	if ( 'stacked' === $view ) {
		// Stacked: background uses secondary, icon uses primary or white.
		$item_style .= '--blockflow-social-bg:' . esc_attr( $secondary_color ) . ';';
		if ( 'custom' === $color_mode && $item_primary ) {
			$item_style .= '--blockflow-social-color:' . esc_attr( $item_primary ) . ';';
		} elseif ( $global_primary_color ) {
			$item_style .= '--blockflow-social-color:' . esc_attr( $global_primary_color ) . ';';
		} else {
			// Default: white icon on colored background.
			$item_style .= '--blockflow-social-color:#ffffff;';
		}
	} elseif ( 'framed' === $view ) {
		// Framed: border and icon use primary, background uses secondary or transparent.
		$item_style .= '--blockflow-social-color:' . esc_attr( $primary_color ) . ';';
		$item_style .= '--blockflow-social-border-color:' . esc_attr( $primary_color ) . ';';
		if ( 'custom' === $color_mode && $item_secondary ) {
			$item_style .= '--blockflow-social-bg:' . esc_attr( $item_secondary ) . ';';
		} elseif ( $global_secondary_color ) {
			$item_style .= '--blockflow-social-bg:' . esc_attr( $global_secondary_color ) . ';';
		} else {
			$item_style .= '--blockflow-social-bg:transparent;';
		}
	} else {
		// Default view: just icon color.
		$item_style .= '--blockflow-social-color:' . esc_attr( $primary_color ) . ';';
	}

	// Build item class with hover and appear animation.
	$item_classes = [ 'blockflow-social-icons__item' ];
	if ( $hover_animation ) {
		$item_classes[] = 'blockflow-hover-' . esc_attr( $hover_animation );
	}
	if ( $item_anim_class ) {
		$item_classes[] = $item_anim_class;
	}
	$item_class = implode( ' ', $item_classes );

	// Build item HTML - $svg from Icon_Renderer::render() uses wp_kses() internally.
	$item_html = sprintf(
		'<span class="%s" style="%s">%s</span>',
		esc_attr( $item_class ),
		$item_style,
		$svg // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer::render() uses wp_kses() internally.
	);

	// Wrap with link if URL is set.
	if ( $url ) {
		$link_data  = [
			'url'    => $url,
			'target' => $target,
			'rel'    => $rel,
		];
		$link_attrs = HTML_Helpers::build_link_attrs( $link_data );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $link_attrs from HTML_Helpers::build_link_attrs() is pre-escaped.
		$item_html = sprintf( '<a %s>%s</a>', $link_attrs, $item_html );
	}

	$items_html .= $item_html;
}

// Build inner wrapper class.
$inner_classes = [ 'blockflow-social-icons__inner' ];
if ( $item_anim_wrapper_class ) {
	$inner_classes[] = $item_anim_wrapper_class;
}
$inner_class = implode( ' ', $inner_classes );

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $items_html: Built from escaped components - esc_attr() for classes/styles,
 *   Icon_Renderer::render() for SVGs (uses wp_kses), HTML_Helpers::build_link_attrs() for links.
 */
printf(
	'<%1$s %2$s%3$s><div class="%4$s">%5$s</div></%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	esc_attr( $inner_class ),
	$items_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
