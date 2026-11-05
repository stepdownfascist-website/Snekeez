<?php
/**
 * Accordion Block - Server-side Render
 *
 * CSS is generated during save-time by class-accordion-css.php.
 * This file only handles HTML output.
 *
 * @package BlockFlow
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    InnerBlocks content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- $attributes, $content, $block are provided by WordPress block API.

use ThemeRuby\BlockFlow\Asset_Loader;
use ThemeRuby\BlockFlow\HTML_Helpers;
use ThemeRuby\BlockFlow\Icon_Renderer;

$block_id        = $attributes['blockId'] ?? '';
$items           = $attributes['items'] ?? [];
$open_mode       = $attributes['openMode'] ?? 'single';
$init_open_first = $attributes['initOpenFirst'] ?? true;
$expand_icon     = $attributes['expandIcon'] ?? [];
$icon_position   = $attributes['iconPosition'] ?? 'right';
$heading_tag     = $attributes['headingTag'] ?? 'h3';
$faq_schema      = $attributes['faqSchema'] ?? false;
$anchor          = $attributes['anchor'] ?? '';
$title_icon      = $attributes['titleIcon'] ?? [];

// Validate heading tag.
$allowed_tags = [ 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div' ];
$heading_tag  = in_array( $heading_tag, $allowed_tags, true ) ? $heading_tag : 'h3';

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-accordion',
	'blockflow-accordion-' . esc_attr( $block_id ),
];

if ( 'left' === $icon_position ) {
	$classes[] = 'blockflow-accordion--icon-left';
}

// Add standard classes (className, visibility, animation).
$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

$wrapper_attrs = get_block_wrapper_attributes(
	[
		'class'                => implode( ' ', $classes ),
		'id'                   => $anchor ? esc_attr( $anchor ) : null,
		'data-open-mode'       => esc_attr( $open_mode ),
		'data-init-open-first' => $init_open_first ? 'true' : 'false',
	]
);

// Build all data attributes.
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Build expand icon SVG using Icon_Renderer.
// CSS rotation handles expand/collapse state via .is-open class.
// Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
$icon_name = $expand_icon['name'] ?? 'chevron';
$icon_svg  = Icon_Renderer::render(
	[
		'name' => $icon_name,
		'wrap' => false,
	]
);

// Get inner blocks.
$inner_blocks = $block->inner_blocks ?? [];

// FAQ Schema data collection.
$faq_items = [];

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

// Build title icon HTML if exists - Icon_Renderer::render() uses wp_kses() internally.
$title_icon_html = '';
if ( ! empty( $title_icon['name'] ) ) {
	$title_icon_html = Icon_Renderer::render(
		[
			'name'          => $title_icon['name'],
			'customSvg'     => $title_icon['customSvg'] ?? [],
			'wrapper_class' => 'blockflow-accordion-item__title-icon',
		]
	);
}

// Build items HTML.
$items_html = '';
foreach ( $inner_blocks as $index => $inner_block ) {
	$item         = $items[ $index ] ?? [];
	$heading      = $item['heading'] ?? '';
	$is_first     = 0 === $index;
	$is_open      = $init_open_first && $is_first;
	$item_id      = $block_id . '-item-' . $index;
	$item_classes = [ 'blockflow-accordion-item' ];

	if ( $is_open ) {
		$item_classes[] = 'is-open';
	}

	// Get child block content.
	$child_content = '';
	if ( ! empty( $inner_block->inner_blocks ) ) {
		foreach ( $inner_block->inner_blocks as $child_block ) {
			$child_content .= render_block( $child_block->parsed_block );
		}
	}

	// Collect FAQ schema data.
	if ( $faq_schema && $heading ) {
		$answer_text = wp_strip_all_tags( $child_content );
		$answer_text = preg_replace( '/\s+/', ' ', trim( $answer_text ) );

		$faq_items[] = [
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $heading ),
			'acceptedAnswer' => [
				'@type' => 'Answer',
				'text'  => $answer_text,
			],
		];
	}

	// Build icon HTML based on position - $icon_svg from Icon_Renderer::render() uses wp_kses() internally.
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer::render() uses wp_kses() internally.
	$icon_html = '<span class="blockflow-accordion-item__icon">' . $icon_svg . '</span>';

	// Build header HTML.
	$header_html  = '<div class="blockflow-accordion-item__header" role="button" tabindex="0" ';
	$header_html .= 'aria-expanded="' . ( $is_open ? 'true' : 'false' ) . '" ';
	$header_html .= 'aria-controls="' . esc_attr( $item_id ) . '-content" ';
	$header_html .= 'id="' . esc_attr( $item_id ) . '-header">';

	// Left icon - $icon_html contains Icon_Renderer output (wp_kses sanitized).
	if ( 'left' === $icon_position ) {
		$header_html .= $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer uses wp_kses().
	}

	// Title wrapper - $title_icon_html from Icon_Renderer::render() uses wp_kses() internally.
	$header_html .= '<div class="blockflow-accordion-item__title-wrapper">';
	$header_html .= $title_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer uses wp_kses().
	$header_html .= '<' . tag_escape( $heading_tag ) . ' class="blockflow-accordion-item__heading">';
	$header_html .= wp_kses( $heading, HTML_Helpers::get_allowed_inline_html() );
	$header_html .= '</' . tag_escape( $heading_tag ) . '>';
	$header_html .= '</div>';

	// Right icon - $icon_html contains Icon_Renderer output (wp_kses sanitized).
	if ( 'right' === $icon_position ) {
		$header_html .= $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer uses wp_kses().
	}

	$header_html .= '</div>';

	// Build content HTML.
	$content_html  = '<div class="blockflow-accordion-item__content" ';
	$content_html .= 'id="' . esc_attr( $item_id ) . '-content" ';
	$content_html .= 'role="region" ';
	$content_html .= 'aria-labelledby="' . esc_attr( $item_id ) . '-header">';
	$content_html .= '<div class="blockflow-accordion-item__content-inner">';
	$content_html .= '<div class="blockflow-accordion-item__content-body">';
	$content_html .= $child_content;
	$content_html .= '</div></div></div>';

	// Build item HTML.
	$items_html .= '<div class="' . esc_attr( implode( ' ', $item_classes ) ) . '">';
	$items_html .= $header_html;
	$items_html .= $content_html;
	$items_html .= '</div>';
}

// Output FAQ schema via wp_footer hook.
if ( $faq_schema && ! empty( $faq_items ) ) {
	$schema = [
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $faq_items,
	];
	Asset_Loader::add_faq_schema( $block_id, $schema );
}

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attrs: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $items_html: Built from escaped components - esc_attr() for classes/IDs, wp_kses() for text,
 *   Icon_Renderer::render() for SVGs (uses wp_kses internally), render_block() for InnerBlocks.
 */
printf(
	'<%1$s %2$s%3$s>%4$s</%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attrs, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$items_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
