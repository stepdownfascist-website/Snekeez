<?php
/**
 * Block Categories
 *
 * Category definitions for admin dashboard grouping.
 * The WordPress editor uses a single 'blockflow' category (registered by Block_Manager).
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow\Admin;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Block Categories class.
 */
class Block_Categories {

	/**
	 * Category definitions for admin dashboard grouping.
	 *
	 * @var array
	 */
	private static $categories = [
		'layout'      => [
			'slug'        => 'layout',
			'title'       => 'Layout',
			'description' => 'Structure and organize your content.',
			'icon'        => 'layout',
			'order'       => 10,
		],
		'content'     => [
			'slug'        => 'content',
			'title'       => 'Content',
			'description' => 'Text, links, and informational blocks.',
			'icon'        => 'text',
			'order'       => 20,
		],
		'media'       => [
			'slug'        => 'media',
			'title'       => 'Media',
			'description' => 'Images, icons, and visual elements.',
			'icon'        => 'image',
			'order'       => 30,
		],
		'interactive' => [
			'slug'        => 'interactive',
			'title'       => 'Interactive',
			'description' => 'Dynamic and engaging elements.',
			'icon'        => 'zap',
			'order'       => 40,
		],
	];

	/**
	 * Get all categories.
	 *
	 * @return array All category definitions.
	 */
	public static function get_categories() {
		return self::$categories;
	}

	/**
	 * Get a single category by key.
	 *
	 * @param string $key Category key.
	 * @return array|null Category data or null if not found.
	 */
	public static function get_category( $key ) {
		return self::$categories[ $key ] ?? null;
	}

	/**
	 * Get category slug by key.
	 *
	 * @param string $key Category key.
	 * @return string|null Category slug or null if not found.
	 */
	public static function get_slug( $key ) {
		$category = self::get_category( $key );
		return $category ? $category['slug'] : null;
	}

	/**
	 * Get categories sorted by order.
	 *
	 * @return array Sorted categories.
	 */
	public static function get_sorted_categories() {
		$categories = self::$categories;
		uasort(
			$categories,
			function ( $a, $b ) {
				return $a['order'] <=> $b['order'];
			}
		);
		return $categories;
	}

	/**
	 * Get categories for REST API / JavaScript.
	 *
	 * @return array Categories formatted for frontend use.
	 */
	public static function get_categories_for_js() {
		$categories = [];
		foreach ( self::get_sorted_categories() as $key => $category ) {
			$categories[] = [
				'key'         => $key,
				'slug'        => $category['slug'],
				'title'       => $category['title'],
				'description' => $category['description'],
				'icon'        => $category['icon'],
				'order'       => $category['order'],
			];
		}
		return $categories;
	}

	/**
	 * Get category key from WordPress category slug.
	 *
	 * @param string $slug WordPress category slug.
	 * @return string|null Category key or null if not found.
	 */
	public static function get_key_from_slug( $slug ) {
		foreach ( self::$categories as $key => $category ) {
			if ( $category['slug'] === $slug ) {
				return $key;
			}
		}
		return null;
	}
}
