<?php
/**
 * Notice displayed in admin panel.
 *
 * @var string $ajax_url     URL of admin-ajax.
 * @var string $close_action Action using in WP Ajax.
 * @var string $settings_url URL of plugin settings page (default view).
 *
 * @package Converter for Media
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-success is-dismissible"
	data-notice="webp-converter-for-media"
	data-notice-action="<?php echo esc_attr( $close_action ); ?>"
	data-notice-url="<?php echo esc_url( $ajax_url ); ?>"
>
	<div class="webpcContent webpcContent--notice">
		<h4>
			<?php
			echo esc_html( __( 'Speed up your website with Cloudflare!', 'webp-converter-for-media' ) );
			?>
		</h4>
		<p>
			<?php
			echo wp_kses_post(
				__( 'We detected Cloudflare on your website. Connect your Cloudflare account to our plugin to enable full CDN caching for WebP/AVIF formats. The setup is fully automated and takes just a minute.', 'webp-converter-for-media' )
			);
			?>
		</p>
		<div class="webpcContent__buttons">
			<div class="webpcContent__buttons">
				<a href="<?php echo esc_url( $settings_url ); ?>"
					class="webpcContent__button webpcButton webpcButton--blue webpcButton--bg"
				>
					<?php echo esc_html( __( 'Go to the plugin settings', 'webp-converter-for-media' ) ); ?>
				</a>
				<a href="https://url.mattplugins.com/converter-notice-cloudflare-token-docs" target="_blank"
					class="webpcContent__button webpcButton webpcButton--blue"
				>
					<?php echo esc_html( __( 'Learn more about how it works', 'webp-converter-for-media' ) ); ?>
				</a>
			</div>
		</div>
	</div>
</div>
