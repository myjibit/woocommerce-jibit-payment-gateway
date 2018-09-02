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

function wjpgValidateHttpStatusCode( $status ) {
	return $status >= 200 && $status < 300;
}
