<?php

namespace WebpConverter\Conversion\Endpoint;

use WebpConverter\Service\CloudflareConfigurator;

/**
 * .
 */
class CloudflareZonesEndpoint extends EndpointAbstract {

	/**
	 * {@inheritdoc}
	 */
	public static function get_route_name(): string {
		return 'cloudflare-zones';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_http_methods(): string {
		return \WP_REST_Server::READABLE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_route_args(): array {
		return array_merge(
			parent::get_route_args(),
			[
				'api_token' => [
					'description'       => 'User API Token',
					'required'          => true,
					'default'           => false,
					'sanitize_callback' => function ( $value ) {
						return sanitize_text_field( $value );
					},
				],
			]
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_route_response( \WP_REST_Request $request ) {
		$zones = CloudflareConfigurator::get_zones( $request->get_param( 'api_token' ) );

		return new \WP_REST_Response(
			[
				'success' => ! empty( $zones ),
				'zones'   => $zones,
			],
			200
		);
	}
}
