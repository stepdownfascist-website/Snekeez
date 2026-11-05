<?php
/**
 * Heading Block - Server-side Render
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

$block_id            = $attributes['blockId'] ?? '';
$heading_content     = $attributes['content'] ?? '';
$heading_tag         = $attributes['tag'] ?? 'h2';
$block_link          = $attributes['link'] ?? [];
$hover_animation     = $attributes['hoverAnimation'] ?? '';
$anchor              = $attributes['anchor'] ?? '';
$text_color          = $attributes['textColor'] ?? [];
$text_color_hover    = $attributes['textColorHover'] ?? [];
$show_subheading     = $attributes['showSubheading'] ?? false;
$subheading_content  = $attributes['subheadingContent'] ?? '';
$subheading_position = $attributes['subheadingPosition'] ?? 'bottom';
$subheading_tag      = $attributes['subheadingTag'] ?? 'span';
$show_separator      = $attributes['showSeparator'] ?? false;
$separator_position  = $attributes['separatorPosition'] ?? 'bottom';

// Check if text color is a gradient.
$text_color_light = $text_color['light'] ?? '';
$is_text_gradient = ! empty( $text_color_light ) && (
	strpos( $text_color_light, 'linear-gradient' ) === 0 ||
	strpos( $text_color_light, 'radial-gradient' ) === 0
);

// Check if hover color is a gradient.
$text_color_hover_light = $text_color_hover['light'] ?? '';
$is_text_gradient_hover = ! empty( $text_color_hover_light ) && (
	strpos( $text_color_hover_light, 'linear-gradient' ) === 0 ||
	strpos( $text_color_hover_light, 'radial-gradient' ) === 0
);

// Validate heading tag.
$allowed_heading_tags = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' ];
if ( ! in_array( $heading_tag, $allowed_heading_tags, true ) ) {
	$heading_tag = 'h2';
}

// Validate subheading tag.
if ( ! in_array( $subheading_tag, $allowed_heading_tags, true ) ) {
	$subheading_tag = 'span';
}

// Get wrapper HTML tag.
$wrapper_tag = HTML_Helpers::get_html_tag( $attributes );

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-heading',
	'blockflow-heading-' . esc_attr( $block_id ),
];

if ( $is_text_gradient ) {
	$classes[] = 'blockflow-heading--text-gradient';
}

if ( $is_text_gradient_hover ) {
	$classes[] = 'blockflow-heading--text-gradient-hover';
}

if ( $show_separator ) {
	$classes[] = 'blockflow-heading--separator-' . esc_attr( $separator_position );
}

$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $classes ),
		'id'    => $anchor ? esc_attr( $anchor ) : null,
	]
);

// Build all data attributes.
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Build heading content with allowed HTML.
$heading_html = wp_kses( $heading_content, HTML_Helpers::get_allowed_inline_html() );

// Build link wrapper if URL is set.
if ( ! empty( $block_link['url'] ) ) {
	$link_url    = esc_url( $block_link['url'] );
	$link_target = $block_link['target'] ?? '';
	$link_rel    = $block_link['rel'] ?? '';

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

	$custom_attrs = $block_link['customAttributes'] ?? [];
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
	$heading_html = sprintf( '<a %s>%s</a>', $link_attrs, $heading_html );
}

// Build heading text class with hover animation.
$text_class = 'blockflow-heading__text';
if ( $hover_animation && ! empty( $block_link['url'] ) ) {
	$text_class .= ' blockflow-hover-' . esc_attr( $hover_animation );
}

// Build subheading HTML.
$subheading_html = '';
if ( $show_subheading && ! empty( $subheading_content ) ) {
	$subheading_html = sprintf(
		'<%1$s class="blockflow-heading__subheading">%2$s</%1$s>',
		tag_escape( $subheading_tag ),
		wp_kses( $subheading_content, HTML_Helpers::get_allowed_inline_html() )
	);
}

// Build separator HTML.
$separator_html = '';
if ( $show_separator ) {
	$separator_html = '<hr class="blockflow-heading__separator" />';
}

// Build main heading HTML.
$main_heading_html = sprintf(
	'<%1$s class="%2$s">%3$s</%1$s>',
	tag_escape( $heading_tag ),
	esc_attr( $text_class ),
	$heading_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
);

// Build inner content based on subheading and separator positions.
// Order: elements are rendered in visual order (top to bottom).
$inner_content = '';

// Determine elements order based on positions.
// Possible positions: separator (top/center/bottom), subheading (top/bottom).
// Default order when all at default positions: heading -> subheading -> separator.

// Top position elements.
if ( $show_separator && 'top' === $separator_position ) {
	$inner_content .= $separator_html;
}
if ( $show_subheading && 'top' === $subheading_position ) {
	$inner_content .= $subheading_html;
}

// Main heading.
$inner_content .= $main_heading_html;

// Center position elements (separator only - between heading and subheading).
if ( $show_separator && 'center' === $separator_position ) {
	$inner_content .= $separator_html;
}

// Bottom position elements.
if ( $show_subheading && 'bottom' === $subheading_position ) {
	$inner_content .= $subheading_html;
}
if ( $show_separator && 'bottom' === $separator_position ) {
	$inner_content .= $separator_html;
}

/*
 * Output - Escaping:
 * - tag_escape( $wrapper_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $inner_content: Built from escaped components - wp_kses() for heading/subheading text,
 *   esc_url()/esc_attr() for links, tag_escape() for heading tags.
 */
printf(
	'<%1$s %2$s%3$s>%4$s</%1$s>',
	tag_escape( $wrapper_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$inner_content // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
