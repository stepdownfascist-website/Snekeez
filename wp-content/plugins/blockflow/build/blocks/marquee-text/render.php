<?php
/**
 * Marquee Text Block - Server-side Render
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
use ThemeRuby\BlockFlow\Icon_Renderer;
use ThemeRuby\BlockFlow\HTML_Helpers;

$block_id              = $attributes['blockId'] ?? '';
$anchor                = $attributes['anchor'] ?? '';
$class_name            = $attributes['className'] ?? '';
$items                 = $attributes['items'] ?? [];
$divider               = $attributes['divider'] ?? [];
$scroll                = $attributes['scroll'] ?? [];
$text_tag              = $attributes['textTag'] ?? 'span';
$animation             = $attributes['animation'] ?? [];
$responsive_visibility = $attributes['responsiveVisibility'] ?? [];
$text_color            = $attributes['textColor'] ?? [];
$text_color_hover      = $attributes['textColorHover'] ?? [];

// Validate HTML tag.
$allowed_tags = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'div' ];
if ( ! in_array( $text_tag, $allowed_tags, true ) ) {
	$text_tag = 'span';
}

// Early return if no items.
if ( empty( $items ) ) {
	return;
}

// Scroll settings.
$direction      = $scroll['direction'] ?? 'left';
$speed          = $scroll['speed'] ?? 50;
$pause_on_hover = ! empty( $scroll['pauseOnHover'] );

// Divider settings.
$divider_type  = $divider['type'] ?? 'icon';
$divider_icon  = $divider['icon'] ?? [];
$divider_image = $divider['image'] ?? [];
$has_divider   = 'none' !== $divider_type;

// Check if text color is a gradient.
$text_color_light       = $text_color['light'] ?? '';
$text_color_hover_light = $text_color_hover['light'] ?? '';
$is_text_gradient       = $text_color_light && ( str_starts_with( $text_color_light, 'linear-gradient' ) || str_starts_with( $text_color_light, 'radial-gradient' ) );
$is_text_gradient_hover = $text_color_hover_light && ( str_starts_with( $text_color_hover_light, 'linear-gradient' ) || str_starts_with( $text_color_hover_light, 'radial-gradient' ) );

// Build wrapper classes.
$classes = HTML_Helpers::build_wrapper_classes(
	[
		'blockflow-block',
		'blockflow-marquee-text',
		'blockflow-marquee-text-' . esc_attr( $block_id ),
	],
	$attributes
);

// Add gradient text modifier classes.
if ( $is_text_gradient ) {
	$classes[] = 'blockflow-marquee-text--text-gradient';
}
if ( $is_text_gradient_hover ) {
	$classes[] = 'blockflow-marquee-text--text-gradient-hover';
}

// Add custom class.
if ( $class_name ) {
	$classes[] = esc_attr( $class_name );
}

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $classes ),
		'id'    => $anchor ? esc_attr( $anchor ) : null,
	]
);

// Build data attributes.
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Add marquee-specific data attributes.
$data_attrs_html .= sprintf( ' data-direction="%s"', esc_attr( $direction ) );
$data_attrs_html .= sprintf( ' data-speed="%d"', intval( $speed ) );
$data_attrs_html .= sprintf( ' data-pause-hover="%s"', $pause_on_hover ? 'true' : 'false' );

// Track animation usage if block animation is set.
if ( ! empty( $animation['type'] ) ) {
	Asset_Loader::track_animation_usage();
}

/**
 * Render divider element.
 *
 * @return string Divider HTML.
 */
// Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
$render_divider = function () use ( $has_divider, $divider_type, $divider_icon, $divider_image ) {
	if ( ! $has_divider ) {
		return '';
	}

	$divider_html = '';

	if ( 'icon' === $divider_type && ! empty( $divider_icon['name'] ) ) {
		$icon_args    = array_merge( $divider_icon, [ 'class' => 'blockflow-marquee-text__icon' ] );
		$icon_html    = Icon_Renderer::render( $icon_args );
		$divider_html = sprintf(
			'<span class="blockflow-marquee-text__divider">%s</span>',
			$icon_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer::render() uses wp_kses() internally.
		);
	} elseif ( 'image' === $divider_type && ! empty( $divider_image['url'] ) ) {
		$divider_html = sprintf(
			'<span class="blockflow-marquee-text__divider"><img src="%s" alt="%s" /></span>',
			esc_url( $divider_image['url'] ),
			esc_attr( $divider_image['alt'] ?? '' )
		);
	}

	return $divider_html;
};

/**
 * Render content items.
 *
 * @return string Content HTML.
 */
$render_content = function () use ( $items, $render_divider, $text_tag ) {
	$content_html = '';
	$item_count   = count( $items );

	foreach ( $items as $index => $item ) {
		$text = $item['text'] ?? '';
		if ( empty( $text ) ) {
			continue;
		}

		$content_html .= sprintf(
			'<%1$s class="blockflow-marquee-text__item">%2$s</%1$s>',
			tag_escape( $text_tag ),
			esc_html( $text )
		);

		// Add divider after each item except the last.
		if ( $index < $item_count - 1 ) {
			$content_html .= $render_divider();
		}
	}

	// Add trailing divider for seamless loop.
	$content_html .= $render_divider();

	return $content_html;
};

// Build content HTML.
$content_html = $render_content();

/*
 * Output - Escaping:
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $content_html: Built from escaped components - wp_kses() for text,
 *   Icon_Renderer::render() for separators (uses wp_kses), tag_escape() for tags.
 */
printf(
	'<div %1$s%2$s>
		<div class="blockflow-marquee-text__track">
			<div class="blockflow-marquee-text__content">%3$s</div>
			<div class="blockflow-marquee-text__content" aria-hidden="true">%3$s</div>
		</div>
	</div>',
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$content_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
