<?php
/**
 * SVG Upload Handler Class
 *
 * Handles SVG upload permission and sanitization.
 *
 * @package BlockFlow
 */

namespace ThemeRuby\BlockFlow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SVG_Upload class.
 */
class SVG_Upload {

	/**
	 * Option key for SVG upload permission.
	 */
	const OPTION_KEY = 'blockflow_enable_svg_upload';

	/**
	 * Initialize the SVG upload handler.
	 */
	public static function init() {
		// AJAX handlers.
		add_action( 'wp_ajax_blockflow_enable_svg_upload', [ __CLASS__, 'ajax_enable_svg_upload' ] );

		// Enable SVG upload if permission granted.
		if ( self::is_enabled() ) {
			add_filter( 'upload_mimes', [ __CLASS__, 'allow_svg_upload' ] );
			add_filter( 'wp_check_filetype_and_ext', [ __CLASS__, 'check_svg_filetype' ], 10, 5 );
		}
	}

	/**
	 * Check if SVG upload is enabled.
	 *
	 * @return bool True if enabled.
	 */
	public static function is_enabled() {
		return (bool) get_option( self::OPTION_KEY, false );
	}

	/**
	 * Enable SVG upload via AJAX.
	 */
	public static function ajax_enable_svg_upload() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'blockflow_admin' ) ) {
			wp_send_json_error( [ 'message' => __( 'Security check failed.', 'blockflow' ) ], 403 );
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'blockflow' ) ], 403 );
		}

		// Enable SVG upload.
		update_option( self::OPTION_KEY, true );

		wp_send_json_success(
			[
				'message' => __( 'SVG upload enabled.', 'blockflow' ),
				'enabled' => true,
			]
		);
	}

	/**
	 * Add SVG to allowed upload MIME types.
	 *
	 * @param array $mimes Allowed MIME types.
	 * @return array Modified MIME types.
	 */
	public static function allow_svg_upload( $mimes ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';

		return $mimes;
	}

	/**
	 * Fix SVG file type detection.
	 *
	 * @param array  $data     File data.
	 * @param string $file     Full path to file.
	 * @param string $filename File name.
	 * @param array  $mimes    Allowed MIME types.
	 * @param string $real_mime Real MIME type.
	 * @return array Modified file data.
	 */
	public static function check_svg_filetype( $data, $file, $filename, $mimes, $real_mime = '' ) {
		$ext = pathinfo( $filename, PATHINFO_EXTENSION );

		if ( 'svg' === strtolower( $ext ) ) {
			$data['type'] = 'image/svg+xml';
			$data['ext']  = 'svg';
		}

		return $data;
	}

	/**
	 * Get SVG upload config for JavaScript.
	 *
	 * @return array Config array.
	 */
	public static function get_js_config() {
		return [
			'enabled'   => self::is_enabled(),
			'canEnable' => current_user_can( 'manage_options' ),
			'nonce'     => wp_create_nonce( 'blockflow_admin' ),
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
		];
	}
}
