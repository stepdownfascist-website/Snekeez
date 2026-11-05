<?php
/**
 * Accordion Item Block - Server-side Render
 *
 * Returns only the InnerBlocks content.
 * Parent accordion block handles the item wrapper, heading, and icon.
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

// Just return the inner content - parent handles wrapping.
// $content: WordPress InnerBlocks content, pre-rendered and escaped by block renderer.
echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- InnerBlocks pre-escaped by WordPress.
