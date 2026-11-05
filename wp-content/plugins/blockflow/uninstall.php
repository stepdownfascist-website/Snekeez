<?php
/**
 * BlockFlow Uninstall
 *
 * Fired when the plugin is uninstalled.
 *
 * @package BlockFlow
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options.
delete_option( 'blockflow_settings' );
delete_option( 'blockflow_version' );
delete_option( 'blockflow_enable_svg_upload' );

// Delete transients.
delete_transient( 'blockflow_activation_redirect' );
