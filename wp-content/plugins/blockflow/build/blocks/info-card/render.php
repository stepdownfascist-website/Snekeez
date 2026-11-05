<?php
/**
 * Info Card Block - Server-side Render
 *
 * CSS is generated during save-time and stored in post meta.
 * This file only handles HTML output.
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

$block_id    = $attributes['blockId'] ?? '';
$media_type  = $attributes['mediaType'] ?? 'icon';
$media_view  = $attributes['mediaView'] ?? 'default';
$media_shape = $attributes['mediaShape'] ?? 'square';

// Extract icon properties from grouped object.
$icon_attr   = $attributes['icon'] ?? [];
$icon_source = $icon_attr['source'] ?? 'library';
$icon_name   = $icon_attr['name'] ?? '';
$custom_svg  = $icon_attr['customSvg'] ?? [];

$media_image     = $attributes['mediaImage'] ?? [];
$card_title      = $attributes['title'] ?? '';
$title_tag       = $attributes['titleTag'] ?? 'h3';
$description     = $attributes['description'] ?? '';
$desc_tag        = $attributes['descTag'] ?? 'p';
$card_link       = $attributes['link'] ?? [];
$link_scope      = $attributes['linkScope'] ?? 'card';
$hover_animation = $attributes['hoverAnimation'] ?? '';
$anchor          = $attributes['anchor'] ?? '';
$cta_button      = $attributes['ctaButton'] ?? [];
$image_overlay   = $attributes['imageOverlay'] ?? [];

// Check if styled view.
$is_styled_view = in_array( $media_view, [ 'stacked', 'framed', 'custom' ], true );

// Build classes.
$classes = [
	'blockflow-block',
	'blockflow-info-card',
	'blockflow-info-card-' . esc_attr( $block_id ),
];

// Hover animation class (on root for box-shadow).
if ( $hover_animation ) {
	$classes[] = 'blockflow-hover-' . esc_attr( $hover_animation );
}

// Add standard classes (className, visibility, animation).
$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $classes ),
		'id'    => $anchor ? esc_attr( $anchor ) : null,
	]
);

// Build all data attributes.
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Build inner class.
$inner_class = 'blockflow-info-card__inner';

// Build media class.
$media_classes = [ 'blockflow-info-card__media' ];
if ( 'default' !== $media_view ) {
	$media_classes[] = 'blockflow-info-card__media--' . esc_attr( $media_view );
}
if ( $is_styled_view && 'square' !== $media_shape ) {
	$media_classes[] = 'blockflow-info-card__media--' . esc_attr( $media_shape );
}

// Render media HTML.
// Icon_Renderer resolves path/viewBox from icons.json via Icon_Data::find().
// Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
$media_html = '';
if ( 'icon' === $media_type ) {
	$media_html = Icon_Renderer::render(
		[
			'name'          => $icon_name,
			'customSvg'     => 'upload' === $icon_source ? $custom_svg : [],
			'wrapper_class' => 'blockflow-info-card__svg',
		]
	);
} elseif ( 'image' === $media_type && ! empty( $media_image['url'] ) ) {
	$overlay_gradient = $image_overlay['gradient']['light'] ?? '';
	$has_overlay      = ! empty( $overlay_gradient );

	if ( $has_overlay ) {
		$media_html = sprintf(
			'<div class="blockflow-info-card__img-wrapper"><img src="%s" alt="%s" class="blockflow-info-card__img" /><div class="blockflow-info-card__img-overlay"></div></div>',
			esc_url( $media_image['url'] ),
			esc_attr( $media_image['alt'] ?? '' )
		);
	} else {
		$media_html = sprintf(
			'<img src="%s" alt="%s" class="blockflow-info-card__img" />',
			esc_url( $media_image['url'] ),
			esc_attr( $media_image['alt'] ?? '' )
		);
	}
}

// Render title HTML.
$title_html = '';
if ( $card_title ) {
	$title_html = sprintf(
		'<%1$s class="blockflow-info-card__title">%2$s</%1$s>',
		tag_escape( $title_tag ),
		wp_kses( $card_title, HTML_Helpers::get_allowed_inline_html() )
	);
}

// Render description HTML.
$desc_html = '';
if ( $description ) {
	$desc_html = sprintf(
		'<%1$s class="blockflow-info-card__description">%2$s</%1$s>',
		tag_escape( $desc_tag ),
		wp_kses( $description, HTML_Helpers::get_allowed_inline_html() )
	);
}

// Render CTA button.
$button_html    = '';
$button_enabled = ! empty( $cta_button['enabled'] );
if ( $button_enabled ) {
	$button_text         = $cta_button['text'] ?? 'Learn More';
	$button_icon         = $cta_button['icon'] ?? [];
	$button_icon_enabled = ! empty( $button_icon['enabled'] );
	$button_icon_source  = $button_icon['source'] ?? 'library';
	$button_icon_name    = $button_icon['name'] ?? '';
	$button_icon_pos     = $button_icon['position'] ?? 'after';
	$button_custom_svg   = $button_icon['customSvg'] ?? [];

	// Build button icon HTML using Icon_Renderer.
	// Icon_Renderer resolves path/viewBox from icons.json via Icon_Data::find().
	// Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
	$button_icon_html = '';
	if ( $button_icon_enabled ) {
		$button_icon_html = Icon_Renderer::render(
			[
				'name'          => $button_icon_name,
				'customSvg'     => 'upload' === $button_icon_source ? $button_custom_svg : [],
				'wrapper_class' => 'blockflow-info-card__btn-icon',
			]
		);
	}

	// Build button content - $button_icon_html from Icon_Renderer::render() uses wp_kses() internally.
	$button_text_html = sprintf(
		'<span class="blockflow-info-card__btn-text">%s</span>',
		esc_html( $button_text )
	);

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $button_icon_html from Icon_Renderer::render() uses wp_kses() internally.
	$button_content = '';
	if ( 'before' === $button_icon_pos ) {
		$button_content = $button_icon_html . $button_text_html;
	} else {
		$button_content = $button_text_html . $button_icon_html;
	}

	// Button classes.
	$button_classes = [ 'blockflow-info-card__btn' ];
	if ( $button_icon_enabled && 'before' === $button_icon_pos ) {
		$button_classes[] = 'blockflow-info-card__btn--icon-before';
	}
	if ( $button_icon_enabled && 'after' === $button_icon_pos ) {
		$button_classes[] = 'blockflow-info-card__btn--icon-after';
	}

	// Check if link applies to button only.
	$has_link          = ! empty( $card_link['url'] );
	$link_apply_button = 'button' === $link_scope && $button_enabled;

	if ( $has_link && $link_apply_button ) {
		$link_url    = esc_url( $card_link['url'] );
		$link_target = $card_link['target'] ?? '';
		$link_rel    = $card_link['rel'] ?? '';

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

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $link_attrs built from esc_url() and esc_attr() escaped values.
		$button_html = sprintf(
			'<a %s class="%s">%s</a>',
			$link_attrs,
			esc_attr( implode( ' ', $button_classes ) ),
			$button_content
		);
	} else {
		$button_html = sprintf(
			'<span class="%s">%s</span>',
			esc_attr( implode( ' ', $button_classes ) ),
			$button_content
		);
	}
}

// Build content HTML (only if has content).
$content_html = '';
$has_content  = $title_html || $desc_html || $button_html;
if ( $has_content ) {
	$content_html = sprintf(
		'<div class="blockflow-info-card__content">%s%s%s</div>',
		$title_html,
		$desc_html,
		$button_html
	);
}

// Build media wrapper HTML (only if media exists).
// $media_html from Icon_Renderer::render() uses wp_kses() internally for icon type.
$media_wrapper_html = '';
if ( $media_html ) {
	$media_wrapper_html = sprintf(
		'<div class="%s">%s</div>',
		esc_attr( implode( ' ', $media_classes ) ),
		$media_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer::render() uses wp_kses() internally, images are escaped with esc_url/esc_attr.
	);
}

// Build inner HTML (only if has media or content).
$inner_html = '';
if ( $media_wrapper_html || $content_html ) {
	$inner_html = sprintf(
		'<div class="%s">%s%s</div>',
		esc_attr( $inner_class ),
		$media_wrapper_html,
		$content_html
	);
}

// Wrap with link if scope is card.
$has_link        = ! empty( $card_link['url'] );
$link_apply_card = 'card' === $link_scope || ( 'button' !== $link_scope && ! $button_enabled );

if ( $has_link && $link_apply_card ) {
	$link_url    = esc_url( $card_link['url'] );
	$link_target = $card_link['target'] ?? '';
	$link_rel    = $card_link['rel'] ?? '';

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

	// Custom attributes.
	$custom_attrs = $card_link['customAttributes'] ?? [];
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
	$inner_html = sprintf(
		'<a %s class="blockflow-info-card__link">%s</a>',
		$link_attrs,
		$inner_html
	);
}

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $inner_html: Built from escaped components - esc_url()/esc_attr() for images/links,
 *   wp_kses() for title/description, Icon_Renderer::render() for icons (uses wp_kses).
 */
printf(
	'<%1$s %2$s%3$s>%4$s</%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$inner_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
