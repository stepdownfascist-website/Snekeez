<?php
/**
 * Team Member Block - Server-side Render
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
use ThemeRuby\BlockFlow\Icon_Data;
use ThemeRuby\BlockFlow\Icon_Renderer;

$block_id              = $attributes['blockId'] ?? '';
$anchor                = $attributes['anchor'] ?? '';
$class_name            = $attributes['className'] ?? '';
$layout                = $attributes['layout'] ?? 'card';
$image                 = $attributes['image'] ?? [];
$name                  = $attributes['name'] ?? '';
$name_tag              = $attributes['nameTag'] ?? 'h3';
$description           = $attributes['description'] ?? '';
$meta_text             = $attributes['meta'] ?? '';
$social_items          = $attributes['socialItems'] ?? [];
$social_view           = $attributes['socialView'] ?? 'default';
$social_shape          = $attributes['socialShape'] ?? 'rounded';
$social_hover_anim     = $attributes['socialHoverAnimation'] ?? '';
$animation             = $attributes['animation'] ?? [];
$responsive_visibility = $attributes['responsiveVisibility'] ?? [];

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-team-member',
	'blockflow-team-member-' . esc_attr( $block_id ),
	'is-layout-' . esc_attr( $layout ),
];

// Alignment (desktop default).
$alignment = $attributes['contentAlignment']['desktop'] ?? 'center';
if ( $alignment ) {
	$classes[] = 'is-align-' . esc_attr( $alignment );
}

$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $classes ),
		'id'    => $anchor ? esc_attr( $anchor ) : null,
	]
);

// Animation data attributes.
$animation_html = '';
if ( ! empty( $animation['type'] ) ) {
	$animation_html .= sprintf( ' data-animation="%s"', esc_attr( $animation['type'] ) );
	$animation_html .= sprintf( ' data-animation-once="%s"', ! empty( $animation['once'] ) ? 'true' : 'false' );
	Asset_Loader::track_animation_usage();
}

// Image classes.
$image_classes = [ 'blockflow-team-member__image' ];

// Build image HTML (only if has image).
$image_html = '';
if ( ! empty( $image['url'] ) ) {
	$image_html = sprintf(
		'<figure class="%s"><img src="%s" alt="%s" /></figure>',
		esc_attr( implode( ' ', $image_classes ) ),
		esc_url( $image['url'] ),
		esc_attr( $image['alt'] ?? '' )
	);
}

// Allowed tags for text fields.
$allowed_tags = [
	'strong' => [],
	'em'     => [],
	'span'   => [
		'class' => [],
		'style' => [],
	],
	'br'     => [],
	'a'      => [
		'href'   => [],
		'target' => [],
		'rel'    => [],
	],
];

// Build name HTML.
$name_html = '';
if ( $name ) {
	$name_html = sprintf(
		'<%1$s class="blockflow-team-member__name">%2$s</%1$s>',
		tag_escape( $name_tag ),
		wp_kses( $name, $allowed_tags )
	);
}

// Build description HTML.
$desc_html = '';
if ( $description ) {
	$desc_html = sprintf(
		'<span class="blockflow-team-member__desc">%s</span>',
		wp_kses( $description, $allowed_tags )
	);
}

// Build meta HTML.
$meta_html = '';
if ( $meta_text ) {
	$meta_html = sprintf(
		'<span class="blockflow-team-member__meta">%s</span>',
		wp_kses( $meta_text, $allowed_tags )
	);
}

// Build social icons HTML.
$social_html = '';
if ( ! empty( $social_items ) ) {
	$social_classes = [ 'blockflow-team-member__social' ];
	if ( $social_view ) {
		$social_classes[] = 'is-view-' . esc_attr( $social_view );
	}
	if ( $social_shape ) {
		$social_classes[] = 'is-shape-' . esc_attr( $social_shape );
	}

	$social_attrs = '';
	if ( $social_hover_anim ) {
		$social_attrs .= sprintf( ' data-hover-animation="%s"', esc_attr( $social_hover_anim ) );
	}

	// Global colors.
	$global_primary   = $attributes['socialPrimaryColor']['light'] ?? '';
	$global_secondary = $attributes['socialSecondaryColor']['light'] ?? '';

	$items_html = '';
	foreach ( $social_items as $item ) {
		$platform = $item['platform'] ?? '';
		$url      = $item['link']['url'] ?? '';
		$target   = $item['link']['target'] ?? '_blank';
		$rel      = $item['link']['rel'] ?? 'noopener noreferrer';

		if ( ! $platform ) {
			continue;
		}

		// Get icon data.
		$icon_data = Icon_Data::find( $platform );
		if ( ! $icon_data ) {
			continue;
		}

		// Get brand color for this platform (fallback to black).
		$brand_color = $icon_data['color'] ?? '#000000';

		// Determine colors based on color mode.
		// Priority: item custom > global > brand color.
		$is_custom      = 'custom' === ( $item['colorMode'] ?? 'official' );
		$item_primary   = $is_custom && ! empty( $item['primaryColor']['light'] ) ? $item['primaryColor']['light'] : '';
		$item_secondary = $is_custom && ! empty( $item['secondaryColor']['light'] ) ? $item['secondaryColor']['light'] : '';

		// Resolved colors with fallback chain.
		$primary_color   = $item_primary ? $item_primary : ( $global_primary ? $global_primary : $brand_color );
		$secondary_color = $item_secondary ? $item_secondary : ( $global_secondary ? $global_secondary : $brand_color );

		// Build item styles based on view mode.
		// NOTE: Per-item colors must be inline styles (not in CSS Generator) because each social
		// icon can have different colors based on platform brand color and custom overrides.
		// CSS Generator handles block-level styles that apply uniformly to all social items.
		$item_style_parts = [];

		if ( 'stacked' === $social_view ) {
			// Stacked: colored background, white icon (unless custom/global primary set).
			$item_style_parts[] = '--blockflow-tm-social-bg:' . $secondary_color;
			$icon_color         = $item_primary ? $item_primary : ( $global_primary ? $global_primary : '#ffffff' );
			$item_style_parts[] = '--blockflow-tm-social-color:' . $icon_color;
		} elseif ( 'framed' === $social_view ) {
			// Framed: icon/border color, transparent background (unless custom/global secondary set).
			$item_style_parts[] = '--blockflow-tm-social-color:' . $primary_color;
			$bg_color           = $item_secondary ? $item_secondary : $global_secondary;
			if ( $bg_color ) {
				$item_style_parts[] = '--blockflow-tm-social-bg:' . $bg_color;
			}
		} else {
			// Default: icon color only.
			$item_style_parts[] = '--blockflow-tm-social-color:' . $primary_color;
		}

		$item_style_attr = '';
		if ( ! empty( $item_style_parts ) ) {
			$item_style_attr = ' style="' . esc_attr( implode( ';', $item_style_parts ) ) . '"';
		}

		// Build SVG using Icon_Renderer.
		// Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
		$svg_html = Icon_Renderer::render(
			[
				'name'        => $platform,
				'wrap'        => false,
				'aria_hidden' => true,
			]
		);

		// Build link or span.
		if ( $url ) {
			$link_attrs = sprintf( 'href="%s"', esc_url( $url ) );
			if ( $target ) {
				$link_attrs .= sprintf( ' target="%s"', esc_attr( $target ) );
			}
			if ( $rel ) {
				$link_attrs .= sprintf( ' rel="%s"', esc_attr( $rel ) );
			}
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $link_attrs, $item_style_attr from esc_url()/esc_attr(), $svg_html from Icon_Renderer (wp_kses).
			$items_html .= sprintf(
				'<a class="blockflow-team-member__social-item"%s %s aria-label="%s">%s</a>',
				$item_style_attr,
				$link_attrs,
				esc_attr( ucfirst( $platform ) ),
				$svg_html
			);
		} else {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $item_style_attr from esc_attr(), $svg_html from Icon_Renderer (wp_kses).
			$items_html .= sprintf(
				'<span class="blockflow-team-member__social-item"%s aria-label="%s">%s</span>',
				$item_style_attr,
				esc_attr( ucfirst( $platform ) ),
				$svg_html
			);
		}
	}

	if ( $items_html ) {
		$social_html = sprintf(
			'<div class="%s"%s>%s</div>',
			esc_attr( implode( ' ', $social_classes ) ),
			$social_attrs,
			$items_html
		);
	}
}

// Build content HTML.
$content_html = '';
if ( $name_html || $desc_html || $meta_html ) {
	$content_html = sprintf(
		'<div class="blockflow-team-member__content">%s%s%s</div>',
		$name_html,
		$desc_html,
		$meta_html
	);
}

// Build inner wrapper HTML (contains content + social).
$inner_html = '';
if ( $content_html || $social_html ) {
	$inner_html = sprintf(
		'<div class="blockflow-team-member__inner">%s%s</div>',
		$content_html,
		$social_html
	);
}

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $animation_html: Built from sprintf() with esc_attr() values.
 * - $image_html: Built with esc_url()/esc_attr() for images.
 * - $inner_html: Built from escaped components - esc_attr() for classes/styles,
 *   wp_kses() for text, esc_url()/esc_attr() for social links, SVG paths via esc_attr().
 */
printf(
	'<%1$s %2$s%3$s>%4$s%5$s</%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$animation_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$image_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$inner_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
