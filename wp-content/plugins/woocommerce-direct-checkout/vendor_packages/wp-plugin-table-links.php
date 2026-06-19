<?php

if ( class_exists( 'QuadLayers\\WP_Plugin_Table_Links\\Load' ) ) {
	add_action('init', function() {
		new \QuadLayers\WP_Plugin_Table_Links\Load(
			QLWCDC_PLUGIN_FILE,
			array(
				array(
					'text' => esc_html__( 'Settings', 'woocommerce-direct-checkout' ),
					'url'  => admin_url( 'admin.php?page=wc-settings&tab=' . sanitize_title( QLWCDC_PREFIX ) ),
					'target' => '_self',
				),
				array(
					'text' => esc_html__( 'Premium', 'woocommerce-direct-checkout' ),
					'url'  => 'https://quadlayers.com/products/woocommerce-direct-checkout/?utm_source=qlwcdc_plugin&utm_medium=plugin_table&utm_campaign=premium_upgrade&utm_content=premium_link',
					'color' => 'green',
					'target' => '_blank',
				),
				array(
					'place' => 'row_meta',
					'text'  => esc_html__( 'Support', 'woocommerce-direct-checkout' ),
					'url'   => 'https://quadlayers.com/account/support/?utm_source=qlwcdc_plugin&utm_medium=plugin_table&utm_campaign=support&utm_content=support_link',
				),
				array(
					'place' => 'row_meta',
					'text'  => esc_html__( 'Documentation', 'woocommerce-direct-checkout' ),
					'url'   => 'https://quadlayers.com/documentation/woocommerce-direct-checkout/?utm_source=qlwcdc_plugin&utm_medium=plugin_table&utm_campaign=documentation&utm_content=documentation_link',
				),
			)
		);
	});

}
