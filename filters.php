<?php
add_filter( 'woocommerce_payment_gateways', 'wjpgAddGateway' );

function wjpgAddGateway( $methods ) {
	$methods[] = 'Jibit_WooCommerce_Payment_Gateway';

	return $methods;
}

add_action( 'woocommerce_after_checkout_validation', 'wjpgValidatePhoneNumberOnCheckoutPage', 10, 2 );

/**
 * Validates that the checkout form contains valid phone number
 *
 * @param  array    $data   An array of posted data.
 * @param  WP_Error $errors Validation errors.
 */
function wjpgValidatePhoneNumberOnCheckoutPage( $data, $errors ) {
	$phoneNumber = trim( $data[ 'billing_phone' ] );

	if ( empty( $phoneNumber ) && ! in_array( 'required-field', $errors->get_error_codes() ) ) {
		$errors->add( 'required-field', __( "Phone number is a required field.", 'wjpg' ) );
	} elseif ( ! empty( $phoneNumber ) ) {
		if ( ! preg_match( '/^09\d{9}$/', $phoneNumber ) ) {
			$errors->add( 'invalid_phone_number', __( "Phone number is invalid.", 'wjpg' ) );
		}
	}
}

add_filter( 'woocommerce_checkout_posted_data', 'wjpgFilterCheckoutPostData' );

function wjpgFilterCheckoutPostData( $data ) {
	$phone = $data[ 'billing_phone' ];

	if ( preg_match( '/^((00|\+)?(98)|0)?(9\d{9})$/', $phone, $matches ) ) {
		$data[ 'billing_phone' ] = "0" . $matches[ 4 ];
	}

	return $data;
}
