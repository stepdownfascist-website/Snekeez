<?php

namespace WebpConverter\Notice;

use WebpConverter\Service\CloudflareConfigurator;
use WebpConverter\Service\OptionsAccessManager;
use WebpConverter\Settings\Page\CdnSettingsPage;
use WebpConverter\Settings\Page\PageIntegrator;

/**
 * Supports notice asking to configure Cloudflare integration.
 */
class CloudflareNotice extends NoticeAbstract implements NoticeInterface {

	const NOTICE_OPTION    = 'webpc_notice_cloudflare_setup';
	const NOTICE_VIEW_PATH = 'components/notices/cloudflare-setup.php';

	/**
	 * {@inheritdoc}
	 */
	public function get_option_name(): string {
		return self::NOTICE_OPTION;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available(): bool {
		$cdn_server = strtolower( $_SERVER['HTTP_CDN_LOOP'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( isset( $_SERVER['KINSTA_CACHE_ZONE'] ) ) {
			$cdn_server = '';
		}

		if ( ( strpos( $cdn_server, 'cloudflare' ) === false ) && ! is_plugin_active( 'cloudflare/cloudflare.php' ) ) {
			return false;
		}

		return ( ( ( $_GET['page'] ?? '' ) === PageIntegrator::SETTINGS_MENU_PAGE ) // phpcs:ignore WordPress.Security
			&& ( ( $_GET[ PageIntegrator::SETTINGS_PAGE_TYPE ] ?? '' ) !== CdnSettingsPage::PAGE_SLUG ) ); // phpcs:ignore WordPress.Security
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_active(): bool {
		return ( ( OptionsAccessManager::get_option( $this->get_option_name() ) !== $this->get_disable_value() )
			&& ( OptionsAccessManager::get_option( CloudflareConfigurator::REQUEST_CACHE_SETTINGS_OPTION, '-' ) !== 'yes' ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_disable_value(): string {
		return 'yes';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_output_path(): string {
		return self::NOTICE_VIEW_PATH;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return mixed[]
	 */
	public function get_vars_for_view(): array {
		return [
			'ajax_url'     => admin_url( 'admin-ajax.php' ),
			'close_action' => self::NOTICE_OPTION,
			'settings_url' => PageIntegrator::get_settings_page_url( 'cdn' ),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_ajax_action_to_disable(): string {
		return self::NOTICE_OPTION;
	}
}
