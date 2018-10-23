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


function wjpgSystemReportTable() {
	?>
    <table class="wc_status_table widefat" cellspacing="0">
        <thead>
        <tr>
            <th colspan="3" data-export-label="Jibit">
                <h2>
                    Jibit
                </h2>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td data-export-label="Token">Token</td>
            <td class="help">&nbsp;</td>
            <td>
				<?php
				$token = get_option( 'jibit_wc_pay_token' );
				if ( $token === false ) {
					?>
                    Not exists
				<?php } else {
					$expiresIn = $token[ 'expires_in' ];
					print_r( $token );
					echo '- token expires in: ' . human_time_diff( $expiresIn, time() );
				} ?>
            </td>
        </tr>
        <tr>
            <td data-export-label="Refresh Token">Refresh Token</td>
            <td class="help">&nbsp;</td>
            <td>
				<?php
				$token = get_option( 'jibit_wc_pay_refresh_token' );
				if ( $token === false ) {
					?>
                    Not exists
				<?php } else {
					print_r( $token );
				} ?>
            </td>
        </tr>
        </tbody>
    </table>
	<?php
}

add_action( 'woocommerce_system_status_report', 'wjpgSystemReportTable' );
