<?php

namespace WebpConverter\Service;

use WebpConverter\HookableInterface;
use WebpConverter\PluginData;
use WebpConverter\PluginInfo;
use WebpConverter\Settings\Option\CloudflareApiTokenOption;
use WebpConverter\Settings\Option\CloudflareZoneIdOption;

/**
 * Manages the cache configuration for Cloudflare CDN.
 */
class CloudflareConfigurator implements HookableInterface {

	const API_ZONES_URL                 = 'https://api.cloudflare.com/client/v4/zones?per_page=1000';
	const API_CACHE_SETTINGS_URL        = 'https://api.cloudflare.com/client/v4/zones/%s/rulesets/phases/http_request_cache_settings/entrypoint';
	const API_CACHE_PURGE_URL           = 'https://api.cloudflare.com/client/v4/zones/%s/purge_cache';
	const API_CACHE_RULE_PREFIX         = '[Converter for Media]';
	const API_CACHE_RULE_DEFAULT_NAME   = self::API_CACHE_RULE_PREFIX . ' WebP and AVIF delivery';
	const REQUEST_CACHE_SETTINGS_OPTION = 'webpc_cloudflare_cache_settings';
	const REQUEST_CACHE_PURGE_OPTION    = 'webpc_cloudflare_cache_purge';

	private PluginInfo $plugin_info;

	private PluginData $plugin_data;

	public function __construct( PluginInfo $plugin_info, PluginData $plugin_data ) {
		$this->plugin_info = $plugin_info;
		$this->plugin_data = $plugin_data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function init_hooks(): void {
		add_action( 'webpc_settings_updated', [ $this, 'clear_after_settings_save' ], 10, 2 );
		register_activation_hook( $this->plugin_info->get_plugin_file(), [ $this, 'purge_cache' ] );
		register_deactivation_hook( $this->plugin_info->get_plugin_file(), [ $this, 'purge_cache' ] );
	}

	/**
	 * @param mixed[] $current_settings  .
	 * @param mixed[] $previous_settings .
	 *
	 * @internal
	 */
	public function clear_after_settings_save( array $current_settings, array $previous_settings ): void {
		if ( ( $previous_settings[ CloudflareZoneIdOption::OPTION_NAME ] === $current_settings[ CloudflareZoneIdOption::OPTION_NAME ] )
			&& ( $previous_settings[ CloudflareApiTokenOption::OPTION_NAME ] === $current_settings[ CloudflareApiTokenOption::OPTION_NAME ] ) ) {
			return;
		}

		$this->set_cache_config();
		$this->purge_cache();
	}

	/**
	 * @internal
	 */
	public function set_cache_config(): void {
		$cache_settings = $this->send_request( self::API_CACHE_SETTINGS_URL, 'GET' );
		if ( $cache_settings === null ) {
			return;
		}

		if ( ! in_array( $cache_settings['http_code'], [ 200, 404 ] ) ) {
			OptionsAccessManager::update_option( self::REQUEST_CACHE_SETTINGS_OPTION, $cache_settings['http_code'] );
			return;
		}

		$new_rule = [
			'expression'        => '(http.request.uri.path.extension in {"jpg" "jpeg" "gif" "png" "webp" "avif"})',
			'description'       => self::API_CACHE_RULE_DEFAULT_NAME,
			'action'            => 'set_cache_settings',
			'action_parameters' => [
				'cache' => true,
				'vary'  => [
					'default' => [
						'action' => 'normalize',
					],
				],
			],
		];

		$rules   = array_values(
			array_filter(
				$cache_settings['body']['result']['rules'] ?? [],
				static fn( array $rule ) => ( strpos( $rule['description'] ?? '', self::API_CACHE_RULE_PREFIX ) !== 0 )
			)
		);
		$rules[] = $new_rule;

		$response = $this->send_request( self::API_CACHE_SETTINGS_URL, 'PUT', [ 'rules' => $rules ] );
		if ( $response !== null ) {
			OptionsAccessManager::update_option(
				self::REQUEST_CACHE_SETTINGS_OPTION,
				( $response['http_code'] === 200 ) ? 'yes' : $response['http_code']
			);
		}
	}

	/**
	 * @internal
	 */
	public function purge_cache(): void {
		$response = $this->send_request(
			self::API_CACHE_PURGE_URL,
			'POST',
			[
				'purge_everything' => true,
			]
		);
		if ( $response === null ) {
			return;
		}

		OptionsAccessManager::update_option(
			self::REQUEST_CACHE_PURGE_OPTION,
			( $response['http_code'] === 200 ) ? 'yes' : $response['http_code']
		);
	}

	/**
	 * @param string           $api_url        .
	 * @param non-empty-string $request_method .
	 * @param mixed[]          $request_data   .
	 *
	 * @return array{http_code: int, body: mixed[]}|null
	 */
	private function send_request( string $api_url, string $request_method, array $request_data = [] ): ?array {
		$plugin_setting = $this->plugin_data->get_plugin_settings();
		if ( ! $plugin_setting[ CloudflareZoneIdOption::OPTION_NAME ] || ! $plugin_setting[ CloudflareApiTokenOption::OPTION_NAME ] ) {
			return null;
		}

		$connect = curl_init( sprintf( $api_url, $plugin_setting[ CloudflareZoneIdOption::OPTION_NAME ] ) );
		if ( ! $connect ) {
			return null;
		}

		curl_setopt( $connect, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $connect, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $connect, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $connect, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $connect, CURLOPT_CUSTOMREQUEST, $request_method );
		if ( $request_data !== [] ) {
			curl_setopt( $connect, CURLOPT_POSTFIELDS, json_encode( $request_data ) ?: '' );
		}
		curl_setopt(
			$connect,
			CURLOPT_HTTPHEADER,
			[
				'Content-Type: application/json',
				'Authorization: Bearer ' . $plugin_setting[ CloudflareApiTokenOption::OPTION_NAME ],
			]
		);

		$response_body = curl_exec( $connect );
		$response_code = curl_getinfo( $connect, CURLINFO_HTTP_CODE );

		$json = json_decode( (string) $response_body, true );
		return [
			'http_code' => (int) $response_code,
			'body'      => ( is_array( $json ) ) ? $json : [],
		];
	}

	/**
	 * @param string $api_token .
	 *
	 * @return array<int, array{id: string, name: string}>
	 */
	public static function get_zones( string $api_token ): array {
		if ( empty( $api_token ) ) {
			return [];
		}

		$connect = curl_init( 'https://api.cloudflare.com/client/v4/zones?per_page=1000' );
		if ( ! $connect ) {
			return [];
		}

		curl_setopt( $connect, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $connect, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $connect, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $connect, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $connect, CURLOPT_CUSTOMREQUEST, 'GET' );
		curl_setopt(
			$connect,
			CURLOPT_HTTPHEADER,
			[
				'Content-Type: application/json',
				'Authorization: Bearer ' . $api_token,
			]
		);

		$response_body = curl_exec( $connect );
		$response_code = curl_getinfo( $connect, CURLINFO_HTTP_CODE );

		if ( (int) $response_code !== 200 ) {
			return [];
		}

		$json = json_decode( (string) $response_body, true );
		if ( empty( $json['success'] ) || empty( $json['result'] ) ) {
			return [];
		}

		$zones = [];
		foreach ( $json['result'] as $zone ) {
			if ( isset( $zone['id'], $zone['name'] ) ) {
				$zones[] = [
					'id'   => $zone['id'],
					'name' => $zone['name'],
				];
			}
		}

		return $zones;
	}
}
