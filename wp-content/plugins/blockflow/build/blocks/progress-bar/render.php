<?php
/**
 * Progress Bar Block - Server-side Render
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

$block_id              = $attributes['blockId'] ?? '';
$anchor                = $attributes['anchor'] ?? '';
$class_name            = $attributes['className'] ?? '';
$value_type            = $attributes['valueType'] ?? 'percentage';
$layout_type           = $attributes['layoutType'] ?? 'line';
$title                 = $attributes['title'] ?? '';
$title_tag             = $attributes['titleTag'] ?? 'div';
$counter_value         = $attributes['counterValue'] ?? 75;
$total_range           = $attributes['totalRange'] ?? 100;
$divider               = $attributes['divider'] ?? '/';
$show_counter          = $attributes['showCounter'] ?? true;
$line_style            = $attributes['lineStyle'] ?? 'solid';
$line_cap              = $attributes['lineCap'] ?? 'round';
$title_position        = $attributes['titlePosition'] ?? 'above';
$progress_animation    = $attributes['progressAnimation'] ?? [];
$animation             = $attributes['animation'] ?? [];
$responsive_visibility = $attributes['responsiveVisibility'] ?? [];

// Circle layout specific.
$is_semi_circle   = 'semi-circle' === $layout_type;
$is_circle        = 'circle' === $layout_type;
$is_circle_layout = $is_semi_circle || $is_circle;

// Calculate percentage.
$percentage = 0;
if ( $total_range > 0 ) {
	$percentage = min( 100, max( 0, ( $counter_value / $total_range ) * 100 ) );
}

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-progress-bar',
	'blockflow-progress-bar-' . esc_attr( $block_id ),
	'blockflow-progress-bar--' . esc_attr( $layout_type ),
];

// Line style class.
if ( 'line' === $layout_type && 'solid' !== $line_style ) {
	$classes[] = 'blockflow-progress-bar--' . esc_attr( $line_style );
}

// Title position class for line layout.
if ( 'line' === $layout_type && 'below' === $title_position ) {
	$classes[] = 'blockflow-progress-bar--title-below';
}

$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $classes ),
		'id'    => $anchor ? esc_attr( $anchor ) : null,
	]
);

// Data attributes for frontend animation.
$data_attrs  = '';
$data_attrs .= sprintf( ' data-value="%s"', esc_attr( $counter_value ) );
$data_attrs .= sprintf( ' data-total="%s"', esc_attr( $total_range ) );
$data_attrs .= sprintf( ' data-percentage="%s"', esc_attr( $percentage ) );

// Animation duration.
$data_attrs .= sprintf( ' data-duration="%s"', esc_attr( $progress_animation['duration'] ?? 1500 ) );
$data_attrs .= sprintf( ' data-layout="%s"', esc_attr( $layout_type ) );

// Block animation data.
$animation_html = '';
if ( ! empty( $animation['type'] ) ) {
	$animation_html .= sprintf( ' data-block-animation="%s"', esc_attr( $animation['type'] ) );
	$animation_html .= sprintf( ' data-block-animation-once="%s"', $animation['once'] ? 'true' : 'false' );
	Asset_Loader::track_animation_usage();
}

// Allowed tags for title.
$allowed_tags = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' ];
if ( ! in_array( $title_tag, $allowed_tags, true ) ) {
	$title_tag = 'div';
}

// Format counter display.
if ( 'absolute' === $value_type ) {
	$counter_display = esc_html( $counter_value ) . esc_html( $divider ) . esc_html( $total_range );
} else {
	$counter_display = esc_html( round( $percentage ) ) . '%';
}

// Check layout type.
$is_box_layout  = 'box' === $layout_type;
$is_line_layout = 'line' === $layout_type;

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

// Build title HTML helper.
$title_html = $title ? sprintf( '<%1$s class="blockflow-progress-bar__title">%2$s</%1$s>', tag_escape( $title_tag ), esc_html( $title ) ) : '';

// Build counter HTML helper.
$counter_html = $show_counter ? sprintf( '<div class="blockflow-progress-bar__counter">%s</div>', $counter_display ) : '';

// Build inner HTML based on layout.
$inner_html = '';

if ( $is_line_layout ) {
	// Line layout.
	$header_html = '';
	if ( $title || $show_counter ) {
		$header_html = sprintf( '<div class="blockflow-progress-bar__header">%s%s</div>', $title_html, $counter_html );
	}
	$inner_html = sprintf(
		'<div class="blockflow-progress-bar__line-wrapper">%s<div class="blockflow-progress-bar__track"><div class="blockflow-progress-bar__fill"></div></div></div>',
		$header_html
	);
} elseif ( $is_box_layout ) {
	// Box layout.
	$box_content = $show_counter ? sprintf( '<div class="blockflow-progress-bar__box-content">%s</div>', $counter_html ) : '';
	$inner_html  = sprintf(
		'<div class="blockflow-progress-bar__box-wrapper"><div class="blockflow-progress-bar__box-fill"></div>%s</div>%s',
		$box_content,
		$title_html
	);
} elseif ( $is_semi_circle ) {
	// Semi-circle layout.
	$radius        = 44;
	$circumference = M_PI * $radius;
	$dash_offset   = $circumference * ( 1 - $percentage / 100 );

	$circle_content = $show_counter ? sprintf( '<div class="blockflow-progress-bar__circle-content">%s</div>', $counter_html ) : '';

	$inner_html = sprintf(
		'<div class="blockflow-progress-bar__circle-wrapper"><svg class="blockflow-progress-bar__svg" viewBox="0 0 100 56"><path class="blockflow-progress-bar__track-path" d="M 50,50 m -44,0 a 44,44 0 1 1 88,0" fill="none" stroke-linecap="%1$s"/><path class="blockflow-progress-bar__fill-path" d="M 50,50 m -44,0 a 44,44 0 1 1 88,0" fill="none" stroke-linecap="%1$s" data-circumference="%2$s" data-offset="%3$s"/></svg>%4$s</div>%5$s',
		esc_attr( $line_cap ),
		esc_attr( $circumference ),
		esc_attr( $dash_offset ),
		$circle_content,
		$title_html
	);
} elseif ( $is_circle ) {
	// Full circle layout.
	$radius        = 44;
	$circumference = 2 * M_PI * $radius;
	$dash_offset   = $circumference * ( 1 - $percentage / 100 );

	$circle_content = '';
	if ( $show_counter || $title ) {
		$circle_content = sprintf( '<div class="blockflow-progress-bar__circle-content">%s%s</div>', $counter_html, $title_html );
	}

	$inner_html = sprintf(
		'<div class="blockflow-progress-bar__circle-wrapper"><svg class="blockflow-progress-bar__svg" viewBox="0 0 100 100"><circle class="blockflow-progress-bar__track-path" cx="50" cy="50" r="44" fill="none" stroke-linecap="%1$s"/><circle class="blockflow-progress-bar__fill-path" cx="50" cy="50" r="44" fill="none" stroke-linecap="%1$s" transform="rotate(-90 50 50)" data-circumference="%2$s" data-offset="%3$s"/></svg>%4$s</div>',
		esc_attr( $line_cap ),
		esc_attr( $circumference ),
		esc_attr( $dash_offset ),
		$circle_content
	);
}

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs: Built from sprintf() with esc_attr() values.
 * - $animation_html: Built from sprintf() with esc_attr() values.
 * - $inner_html: Built from escaped components - esc_html() for title/percentage,
 *   esc_attr() for classes and data attributes.
 */
printf(
	'<%1$s %2$s%3$s%4$s>%5$s</%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$animation_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$inner_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
