<?php
/**
 * Carousel Images Block - Server-side Render
 *
 * Combines Slider's Swiper functionality with Gallery's image management.
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

$block_id     = $attributes['blockId'] ?? '';
$anchor       = $attributes['anchor'] ?? '';
$class_name   = $attributes['className'] ?? '';
$images       = $attributes['images'] ?? [];
$image_size   = $attributes['imageSize'] ?? 'large';
$click_action = $attributes['clickAction'] ?? 'lightbox';

// Caption settings.
$caption_settings   = $attributes['caption'] ?? [];
$caption_display    = $caption_settings['display'] ?? 'none';
$caption_enabled    = 'none' !== $caption_display;
$caption_position   = $caption_settings['position'] ?? 'bottom-center';
$caption_visibility = $caption_settings['visibility'] ?? 'show-hover';

// Hover effect.
$hover_effect = $attributes['hoverEffect'] ?? 'zoom-in';

// Lazy load.
$lazy_load = $attributes['lazyLoad'] ?? true;

// Aspect ratio settings.
$aspect_ratio         = $attributes['aspectRatio'] ?? [];
$aspect_ratio_enabled = ! empty( $aspect_ratio['enabled'] );

// Slider settings.
$slides_per_view = $attributes['slidesPerView'] ?? [];
$gap             = $attributes['gap'] ?? [];
$autoplay        = $attributes['autoplay'] ?? false;
$autoplay_delay  = $attributes['autoplayDelay'] ?? 3000;
$pause_on_hover  = $attributes['pauseOnHover'] ?? true;
$loop            = $attributes['loop'] ?? true;
$speed           = $attributes['speed'] ?? 400;
$effect          = $attributes['effect'] ?? 'slide';
$free_scroll     = $attributes['freeScroll'] ?? false;

// Navigation.
$show_arrows         = $attributes['showArrows'] ?? true;
$arrow_icon_prev     = $attributes['arrowIconPrev'] ?? [ 'name' => 'arrow-left' ];
$arrow_icon_next     = $attributes['arrowIconNext'] ?? [ 'name' => 'arrow-right' ];
$show_pagination     = $attributes['showPagination'] ?? true;
$pagination_type     = $attributes['paginationType'] ?? 'bullets';
$pagination_position = $attributes['paginationPosition'] ?? 'below';

// Lightbox.
$lightbox              = $attributes['lightbox'] ?? [];
$animation             = $attributes['animation'] ?? [];
$responsive_visibility = $attributes['responsiveVisibility'] ?? [];

// Exit if no images.
if ( empty( $images ) ) {
	return;
}

// Get responsive values for Swiper config.
$slides_desktop = $slides_per_view['desktop']['value'] ?? '1';
$slides_tablet  = $slides_per_view['tablet']['value'] ?? $slides_desktop;
$slides_mobile  = $slides_per_view['mobile']['value'] ?? $slides_tablet;

$gap_desktop = ( $gap['desktop']['value'] ?? '16' ) . ( $gap['desktop']['unit'] ?? 'px' );
$gap_tablet  = isset( $gap['tablet']['value'] ) ? $gap['tablet']['value'] . ( $gap['tablet']['unit'] ?? 'px' ) : $gap_desktop;
$gap_mobile  = isset( $gap['mobile']['value'] ) ? $gap['mobile']['value'] . ( $gap['mobile']['unit'] ?? 'px' ) : $gap_tablet;

// Build Swiper config.
$swiper_config = [
	'speed'         => $speed,
	'effect'        => $effect,
	'slidesPerView' => (int) $slides_mobile,
	'spaceBetween'  => (int) preg_replace( '/[^0-9]/', '', $gap_mobile ),
	'breakpoints'   => [
		768  => [
			'slidesPerView' => (int) $slides_tablet,
			'spaceBetween'  => (int) preg_replace( '/[^0-9]/', '', $gap_tablet ),
		],
		1025 => [
			'slidesPerView' => (int) $slides_desktop,
			'spaceBetween'  => (int) preg_replace( '/[^0-9]/', '', $gap_desktop ),
		],
	],
];

if ( $loop ) {
	$swiper_config['loop'] = true;
}

if ( $free_scroll ) {
	$swiper_config['freeMode'] = true;
}

if ( $autoplay ) {
	$swiper_config['autoplay'] = [
		'delay'             => $autoplay_delay,
		'pauseOnMouseEnter' => $pause_on_hover,
	];
}

if ( $show_arrows ) {
	$swiper_config['navigation'] = [
		'nextEl' => '.blockflow-carousel-images-' . $block_id . ' .swiper-button-next',
		'prevEl' => '.blockflow-carousel-images-' . $block_id . ' .swiper-button-prev',
	];
}

if ( $show_pagination ) {
	$swiper_config['pagination'] = [
		'el'        => '.blockflow-carousel-images-' . $block_id . ' .swiper-pagination',
		'type'      => $pagination_type,
		'clickable' => true,
	];
}

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-carousel-images',
	'blockflow-carousel-images-' . esc_attr( $block_id ),
];

// Hover effect class - will be applied to each slide element.
$hover_effect_class = '';
if ( $hover_effect && 'none' !== $hover_effect ) {
	$hover_effect_class = 'blockflow-img-hover-' . esc_attr( $hover_effect );
}

// Aspect ratio class.
if ( $aspect_ratio_enabled ) {
	$classes[] = 'blockflow-carousel-images--has-ratio';
}

// Caption classes.
if ( $caption_enabled ) {
	$classes[] = 'blockflow-carousel-images--caption-' . esc_attr( $caption_display );
	// Position and visibility for overlay and bar modes.
	if ( 'overlay' === $caption_display || 'bar' === $caption_display ) {
		$classes[] = 'blockflow-carousel-images--caption-' . esc_attr( $caption_position );
		$classes[] = 'blockflow-carousel-images--caption-' . esc_attr( $caption_visibility );
	}
}

// Pagination overlay class.
if ( $show_pagination && 'overlay' === $pagination_position ) {
	$classes[] = 'blockflow-carousel-images--pagination-overlay';
}

$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

// Wrapper attributes.
$wrapper_attrs = [
	'class' => implode( ' ', $classes ),
];

if ( $anchor ) {
	$wrapper_attrs['id'] = esc_attr( $anchor );
}

$wrapper_attributes = get_block_wrapper_attributes( $wrapper_attrs );

// Data attributes (animation, item animation, etc.).
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Lightbox data attributes.
$lightbox_attrs_html = '';
if ( 'lightbox' === $click_action ) {
	$lightbox_attrs_html = HTML_Helpers::build_lightbox_attrs( $lightbox, false );
}

// Swiper data attribute.
$swiper_data = sprintf( " data-swiper='%s'", wp_json_encode( $swiper_config ) );

// Arrow SVGs from icon library - Icon_Renderer::render() uses wp_kses() internally for safe SVG output.

if ( ! function_exists( 'blockflow_get_carousel_image_url' ) ) {
	/**
	 * Get image URL for specific size.
	 *
	 * @param array  $image Image data.
	 * @param string $size  Image size.
	 * @return string Image URL.
	 */
	function blockflow_get_carousel_image_url( $image, $size = 'large' ) {
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

// Build slides HTML.
$slides_html = '';
$item_index  = 0;

foreach ( $images as $image ) {
	$image_id    = $image['id'] ?? 0;
	$image_url   = blockflow_get_carousel_image_url( $image, $image_size );
	$image_full  = blockflow_get_carousel_image_url( $image, 'full' );
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
		$caption_txt = wp_get_attachment_caption( $image_id );
	}

	// Build slide data attributes for lightbox.
	$slide_data = '';
	if ( 'lightbox' === $click_action ) {
		$slide_data .= sprintf( ' data-src="%s"', esc_url( $image_full ) );
		$slide_data .= sprintf( ' data-caption="%s"', esc_attr( $caption_txt ) );
		$slide_data .= sprintf( ' data-index="%d"', $item_index );
	} elseif ( 'media' === $click_action ) {
		$slide_data .= sprintf( ' data-href="%s"', esc_url( $image_full ) );
	}

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

	// Build slide class.
	$slide_class = 'swiper-slide blockflow-carousel-images__slide';
	if ( $hover_effect_class ) {
		$slide_class .= ' ' . $hover_effect_class;
	}

	// Start slide HTML.
	$slide_html = sprintf(
		'<div class="%s"%s>',
		esc_attr( $slide_class ),
		$slide_data
	);

	// Open link wrapper if has link.
	if ( $has_link ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $link_attrs built from esc_url() and esc_attr() escaped values.
		$slide_html .= sprintf( '<a class="blockflow-carousel-images__link"%s>', $link_attrs );
	}

	// Figure element.
	$slide_html .= '<figure class="blockflow-carousel-images__media">';

	// Image.
	$slide_html .= sprintf(
		'<img src="%s" alt="%s" class="blockflow-carousel-images__image"%s />',
		esc_url( $image_url ),
		esc_attr( $image_alt ),
		$lazy_load ? ' loading="lazy"' : ''
	);

	// Overlay.
	$slide_html .= '<div class="blockflow-carousel-images__overlay"></div>';

	// Caption (overlay and bar modes - inside figure).
	if ( $caption_enabled && $caption_txt && ( 'overlay' === $caption_display || 'bar' === $caption_display ) ) {
		$slide_html .= sprintf(
			'<figcaption class="blockflow-carousel-images__caption">%s</figcaption>',
			wp_kses( $caption_txt, HTML_Helpers::get_allowed_inline_html() )
		);
	}

	$slide_html .= '</figure>';

	// Caption (below mode - outside figure).
	if ( $caption_enabled && $caption_txt && 'below' === $caption_display ) {
		$slide_html .= sprintf(
			'<figcaption class="blockflow-carousel-images__caption blockflow-carousel-images__caption--below">%s</figcaption>',
			wp_kses( $caption_txt, HTML_Helpers::get_allowed_inline_html() )
		);
	}

	// Close link wrapper if has link.
	if ( $has_link ) {
		$slide_html .= '</a>';
	}

	$slide_html .= '</div>';

	$slides_html .= $slide_html;
	++$item_index;
}

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

