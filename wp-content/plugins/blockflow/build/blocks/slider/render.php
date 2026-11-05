<?php
/**
 * Slider Block - Server-side Render
 *
 * Structure: outer wrapper (blockflow styles) > inner swiper container.
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
use ThemeRuby\BlockFlow\Icon_Renderer;

$block_id = $attributes['blockId'] ?? '';
$anchor   = $attributes['anchor'] ?? '';

// Slider settings.
$autoplay             = $attributes['autoplay'] ?? false;
$autoplay_delay       = $attributes['autoplayDelay'] ?? 3000;
$pause_on_hover       = $attributes['pauseOnHover'] ?? true;
$pause_on_interaction = $attributes['pauseOnInteraction'] ?? true;
$loop                 = $attributes['loop'] ?? true;
$speed                = $attributes['speed'] ?? 400;
$effect               = $attributes['effect'] ?? 'slide';

// Navigation.
$show_arrows         = $attributes['showArrows'] ?? true;
$arrow_icon_prev     = $attributes['arrowIconPrev'] ?? [ 'name' => 'arrow-left' ];
$arrow_icon_next     = $attributes['arrowIconNext'] ?? [ 'name' => 'arrow-right' ];
$show_pagination     = $attributes['showPagination'] ?? true;
$pagination_type     = $attributes['paginationType'] ?? 'bullets';
$pagination_position = $attributes['paginationPosition'] ?? 'below';

// Determine actual Swiper effect and direction.
$swiper_effect    = $effect;
$swiper_direction = 'horizontal';

if ( 'vertical' === $effect ) {
	$swiper_effect    = 'slide';
	$swiper_direction = 'vertical';
}

// Build Swiper config (selectors target inner swiper container).
$swiper_config = [
	'speed'         => $speed,
	'effect'        => $swiper_effect,
	'slidesPerView' => 1,
	'direction'     => $swiper_direction,
];

// Creative effect config.
if ( 'creative' === $effect ) {
	$swiper_config['creativeEffect'] = [
		'prev' => [
			'shadow'    => true,
			'translate' => [ 0, 0, -400 ],
		],
		'next' => [
			'translate' => [ '100%', 0, 0 ],
		],
	];
}

if ( $loop ) {
	$swiper_config['loop'] = true;
}

if ( $autoplay ) {
	$swiper_config['autoplay'] = [
		'delay'                => $autoplay_delay,
		'pauseOnMouseEnter'    => $pause_on_hover,
		'disableOnInteraction' => $pause_on_interaction,
	];
}

if ( $show_arrows ) {
	$swiper_config['navigation'] = [
		'nextEl' => '.blockflow-slider-' . $block_id . ' .swiper-button-next',
		'prevEl' => '.blockflow-slider-' . $block_id . ' .swiper-button-prev',
	];
}

if ( $show_pagination ) {
	$swiper_config['pagination'] = [
		'el'        => '.blockflow-slider-' . $block_id . ' .swiper-pagination',
		'type'      => $pagination_type,
		'clickable' => true,
	];
}

// Build outer wrapper classes (blockflow styles).
$classes = [
	'blockflow-block',
	'blockflow-slider',
	'blockflow-slider-' . esc_attr( $block_id ),
];

// Add effect-specific class for vertical mode.
if ( 'vertical' === $effect ) {
	$classes[] = 'blockflow-slider--vertical';
}

// Pagination overlay class.
if ( $show_pagination && 'overlay' === $pagination_position ) {
	$classes[] = 'blockflow-slider--pagination-overlay';
}

// Add standard classes (className, visibility, animation).
$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

// Wrapper attributes.
$wrapper_attrs = [
	'class' => implode( ' ', $classes ),
];

if ( $anchor ) {
	$wrapper_attrs['id'] = esc_attr( $anchor );
}

$wrapper_attributes = get_block_wrapper_attributes( $wrapper_attrs );

// Build data attributes (animation, background image, custom attrs).
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Swiper data attribute (on inner container).
$swiper_data = sprintf( " data-swiper='%s'", wp_json_encode( $swiper_config ) );

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

// Build arrows HTML - Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
$arrows_html = '';
if ( $show_arrows ) {
	$arrows_html = sprintf(
		'<button type="button" class="swiper-button-prev blockflow-slider__arrow" aria-label="%1$s">%2$s</button><button type="button" class="swiper-button-next blockflow-slider__arrow" aria-label="%3$s">%4$s</button>',
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
	'<div class="blockflow-slider__swiper swiper"%1$s><div class="swiper-wrapper">%2$s</div>%3$s%4$s</div>',
	$swiper_data,
	$content,
	$arrows_html,
	$pagination_html
);

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $inner_html: Built from escaped components - esc_attr() for classes,
 *   render_block() for InnerBlocks (pre-escaped by WordPress block renderer).
 */
printf(
	'<%1$s %2$s%3$s>%4$s</%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$inner_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
