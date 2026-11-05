<?php
/**
 * Video Block - Server-side Render
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
use ThemeRuby\BlockFlow\Icon_Renderer;
use ThemeRuby\BlockFlow\Video_Helpers;

$block_id         = $attributes['blockId'] ?? '';
$source           = $attributes['source'] ?? 'youtube';
$video_url        = $attributes['videoUrl'] ?? '';
$video_id         = $attributes['videoId'] ?? '';
$video_file       = $attributes['videoFile'] ?? [];
$start_time       = $attributes['startTime'] ?? 0;
$end_time         = $attributes['endTime'] ?? 0;
$autoplay         = $attributes['autoplay'] ?? false;
$mute             = $attributes['mute'] ?? false;
$loop             = $attributes['loop'] ?? false;
$controls         = $attributes['controls'] ?? true;
$lazy_load        = $attributes['lazyLoad'] ?? true;
$privacy_mode     = $attributes['privacyMode'] ?? false;
$captions         = $attributes['captions'] ?? false;
$suggested_videos = $attributes['suggestedVideos'] ?? 'channel';
$download_button  = $attributes['downloadButton'] ?? false;
$preload          = $attributes['preload'] ?? 'metadata';
$poster           = $attributes['poster'] ?? [];
$overlay          = $attributes['overlay'] ?? [];
$play_icon        = $attributes['playIcon'] ?? [];
$lightbox         = $attributes['lightbox'] ?? [];
$anchor           = $attributes['anchor'] ?? '';

// Build all data attributes.
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

// Overlay settings.
$overlay_enabled    = $overlay['enabled'] ?? false;
$overlay_image      = $overlay['image'] ?? [];
$overlay_image_size = $overlay['imageSize'] ?? 'full';
$overlay_lightbox   = $overlay['lightbox'] ?? false;

// Play icon settings.
$play_icon_enabled      = $play_icon['enabled'] ?? true;
$play_icon_type         = $play_icon['type'] ?? 'icon';
$play_icon_icon_data    = $play_icon['icon'] ?? [];
$play_icon_name         = $play_icon_icon_data['name'] ?? 'play-circle';
$play_icon_custom_image = $play_icon['customImage'] ?? [];

// Check if we have video.
$is_self_hosted = 'self-hosted' === $source;
$has_video      = $is_self_hosted ? ! empty( $video_file['url'] ) : ! empty( $video_id );

if ( ! $has_video ) {
	return;
}

// Build embed URL for external videos.
$embed_url = '';
if ( ! $is_self_hosted ) {
	$embed_url = Video_Helpers::build_embed_url(
		$source,
		$video_id,
		[
			'autoplay'         => $autoplay,
			'mute'             => $mute,
			'loop'             => $loop,
			'controls'         => $controls,
			'privacy_mode'     => $privacy_mode,
			'captions'         => $captions,
			'suggested_videos' => $suggested_videos,
			'start_time'       => $start_time,
			'end_time'         => $end_time,
		]
	);
}

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-video',
	'blockflow-video-' . esc_attr( $block_id ),
];

if ( $overlay_enabled ) {
	$classes[] = 'blockflow-video--overlay';
}

if ( $lazy_load && ! $overlay_enabled ) {
	$classes[] = 'blockflow-video--lazy';
}

// Lightbox data attributes.
$lightbox_attrs_html = '';
if ( $overlay_lightbox ) {
	$classes[]           = 'blockflow-video--lightbox';
	$lightbox_attrs_html = HTML_Helpers::build_lightbox_attrs( $lightbox, false );
}

// Add standard classes (className, visibility, animation).
$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $classes ),
		'id'    => $anchor ? esc_attr( $anchor ) : null,
	]
);

// Data attributes for frontend JS.
$video_data_attrs = '';
if ( $overlay_enabled ) {
	$video_data_attrs .= sprintf( ' data-source="%s"', esc_attr( $source ) );
	$video_data_attrs .= sprintf( ' data-video-id="%s"', esc_attr( $video_id ) );
	$video_data_attrs .= sprintf( ' data-autoplay="%s"', $autoplay ? 'true' : 'false' );
	$video_data_attrs .= sprintf( ' data-mute="%s"', $mute ? 'true' : 'false' );

	if ( $is_self_hosted ) {
		$video_data_attrs .= sprintf( ' data-video-url="%s"', esc_url( $video_file['url'] ?? '' ) );
	} else {
		$video_data_attrs .= sprintf( ' data-embed-url="%s"', esc_url( $embed_url ) );
	}

	// Track video JS usage.
	Asset_Loader::track_video_usage();
}

// Get play icon SVG.
// Icon_Renderer resolves path/viewBox from icons.json via Icon_Data::find().
// Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
$play_icon_svg = '';
if ( $overlay_enabled && $play_icon_enabled && 'icon' === $play_icon_type ) {
	$play_icon_svg = Icon_Renderer::render(
		[
			'name'  => $play_icon_name,
			'class' => 'blockflow-video__play-icon',
			'wrap'  => false,
		]
	);
}

// Get overlay image URL.
$overlay_image_url = '';
if ( $overlay_enabled && ! empty( $overlay_image['id'] ) ) {
	$image_data = wp_get_attachment_image_src( $overlay_image['id'], $overlay_image_size );
	if ( $image_data ) {
		$overlay_image_url = $image_data[0];
	}
} elseif ( $overlay_enabled && ! empty( $overlay_image['url'] ) ) {
	$overlay_image_url = $overlay_image['url'];
}

// Get HTML tag.
$html_tag = HTML_Helpers::get_html_tag( $attributes );

// Build video content HTML.
$video_content_html = '';

if ( $overlay_enabled ) {
	// Overlay image or placeholder.
	$overlay_media = $overlay_image_url
		? sprintf( '<img src="%s" alt="" class="blockflow-video__overlay-image" loading="lazy" />', esc_url( $overlay_image_url ) )
		: '<div class="blockflow-video__overlay-placeholder"></div>';

	// Play button - $play_icon_svg from Icon_Renderer::render() uses wp_kses() internally.
	$play_btn_html = '';
	if ( $play_icon_enabled ) {
		$play_icon_content = ( 'upload' === $play_icon_type && ! empty( $play_icon_custom_image['url'] ) )
			? sprintf( '<img src="%s" alt="" class="blockflow-video__play-custom" />', esc_url( $play_icon_custom_image['url'] ) )
			: $play_icon_svg;

		$play_btn_html = sprintf(
			'<button class="blockflow-video__play-btn" type="button" aria-label="%s">%s</button>',
			esc_attr__( 'Play video', 'blockflow' ),
			$play_icon_content // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer::render() uses wp_kses() internally.
		);
	}

	$video_content_html = sprintf(
		'<div class="blockflow-video__overlay"%s>%s%s</div>',
		$video_data_attrs,
		$overlay_media,
		$play_btn_html
	);
} elseif ( $is_self_hosted ) {
	// Self-hosted video.
	$video_attrs   = [];
	$video_attrs[] = sprintf( 'src="%s"', esc_url( $video_file['url'] ?? '' ) );
	if ( ! empty( $poster['url'] ) ) {
		$video_attrs[] = sprintf( 'poster="%s"', esc_url( $poster['url'] ) );
	}
	if ( $controls ) {
		$video_attrs[] = 'controls';
	}
	if ( $mute ) {
		$video_attrs[] = 'muted';
	}
	if ( $loop ) {
		$video_attrs[] = 'loop';
	}
	if ( $autoplay ) {
		$video_attrs[] = 'autoplay';
	}
	if ( ! $download_button ) {
		$video_attrs[] = 'controlslist="nodownload"';
	}
	$video_attrs[] = sprintf( 'preload="%s"', esc_attr( $preload ) );
	$video_attrs[] = 'playsinline';

	$video_content_html = sprintf(
		'<video class="blockflow-video__player" %s></video>',
		implode( ' ', $video_attrs )
	);
} elseif ( $lazy_load ) {
	// Embed video (YouTube/Vimeo) - lazy load.
	$video_content_html = sprintf( '<div class="blockflow-video__lazy-wrapper" data-src="%s"></div>', esc_url( $embed_url ) );
} else {
	// Embed video (YouTube/Vimeo) - iframe.
	$video_content_html = sprintf(
		'<iframe class="blockflow-video__iframe" src="%s" title="%s" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>',
		esc_url( $embed_url ),
		esc_attr__( 'Video', 'blockflow' )
	);
}

// Build inner HTML.
$inner_html = sprintf( '<div class="blockflow-video__wrapper">%s</div>', $video_content_html );

/*
 * Output - Escaping:
 * - tag_escape( $html_tag ): Sanitizes HTML tag name to prevent injection.
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $lightbox_attrs_html: Pre-escaped by HTML_Helpers::build_lightbox_attrs() using esc_attr().
 * - $inner_html: Built from escaped components - esc_url()/esc_attr() for video/images,
 *   Icon_Renderer::render() for play icons (uses wp_kses).
 */
printf(
	'<%1$s %2$s%3$s%4$s>%5$s</%1$s>',
	tag_escape( $html_tag ),
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$lightbox_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$inner_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
