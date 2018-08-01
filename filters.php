<?php
add_filter( 'woocommerce_payment_gateways', 'wjpgAddGateway' );

function wjpgAddGateway( $methods ) {
	$methods[] = 'Jibit_WooCommerce_Payment_Gateway';

	return $methods;
}
