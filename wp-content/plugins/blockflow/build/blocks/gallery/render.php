<?php
/**
 * Gallery Block - Server-side Render
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

$block_id              = $attributes['blockId'] ?? '';
$anchor                = $attributes['anchor'] ?? '';
$class_name            = $attributes['className'] ?? '';
$images                = $attributes['images'] ?? [];
$display_type          = $attributes['displayType'] ?? 'grid';
$image_size            = $attributes['imageSize'] ?? 'large';
$click_action          = $attributes['clickAction'] ?? 'lightbox';
$caption               = $attributes['caption'] ?? [];
$hover_effect          = $attributes['hoverEffect'] ?? 'zoom-in';
$lazy_load             = $attributes['lazyLoad'] ?? true;
$aspect_ratio          = $attributes['aspectRatio'] ?? [];
$lightbox              = $attributes['lightbox'] ?? [];
$item_animation        = $attributes['itemAnimation'] ?? [];
$animation             = $attributes['animation'] ?? [];
$responsive_visibility = $attributes['responsiveVisibility'] ?? [];

// Exit if no images.
if ( empty( $images ) ) {
	return;
}

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-gallery',
	'blockflow-gallery-' . esc_attr( $block_id ),
	'blockflow-gallery--' . esc_attr( $display_type ),
];

// Aspect ratio class (grid only).
if ( 'grid' === $display_type && ! empty( $aspect_ratio['enabled'] ) ) {
	$classes[] = 'blockflow-gallery--has-ratio';
}

// Hover effect class - will be applied to each item element.
$hover_effect_class = '';
if ( $hover_effect && 'none' !== $hover_effect ) {
	$hover_effect_class = 'blockflow-img-hover-' . esc_attr( $hover_effect );
}

// Caption classes.
$caption_display    = $caption['display'] ?? 'none';
$caption_visibility = $caption['visibility'] ?? 'show-hover';
$caption_position   = $caption['position'] ?? 'bottom-center';
$caption_alignment  = $caption['alignment'] ?? 'center';
$caption_enabled    = 'none' !== $caption_display;
$is_overlay_or_bar  = 'overlay' === $caption_display || 'bar' === $caption_display;

if ( $caption_enabled ) {
	$classes[] = 'blockflow-gallery--caption-display-' . esc_attr( $caption_display );

	// Visibility and position only for overlay/bar modes.
	if ( $is_overlay_or_bar ) {
		$classes[] = 'blockflow-gallery--caption-' . esc_attr( $caption_visibility );
		$classes[] = 'blockflow-gallery--caption-pos-' . esc_attr( $caption_position );
	}

	// Alignment only for below mode.
	if ( 'below' === $caption_display ) {
		$classes[] = 'blockflow-gallery--caption-align-' . esc_attr( $caption_alignment );
	}
}

$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $classes ),
		'id'    => $anchor ? esc_attr( $anchor ) : null,
	]
);

// Data attributes (animation, item animation, etc.).
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Lightbox data attributes.
$lightbox_attrs_html = '';
if ( 'lightbox' === $click_action ) {
	$lightbox_attrs_html = HTML_Helpers::build_lightbox_attrs( $lightbox, true );
}

// Item animation classes.
$item_anim_wrapper_class = HTML_Helpers::get_item_animation_wrapper_class( $item_animation );
$item_anim_class         = HTML_Helpers::get_item_animation_class( $item_animation );

// Build inner wrapper classes.
$inner_classes = [ 'blockflow-gallery__items' ];
if ( $item_anim_wrapper_class ) {
	$inner_classes[] = $item_anim_wrapper_class;
}
$inner_class_str = implode( ' ', $inner_classes );

if ( ! function_exists( 'blockflow_get_gallery_caption' ) ) {
	/**
	 * Get caption text from media library.
	 *
	 * @param int $image_id Image attachment ID.
	 * @return string Caption text.
	 */
	function blockflow_get_gallery_caption( $image_id ) {
		if ( ! $image_id ) {
			return '';
		}
		return wp_get_attachment_caption( $image_id );
	}
}

if ( ! function_exists( 'blockflow_get_gallery_image_url' ) ) {
	/**
	 * Get image URL for specific size.
	 *
	 * @param array  $image Image data.
	 * @param string $size  Image size.
	 * @return string Image URL.
	 */
	function blockflow_get_gallery_image_url( $image, $size = 'large' ) {
		$image_id = $image['id'] ?? 0;

		if ( $image_id ) {
			$src = wp_get_attachment_image_src( $image_id, $size );
			if ( $src ) {
				return $src[0];
			}
		}

		// Fallback to stored sizes or URL.
		if ( ! empty( $image['sizes'][ $size ]['url'] ) ) {
			return $image['sizes'][ $size ]['url'];
		}

		return $image['url'] ?? '';
	}
}

