<?php

namespace WebpConverter\Settings\Option;

/**
 * {@inheritdoc}
 */
class CloudflareApiTokenOption extends OptionAbstract {

	const OPTION_NAME = 'cloudflare_api_token_zone_settings';

	/**
	 * {@inheritdoc}
	 */
	public function get_name(): string {
		return self::OPTION_NAME;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_form_name(): string {
		return OptionAbstract::FORM_TYPE_CDN;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type(): string {
		return OptionAbstract::OPTION_TYPE_INPUT;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_label(): string {
		return 'Cloudflare API Token';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_info(): string {
		return implode(
			'</p><p>',
			[
				sprintf(
				/* translators: %1$s: open strong tag, %2$s: close strong tag */
					__( '%1$sWant your images to load even faster?%2$s By default, Cloudflare cannot dynamically deliver modern WebP and AVIF formats while keeping images cached on its CDN servers. Connect your Cloudflare account to our plugin to fix this and enable lightning-fast image delivery.', 'webp-converter-for-media' ),
					'<strong>',
					'</strong>'
				),
				sprintf(
				/* translators: %s: service name */
					'<a href="https://url.mattplugins.com/converter-field-cloudflare-token-docs" target="_blank">' . __( 'Learn more about how it works', 'webp-converter-for-media' ) . '.</a> ' . __( 'You can generate API Token in %1$sthe Cloudflare dashboard%2$s.', 'webp-converter-for-media' ),
					'<a href="https://url.mattplugins.com/converter-field-cloudflare-token-generate" target="_blank">',
					'</a>'
				),
			]
		);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return string[]
	 */
	public function get_available_values( array $settings ): array {
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_default_value(): string {
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate_value( $current_value, ?array $available_values = null, ?array $disabled_values = null ): string {
		return sanitize_text_field( $current_value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize_value( $current_value ): string {
		return $this->validate_value( $current_value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_public_value( $current_value = null ) {
		if ( $current_value === null ) {
			return $current_value;
		}

		return str_repeat( '*', max( strlen( $current_value ), 0 ) );
	}
}
