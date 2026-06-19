<?php
/**
 * WCDC Tabs
 *
 * @package  WooCommerce Direct Checkout
 */

?>
<ul class="subsubsub">
	<?php
		/**
		 * Do action qlwcdc_sections_header.
		 *
		 * @since  1.0.0
		 */
		do_action( 'qlwcdc_sections_header' );
	?>
	<li><a target="_blank" href="<?php echo esc_url( 'https://quadlayers.com/documentation/woocommerce-direct-checkout/?utm_source=qlwcdc_plugin&utm_medium=admin_tab&utm_campaign=documentation&utm_content=documentation_link' ); ?>"><?php echo esc_html__( 'Documentation', 'woocommerce-direct-checkout' ); ?></a></li>
	| <li><a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings_suggestions' ) ); ?>"><?php echo esc_html__( 'Suggestions', 'woocommerce-direct-checkout' ); ?></a></li>
</ul>
<br class="clear" />
