<?php
function wjpgGetMessageFromRedirectStatus( $status, $woocommerceSettings ) {
	switch ( $status ) {
		case WC_JIBIT_ORDER_INITIAL:
			return __( "Order created.", "wjpg" );
		case WC_JIBIT_ORDER_PGENTRY:
			return __( "User redirected to Jibit payment gateway.", "wjpg" );
		case WC_JIBIT_ORDER_CANCEL_BY_SYSTEM:
			return $woocommerceSettings[ 'payment_expired_message' ];
		case WC_JIBIT_ORDER_CANCEL_BY_USER:
			return $woocommerceSettings[ 'user_canceled_payment_message' ];
		case WC_JIBIT_ORDER_PURCHASE_BY_USER:
			return $woocommerceSettings[ 'successful_payment_message' ];
		case WC_JIBIT_ORDER_PURCHASE_CONFIRM_BY_MERCHANT:
			return $woocommerceSettings[ 'successful_payment_message' ];
	}

	return $woocommerceSettings[ 'failed_payment_message' ];
}

function wjpgGetOption( $option, $default = false ) {
	$options = get_option( 'woocommerce_jibit_settings' );

	return array_key_exists( $option, $options ) ? $options[ $option ] : $default;
}

function wjpgValidateHttpStatusCode( $status ) {
	return $status >= 200 && $status < 300;
}

function wjpgLog( $message, $level = 'info' ) {
	static $log;
	if ( empty( $log ) ) {
		$log = wc_get_logger();
	}
	$debug = wjpgGetOption( 'debug', 'no' ) === 'yes';
	if ( $debug ) {
		$log->log( $level, $message, array( 'source' => 'jibit' ) );
	}
}

function wjpgRequestOrder( $order_id ) {

	$debug            = wjpgGetOption( 'debug', 'no' ) === 'yes';
	$merchantId       = wjpgGetOption( 'merchant_id', '' );
	$merchantPassword = wjpgGetOption( 'merchant_password', '' );


	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		return array(
			'result' => 'error',
			'error'  => __( 'Order not found.', 'wjpg' )
		);
	}

	$redirect = array(
		'result'   => 'error',
		'redirect' => $order->get_checkout_payment_url( true )
	);

	if ( $order->get_payment_method() !== 'jibit' ) {
		return array(
			'result' => 'error',
			'error'  => __( 'Payment method for requested order is not Jibit.', 'wjpg' )
		);
	}

	$currency = get_woocommerce_currency();

	if ( ! in_array( $currency, array( 'IRT', 'IRR' ) ) ) {
		return array(
			'result' => 'error',
			'error'  => __( 'Jibit gateway supports only Rial and Toman currencies.', 'wjpg' )
		);
	}

	$amount = absint( $order->get_total() );

	if ( $currency === 'IRT' ) {
		$amount *= 10;
	}

	$token = Jibit_API::getCachedToken( $merchantId, $merchantPassword );
	if ( ! $token[ 'succeed' ] ) {
		if ( $debug ) {
			wjpgLog( 'Get cached token result: ' . $token[ 'error' ] . ' - ' . wc_print_r( $token[ 'request' ], true ) );
		}

		$message = $debug ? $token[ 'error' ] : __( 'An error has occurred. Please notice site administrator.', 'wjpg' );

		return array(
			'result' => 'error',
			'error'  => $message
		);
	}
	$token = $token[ 'token' ];

	$phone       = get_post_meta( $order_id, '_billing_phone', true );
	$callbackUrl = add_query_arg( 'wc_order', $order_id, WC()->api_request_url( 'wc_jibit_gateway' ) );


	$jibitOrder = Jibit_API::requestOrder(
		array(
			'amount'          => $amount,
			'callBackUrl'     => $callbackUrl,
			'userIdentity'    => $phone,
			'additionalData'  => json_encode( array() ),
			'description'     => '',
			'merchantOrderId' => $order_id
		),
		$token
	);


	if ( ! $jibitOrder[ 'succeed' ] ) {
		if ( $debug ) {
			wjpgLog( 'Get order from jibit: ' . $token[ 'error' ] . ' - ' . wc_print_r( $token[ 'request' ], true ) );
		}

		$message = $debug ? $token[ 'error' ] : __( 'An error has occurred. Please notice site administrator.', 'wjpg' );

		return array(
			'result' => 'error',
			'error'  => $message
		);
	}

	update_post_meta( $order_id, 'jibit_order_id', $jibitOrder[ 'order_id' ] );
	update_post_meta( $order_id, 'jibit_order_redirect_url', $jibitOrder[ 'redirect_url' ] );

	return array(
		'result'   => 'success',
		'redirect' => $jibitOrder[ 'redirect_url' ]
	);
}
