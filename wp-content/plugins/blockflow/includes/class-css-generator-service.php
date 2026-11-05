<?php
/**
 * CSS Generator Service for BlockFlow.
 *
 * Orchestrates save-time CSS generation for all BlockFlow in a post.
 * Parses post content, dispatches to block-specific CSS generators,
 * and stores the combined CSS in post meta.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSS Generator Service class.
 */
class CSS_Generator_Service {

	/**
	 * Meta key for storing generated CSS.
	 *
	 * @var string
	 */
	const CSS_META_KEY = '_blockflow_css';

	/**
	 * Meta key for storing CSS version.
	 *
	 * @var string
	 */
	const VERSION_META_KEY = '_blockflow_css_version';

	/**
	 * Meta key for storing Google Fonts.
	 *
	 * @var string
	 */
	const FONTS_META_KEY = '_blockflow_fonts';

	/**
	 * System fonts to skip.
	 *
	 * @var array
	 */
	private static $system_fonts = [
		'arial',
		'helvetica',
		'times',
		'georgia',
		'verdana',
		'tahoma',
		'trebuchet',
		'courier',
		'system-ui',
		'sans-serif',
		'serif',
		'monospace',
		'inherit',
	];

	/**
	 * Supported BlockFlow block types.
	 *
	 * @var array
	 */
	private static $supported_blocks = [
		'blockflow/container',
		'blockflow/grid',
		'blockflow/icon',
		'blockflow/icon-set',
		'blockflow/icon-list',
		'blockflow/separator',
		'blockflow/heading',
		'blockflow/text',
		'blockflow/social-icons',
		'blockflow/countdown',
		'blockflow/button',
		'blockflow/video',
		'blockflow/accordion',
		'blockflow/note',
		'blockflow/info-card',
		'blockflow/google-map',
		'blockflow/image',
		'blockflow/counter',
		'blockflow/progress-bar',
		'blockflow/slider',
		'blockflow/slider-slide',
		'blockflow/team-member',
		'blockflow/gallery',
		'blockflow/brand-logos',
		'blockflow/marquee-text',
		'blockflow/testimonial',
		'blockflow/carousel-images',
	];

	/**
	 * Generate CSS for a post and store in meta.
	 *
	 * @param int $post_id Post ID.
	 * @return string Generated CSS.
	 */
	public static function generate_for_post( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || empty( $post->post_content ) ) {
			delete_post_meta( $post_id, self::CSS_META_KEY );
			delete_post_meta( $post_id, self::VERSION_META_KEY );
			delete_post_meta( $post_id, self::FONTS_META_KEY );
			return '';
		}

		$blocks = parse_blocks( $post->post_content );
		$css    = self::generate_for_blocks( $blocks );

		// Extract and save Google Fonts.
		$fonts = self::extract_fonts_from_blocks( $blocks );

		if ( ! empty( $css ) ) {
			update_post_meta( $post_id, self::CSS_META_KEY, $css );
			update_post_meta( $post_id, self::VERSION_META_KEY, BLOCKFLOW_VER );
		} else {
			delete_post_meta( $post_id, self::CSS_META_KEY );
			delete_post_meta( $post_id, self::VERSION_META_KEY );
		}

		// Save fonts separately (even if no CSS, fonts might exist).
		if ( ! empty( $fonts ) ) {
			update_post_meta( $post_id, self::FONTS_META_KEY, $fonts );
		} else {
			delete_post_meta( $post_id, self::FONTS_META_KEY );
		}

