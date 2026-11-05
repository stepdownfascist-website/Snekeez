<?php
/**
 * Image Block - Server-side Render
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

if ( empty( $block_id ) ) {
	return;
}

// Extract attributes.
$image         = $attributes['image'] ?? [];
$image_size    = $attributes['imageSize'] ?? 'full';
$lazy_load     = $attributes['lazyLoad'] ?? true;
$click_action  = $attributes['clickAction'] ?? 'none';
$link_settings = $attributes['link'] ?? [];
$lightbox      = $attributes['lightbox'] ?? [];
$image_mask    = $attributes['imageMask'] ?? [];
$hover_effect  = $attributes['hoverEffect'] ?? 'none';
$image_overlay = $attributes['imageOverlay'] ?? [];
$aspect_ratio  = $attributes['aspectRatio'] ?? [];
$anchor        = $attributes['anchor'] ?? '';

// Caption settings (new structure like carousel-images).
$caption_settings   = $attributes['caption'] ?? [];
$caption_display    = $caption_settings['display'] ?? 'none';
$caption_enabled    = 'none' !== $caption_display;
$caption_position   = $caption_settings['position'] ?? 'bottom-center';
$caption_visibility = $caption_settings['visibility'] ?? 'always';

// Check if we have an image.
$image_url = $image['url'] ?? '';
if ( empty( $image_url ) ) {
	return;
}

$image_alt = $image['alt'] ?? '';
$image_id  = $image['id'] ?? null;

// Get image dimensions.
$image_width  = null;
$image_height = null;

// Get proper image URL and dimensions based on size.
if ( $image_id ) {
	$sized_image = wp_get_attachment_image_src( $image_id, $image_size );
	if ( $sized_image ) {
		$image_url    = $sized_image[0];
		$image_width  = $sized_image[1];
		$image_height = $sized_image[2];
	}
} else {
	// Fallback to stored image dimensions.
	$image_width  = $image['width'] ?? null;
	$image_height = $image['height'] ?? null;
}

// Get full-size image URL for lightbox.
$full_image_url = $image_url;
if ( $image_id ) {
	$full_src = wp_get_attachment_image_src( $image_id, 'full' );
	if ( $full_src ) {
		$full_image_url = $full_src[0];
	}
}

// Get caption text from media library.
$caption_text = '';
if ( $caption_enabled && $image_id ) {
	$caption_text = wp_get_attachment_caption( $image_id );
}

// Mask settings.
$mask_shape = $image_mask['shape'] ?? 'none';
$is_masked  = 'none' !== $mask_shape;

// Overlay settings.
$has_overlay = ! empty( $image_overlay['gradient']['light'] );

// Built-in mask shapes.
$built_in_shapes  = [
	'circle',
	'ellipse',
	'rounded',
	'diamond',
	'hexagon',
	'octagon',
	'pentagon',
	'triangle',
	'star',
	'heart',
	'arch',
	'parallelogram',
	'blob1',
	'blob2',
	'blob3',
	'blob4',
	'blob5',
	'blob6',
	'blob7',
	'blob8',
	'blob9',
];
$is_built_in_mask = $is_masked && in_array( $mask_shape, $built_in_shapes, true );

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-image',
	'blockflow-image-' . esc_attr( $block_id ),
];

// Hover effect class - will be applied to wrapper element.
$hover_effect_class = '';
if ( $hover_effect && 'none' !== $hover_effect ) {
	$hover_effect_class = 'blockflow-img-hover-' . esc_attr( $hover_effect );
}

// Mask classes.
if ( $is_masked ) {
	$classes[] = 'blockflow-image--masked';
}
if ( $is_built_in_mask ) {
	$classes[] = 'blockflow-image--mask-' . esc_attr( $mask_shape );
}

// Aspect ratio class.
$aspect_ratio_enabled = ! empty( $aspect_ratio['enabled'] );
if ( $aspect_ratio_enabled ) {
	$classes[] = 'blockflow-image--has-ratio';
}

if ( $caption_enabled ) {
	$classes[] = 'blockflow-image--caption-' . esc_attr( $caption_display );
	// Position and visibility for overlay and bar modes.
	if ( 'overlay' === $caption_display || 'bar' === $caption_display ) {
		$classes[] = 'blockflow-image--caption-' . esc_attr( $caption_position );
		$classes[] = 'blockflow-image--caption-' . esc_attr( $caption_visibility );
	}
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

// Lightbox data attributes.
$lightbox_attrs_html = '';
if ( 'lightbox' === $click_action ) {
	$lightbox_attrs_html = HTML_Helpers::build_lightbox_attrs( $lightbox, false );
} elseif ( 'media' === $click_action ) {
	$lightbox_attrs_html = ' data-click-action="media"';
}

// Build image element.
$img_attrs = [
	'src'   => esc_url( $image_url ),
	'alt'   => esc_attr( $image_alt ),
	'class' => 'blockflow-image__img',
];

// Add width and height if available.
if ( $image_width ) {
	$img_attrs['width'] = absint( $image_width );
}
if ( $image_height ) {
	$img_attrs['height'] = absint( $image_height );
}

// Lazy loading.
if ( $lazy_load ) {
	$img_attrs['loading'] = 'lazy';
}

// Build img tag.
$img_attrs_html = '';
foreach ( $img_attrs as $attr_name => $attr_value ) {
	$img_attrs_html .= sprintf( ' %s="%s"', esc_attr( $attr_name ), $attr_value );
}

$img_html = sprintf( '<img%s />', $img_attrs_html );

// Build overlay element.
$overlay_html = '';
if ( $has_overlay ) {
	$overlay_html = '<div class="blockflow-image__overlay"></div>';
}

// Build inner wrapper content (figure with image and overlay).
$figure_content = $img_html . $overlay_html;

// Caption (overlay and bar modes - inside figure).
if ( $caption_enabled && $caption_text && ( 'overlay' === $caption_display || 'bar' === $caption_display ) ) {
	$figure_content .= sprintf(
		'<figcaption class="blockflow-image__caption">%s</figcaption>',
		wp_kses( $caption_text, HTML_Helpers::get_allowed_inline_html() )
	);
}

// Build wrapper classes.
$wrapper_classes = 'blockflow-image__wrapper';
if ( $hover_effect_class ) {
	$wrapper_classes .= ' ' . $hover_effect_class;
}

// Build wrapper data attributes for lightbox/media.
$wrapper_data = '';
if ( 'lightbox' === $click_action ) {
	$wrapper_data .= sprintf( ' data-src="%s"', esc_url( $full_image_url ) );
	$wrapper_data .= sprintf( ' data-caption="%s"', esc_attr( $caption_text ) );
} elseif ( 'media' === $click_action ) {
	$wrapper_data .= sprintf( ' data-href="%s"', esc_url( $full_image_url ) );
}

// Build wrapper HTML.
$wrapper_html = sprintf(
	'<div class="%s"%s><figure class="blockflow-image__media">%s</figure></div>',
	esc_attr( $wrapper_classes ),
	$wrapper_data,
	$figure_content
);

// Build final content.
$content_html = $wrapper_html;

// Wrap with link only if click action is 'link' and URL is set.
if ( 'link' === $click_action && ! empty( $link_settings['url'] ) ) {
	$link_url    = esc_url( $link_settings['url'] );
	$link_target = $link_settings['target'] ?? '';
	$link_rel    = $link_settings['rel'] ?? '';

	// Add noopener to rel if opening in new tab.
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
	$content_html = sprintf( '<a %s class="blockflow-image__link">%s</a>', $link_attrs, $wrapper_html );
}

// Caption (below mode - outside wrapper/link).
if ( $caption_enabled && $caption_text && 'below' === $caption_display ) {
	$content_html .= sprintf(
		'<figcaption class="blockflow-image__caption blockflow-image__caption--below">%s</figcaption>',
		wp_kses( $caption_text, HTML_Helpers::get_allowed_inline_html() )
	);
}

/*
 * Output - Escaping:
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $lightbox_attrs_html: Pre-escaped by HTML_Helpers::build_lightbox_attrs() using esc_attr().
 * - $content_html: Built from escaped components - esc_url()/esc_attr() for images,
 *   HTML_Helpers::build_link_attrs() for links, wp_kses() for captions.
 */
printf(
	'<figure %1$s%2$s%3$s>%4$s</figure>',
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$lightbox_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$content_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
