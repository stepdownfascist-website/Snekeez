<?php
/**
 * Countdown Block - Server-side Render
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

$block_id       = $attributes['blockId'] ?? '';
$end_date       = $attributes['endDate'] ?? '';
$show_days      = $attributes['showDays'] ?? true;
$show_hours     = $attributes['showHours'] ?? true;
$show_minutes   = $attributes['showMinutes'] ?? true;
$show_seconds   = $attributes['showSeconds'] ?? true;
$aria_live      = $attributes['ariaLive'] ?? 'off';
$separator      = $attributes['separator'] ?? [
	'type'   => 'colon',
	'custom' => '',
];
$labels         = $attributes['labels'] ?? [];
$expired_action = $attributes['expiredAction'] ?? [
	'type'    => 'hide',
	'message' => '',
];
$animation      = $attributes['animation'] ?? [];

// Default labels.
$default_labels = [
	'show'     => true,
	'position' => 'below',
	'days'     => 'Days',
	'day'      => 'Day',
	'hours'    => 'Hours',
	'hour'     => 'Hour',
	'minutes'  => 'Minutes',
	'minute'   => 'Minute',
	'seconds'  => 'Seconds',
	'second'   => 'Second',
];
$labels         = wp_parse_args( $labels, $default_labels );

// Get separator character.
$separator_type  = $separator['type'] ?? 'colon';
$separator_chars = [
	'none'   => '',
	'colon'  => ':',
	'slash'  => '/',
	'dot'    => '•',
	'dash'   => '-',
	'custom' => $separator['custom'] ?? '',
];
$separator_char  = $separator_chars[ $separator_type ] ?? ':';

// Build wrapper classes.
$wrapper_classes = [ 'blockflow-block', 'blockflow-countdown', 'blockflow-countdown--loading' ];

if ( $block_id ) {
	$wrapper_classes[] = 'blockflow-countdown-' . $block_id;
}

// Add standard classes (className, visibility, animation).
$wrapper_classes = HTML_Helpers::build_wrapper_classes( $wrapper_classes, $attributes );

// Labels position.
if ( false !== $labels['show'] ) {
	$wrapper_classes[] = 'blockflow-countdown--labels-' . ( $labels['position'] ?? 'below' );
}

// Build all data attributes.
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Extra countdown-specific animation attributes (duration, delay, easing).
$extra_animation_attrs_html = '';
if ( ! empty( $animation['type'] ) ) {
	$extra_animation_attrs_html .= sprintf( ' data-animation-duration="%s"', esc_attr( $animation['duration'] ?? 400 ) );
	$extra_animation_attrs_html .= sprintf( ' data-animation-delay="%s"', esc_attr( $animation['delay'] ?? 0 ) );
	$extra_animation_attrs_html .= sprintf( ' data-animation-easing="%s"', esc_attr( $animation['easing'] ?? 'ease' ) );
}

// Get per-unit show label flags.
$custom_label_flags = $labels['custom'] ?? [];
$show_label_days    = $custom_label_flags['days'] ?? true;
$show_label_hours   = $custom_label_flags['hours'] ?? true;
$show_label_minutes = $custom_label_flags['minutes'] ?? true;
$show_label_seconds = $custom_label_flags['seconds'] ?? true;

// Build data attributes for JS.
$data_attrs = [
	'end-date'           => $end_date,
	'show-days'          => $show_days ? 'true' : 'false',
	'show-hours'         => $show_hours ? 'true' : 'false',
	'show-minutes'       => $show_minutes ? 'true' : 'false',
	'show-seconds'       => $show_seconds ? 'true' : 'false',
	'separator'          => $separator_char,
	'show-label-days'    => ( false !== $show_label_days ) ? 'true' : 'false',
	'show-label-hours'   => ( false !== $show_label_hours ) ? 'true' : 'false',
	'show-label-minutes' => ( false !== $show_label_minutes ) ? 'true' : 'false',
	'show-label-seconds' => ( false !== $show_label_seconds ) ? 'true' : 'false',
	'label-days'         => $labels['days'],
	'label-day'          => $labels['day'],
	'label-hours'        => $labels['hours'],
	'label-hour'         => $labels['hour'],
	'label-minutes'      => $labels['minutes'],
	'label-minute'       => $labels['minute'],
	'label-seconds'      => $labels['seconds'],
	'label-second'       => $labels['second'],
	'expired-action'     => $expired_action['type'] ?? 'hide',
	'expired-msg'        => $expired_action['message'] ?? '',
];

$data_attrs_html = '';
foreach ( $data_attrs as $key => $value ) {
	$data_attrs_html .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
}

// ARIA live attribute.
$aria_attr = '';
if ( 'off' !== $aria_live ) {
	$aria_attr = ' aria-live="' . esc_attr( $aria_live ) . '"';
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $wrapper_classes ),
	]
);

// Calculate initial countdown values from end date (avoids showing "00" on cached pages).
$countdown_days    = 0;
$countdown_hours   = 0;
$countdown_minutes = 0;
$countdown_seconds = 0;

if ( $end_date ) {
	$end_timestamp = strtotime( $end_date );
	$now           = time();
	$diff          = $end_timestamp - $now;

	if ( $diff > 0 ) {
		$countdown_days    = (int) floor( $diff / 86400 );
		$countdown_hours   = (int) floor( ( $diff % 86400 ) / 3600 );
		$countdown_minutes = (int) floor( ( $diff % 3600 ) / 60 );
		$countdown_seconds = (int) ( $diff % 60 );
	}
}

// Build countdown items for initial render (JS will take over and update live).
$items = [];

if ( $show_days ) {
	$items[] = [
		'key'        => 'days',
		'value'      => str_pad( $countdown_days, 2, '0', STR_PAD_LEFT ),
		'label'      => 1 === $countdown_days ? $labels['day'] : $labels['days'],
		'show_label' => $show_label_days,
	];
}
if ( $show_hours ) {
	$items[] = [
		'key'        => 'hours',
		'value'      => str_pad( $countdown_hours, 2, '0', STR_PAD_LEFT ),
		'label'      => 1 === $countdown_hours ? $labels['hour'] : $labels['hours'],
		'show_label' => $show_label_hours,
	];
}
if ( $show_minutes ) {
	$items[] = [
		'key'        => 'minutes',
		'value'      => str_pad( $countdown_minutes, 2, '0', STR_PAD_LEFT ),
		'label'      => 1 === $countdown_minutes ? $labels['minute'] : $labels['minutes'],
		'show_label' => $show_label_minutes,
	];
}
if ( $show_seconds ) {
	$items[] = [
		'key'        => 'seconds',
		'value'      => str_pad( $countdown_seconds, 2, '0', STR_PAD_LEFT ),
		'label'      => 1 === $countdown_seconds ? $labels['second'] : $labels['seconds'],
		'show_label' => $show_label_seconds,
	];
}

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

// Build items HTML.
$items_html  = '';
$total_items = count( $items );
foreach ( $items as $index => $item ) {
	$label_html = '';
	if ( false !== $item['show_label'] ) {
		$label_html = sprintf( '<span class="blockflow-countdown__label">%s</span>', esc_html( $item['label'] ) );
	}

	$items_html .= sprintf(
		'<div class="blockflow-countdown__item" data-unit="%s"><span class="blockflow-countdown__digit">%s</span>%s</div>',
		esc_attr( $item['key'] ),
		esc_html( $item['value'] ),
		$label_html
	);

	if ( $separator_char && $index < $total_items - 1 ) {
		$items_html .= sprintf( '<span class="blockflow-countdown__separator">%s</span>', esc_html( $separator_char ) );
	}
}

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $extra_animation_attrs_html: Built from sprintf() with esc_attr() values.
 * - $aria_attr: Built from sprintf() with esc_attr() values.
 * - $items_html: Built from escaped components - esc_html() for labels,
 *   esc_attr() for classes and data attributes.
 */
printf(
	'<%1$s %2$s%3$s%4$s%5$s><div class="blockflow-countdown__items">%6$s</div></%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$extra_animation_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$aria_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$items_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
