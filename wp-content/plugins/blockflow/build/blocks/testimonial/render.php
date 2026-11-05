<?php
/**
 * Testimonial Card Block - Server-side Render
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

$block_id = $attributes['blockId'] ?? '';
$anchor   = $attributes['anchor'] ?? '';

// Content attributes.
$show_rating  = $attributes['showRating'] ?? true;
$show_avatar  = $attributes['showAvatar'] ?? true;
$rating       = $attributes['rating'] ?? 5;
$card_title   = $attributes['title'] ?? '';
$quote        = $attributes['quote'] ?? '';
$author_image = $attributes['authorImage'] ?? [];
$author_name  = $attributes['authorName'] ?? '';
$author_job   = $attributes['authorJob'] ?? '';
$alignment    = $attributes['alignment'] ?? 'left';

// Check if block has any content.
$has_rating  = $show_rating && $rating > 0;
$has_title   = ! empty( trim( wp_strip_all_tags( $card_title ) ) );
$has_quote   = ! empty( trim( wp_strip_all_tags( $quote ) ) );
$has_image   = $show_avatar && ! empty( $author_image['url'] );
$has_name    = ! empty( trim( wp_strip_all_tags( $author_name ) ) );
$has_job     = ! empty( trim( wp_strip_all_tags( $author_job ) ) );
$has_author  = $has_image || $has_name || $has_job;
$has_content = $has_rating || $has_title || $has_quote || $has_author;

// Don't render if no content.
if ( ! $has_content ) {
	return;
}

// Build wrapper classes.
$classes = [
	'blockflow-block',
	'blockflow-testimonial-card',
	'blockflow-testimonial-card-' . esc_attr( $block_id ),
];

if ( 'left' !== $alignment ) {
	$classes[] = 'blockflow-testimonial-card--align-' . esc_attr( $alignment );
}

$classes = HTML_Helpers::build_wrapper_classes( $classes, $attributes );

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $classes ),
		'id'    => $anchor ? esc_attr( $anchor ) : null,
	]
);

// Data attributes for animation and background.
$data_attrs_html = HTML_Helpers::build_data_attrs( $attributes );

if ( ! function_exists( 'blockflow_render_stars' ) ) {
	/**
	 * Render stars with half-star support.
	 * Icon_Renderer::render() uses wp_kses() internally for safe SVG output.
	 *
	 * @param float $rating Rating value (0-5).
	 * @return string Stars HTML.
	 */
	function blockflow_render_stars( $rating ) {
		$output     = '';
		$full_stars = floor( $rating );
		$has_half   = ( $rating - $full_stars ) >= 0.5;

		for ( $i = 0; $i < 5; $i++ ) {
			if ( $i < $full_stars ) {
				$star_svg = Icon_Renderer::render(
					[
						'name' => 'star-bold',
						'wrap' => false,
					]
				);
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer::render() uses wp_kses() internally.
				$output .= '<span class="blockflow-testimonial-card__star">' . $star_svg . '</span>';
			} elseif ( $i === $full_stars && $has_half ) {
				$star_svg = Icon_Renderer::render(
					[
						'name' => 'star-half',
						'wrap' => false,
					]
				);
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer::render() uses wp_kses() internally.
				$output .= '<span class="blockflow-testimonial-card__star blockflow-testimonial-card__star--half">' . $star_svg . '</span>';
			} else {
				$star_svg = Icon_Renderer::render(
					[
						'name' => 'star',
						'wrap' => false,
					]
				);
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon_Renderer::render() uses wp_kses() internally.
				$output .= '<span class="blockflow-testimonial-card__star blockflow-testimonial-card__star--empty">' . $star_svg . '</span>';
			}
		}

		return $output;
	}
}

// Build output.
$output = '';

// Rating.
if ( $has_rating ) {
	$output .= '<div class="blockflow-testimonial-card__rating">';
	$output .= blockflow_render_stars( $rating );
	$output .= '</div>';
}

// Title.
if ( $has_title ) {
	$output .= sprintf(
		'<h3 class="blockflow-testimonial-card__title">%s</h3>',
		wp_kses( $card_title, HTML_Helpers::get_allowed_inline_html() )
	);
}

// Quote.
if ( $has_quote ) {
	$output .= sprintf(
		'<p class="blockflow-testimonial-card__quote">%s</p>',
		wp_kses( $quote, HTML_Helpers::get_allowed_inline_html() )
	);
}

// Author section.
if ( $has_author ) {
	$author_html = '<div class="blockflow-testimonial-card__author">';

	// Avatar.
	if ( $show_avatar && $has_image ) {
		$author_html .= sprintf(
			'<img src="%s" alt="%s" class="blockflow-testimonial-card__avatar" />',
			esc_url( $author_image['url'] ),
			esc_attr( $author_image['alt'] ?? '' )
		);
	}

	// Author info (name + job).
	if ( $has_name || $has_job ) {
		$author_html .= '<div class="blockflow-testimonial-card__author-info">';

		if ( $has_name ) {
			$author_html .= sprintf(
				'<span class="blockflow-testimonial-card__name">%s</span>',
				wp_kses( $author_name, HTML_Helpers::get_allowed_inline_html() )
			);
		}

		if ( $has_job ) {
			$author_html .= sprintf(
				'<span class="blockflow-testimonial-card__job">%s</span>',
				wp_kses( $author_job, HTML_Helpers::get_allowed_inline_html() )
			);
		}

		$author_html .= '</div>';
	}

	$author_html .= '</div>';
	$output      .= $author_html;
}

/*
 * Output - Escaping:
 * - $wrapper_attributes: Pre-escaped by WordPress get_block_wrapper_attributes().
 * - $data_attrs_html: Pre-escaped by HTML_Helpers::build_data_attrs() using esc_attr().
 * - $output: Built from escaped components - esc_url()/esc_attr() for images,
 *   wp_kses() for quote/author text, Icon_Renderer::render() for icons (uses wp_kses).
 */
printf(
	'<div %1$s%2$s>%3$s</div>',
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$data_attrs_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
	$output // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See escaping note above.
);
