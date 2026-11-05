<?php
/**
 * Counter Block - Server-side Render
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

// Get block attributes.
$block_id         = $attributes['blockId'] ?? '';
$items            = $attributes['items'] ?? [];
$separator        = $attributes['separator'] ?? [];
$show_icon        = $attributes['showIcon'] ?? false;
$icon_position    = $attributes['iconPosition'] ?? 'top';
$item_animation   = $attributes['itemAnimation'] ?? [];
$number_animation = $attributes['numberAnimation'] ?? [];
$number_color     = $attributes['numberColor'] ?? [];
$anchor           = $attributes['anchor'] ?? '';

// Check if number color is a gradient.
$number_color_light = $number_color['light'] ?? '';
$is_number_gradient = ! empty( $number_color_light ) && (
	strpos( $number_color_light, 'linear-gradient' ) === 0 ||
	strpos( $number_color_light, 'radial-gradient' ) === 0
);

// Skip if no block ID.
if ( empty( $block_id ) ) {
	return;
}

// Skip if no items.
if ( empty( $items ) ) {
	return;
}

// Separator settings.
$sep_enabled = $separator['enabled'] ?? false;

// Item animation settings.
$item_anim_type = $item_animation['type'] ?? '';
$item_stagger   = $item_animation['stagger'] ?? 100;
$item_duration  = $item_animation['duration'] ?? 600;

// Build wrapper classes (root element).
$wrapper_classes = [
	'blockflow-block',
	'blockflow-counter',
	'blockflow-counter-' . $block_id,
];

// Add gradient class if number color is a gradient.
if ( $is_number_gradient ) {
	$wrapper_classes[] = 'blockflow-counter--number-gradient';
}

// Add standard classes (className, visibility, animation).
$wrapper_classes = HTML_Helpers::build_wrapper_classes( $wrapper_classes, $attributes );

// Build wrapper attributes.
$wrapper_attrs = [
	'class' => implode( ' ', $wrapper_classes ),
];

if ( $anchor ) {
	$wrapper_attrs['id'] = esc_attr( $anchor );
}

// Number animation data attributes.
$number_anim_type = $number_animation['type'] ?? '';
if ( $number_anim_type ) {
	$wrapper_attrs['data-number-animation'] = esc_attr( $number_anim_type );
	$wrapper_attrs['data-number-duration']  = esc_attr( $number_animation['duration'] ?? 2000 );
	$wrapper_attrs['data-number-stagger']   = esc_attr( $number_animation['stagger'] ?? 200 );
}

// Get wrapper attributes string.
$wrapper_attributes = get_block_wrapper_attributes( $wrapper_attrs );

// Build all data attributes.
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Build inner wrapper classes and attributes (for item animation).
$inner_classes = [ 'blockflow-counter__items', 'blockflow-grid' ];
$inner_attrs   = '';

if ( $item_anim_type ) {
	$inner_classes[] = 'blockflow-animate-items';
	$inner_attrs     = sprintf(
		' data-animation="%s" data-duration="%s" data-stagger="%s"',
		esc_attr( $item_anim_type ),
		esc_attr( $item_duration ),
		esc_attr( $item_stagger )
	);
}

$inner_class_str = implode( ' ', $inner_classes );

// Build items HTML.
$items_html = '';
$item_index = 0;
$item_class = $item_anim_type ? 'blockflow-counter__item blockflow-animate-item' : 'blockflow-counter__item';

foreach ( $items as $item ) {
	$item_id     = $item['id'] ?? '';
	$number_data = $item['number'] ?? [];
	$number      = $number_data['value'] ?? '0';
	$prefix      = $number_data['prefix'] ?? '';
	$suffix      = $number_data['suffix'] ?? '';
	$label       = $item['label'] ?? '';
	$icon_data   = $item['icon'] ?? [];

	// Extract icon properties.
	$icon_source = $icon_data['source'] ?? 'library';
	$icon_name   = $icon_data['name'] ?? '';
	$custom_svg  = $icon_data['customSvg'] ?? [];

	// Check if icon exists (library with name or upload with URL).
	$has_icon = $show_icon && (
		( 'library' === $icon_source && ! empty( $icon_name ) ) ||
		( 'upload' === $icon_source && ! empty( $custom_svg['url'] ) )
	);

	// Build item content.
	$item_content = '';

	// Icon at top position (before number-row).
	// Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
	if ( $has_icon && 'top' === $icon_position ) {
		$icon_html = Icon_Renderer::render(
			[
				'name'      => $icon_name,
				'customSvg' => 'upload' === $icon_source ? $custom_svg : [],
				'wrap'      => false,
			]
		);

		$item_content .= sprintf(
			'<span class="blockflow-counter__icon">%s</span>',
			$icon_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer::render() uses wp_kses() internally.
		);
	}

	// Number row (icon inline + prefix + number + suffix).
	$number_row_content = '';

	// Icon at inline position (inside number-row).
	// Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
	if ( $has_icon && 'inline' === $icon_position ) {
		$icon_html = Icon_Renderer::render(
			[
				'name'      => $icon_name,
				'customSvg' => 'upload' === $icon_source ? $custom_svg : [],
				'wrap'      => false,
			]
		);

		$number_row_content .= sprintf(
			'<span class="blockflow-counter__icon blockflow-counter__icon--inline">%s</span>',
			$icon_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer::render() uses wp_kses() internally.
		);
	}

	// Prefix.
	if ( ! empty( $prefix ) ) {
		$number_row_content .= sprintf(
			'<span class="blockflow-counter__prefix">%s</span>',
			esc_html( $prefix )
		);
	}

	// Value wrapper (number + suffix grouped together).
	$value_content = '';

	// Number.
	if ( $number_anim_type ) {
		$value_content .= sprintf(
			'<span class="blockflow-counter__number" data-target="%s">0</span>',
			esc_attr( $number )
		);
	} else {
		$value_content .= sprintf(
			'<span class="blockflow-counter__number">%s</span>',
			esc_html( $number )
		);
	}

	// Suffix.
	if ( ! empty( $suffix ) ) {
		$value_content .= sprintf(
			'<span class="blockflow-counter__suffix">%s</span>',
			esc_html( $suffix )
		);
	}

	$number_row_content .= sprintf(
		'<span class="blockflow-counter__value">%s</span>',
		$value_content
	);

	$item_content .= sprintf(
		'<div class="blockflow-counter__number-row">%s</div>',
		$number_row_content
	);

	// Separator.
	if ( $sep_enabled ) {
		$item_content .= '<span class="blockflow-counter__separator"></span>';
	}

	// Label.
	if ( ! empty( $label ) ) {
		$item_content .= sprintf(
			'<span class="blockflow-counter__label">%s</span>',
			esc_html( $label )
		);
	}

	// Build item style for stagger delay (uses CSS variables).
	// NOTE: Animation delay must be inline style (not in CSS Generator) because it's calculated
	// per item based on index position: delay = item_index * stagger. Each item gets a different
	// delay value to create the staggered animation effect.
	$item_style = '';
	if ( $item_anim_type ) {
		$delay      = $item_index * $item_stagger;
		$item_style = sprintf(
			'--blockflow-anim-delay:%dms;--blockflow-anim-duration:%dms;',
			$delay,
			$item_duration
		);
	}

	// Wrap in item div (no separate content wrapper).
	if ( $item_style ) {
		$items_html .= sprintf(
			'<div class="%s" style="%s">%s</div>',
			esc_attr( $item_class ),
			esc_attr( $item_style ),
			$item_content
		);
	} else {
		$items_html .= sprintf(
			'<div class="%s">%s</div>',
			esc_attr( $item_class ),
			$item_content
		);
	}

	++$item_index;
}

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $inner_attrs: Built from sprintf() with esc_attr() values.
 * - $items_html: Built from escaped components - esc_attr() for classes/styles,
 *   esc_html() for text content, Icon_Renderer::render() for SVGs (uses wp_kses).
 */
printf(
	'<%1$s %2$s%3$s><div class="%4$s"%5$s>%6$s</div></%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	esc_attr( $inner_class_str ),
	$inner_attrs, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$items_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
