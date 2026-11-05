<?php
/**
 * Block Manager Class
 *
 * Single source of truth for all blocks.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Manager class.
 */
class Block_Manager {

	/**
	 * Base blocks registry.
	 * Order defines display order.
	 */
	const BASE_BLOCKS = [
		// Layout.
		[
			'slug'        => 'container',
			'title'       => 'Container',
			'description' => 'Flexible container with responsive layout options.',
			'category'    => 'layout',
			'is_core'     => true,
		],
		[
			'slug'        => 'grid',
			'title'       => 'Grid',
			'description' => 'CSS Grid container with responsive column layouts.',
			'category'    => 'layout',
		],

		// Content.
		[
			'slug'        => 'accordion',
			'title'       => 'Accordion',
			'description' => 'Collapsible accordion with expandable sections.',
			'category'    => 'content',
		],
		[
			'slug'        => 'accordion-item',
			'title'       => 'Accordion Item',
			'description' => 'Content container for accordion item.',
			'category'    => 'content',
			'is_child'    => true,
			'parent'      => 'accordion',
		],
		[
			'slug'        => 'button',
			'title'       => 'Button',
			'description' => 'A customizable button with optional icon.',
			'category'    => 'content',
		],
		[
			'slug'        => 'heading',
			'title'       => 'Heading',
			'description' => 'Advanced heading block with typography, effects, and hover states.',
			'category'    => 'content',
		],
		[
			'slug'        => 'text',
			'title'       => 'Text',
			'description' => 'Advanced text block with typography, columns, and effects.',
			'category'    => 'content',
		],
		[
			'slug'        => 'note',
			'title'       => 'Note',
			'description' => 'Callout note with customizable type, icon, and styling.',
			'category'    => 'content',
		],
		[
			'slug'        => 'info-card',
			'title'       => 'Info Card',
			'description' => 'Single info card with media, title, description and optional link.',
			'category'    => 'content',
		],
		[
			'slug'        => 'social-icons',
			'title'       => 'Social Icons',
			'description' => 'Display a collection of social media icons with links.',
			'category'    => 'content',
		],
		[
			'slug'        => 'team-member',
			'title'       => 'Team Member',
			'description' => 'Display team member profiles with image, name, description, and social links.',
			'category'    => 'content',
		],
		[
			'slug'        => 'testimonial',
			'title'       => 'Testimonial',
			'description' => 'Display customer testimonials with rating, quote, and author information.',
			'category'    => 'content',
		],

		// Media.
		[
			'slug'        => 'icon',
			'title'       => 'Icon',
			'description' => 'SVG icon block with customization options.',
			'category'    => 'media',
		],
		[
			'slug'        => 'icon-list',
			'title'       => 'Icon List',
			'description' => 'Display a list of items with icons and text.',
			'category'    => 'media',
		],
		[
			'slug'        => 'icon-set',
			'title'       => 'Icon Set',
			'description' => 'Display a collection of icons inline with optional links.',
			'category'    => 'media',
		],
		[
			'slug'        => 'image',
			'title'       => 'Image',
			'description' => 'Display images with advanced styling options including masks, overlays, and hover effects.',
			'category'    => 'media',
		],
		[
			'slug'        => 'video',
			'title'       => 'Video',
			'description' => 'Embed videos from YouTube, Vimeo, Dailymotion, or self-hosted sources.',
			'category'    => 'media',
		],
		[
			'slug'        => 'brand-logos',
			'title'       => 'Brand Logos',
			'description' => 'Display brand, client, or partner logos in a slider or grid layout.',
			'category'    => 'media',
		],
		[
			'slug'        => 'carousel-images',
			'title'       => 'Carousel Images',
			'description' => 'Display images in a beautiful carousel with lightbox, navigation, and customizable effects.',
			'category'    => 'media',
		],
		[
			'slug'        => 'gallery',
			'title'       => 'Gallery',
			'description' => 'Display images in beautiful grid, masonry, or tiled layouts with lightbox support.',
			'category'    => 'media',
		],
		[
			'slug'        => 'slider',
			'title'       => 'Slider',
			'description' => 'Create beautiful slideshows with customizable navigation and effects.',
			'category'    => 'media',
		],
		[
			'slug'        => 'slider-slide',
			'title'       => 'Slide',
			'description' => 'Individual slide for the slider block.',
			'category'    => 'media',
			'is_child'    => true,
			'parent'      => 'slider',
		],

		// Interactive.
		[
			'slug'        => 'countdown',
			'title'       => 'Countdown',
			'description' => 'Display a countdown timer to a specific date and time.',
			'category'    => 'interactive',
		],
		[
			'slug'        => 'counter',
			'title'       => 'Counter',
			'description' => 'Display animated counter statistics with icons and labels.',
			'category'    => 'interactive',
		],
		[
			'slug'        => 'google-map',
			'title'       => 'Google Map',
			'description' => 'Embed a Google Map with customizable location, zoom level, and styling options.',
			'category'    => 'interactive',
		],
		[
			'slug'        => 'marquee-text',
			'title'       => 'Marquee Text',
			'description' => 'Display scrolling text with decorative dividers for announcements and highlights.',
			'category'    => 'interactive',
		],
		[
			'slug'        => 'progress-bar',
			'title'       => 'Progress Bar',
			'description' => 'Display progress with various layouts including line, circle, and box styles.',
			'category'    => 'interactive',
		],
		[
			'slug'        => 'separator',
			'title'       => 'Separator',
			'description' => 'A customizable separator line with style options.',
			'category'    => 'interactive',
		],
	];