// Build items HTML.
$items_html = '';
$item_index = 0;

foreach ( $images as $image ) {
	$image_id    = $image['id'] ?? 0;
	$image_url   = blockflow_get_gallery_image_url( $image, $image_size );
	$image_full  = blockflow_get_gallery_image_url( $image, 'full' );
	$image_alt   = '';
	$caption_txt = '';

	// Get alt text.
	if ( $image_id ) {
		$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	}
	if ( ! $image_alt ) {
		$image_alt = $image['alt'] ?? '';
	}

	// Get caption text directly from media library.
	if ( $caption_enabled && $image_id ) {
		$caption_txt = blockflow_get_gallery_caption( $image_id );
	}

	// Build item class.
	$item_class = 'blockflow-gallery__item';
	if ( $hover_effect_class ) {
		$item_class .= ' ' . $hover_effect_class;
	}
	if ( $item_anim_class ) {
		$item_class .= ' ' . $item_anim_class;
	}

	// Build item data attributes for lightbox.
	$item_data = '';
	if ( 'lightbox' === $click_action ) {
		$item_data .= sprintf( ' data-src="%s"', esc_url( $image_full ) );
		$item_data .= sprintf( ' data-caption="%s"', esc_attr( $caption_txt ) );
		$item_data .= sprintf( ' data-index="%d"', $item_index );
	} elseif ( 'media' === $click_action ) {
		$item_data .= sprintf( ' data-href="%s"', esc_url( $image_full ) );
	}

	// Start item HTML.
	$item_html = sprintf(
		'<div class="%s"%s>',
		esc_attr( $item_class ),
		$item_data
	);

	// Check if this image has a custom link.
	$image_link  = $image['link'] ?? [];
	$link_url    = $image_link['url'] ?? '';
	$link_target = $image_link['target'] ?? '';
	$link_rel    = $image_link['rel'] ?? '';
	$has_link    = 'link' === $click_action && ! empty( $link_url );

	// Build link attributes.
	$link_attrs = '';
	if ( $has_link ) {
		$link_attrs .= sprintf( ' href="%s"', esc_url( $link_url ) );
		if ( $link_target ) {
			$link_attrs .= sprintf( ' target="%s"', esc_attr( $link_target ) );
		}
		if ( $link_rel ) {
			$link_attrs .= sprintf( ' rel="%s"', esc_attr( $link_rel ) );
		}
	}

	// Open link wrapper if has link.
	if ( $has_link ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $link_attrs built from esc_url() and esc_attr() escaped values.
		$item_html .= sprintf( '<a class="blockflow-gallery__link"%s>', $link_attrs );
	}

	// Figure element.
	$item_html .= '<figure class="blockflow-gallery__media">';

	// Image.
	$item_html .= sprintf(
		'<img src="%s" alt="%s" class="blockflow-gallery__image"%s />',
		esc_url( $image_url ),
		esc_attr( $image_alt ),
		$lazy_load ? ' loading="lazy"' : ''
	);

	// Overlay.
	$item_html .= '<div class="blockflow-gallery__overlay"></div>';

	// Caption for overlay/bar modes (inside figure).
	if ( $caption_enabled && $is_overlay_or_bar && $caption_txt ) {
		$item_html .= sprintf(
			'<figcaption class="blockflow-gallery__caption">%s</figcaption>',
			wp_kses( $caption_txt, HTML_Helpers::get_allowed_inline_html() )
		);
	}

	$item_html .= '</figure>';

	// Caption for below mode (outside figure).
	if ( $caption_enabled && 'below' === $caption_display && $caption_txt ) {
		$item_html .= sprintf(
			'<figcaption class="blockflow-gallery__caption blockflow-gallery__caption--below">%s</figcaption>',
			wp_kses( $caption_txt, HTML_Helpers::get_allowed_inline_html() )
		);
	}

	// Close link wrapper if has link.
	if ( $has_link ) {
		$item_html .= '</a>';
	}

	$item_html .= '</div>';

	$items_html .= $item_html;
	++$item_index;
}

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $lightbox_attrs_html: Pre-escaped by HTML_Helpers::build_lightbox_attrs() using esc_attr().
 * - $items_html: Built from escaped components - esc_url()/esc_attr() for images,
 *   HTML_Helpers::build_link_attrs() for links, wp_kses() for captions.
 */
printf(
	'<%1$s %2$s%3$s%4$s><div class="%5$s">%6$s</div></%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$lightbox_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	esc_attr( $inner_class_str ),
	$items_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
