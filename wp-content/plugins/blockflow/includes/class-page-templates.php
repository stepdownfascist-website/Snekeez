<?php
/**
 * Page Templates - Register custom page templates for the plugin.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page_Templates class.
 *
 * Registers and renders custom page templates (Canvas and Full Width)
 * for building layouts with BlockFlow container blocks.
 */
class Page_Templates {

	/**
	 * Template slug prefix.
	 *
	 * @var string
	 */
	const TEMPLATE_PREFIX = 'blockflow-';

	/**
	 * Available templates.
	 *
	 * @var array
	 */
	private static $templates = [];

	/**
	 * Initialize page templates.
	 */
	public static function init() {
		// Register templates in admin.
		add_filter( 'theme_page_templates', [ __CLASS__, 'register_templates' ], 10, 3 );

		// Support for post types other than pages.
		add_filter( 'theme_post_templates', [ __CLASS__, 'register_templates' ], 10, 3 );

		// Load template file.
		add_filter( 'template_include', [ __CLASS__, 'load_template' ], 99 );

		// Add body class for templates.
		add_filter( 'body_class', [ __CLASS__, 'add_body_class' ] );

		// Enqueue template styles.
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_styles' ] );
	}

	/**
	 * Get available templates.
	 *
	 * @return array Templates array.
	 */
	private static function get_templates() {
		if ( empty( self::$templates ) ) {
			self::$templates = [
				self::TEMPLATE_PREFIX . 'canvas'     => __( 'BlockFlow Canvas', 'blockflow' ),
				self::TEMPLATE_PREFIX . 'full-width' => __( 'BlockFlow Full Width', 'blockflow' ),
			];
		}
		return self::$templates;
	}

	/**
	 * Register custom templates in the page template dropdown.
	 *
	 * @param array    $templates Array of templates.
	 * @param WP_Theme $theme     Theme object.
	 * @param WP_Post  $post      Post object.
	 * @return array Modified templates array.
	 */
	public static function register_templates( $templates, $theme = null, $post = null ) {
		return array_merge( $templates, self::get_templates() );
	}

	/**
	 * Load the template file if a BlockFlow template is selected.
	 *
	 * @param string $template Template file path.
	 * @return string Modified template file path.
	 */
	public static function load_template( $template ) {
		global $post;

		if ( ! $post ) {
			return $template;
		}

		$page_template = get_post_meta( $post->ID, '_wp_page_template', true );

		// Check if it's a BlockFlow template.
		if ( ! $page_template || 0 !== strpos( $page_template, self::TEMPLATE_PREFIX ) ) {
			return $template;
		}

		// Get template slug without prefix.
		$template_slug = str_replace( self::TEMPLATE_PREFIX, '', $page_template );
		$template_file = BLOCKFLOW_DIR . 'templates/' . $template_slug . '.php';

		if ( file_exists( $template_file ) ) {
			return $template_file;
		}

		return $template;
	}

	/**
	 * Add body class for BlockFlow templates.
	 *
	 * @param array $classes Body classes.
	 * @return array Modified body classes.
	 */
	public static function add_body_class( $classes ) {
		if ( ! is_singular() ) {
			return $classes;
		}

		$page_template = get_page_template_slug();

		if ( ! $page_template || 0 !== strpos( $page_template, self::TEMPLATE_PREFIX ) ) {
			return $classes;
		}

		// Add general BlockFlow template class.
		$classes[] = 'blockflow-template';

		// Add specific template class.
		$template_slug = str_replace( self::TEMPLATE_PREFIX, '', $page_template );
		$classes[]     = 'blockflow-template-' . sanitize_html_class( $template_slug );

		return $classes;
	}

	/**
	 * Enqueue template styles.
	 */
	public static function enqueue_styles() {
		if ( ! is_singular() ) {
			return;
		}

		$page_template = get_page_template_slug();

		if ( ! $page_template || 0 !== strpos( $page_template, self::TEMPLATE_PREFIX ) ) {
			return;
		}

		wp_enqueue_style(
			'blockflow-templates',
			BLOCKFLOW_URL . 'assets/css/templates.css',
			[],
			BLOCKFLOW_VER
		);
	}

	/**
	 * Check if current page uses a BlockFlow template.
	 *
	 * @return bool True if using BlockFlow template.
	 */
	public static function is_blockflow_template() {
		if ( ! is_singular() ) {
			return false;
		}

		$page_template = get_page_template_slug();

		return $page_template && 0 === strpos( $page_template, self::TEMPLATE_PREFIX );
	}

	/**
	 * Get the current BlockFlow template slug.
	 *
	 * @return string|false Template slug or false if not using BlockFlow template.
	 */
	public static function get_current_template() {
		if ( ! self::is_blockflow_template() ) {
			return false;
		}

		$page_template = get_page_template_slug();

		return str_replace( self::TEMPLATE_PREFIX, '', $page_template );
	}
}
