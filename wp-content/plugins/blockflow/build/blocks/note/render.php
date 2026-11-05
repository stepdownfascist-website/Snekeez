<?php
/**
 * Note Block - Server-side Render
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

use ThemeRuby\BlockFlow\HTML_Helpers;
use ThemeRuby\BlockFlow\Icon_Renderer;

$block_id         = $attributes['blockId'] ?? '';
$note_title       = $attributes['title'] ?? '';
$show_title       = $attributes['showTitle'] ?? true;
$show_divider     = $attributes['showDivider'] ?? true;
$title_tag        = $attributes['titleTag'] ?? 'h2';
$note_type        = $attributes['noteType'] ?? 'default';
$note_style       = $attributes['noteStyle'] ?? 'shadow';
$note_icon        = $attributes['noteIcon'] ?? [];
$collapsible      = $attributes['collapsible'] ?? false;
$toggle_icon      = $attributes['toggleIcon'] ?? [];
$dismissible      = $attributes['dismissible'] ?? false;
$dismiss_icon     = $attributes['dismissIcon'] ?? [];
$dismiss_duration = $attributes['dismissDuration'] ?? 1;
$anchor           = $attributes['anchor'] ?? '';

// Check if collapsible/dismissible (boolean attributes).
$is_collapsible = (bool) $collapsible;
$is_dismissible = (bool) $dismissible;

// Build all data attributes.
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Validate title tag.
$allowed_tags = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span' ];
$title_tag    = in_array( $title_tag, $allowed_tags, true ) ? $title_tag : 'h2';

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-note',
	'blockflow-note-' . esc_attr( $block_id ),
	'note-type-' . esc_attr( $note_type ),
	'note-style-' . esc_attr( $note_style ),
];

if ( $show_divider ) {
	$classes[] = 'has-divider';
}

if ( $is_collapsible ) {
	$classes[] = 'is-collapsible';
}

$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

// Build wrapper attributes.
$wrapper_attrs_array = [
	'class' => implode( ' ', $classes ),
];

if ( $anchor ) {
	$wrapper_attrs_array['id'] = esc_attr( $anchor );
}

if ( $is_collapsible ) {
	$wrapper_attrs_array['data-collapsible'] = 'true';
}

if ( $is_dismissible ) {
	$wrapper_attrs_array['data-dismissible']      = 'true';
	$wrapper_attrs_array['data-dismiss-duration'] = intval( $dismiss_duration );
}

$wrapper_attrs = get_block_wrapper_attributes( $wrapper_attrs_array );

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

// Build icon HTML - Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
$note_icon_html = '';
if ( $show_title && ! empty( $note_icon['name'] ) ) {
	$note_icon_svg  = Icon_Renderer::render(
		[
			'name' => $note_icon['name'],
			'wrap' => false,
		]
	);
	$note_icon_html = sprintf(
		'<span class="blockflow-note__icon">%s</span>',
		$note_icon_svg // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer::render() uses wp_kses() internally.
	);
}

// Build title HTML.
$title_html = '';
if ( $show_title && $note_title ) {
	$title_html = sprintf(
		'<%1$s class="blockflow-note__title">%2$s</%1$s>',
		tag_escape( $title_tag ),
		wp_kses( $note_title, HTML_Helpers::get_allowed_inline_html() )
	);
}

// Build toggle button HTML - Icon_Renderer::render() uses wp_kses() internally.
$toggle_html = '';
if ( $is_collapsible && ! empty( $toggle_icon['name'] ) ) {
	$toggle_icon_svg = Icon_Renderer::render(
		[
			'name' => $toggle_icon['name'],
			'wrap' => false,
		]
	);
	$toggle_html     = sprintf(
		'<button type="button" class="blockflow-note__toggle" aria-label="%s" aria-expanded="true">%s</button>',
		esc_attr__( 'Toggle content', 'blockflow' ),
		$toggle_icon_svg // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer::render() uses wp_kses() internally.
	);
}

// Build dismiss button HTML - Icon_Renderer::render() uses wp_kses() internally.
$dismiss_html = '';
if ( $is_dismissible && ! empty( $dismiss_icon['name'] ) ) {
	$dismiss_icon_svg = Icon_Renderer::render(
		[
			'name' => $dismiss_icon['name'],
			'wrap' => false,
		]
	);
	$dismiss_html     = sprintf(
		'<button type="button" class="blockflow-note__dismiss" aria-label="%s" title="%s">%s</button>',
		esc_attr__( 'Dismiss note', 'blockflow' ),
		esc_attr__( 'Dismiss', 'blockflow' ),
		$dismiss_icon_svg // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer::render() uses wp_kses() internally.
	);
}

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attrs: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $note_icon_html, $toggle_html, $dismiss_html: Icon_Renderer::render() uses wp_kses().
 * - $title_html: Built with tag_escape() and esc_html() for title text.
 * - $content: WordPress InnerBlocks content, pre-rendered and escaped by block renderer.
 */
printf(
	'<%1$s %2$s%3$s>
	<div class="blockflow-note__header">%4$s%5$s%6$s%7$s</div>
	<div class="blockflow-note__content">%8$s</div>
</%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attrs, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$note_icon_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$title_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$toggle_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$dismiss_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$content // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
