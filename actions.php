<?php

add_action( 'init', 'wjpgHandleRedirectToJibit' );

function wjpgHandleRedirectToJibit() {
	if ( ! did_action( 'woocommerce_loaded' ) ) {
		return;
	}
	$action = 'redirect_to_jibit';
	if (
		! isset( $_REQUEST[ 'action' ] )
		|| $_REQUEST[ 'action' ] !== $action
		|| ! isset( $_REQUEST[ 'order' ] )
		|| ! is_numeric( $_REQUEST[ 'order' ] )
	) {
		return;
	}

	if ( ! isset( $_REQUEST[ 'nonce' ] ) || ! wp_verify_nonce( $_REQUEST[ 'nonce' ], $action ) ) {
		wp_die( __( 'Cheatin&#8217; uh?' ) );
	}

	$redirect = wjpgRequestOrder( $_REQUEST[ 'order' ] );

	if ( $redirect[ 'result' ] !== 'success' ) {
		wp_die( $redirect[ 'error' ] );
	}

	wp_redirect( $redirect[ 'redirect' ] );
	die;
}

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

add_action( 'woocommerce_after_checkout_validation', 'wjpgValidatePhoneNumberOnCheckoutPage', 10, 2 );

add_Action( 'wp_head', function () {
	?>
    <script type="text/javascript">
      if (window.attachEvent) {
        window.attachEvent('onload', jibitDocumentLoad);
      } else {
        if (window.onload) {
          var curronload = window.onload;
          var newOnLoad = function (evt) {
            curronload(evt);
            jibitDocumentLoad(evt);
          };
          window.onload = newOnLoad;
        } else {
          window.onload = jibitDocumentLoad;
        }
      }

      function jibitDocumentLoad() {
        var $payButton = document.getElementById('jibit-pay-button');
        if (!$payButton) {
          return true;
        }
        if ($payButton.getAttribute('class').indexOf('auto-redirect') !== -1) {
          $payButton.click();
        }
      }
    </script>
	<?php
} );
