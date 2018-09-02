<?php
function wjpgGetMessageFromRedirectStatus( $status ) {
	switch ( $status ) {
		case WC_JIBIT_ORDER_INITIAL:
			return __( "Order created.", "wjpg" );
		case WC_JIBIT_ORDER_PGENTRY:
			return __( "User redirected to Jibit payment gateway.", "wjpg" );
		case WC_JIBIT_ORDER_CANCEL_BY_SYSTEM:
			return __( "Your payment request has been canceled by Jibit.", "wjpg" );
		case WC_JIBIT_ORDER_CANCEL_BY_USER:
			return __( "Payment request canceled by user.", "wjpg" );
		case WC_JIBIT_ORDER_PURCHASE_BY_USER:
			return __( "User has paid the order.", "wjpg" );
		case WC_JIBIT_ORDER_PURCHASE_CONFIRM_BY_MERCHANT:
			return __( "Order is verified by Jibit.", "wjpg" );
	}

	return '';
}

function wjpgValidateHttpStatusCode($status){
	return $status >= 200 && $status < 300;
}