// Build arrows HTML - Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
$arrows_html = '';
if ( $show_arrows ) {
	$arrows_html = sprintf(
		'<button type="button" class="swiper-button-prev blockflow-carousel-images__arrow blockflow-slider-arrow blockflow-slider-arrow--prev" aria-label="%1$s">%2$s</button><button type="button" class="swiper-button-next blockflow-carousel-images__arrow blockflow-slider-arrow blockflow-slider-arrow--next" aria-label="%3$s">%4$s</button>',
		esc_attr__( 'Previous slide', 'blockflow' ),
		Icon_Renderer::render( [ 'name' => $arrow_icon_prev['name'] ?? 'arrow-left', 'wrap' => false ] ),
		esc_attr__( 'Next slide', 'blockflow' ),
		Icon_Renderer::render( [ 'name' => $arrow_icon_next['name'] ?? 'arrow-right', 'wrap' => false ] )
	);
}

// Build pagination HTML.
$pagination_html = $show_pagination ? '<div class="swiper-pagination"></div>' : '';

// Build inner HTML.
$inner_html = sprintf(
	'<div class="blockflow-carousel-images__swiper swiper"%1$s><div class="swiper-wrapper">%2$s</div>%3$s%4$s</div>',
	$swiper_data,
	$slides_html,
	$arrows_html,
	$pagination_html
);

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $lightbox_attrs_html: Pre-escaped by HTML_Helpers::build_lightbox_attrs() using esc_attr().
 * - $inner_html: Built from escaped components - esc_url()/esc_attr() for images,
 *   HTML_Helpers::build_link_attrs() for links, wp_kses() for captions.
 */
printf(
	'<%1$s %2$s%3$s%4$s>%5$s</%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$lightbox_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$inner_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