		return $css;
	}

	/**
	 * Generate CSS for an array of blocks.
	 *
	 * @param array $blocks Array of parsed blocks.
	 * @return string Generated CSS.
	 */
	public static function generate_for_blocks( $blocks ) {
		$css_generator = new CSS_Builder();

		self::process_blocks( $blocks, $css_generator );

		return $css_generator->get_output();
	}

	/**
	 * Recursively process blocks and generate CSS.
	 *
	 * @param array              $blocks        Array of blocks to process.
	 * @param CSS_Builder $css_generator CSS generator instance.
	 */
	private static function process_blocks( $blocks, $css_generator ) {
		foreach ( $blocks as $block ) {
			// Skip empty blocks.
			if ( empty( $block['blockName'] ) ) {
				continue;
			}

			// Generate CSS for BlockFlow.
			if ( in_array( $block['blockName'], self::$supported_blocks, true ) ) {
				self::generate_block_css( $block, $css_generator );
			}

			// Process inner blocks recursively.
			if ( ! empty( $block['innerBlocks'] ) ) {
				self::process_blocks( $block['innerBlocks'], $css_generator );
			}
		}
	}

	/**
	 * Generate CSS for a single block.
	 *
	 * @param array              $block         Block data.
	 * @param CSS_Builder $css_generator CSS generator instance.
	 */
	private static function generate_block_css( $block, $css_generator ) {
		$block_name = $block['blockName'];
		$attrs      = $block['attrs'] ?? [];

		// Skip blocks without blockId.
		if ( empty( $attrs['blockId'] ) ) {
			return;
		}

		// Merge saved attrs with block.json defaults.
		$attrs = self::get_merged_attrs( $block_name, $attrs );

		// Map block names to CSS generator classes.
		$generators = [
			'blockflow/container'       => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Container_CSS',
			'blockflow/grid'            => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Grid_CSS',
			'blockflow/icon'            => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Icon_CSS',
			'blockflow/icon-set'        => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Icon_Set_CSS',
			'blockflow/icon-list'       => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Icon_List_CSS',
			'blockflow/separator'       => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Separator_CSS',
			'blockflow/heading'         => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Heading_CSS',
			'blockflow/text'            => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Text_CSS',
			'blockflow/social-icons'    => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Social_Icons_CSS',
			'blockflow/countdown'       => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Countdown_CSS',
			'blockflow/button'          => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Button_CSS',
			'blockflow/video'           => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Video_CSS',
			'blockflow/accordion'       => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Accordion_CSS',
			'blockflow/note'            => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Note_CSS',
			'blockflow/info-card'       => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Info_Card_CSS',
			'blockflow/google-map'      => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Google_Map_CSS',
			'blockflow/image'           => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Image_CSS',
			'blockflow/counter'         => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Counter_CSS',
			'blockflow/progress-bar'    => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Progress_Bar_CSS',
			'blockflow/slider'          => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Slider_CSS',
			'blockflow/slider-slide'    => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Slider_Slide_CSS',
			'blockflow/team-member'     => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Team_Member_CSS',
			'blockflow/gallery'         => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Gallery_CSS',
			'blockflow/brand-logos'     => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Brand_Logos_CSS',
			'blockflow/marquee-text'    => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Marquee_Text_CSS',
			'blockflow/testimonial'     => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Testimonial_Card_CSS',
			'blockflow/carousel-images' => 'ThemeRuby\\BlockFlow\\CSS_Generators\\Carousel_Images_CSS',
		];

		if ( isset( $generators[ $block_name ] ) && class_exists( $generators[ $block_name ] ) ) {
			$generator_class = $generators[ $block_name ];
			$generator_class::generate( $attrs, $css_generator );
		}
	}

	/**
	 * Cached defaults per block type.
	 *
	 * @var array
	 */
	private static $defaults_cache = [];

	/**
	 * Merge saved attributes with block.json defaults.
	 *
	 * WordPress parse_blocks() returns only saved attributes, not merged with
	 * block.json defaults. This method fetches defaults from the block registry
	 * and deep merges them with saved attributes.
	 *
	 * @param string $block_name Block name (e.g., 'blockflow/heading').
	 * @param array  $attrs      Saved attributes from parse_blocks().
	 * @return array Merged attributes with defaults applied.
	 */
	private static function get_merged_attrs( $block_name, $attrs ) {
		// Return early if no saved attrs.
		if ( empty( $attrs ) ) {
			return self::get_block_defaults( $block_name );
		}

		$defaults = self::get_block_defaults( $block_name );

		if ( empty( $defaults ) ) {
			return $attrs;
		}

		// Deep merge: defaults first, then saved attrs override.
		return self::deep_merge( $defaults, $attrs );
	}

	/**
	 * Get cached defaults for a block type.
	 *
	 * @param string $block_name Block name.
	 * @return array Defaults array.
	 */
	private static function get_block_defaults( $block_name ) {
		// Return from cache if available.
		if ( isset( self::$defaults_cache[ $block_name ] ) ) {
			return self::$defaults_cache[ $block_name ];
		}

		$registry   = \WP_Block_Type_Registry::get_instance();
		$block_type = $registry->get_registered( $block_name );

		if ( ! $block_type || empty( $block_type->attributes ) ) {
			self::$defaults_cache[ $block_name ] = [];
			return [];
		}

		// Extract defaults from block type attributes.
		$defaults = [];
		foreach ( $block_type->attributes as $key => $config ) {
			if ( isset( $config['default'] ) ) {
				$defaults[ $key ] = $config['default'];
			}
		}

		self::$defaults_cache[ $block_name ] = $defaults;
		return $defaults;
	}

	/**
	 * Deep merge two arrays recursively.
	 *
	 * Merges saved attributes (override) into defaults (base).
	 * - Associative arrays: merge recursively (preserves structure)
	 * - Indexed arrays: replace entirely (list-type data)
	 * - Scalars: override replaces base
	 *
	 * Edge case handling:
	 * - Empty arrays `[]` are treated as associative (merge) to preserve object structure
	 * - Only non-empty indexed arrays trigger replacement
	 * - PHP can't distinguish `{}` from `[]` after JSON parse, so we use this safe default
	 *
	 * @param array $base     Base array (defaults from block.json).
	 * @param array $override Override array (saved attributes).
	 * @return array Merged array.
	 */
	private static function deep_merge( $base, $override ) {
		foreach ( $override as $key => $value ) {
			// New key not in base: add directly.
			if ( ! array_key_exists( $key, $base ) ) {
				$base[ $key ] = $value;
				continue;
			}

			// Both are arrays: decide merge strategy.
			if ( is_array( $base[ $key ] ) && is_array( $value ) ) {
				// Only replace if override is a non-empty indexed array.
				// Fast check: non-empty and first key is integer 0.
				// Block attributes are either pure indexed or pure associative, never mixed.
				if ( ! empty( $value ) && isset( $value[0] ) ) {
					// Non-empty indexed array: replace entirely.
					$base[ $key ] = $value;
				} else {
					// Associative or empty: merge recursively.
					$base[ $key ] = self::deep_merge( $base[ $key ], $value );
				}
			} else {
				// Scalar or type mismatch: override wins.
				$base[ $key ] = $value;
			}
		}

		return $base;
	}

	/**
	 * Get stored CSS for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return string|false Stored CSS or false if not found.
	 */
	public static function get_post_css( $post_id ) {
		return get_post_meta( $post_id, self::CSS_META_KEY, true );
	}

	/**
	 * Check if post CSS needs regeneration.
	 *
	 * @param int $post_id Post ID.
	 * @return bool True if regeneration needed.
	 */
	public static function needs_regeneration( $post_id ) {
		$version = get_post_meta( $post_id, self::VERSION_META_KEY, true );

		// No version means never generated.
		if ( empty( $version ) ) {
			return true;
		}

		// Different version means plugin updated.
		if ( $version !== BLOCKFLOW_VER ) {
			return true;
		}

		return false;
	}

	/**
	 * Clear CSS cache for a post.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function clear_cache( $post_id ) {
		delete_post_meta( $post_id, self::CSS_META_KEY );
		delete_post_meta( $post_id, self::VERSION_META_KEY );
		delete_post_meta( $post_id, self::FONTS_META_KEY );
	}

	/**
	 * Extract Google Fonts from blocks.
	 *
	 * @param array $blocks Array of parsed blocks.
	 * @return array Fonts array: ['Font Name' => ['400', '700'], ...].
	 */
	private static function extract_fonts_from_blocks( $blocks ) {
		$fonts = [];

		foreach ( $blocks as $block ) {
			if ( empty( $block['blockName'] ) ) {
				continue;
			}

			// Extract fonts from BlockFlow.
			if ( in_array( $block['blockName'], self::$supported_blocks, true ) ) {
				self::extract_block_fonts( $block, $fonts );
			}

			// Process inner blocks recursively.
			if ( ! empty( $block['innerBlocks'] ) ) {
				$inner_fonts = self::extract_fonts_from_blocks( $block['innerBlocks'] );
				$fonts       = self::merge_fonts( $fonts, $inner_fonts );
			}
		}

		return $fonts;
	}

	/**
	 * Extract fonts from a single block.
	 *
	 * Auto-detects typography attributes by checking for attributes named
	 * "typography" or ending with "Typography" that have the expected structure.
	 *
	 * @param array $block Block data.
	 * @param array $fonts Fonts array (passed by reference).
	 */
	private static function extract_block_fonts( $block, &$fonts ) {
		$block_name = $block['blockName'];
		$attrs      = $block['attrs'] ?? [];

		// Merge saved attrs with block.json defaults.
		$attrs = self::get_merged_attrs( $block_name, $attrs );

		// Auto-detect typography attributes.
		foreach ( $attrs as $attr_name => $attr_value ) {
			// Check if attribute is named "typography" or ends with "Typography".
			if ( 'typography' !== $attr_name && ! str_ends_with( $attr_name, 'Typography' ) ) {
				continue;
			}

			// Must be an array with desktop/tablet/mobile structure.
			if ( ! is_array( $attr_value ) || ! isset( $attr_value['desktop'] ) ) {
				continue;
			}

			self::add_font_from_typography( $attr_value, $fonts );
		}
	}

	/**
	 * Add font from typography attribute (supports responsive).
	 *
	 * Stores fonts with italic info using format: "ital,weight" (e.g., "0,400", "1,400").
	 * This format is compatible with Google Fonts API v2.
	 *
	 * @param array $typography Typography settings.
	 * @param array $fonts      Fonts array (passed by reference).
	 */
	private static function add_font_from_typography( $typography, &$fonts ) {
		$devices = [ 'desktop', 'tablet', 'mobile' ];

		foreach ( $devices as $device ) {
			if ( empty( $typography[ $device ] ) ) {
				continue;
			}

			$device_typo = $typography[ $device ];
			$font_family = $device_typo['fontFamily'] ?? '';
			$font_weight = $device_typo['fontWeight'] ?? '';
			$font_style  = $device_typo['fontStyle'] ?? '';

			// Default to 400 if no weight specified.
			if ( empty( $font_weight ) ) {
				$font_weight = '400';
			}

			if ( empty( $font_family ) ) {
				continue;
			}

			// Skip system fonts.
			if ( self::is_system_font( $font_family ) ) {
				continue;
			}

			// Initialize font if not exists.
			if ( ! isset( $fonts[ $font_family ] ) ) {
				$fonts[ $font_family ] = [];
			}

			// Build variant string: "ital,weight" format (e.g., "0,400" or "1,400").
			$is_italic   = 'italic' === $font_style;
			$variant_str = ( $is_italic ? '1' : '0' ) . ',' . $font_weight;

			// Add variant if not already added.
			if ( ! in_array( $variant_str, $fonts[ $font_family ], true ) ) {
				$fonts[ $font_family ][] = $variant_str;
			}
		}
	}

	/**
	 * Check if font is a system font.
	 *
	 * @param string $font_family Font family name.
	 * @return bool True if system font.
	 */
	private static function is_system_font( $font_family ) {
		$font_lower = strtolower( trim( $font_family ) );

		// Exact match only (not substring).
		return in_array( $font_lower, self::$system_fonts, true );
	}

	/**
	 * Merge two fonts arrays.
	 *
	 * @param array $fonts1 First fonts array.
	 * @param array $fonts2 Second fonts array.
	 * @return array Merged fonts array.
	 */
	private static function merge_fonts( $fonts1, $fonts2 ) {
		foreach ( $fonts2 as $font => $weights ) {
			if ( ! isset( $fonts1[ $font ] ) ) {
				$fonts1[ $font ] = $weights;
			} else {
				$fonts1[ $font ] = array_unique( array_merge( $fonts1[ $font ], $weights ) );
			}
		}

		return $fonts1;
	}

	/**
	 * Get stored fonts for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array Fonts array or empty array.
	 */
	public static function get_post_fonts( $post_id ) {
		$fonts = get_post_meta( $post_id, self::FONTS_META_KEY, true );
		return is_array( $fonts ) ? $fonts : [];
	}

	/**
	 * Check if a post contains any BlockFlow.
	 *
	 * @param int $post_id Post ID.
	 * @return bool True if post has BlockFlow.
	 */
	public static function has_blockflow( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || empty( $post->post_content ) ) {
			return false;
		}

		foreach ( self::$supported_blocks as $block_name ) {
			if ( has_block( $block_name, $post ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get list of supported block types.
	 *
	 * @return array Supported block names.
	 */
	public static function get_supported_blocks() {
		return self::$supported_blocks;
	}
}
