<?php
/**
 * Asset Loader Class
 *
 * Handles conditional loading of CSS and JS for blocks.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Asset_Loader {

	/**
	 * Blocks used on the current page.
	 *
	 * @var array
	 */
	private static $used_blocks = [];

	/**
	 * Generated CSS for the page (fallback for render-time).
	 *
	 * @var string
	 */
	private static $generated_css = '';

	/**
	 * Whether animations are used on the current page.
	 *
	 * @var bool
	 */
	private static $has_animations = false;

	/**
	 * Whether lazy background images are used on the current page.
	 *
	 * @var bool
	 */
	private static $has_lazy_bg = false;

	/**
	 * Whether video blocks with overlay are used on the current page.
	 *
	 * @var bool
	 */
	private static $has_video = false;

	/**
	 * FAQ schemas to output in footer.
	 *
	 * @var array
	 */
	private static $faq_schemas = [];

	/**
	 * CSS from post meta (save-time generated).
	 *
	 * @var string
	 */
	private static $saved_css = '';

	/**
	 * Whether saved CSS was already loaded.
	 *
	 * @var bool
	 */
	private static $css_loaded = false;

	/**
	 * Initialize the asset loader.
	 *
	 * Hook priorities and execution order:
	 *
	 * Frontend:
	 * - wp_enqueue_scripts @ 5: Register conditional scripts (animations, lazy-bg)
	 * - enqueue_block_assets @ 5: Register common block assets
	 * - enqueue_block_assets @ 10: Enqueue common block assets
	 * - wp_footer @ 5: Enqueue conditional scripts after render
	 *
	 * Editor:
	 * - enqueue_block_assets @ 5: Register common block assets
	 * - enqueue_block_assets @ 10: Enqueue common block assets
	 * - enqueue_block_editor_assets @ 5: Register editor scripts
	 * - enqueue_block_editor_assets @ 10: Localize and enqueue editor scripts
	 */
	public static function init() {
		// Register data script early (before blocks are registered).
		// This ensures blockflow-data is available as a dependency.
		add_action( 'init', [ __CLASS__, 'register_data_script' ], 5 );

		// Add data dependency to all blockflow block scripts.
		add_filter( 'block_type_metadata', [ __CLASS__, 'add_block_data_dependency' ] );

		// Common block assets: Register @ 5, enqueue @ 10 (works for both frontend and editor).
		add_action( 'enqueue_block_assets', [ __CLASS__, 'register_block_assets' ], 5 );
		add_action( 'enqueue_block_assets', [ __CLASS__, 'enqueue_block_assets' ], 10 );

		// Frontend-only: Register conditional scripts early.
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'register_frontend_scripts' ], 5 );

		// Editor-only: Register @ 5, localize and enqueue @ 10.
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'register_editor_assets' ], 5 );
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_editor_assets' ], 10 );

		// Google Fonts - must run during wp_enqueue_scripts before styles are printed.
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'output_google_fonts' ], 15 );

		// Page CSS - must run during wp_enqueue_scripts before styles are printed.
		// Priority 20 ensures it runs after blockflow-common is enqueued (priority 10).
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'output_page_css' ], 20 );

		// Conditional frontend scripts - enqueue in footer after render completes.
		add_action( 'wp_footer', [ __CLASS__, 'enqueue_conditional_scripts' ], 5 );

		// Output FAQ schemas in footer (after content renders).
		add_action( 'wp_footer', [ __CLASS__, 'output_faq_schemas' ], 20 );

		// Save-time CSS generation hooks.
		add_action( 'save_post', [ __CLASS__, 'generate_post_css' ], 20, 2 );
		add_action( 'rest_after_insert_wp_template', [ __CLASS__, 'generate_template_css' ], 20, 1 );
		add_action( 'rest_after_insert_wp_template_part', [ __CLASS__, 'generate_template_css' ], 20, 1 );
	}

	/**
	 * Register common block assets.
	 *
	 * Runs on enqueue_block_assets @ 5 (both frontend and editor).
	 * Registration happens before enqueue so inline styles can be added if needed.
	 */
	public static function register_block_assets() {
		$common_css = BLOCKFLOW_DIR . 'build/common.css';
		if ( file_exists( $common_css ) ) {
			wp_register_style(
				'blockflow-common',
				BLOCKFLOW_URL . 'build/common.css',
				[],
				BLOCKFLOW_VER
			);
		}
	}

	/**
	 * Register frontend-only conditional scripts.
	 *
	 * Runs on wp_enqueue_scripts @ 5 (frontend only).
	 * These scripts are conditionally enqueued in wp_footer based on block usage.
	 */
	public static function register_frontend_scripts() {
		// Register animation script.
		$animation_js = BLOCKFLOW_DIR . 'build/frontend/animations.js';
		if ( file_exists( $animation_js ) ) {
			wp_register_script(
				'blockflow-animations',
				BLOCKFLOW_URL . 'build/frontend/animations.js',
				[],
				BLOCKFLOW_VER,
				true
			);
		}

		// Register lazy background script.
		$lazy_bg_js = BLOCKFLOW_DIR . 'build/frontend/lazy-bg.js';
		if ( file_exists( $lazy_bg_js ) ) {
			wp_register_script(
				'blockflow-lazy-bg',
				BLOCKFLOW_URL . 'build/frontend/lazy-bg.js',
				[],
				BLOCKFLOW_VER,
				true
			);
		}

		// Register shared Swiper CSS (used by slider and carousel blocks).
		$swiper_css = BLOCKFLOW_DIR . 'build/frontend/swiper.css';
		if ( file_exists( $swiper_css ) ) {
			wp_register_style(
				'blockflow-swiper',
				BLOCKFLOW_URL . 'build/frontend/swiper.css',
				[],
				BLOCKFLOW_VER
			);
		}
	}

	/**
	 * Register editor assets early.
	 *
	 * Registration happens at priority 5, before localize/enqueue at priority 10.
	 */
	public static function register_editor_assets() {
		$editor_js    = BLOCKFLOW_DIR . 'build/editor.js';
		$editor_asset = BLOCKFLOW_DIR . 'build/editor.asset.php';

		// Register editor JavaScript.
		if ( file_exists( $editor_js ) && file_exists( $editor_asset ) ) {
			$asset = require $editor_asset;

			wp_register_script(
				'blockflow-editor',
				BLOCKFLOW_URL . 'build/editor.js',
				array_merge( $asset['dependencies'], [ 'blockflow-data' ] ),
				$asset['version'],
				true
			);
		}

		// Register editor CSS.
		$editor_css = BLOCKFLOW_DIR . 'build/editor.css';
		if ( file_exists( $editor_css ) ) {
			wp_register_style(
				'blockflow-editor',
				BLOCKFLOW_URL . 'build/editor.css',
				[],
				BLOCKFLOW_VER
			);
		}
	}

	/**
	 * Add blockflow-data as dependency to all blockflow block scripts.
	 *
	 * This ensures window.blockflowData is available before any block code runs.
	 * Uses block_type_metadata filter which runs before scripts are registered.
	 *
	 * @param array $metadata Block metadata from block.json.
	 * @return array Modified metadata.
	 */
	public static function add_block_data_dependency( $metadata ) {
		// Only modify blockflow blocks.
		if ( empty( $metadata['name'] ) || 0 !== strpos( $metadata['name'], 'blockflow/' ) ) {
			return $metadata;
		}

		// Add blockflow-data to editorScript dependencies.
		// WordPress will handle registering this dependency.
		if ( ! isset( $metadata['editorScriptDependencies'] ) ) {
			$metadata['editorScriptDependencies'] = [];
		}

		if ( ! in_array( 'blockflow-data', $metadata['editorScriptDependencies'], true ) ) {
			$metadata['editorScriptDependencies'][] = 'blockflow-data';
		}

		return $metadata;
	}

	/**
	 * Register the data script that provides icons and fonts.
	 *
	 * This script is a dependency for all block scripts to ensure
	 * window.blockflowData is available before any block code runs.
	 * Runs early during init (priority 5) before blocks are registered.
	 */
	public static function register_data_script() {
		// Skip if already registered.
		if ( wp_script_is( 'blockflow-data', 'registered' ) ) {
			return;
		}

		// Register an empty script as a dependency anchor.
		// Depends on wp-blocks which all blocks depend on.
		wp_register_script(
			'blockflow-data',
			false, // No actual file - just inline script.
			[ 'wp-blocks' ], // All blocks depend on wp-blocks.
			BLOCKFLOW_VER,
			false // Load in header to ensure it's available early.
		);

		// Add the data as inline script.
		$data_script = self::get_block_data_inline_script();
		if ( $data_script ) {
			wp_add_inline_script( 'blockflow-data', $data_script, 'after' );
		}
	}

	/**
	 * Get inline script that defines blockflowData global.
	 *
	 * Loads icons and fonts data once for all blocks.
	 * Uses pre-generated PHP files that return JSON strings directly.
	 *
	 * @return string Inline script content.
	 */
	private static function get_block_data_inline_script() {
		static $script = null;

		if ( null !== $script ) {
			return $script;
		}

		$icons_file = BLOCKFLOW_DIR . 'includes/data/icons-data.php';
		$fonts_file = BLOCKFLOW_DIR . 'includes/data/google-fonts-data.php';

		// Load pre-encoded JSON strings from PHP files.
		// Files return valid JSON strings via heredoc.
		$icons_json = file_exists( $icons_file ) ? include $icons_file : '{}';
		$fonts_json = file_exists( $fonts_file ) ? include $fonts_file : '[]';

		// Validate JSON strings (basic check).
		if ( ! is_string( $icons_json ) || '' === $icons_json ) {
			$icons_json = '{}';
		}
		if ( ! is_string( $fonts_json ) || '' === $fonts_json ) {
			$fonts_json = '[]';
		}

		// Build inline script (data is already JSON encoded).
		$script = sprintf(
			'window.blockflowData=window.blockflowData||{};window.blockflowData.icons=%s;window.blockflowData.fonts=%s;',
			$icons_json,
			$fonts_json
		);

		return $script;
	}

	/**
	 * Enqueue block assets for frontend and editor.
	 *
	 * Runs on enqueue_block_assets @ 10 (both frontend and editor).
	 * Assets are registered earlier via register_block_assets() at priority 5.
	 */
	public static function enqueue_block_assets() {
		if ( wp_style_is( 'blockflow-common', 'registered' ) ) {
			wp_enqueue_style( 'blockflow-common' );
		}
	}

	/**
	 * Enqueue editor-only assets.
	 *
	 * Assets are registered earlier via register_editor_assets() at priority 5.
	 * This runs at priority 10 to localize and enqueue.
	 */
	public static function enqueue_editor_assets() {
		// Check if editor script was registered.
		if ( ! wp_script_is( 'blockflow-editor', 'registered' ) ) {
			return;
		}

		// Add translations (must be after registration, before enqueue).
		wp_set_script_translations(
			'blockflow-editor',
			'blockflow',
			BLOCKFLOW_DIR . 'languages'
		);

		// Pass configuration to JavaScript (must be after registration).
		wp_localize_script(
			'blockflow-editor',
			'blockflowConfig',
			[
				'isPro'           => blockflow_is_pro(),
				'breakpoints'     => [
					'tablet' => 1024,
					'mobile' => 767,
				],
				'pluginUrl'       => BLOCKFLOW_URL,
				'svgUpload'       => SVG_Upload::get_js_config(),
				'darkModeEnabled' => Dark_Mode_Settings::is_enabled(),
				'imageSizes'      => self::get_image_size_options(),
			]
		);

		// Enqueue script (after localization).
		wp_enqueue_script( 'blockflow-editor' );

		// Enqueue editor CSS.
		if ( wp_style_is( 'blockflow-editor', 'registered' ) ) {
			wp_enqueue_style( 'blockflow-editor' );
		}
	}

	/**
	 * Get image size options for editor.
	 *
	 * @return array Array of [ 'value' => $slug, 'label' => $label ].
	 */
	private static function get_image_size_options() {
		$sizes   = get_intermediate_image_sizes();
		$options = [];

		// Ensure 'full' is included.
		if ( ! in_array( 'full', $sizes, true ) ) {
			$sizes[] = 'full';
		}

		foreach ( $sizes as $slug ) {
			$label     = ucwords( str_replace( [ '-', '_' ], ' ', $slug ) );
			$options[] = [
				'value' => $slug,
				'label' => $label,
			];
		}

		return $options;
	}

	/**
	 * Track blocks used on the page.
	 *
	 * @param string $block_name Block name.
	 */
	public static function track_block_usage( $block_name ) {
		if ( ! in_array( $block_name, self::$used_blocks, true ) ) {
			self::$used_blocks[] = $block_name;
		}
	}

	/**
	 * Add generated CSS for a block (fallback for render-time).
	 *
	 * @param string $css CSS string to add.
	 */
	public static function add_generated_css( $css ) {
		if ( ! empty( $css ) ) {
			self::$generated_css .= $css;
		}
	}

	/**
	 * Output CSS in the head.
	 *
	 * Prioritizes saved CSS from post meta. Falls back to render-time CSS
	 * for posts that haven't been re-saved yet.
	 */
	public static function output_page_css() {
		// Enqueue Swiper CSS if slider or carousel blocks are on the page.
		if ( has_block( 'blockflow/slider' ) || has_block( 'blockflow/carousel-images' ) ) {
			wp_enqueue_style( 'blockflow-swiper' );
		}

		$css = '';

		// First, try to load saved CSS.
		$css .= self::get_page_saved_css();

		// Add any render-time CSS (fallback or additional).
		if ( ! empty( self::$generated_css ) ) {
			$css .= self::$generated_css;
		}

		if ( empty( $css ) ) {
			return;
		}

		// wp_strip_all_tags() is the correct sanitization for wp_add_inline_style() per WordPress core pattern.
		// CSS is generated via CSS_Builder with controlled property names; values come from sanitized block attributes.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_strip_all_tags() is appropriate for CSS context.
		wp_add_inline_style( 'blockflow-common', wp_strip_all_tags( $css ) );
	}

	/**
	 * Get saved CSS for the current page context.
	 *
	 * @return string Combined CSS from all relevant sources.
	 */
	private static function get_page_saved_css() {
		if ( self::$css_loaded ) {
			return self::$saved_css;
		}

		self::$css_loaded = true;
		$css              = '';

		// Get template CSS (always needed for FSE sites).
		$css .= self::get_template_css();

		// Get content CSS based on context.
		if ( is_singular() ) {
			$css .= self::get_singular_css();
		} elseif ( is_archive() || is_home() || is_search() ) {
			$css .= self::get_archive_css();
		}

		self::$saved_css = $css;

		return $css;
	}

	/**
	 * Get CSS for the current template and template parts.
	 *
	 * @return string Template CSS.
	 */
	private static function get_template_css() {
		if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() ) {
			return '';
		}

		$css = '';

		// Get current template.
		$template_slug = get_page_template_slug();
		if ( empty( $template_slug ) ) {
			$template_slug = self::get_current_template_slug();
		}

		if ( $template_slug ) {
			$template = get_block_template( get_stylesheet() . '//' . $template_slug );
			if ( $template && ! empty( $template->wp_id ) ) {
				$template_css = CSS_Generator_Service::get_post_css( $template->wp_id );
				if ( $template_css ) {
					$css .= $template_css;
				} elseif ( CSS_Generator_Service::has_blockflow( $template->wp_id ) ) {
					// Generate on-the-fly for templates not yet saved.
					$css .= CSS_Generator_Service::generate_for_post( $template->wp_id );
				}
			}
		}

		// Get common template parts (header, footer).
		$parts = [ 'header', 'footer' ];
		foreach ( $parts as $part_slug ) {
			$part = get_block_template( get_stylesheet() . '//' . $part_slug, 'wp_template_part' );
			if ( $part && ! empty( $part->wp_id ) ) {
				$part_css = CSS_Generator_Service::get_post_css( $part->wp_id );
				if ( $part_css ) {
					$css .= $part_css;
				} elseif ( CSS_Generator_Service::has_blockflow( $part->wp_id ) ) {
					$css .= CSS_Generator_Service::generate_for_post( $part->wp_id );
				}
			}
		}

		return $css;
	}

	/**
	 * Get the current template slug based on WordPress template hierarchy.
	 *
	 * @return string Template slug.
	 */
	private static function get_current_template_slug() {
		if ( is_front_page() && is_home() ) {
			return 'index';
		}

		if ( is_front_page() ) {
			return 'front-page';
		}

		if ( is_home() ) {
			return 'home';
		}

		if ( is_single() ) {
			return 'single';
		}

		if ( is_page() ) {
			return 'page';
		}

		if ( is_archive() ) {
			return 'archive';
		}

		if ( is_search() ) {
			return 'search';
		}

		if ( is_404() ) {
			return '404';
		}

		return 'index';
	}

	/**
	 * Get CSS for a singular post/page.
	 *
	 * @return string Post CSS.
	 */
	private static function get_singular_css() {
		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return '';
		}

		// Check for saved CSS.
		$css = CSS_Generator_Service::get_post_css( $post_id );

		if ( $css ) {
			// Check if regeneration needed (version mismatch).
			if ( CSS_Generator_Service::needs_regeneration( $post_id ) ) {
				$css = CSS_Generator_Service::generate_for_post( $post_id );
			}
			return $css;
		}

		// No saved CSS - check if post has BlockFlow.
		if ( CSS_Generator_Service::has_blockflow( $post_id ) ) {
			// Generate on-the-fly (will be saved on next edit).
			return CSS_Generator_Service::generate_for_post( $post_id );
		}

		return '';
	}

	/**
	 * Get CSS for archive/home/search pages.
	 *
	 * @return string Combined CSS from displayed posts.
	 */
	private static function get_archive_css() {
		global $wp_query;

		if ( empty( $wp_query->posts ) ) {
			return '';
		}

		$css = '';

		foreach ( $wp_query->posts as $post ) {
			$post_css = CSS_Generator_Service::get_post_css( $post->ID );

			if ( $post_css ) {
				$css .= $post_css;
			} elseif ( CSS_Generator_Service::has_blockflow( $post->ID ) ) {
				// Generate for posts without saved CSS.
				$css .= CSS_Generator_Service::generate_for_post( $post->ID );
			}
		}

		return $css;
	}

	/**
	 * Generate CSS on post save.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public static function generate_post_css( $post_id, $post ) {
		// Skip autosaves and revisions.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Skip if not a valid post type.
		$allowed_types = [ 'post', 'page', 'wp_template', 'wp_template_part' ];
		if ( ! in_array( $post->post_type, $allowed_types, true ) ) {
			return;
		}

		// Generate CSS if post has BlockFlow.
		if ( CSS_Generator_Service::has_blockflow( $post_id ) ) {
			CSS_Generator_Service::generate_for_post( $post_id );
		} else {
			// Clear old CSS if no BlockFlow.
			CSS_Generator_Service::clear_cache( $post_id );
		}
	}

	/**
	 * Generate CSS for FSE templates (REST API save).
	 *
	 * @param \WP_Post $post Template post object.
	 */
	public static function generate_template_css( $post ) {
		if ( ! $post || empty( $post->ID ) ) {
			return;
		}

		if ( CSS_Generator_Service::has_blockflow( $post->ID ) ) {
			CSS_Generator_Service::generate_for_post( $post->ID );
		} else {
			CSS_Generator_Service::clear_cache( $post->ID );
		}
	}

	/**
	 * Get blocks used on the current page.
	 *
	 * @return array Array of block names.
	 */
	public static function get_used_blocks() {
		return self::$used_blocks;
	}

	/**
	 * Check if a block is used on the current page.
	 *
	 * @param string $block_name Block name to check.
	 * @return bool True if block is used.
	 */
	public static function is_block_used( $block_name ) {
		return in_array( $block_name, self::$used_blocks, true );
	}

	/**
	 * Get asset URL for a specific block.
	 *
	 * @param string $block_name Block name.
	 * @param string $type       Asset type (style, script, editor-style, editor-script).
	 * @return string|false Asset URL or false if not found.
	 */
	public static function get_block_asset_url( $block_name, $type = 'style' ) {
		$extension = 'style' === $type || 'editor-style' === $type ? 'css' : 'js';
		$file_path = BLOCKFLOW_DIR . "build/{$block_name}/{$type}.{$extension}";

		if ( file_exists( $file_path ) ) {
			return BLOCKFLOW_URL . "build/{$block_name}/{$type}.{$extension}";
		}

		return false;
	}

	/**
	 * Track that animations are used on the current page.
	 */
	public static function track_animation_usage() {
		self::$has_animations = true;
	}

	/**
	 * Track that video blocks with overlay are used on the current page.
	 */
	public static function track_video_usage() {
		self::$has_video = true;
	}

	/**
	 * Check if video blocks with overlay are used on the current page.
	 *
	 * @return bool True if video blocks are used.
	 */
	public static function has_video() {
		return self::$has_video;
	}

	/**
	 * Check if animations are used on the current page.
	 *
	 * @return bool True if animations are used.
	 */
	public static function has_animations() {
		return self::$has_animations;
	}

	/**
	 * Enqueue Google Fonts stylesheet.
	 *
	 * Reads fonts from post meta (saved at save-time) for instant loading.
	 * Handles singular posts, archive pages, and FSE template parts.
	 * Runs during wp_enqueue_scripts before styles are printed.
	 */
	public static function output_google_fonts() {
		$all_fonts = [];

		// Get fonts from global typography settings.
		$all_fonts = self::merge_page_fonts( $all_fonts, Typography_CSS::get_fonts() );

		// Get fonts from FSE template parts (header/footer).
		$all_fonts = self::merge_page_fonts( $all_fonts, self::get_template_fonts() );

		// Get fonts based on page context.
		if ( is_singular() ) {
			$post_id = get_queried_object_id();
			if ( $post_id ) {
				$all_fonts = self::merge_page_fonts( $all_fonts, CSS_Generator_Service::get_post_fonts( $post_id ) );
			}
		} elseif ( is_archive() || is_home() || is_search() ) {
			global $wp_query;
			if ( ! empty( $wp_query->posts ) ) {
				foreach ( $wp_query->posts as $post ) {
					$all_fonts = self::merge_page_fonts( $all_fonts, CSS_Generator_Service::get_post_fonts( $post->ID ) );
				}
			}
		}

		if ( empty( $all_fonts ) ) {
			return;
		}

		// Build Google Fonts URL with specific weights and italic variants.
		$families = [];
		foreach ( $all_fonts as $font_family => $variants ) {
			// Filter out empty variants.
			$variants = array_filter(
				$variants,
				function ( $v ) {
					return ! empty( $v );
				}
			);

			// Default to regular 400 if no valid variants.
			if ( empty( $variants ) ) {
				$variants = [ '0,400' ];
			}

			// Check if any variant has italic (starts with "1,").
			$has_italic = false;
			$normalized = [];

			foreach ( $variants as $variant ) {
				// Handle both old format ("400") and new format ("0,400" or "1,400").
				if ( strpos( $variant, ',' ) === false ) {
					// Old format: just weight - convert to "0,weight" (regular).
					$normalized[] = '0,' . $variant;
				} else {
					$normalized[] = $variant;
					// Check if this is an italic variant.
					if ( strpos( $variant, '1,' ) === 0 ) {
						$has_italic = true;
					}
				}
			}

			// Sort variants for consistent URL (e.g., 0,400;0,700;1,400;1,700).
			sort( $normalized );
			$variant_str = implode( ';', $normalized );

			// Use "ital,wght@" axis format when italic variants exist.
			if ( $has_italic ) {
				$families[] = rawurlencode( $font_family ) . ':ital,wght@' . $variant_str;
			} else {
				// No italic - use simple "wght@" format, extract just the weights.
				$weights = array_map(
					function ( $v ) {
						// Extract weight from "0,400" format.
						$parts = explode( ',', $v );
						return isset( $parts[1] ) ? $parts[1] : $parts[0];
					},
					$normalized
				);
				$weights = array_unique( $weights );
				sort( $weights );
				$families[] = rawurlencode( $font_family ) . ':wght@' . implode( ';', $weights );
			}
		}

		// Skip if no valid families.
		if ( empty( $families ) ) {
			return;
		}

		$url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $families ) . '&display=swap';

		// Enqueue the font stylesheet using WordPress enqueue system.
		wp_enqueue_style( 'blockflow-google-fonts', esc_url( $url ), [], null );

		// Add preconnect hints via filter.
		add_filter( 'wp_resource_hints', [ __CLASS__, 'add_google_fonts_preconnect' ], 10, 2 );
	}

	/**
	 * Add preconnect hints for Google Fonts.
	 *
	 * @param array  $hints          URLs to print for resource hints.
	 * @param string $relation_type  Hint type (preconnect, dns-prefetch, etc).
	 * @return array Modified hints.
	 */
	public static function add_google_fonts_preconnect( $hints, $relation_type ) {
		if ( 'preconnect' === $relation_type ) {
			$hints[] = [
				'href' => 'https://fonts.googleapis.com',
			];
			$hints[] = [
				'href'        => 'https://fonts.gstatic.com',
				'crossorigin' => 'anonymous',
			];
		}
		return $hints;
	}

	/**
	 * Get fonts from FSE template parts (header/footer).
	 *
	 * @return array Fonts array.
	 */
	private static function get_template_fonts() {
		if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() ) {
			return [];
		}

		$fonts = [];
		$parts = [ 'header', 'footer' ];

		foreach ( $parts as $part_slug ) {
			$part = get_block_template( get_stylesheet() . '//' . $part_slug, 'wp_template_part' );
			if ( $part && ! empty( $part->wp_id ) ) {
				$part_fonts = CSS_Generator_Service::get_post_fonts( $part->wp_id );
				$fonts      = self::merge_page_fonts( $fonts, $part_fonts );
			}
		}

		return $fonts;
	}

	/**
	 * Merge fonts arrays for page-level aggregation.
	 *
	 * @param array $fonts1 First fonts array.
	 * @param array $fonts2 Second fonts array.
	 * @return array Merged fonts array.
	 */
	private static function merge_page_fonts( $fonts1, $fonts2 ) {
		if ( empty( $fonts2 ) ) {
			return $fonts1;
		}

		foreach ( $fonts2 as $font => $weights ) {
			if ( ! isset( $fonts1[ $font ] ) ) {
				$fonts1[ $font ] = $weights;
			} else {
				$fonts1[ $font ] = array_values( array_unique( array_merge( $fonts1[ $font ], $weights ) ) );
			}
		}

		return $fonts1;
	}

	/**
	 * Enqueue conditional frontend scripts.
	 *
	 * Scripts are registered early via register_frontend_assets().
	 * This runs in wp_footer after block rendering determines which scripts are needed.
	 */
	public static function enqueue_conditional_scripts() {
		if ( is_admin() ) {
			return;
		}

		// Enqueue animation script if animations are used.
		if ( self::$has_animations && wp_script_is( 'blockflow-animations', 'registered' ) ) {
			wp_enqueue_script( 'blockflow-animations' );
		}

		// Enqueue lazy background script if lazy bg images are used.
		if ( self::$has_lazy_bg && wp_script_is( 'blockflow-lazy-bg', 'registered' ) ) {
			wp_enqueue_script( 'blockflow-lazy-bg' );
		}
	}

	/**
	 * Track that lazy background images are used on the current page.
	 */
	public static function track_lazy_bg_usage() {
		self::$has_lazy_bg = true;
	}

	/**
	 * Check if lazy background images are used on the current page.
	 *
	 * @return bool True if lazy bg images are used.
	 */
	public static function has_lazy_bg() {
		return self::$has_lazy_bg;
	}

	/**
	 * Add FAQ schema for output in footer.
	 *
	 * @param string $block_id Unique block ID.
	 * @param array  $schema   FAQ schema data.
	 */
	public static function add_faq_schema( $block_id, $schema ) {
		if ( ! empty( $schema ) && ! empty( $block_id ) ) {
			self::$faq_schemas[ $block_id ] = $schema;
		}
	}

	/**
	 * Output FAQ schemas as JSON-LD in footer.
	 *
	 * Outputs collected FAQ schemas from accordion blocks.
	 */
	public static function output_faq_schemas() {
		if ( is_admin() || empty( self::$faq_schemas ) ) {
			return;
		}

		foreach ( self::$faq_schemas as $block_id => $schema ) {
			// JSON-LD structured data output - script type="application/ld+json" is non-executable.
			// wp_json_encode() safely encodes the schema data.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON-LD is non-executable, wp_json_encode is safe.
			printf(
				'<script type="application/ld+json" id="blockflow-faq-schema-%s">%s</script>' . "\n",
				esc_attr( $block_id ),
				wp_json_encode( $schema )
			);
		}
	}

}
