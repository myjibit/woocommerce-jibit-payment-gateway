<?php
add_filter( 'woocommerce_payment_gateways', 'wjpgAddGateway' );

function wjpgAddGateway( $methods ) {
	$methods[] = 'Jibit_WooCommerce_Payment_Gateway';

	return $methods;
}

function wjpgFilterCheckoutPostData( $data ) {
	$phone = $data[ 'billing_phone' ];

	if ( preg_match( '/^((00|\+)?(98)|0)?(9\d{9})$/', $phone, $matches ) ) {
		$data[ 'billing_phone' ] = "0" . $matches[ 4 ];
	}

	return $data;
}

add_filter( 'woocommerce_checkout_posted_data', 'wjpgFilterCheckoutPostData' );

function wjpgDebugToolsResetTokens() {
	if ( ! class_exists( 'Jibit_API' ) ) {
		require WC_JIBIT_PAYMENT_GATEWAY_DIR . '/class-jibit-api.php';
	}
	$delete = Jibit_API::deleteToken();

	return __( 'Tokens reset.', 'wjpg' );
}

function wjpgDebugToolsResetTokensTool( $tools ) {
	$tools[ 'reset_jibit_tokens' ] = array(
		'name'     => __( 'Reset Jibit Tokens', 'wjpg' ),
		'button'   => __( 'Reset', 'wjpg' ),
		'callback' => 'wjpgDebugToolsResetTokens',
		'desc'     => __( 'This will reset token and refresh token for pay request.', 'wjpg' ),
	);

	return $tools;
}

add_filter( 'woocommerce_debug_tools', 'wjpgDebugToolsResetTokensTool' );