	/**
	 * Cached blocks.
	 *
	 * @var array|null
	 */
	private static $blocks = null;

	/**
	 * Registered blocks.
	 *
	 * @var array
	 */
	private static $registered = [];

	/**
	 * Initialize.
	 */
	public static function init() {
		add_action( 'init', [ __CLASS__, 'register_blocks' ], 5 );
		add_filter( 'block_categories_all', [ __CLASS__, 'register_block_category' ], 10, 2 );
	}

	/**
	 * Register block category.
	 */
	public static function register_block_category( $categories, $context ) {
		return array_merge(
			[
				[
					'slug'  => 'blockflow',
					'title' => __( 'BlockFlow', 'blockflow' ),
					'icon'  => null,
				],
			],
			$categories
		);
	}

	/**
	 * Get all blocks.
	 *
	 * Pro addon usage:
	 * add_filter( 'blockflow_blocks', function( $blocks ) {
	 *     $blocks[] = [ 'slug' => 'tabs', 'title' => 'Tabs', 'description' => '...', 'category' => 'content', 'is_pro' => true, 'path' => BLOCKFLOW_PRO_DIR . 'build/blocks/tabs' ];
	 *     return $blocks;
	 * } );
	 */
	public static function get_blocks() {
		if ( null === self::$blocks ) {
			self::$blocks = apply_filters( 'blockflow_blocks', self::BASE_BLOCKS );
		}
		return self::$blocks;
	}

	/**
	 * Get blocks directory.
	 */
	private static function get_blocks_dir() {
		$dir = BLOCKFLOW_DIR . 'build/blocks/';
		return is_dir( $dir ) ? $dir : BLOCKFLOW_DIR . 'src/blocks/';
	}

	/**
	 * Register all blocks.
	 */
	public static function register_blocks() {
		$blocks     = self::get_blocks();
		$blocks_dir = self::get_blocks_dir();
		$disabled   = get_option( 'blockflow_settings', [] )['disabled_blocks'] ?? [];

		foreach ( $blocks as $block ) {
			$slug = $block['slug'];

			// Skip pro blocks (pro addon handles registration).
			if ( ! empty( $block['is_pro'] ) ) {
				continue;
			}

			// Skip disabled (core and child blocks always register).
			if ( empty( $block['is_core'] ) && empty( $block['is_child'] ) && in_array( $slug, $disabled, true ) ) {
				continue;
			}

			// Child follows parent.
			if ( ! empty( $block['is_child'] ) && in_array( $block['parent'], $disabled, true ) ) {
				continue;
			}

			// Register.
			$path = $blocks_dir . $slug;
			if ( file_exists( $path . '/block.json' ) ) {
				$result = register_block_type( $path );
				if ( $result ) {
					self::$registered[ $slug ] = $result;
				}
			}
		}

		do_action( 'blockflow_blocks_registered', self::$registered );
	}

	/**
	 * Get blocks for admin page.
	 */
	public static function get_blocks_for_admin() {
		$blocks   = self::get_blocks();
		$disabled = get_option( 'blockflow_settings', [] )['disabled_blocks'] ?? [];

		$result = [];
		foreach ( $blocks as $block ) {
			if ( ! empty( $block['is_child'] ) ) {
				continue;
			}

			$slug    = $block['slug'];
			$is_core = ! empty( $block['is_core'] );

			$result[] = [
				'slug'        => $slug,
				'title'       => $block['title'],
				'description' => $block['description'],
				'categoryKey' => $block['category'],
				'is_pro'      => ! empty( $block['is_pro'] ),
				'is_core'     => $is_core,
				'enabled'     => $is_core || ! in_array( $slug, $disabled, true ),
			];
		}

		return $result;
	}

	/**
	 * Check if block is enabled.
	 */
	public static function is_block_enabled( $slug ) {
		$disabled = get_option( 'blockflow_settings', [] )['disabled_blocks'] ?? [];
		return ! in_array( $slug, $disabled, true );
	}

	/**
	 * Get registered block slugs.
	 */
	public static function get_registered_blocks() {
		return array_keys( self::$registered );
	}

	/**
	 * Get core block slugs.
	 */
	public static function get_core_slugs() {
		$slugs = [];
		foreach ( self::BASE_BLOCKS as $block ) {
			if ( ! empty( $block['is_core'] ) ) {
				$slugs[] = $block['slug'];
			}
		}
		return $slugs;
	}
}
