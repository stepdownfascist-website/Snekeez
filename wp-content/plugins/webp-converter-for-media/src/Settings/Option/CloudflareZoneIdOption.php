<?php

namespace WebpConverter\Settings\Option;

/**
 * {@inheritdoc}
 */
class CloudflareZoneIdOption extends OptionAbstract {

	const OPTION_NAME = 'cloudflare_zone_id';

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
		return 'Cloudflare Zone ID';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_info(): string {
		return sprintf(
		/* translators: %s: service name */
			__( 'Enter a value in the %1$s field above to load zones, then select yours or enter the %2$s manually.', 'webp-converter-for-media' ),
			'Cloudflare API Token',
			'Zone ID'
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
