<?php

/**
 * WooCommerce Jibit Payment Gateway
 *
 * @class       Jibit_API
 * @author      Jibit Tech Team
 * @version     1.0
 * @since       1.0
 * @extends     WC_Payment_Gateway
 * @package     WooCommerce Jibit Payment Gateway
 */
class Jibit_WooCommerce_Payment_Gateway extends WC_Payment_Gateway {
	/**
	 * Logger instance
	 *
	 * @var WC_Logger
	 */
	public static $log = false;

	/**
	 * Jibit_WooCommerce_Payment_Gateway constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->id                 = 'jibit';
		$this->method_title       = __( 'Jibit Gateway', 'wjpg' );
		$this->method_description = __( 'Jibit Gateway', 'wjpg' );
		$this->icon               = WC_JIBIT_PAYMENT_GATEWAY_URL . 'images/logo.png';
		$this->has_fields         = false;

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->settings[ 'title' ];
		$this->description = $this->settings[ 'description' ];

		$this->merchant_id       = $this->settings[ 'merchant_id' ];
		$this->merchant_password = $this->settings[ 'merchant_password' ];

		if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
				$this,
				'process_admin_options'
			) );
		} else {
			add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );
		}

		add_action( 'woocommerce_checkout_order_processed', array( $this, 'requestJibitOrder' ) );
		add_action( 'woocommerce_receipt_' . $this->id . '', array( $this, 'redirectToJibit' ) );
		add_action( 'woocommerce_api_wc_jibit_gateway', array(
			$this,
			'jibitCallback'
		) );
	}

	/**
	 * Output the gateway settings screen.
	 *
	 * @return void
	 */
	public function admin_options() {
		parent::admin_options();
	}

	/**
	 * Initialise settings form fields.
	 *
	 * Add an array of fields to be displayed on the gateway's settings screen.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = apply_filters( 'jibit_woocommerce_config', array(
				'enabled'                       => array(
					'title'    => __( 'Enable Jibit gateway', 'wjpg' ),
					'type'     => 'checkbox',
					'label'    => __( 'Enable Jibit gateway', 'wjpg' ),
					'default'  => 'yes',
					'desc_tip' => true,
				),
				'title'                         => array(
					'title'       => __( 'Gateway title', 'wjpg' ),
					'type'        => 'text',
					'description' => __( 'This title will be shown in checkout page', 'wjpg' ),
					'default'     => __( 'Jibit Payment', 'wjpg' ),
					'desc_tip'    => true,
				),
				'description'                   => array(
					'title'       => __( 'Gateway description', 'wjpg' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => __( 'Description will be shown on payment process to the user', 'wjpg' ),
					'default'     => __( 'Pay with your Jibit account', 'wjpg' )
				),
				'merchant_settings'             => array(
					'title'       => __( 'Gateway settings', 'wjpg' ),
					'type'        => 'title',
					'description' => '',
				),
				'merchant_id'                   => array(
					'title'       => __( 'Merchant ID (username)', 'wjpg' ),
					'type'        => 'text',
					'description' => __( 'Your merchant ID in Jibit', 'wjpg' ),
					'default'     => '',
					'desc_tip'    => true
				),
				'merchant_password'             => array(
					'title'       => __( 'Merchant Password', 'wjpg' ),
					'type'        => 'text',
					'description' => __( 'Your merchant password', 'wjpg' ),
					'default'     => '',
					'desc_tip'    => true
				),
				'messages'                      => array(
					'title'       => __( 'Messages', 'wjpg' ),
					'type'        => 'title',
					'description' => '',
				),
				'successful_payment_message'    => array(
					'title'       => __( 'Successful payment message', 'wjpg' ),
					'type'        => 'text',
					'description' => __( 'Enter the message which will be shown to the customer after successful payment', 'wjpg' ),
					'default'     => __( 'Your payment was successful', 'wjpg' ),
					'desc_tip'    => true
				),
				'failed_payment_message'        => array(
					'title'       => __( 'Failed payment message', 'wjpg' ),
					'type'        => 'text',
					'description' => __( 'Enter the message which will be shown to the customer after failed payment', 'wjpg' ),
					'default'     => __( 'An error has occurred. Please try again.', 'wjpg' ),
					'desc_tip'    => true
				),
				'user_canceled_payment_message' => array(
					'title'       => __( 'User canceled payment message', 'wjpg' ),
					'type'        => 'text',
					'description' => __( 'This message will be shown when user has clicked on cancel button in gateway page.', 'wjpg' ),
					'default'     => __( 'You have canceled the payment.', 'wjpg' ),
					'desc_tip'    => true
				),
				'payment_expired_message'       => array(
					'title'       => __( 'Payment expired message', 'wjpg' ),
					'type'        => 'text',
					'description' => __( 'This message will be shown when payment has expired.', 'wjpg' ),
					'default'     => __( 'The payment is expired.', 'wjpg' ),
					'desc_tip'    => true
				),
				'unverified_payment_message'    => array(
					'title'       => __( 'Unverified payment message', 'wjpg' ),
					'type'        => 'text',
					'description' => __( 'This message will be shown when order is not verified by Jibit.', 'wjpg' ),
					'default'     => __( 'Couldn\'t verify order by Jibit. Please contact admin.', 'wjpg' ),
					'desc_tip'    => true
				),
				'tools'                         => array(
					'title'       => __( 'Tools', 'wjpg' ),
					'type'        => 'title',
					'description' => '',
				),
				'debug'                         => array(
					'title'    => __( 'Enable debugging', 'wjpg' ),
					'type'     => 'checkbox',
					'label'    => __( 'Enables logs on errors', 'wjpg' ),
					'default'  => 'no',
					'desc_tip' => true,
				),
			)
		);
	}

	/**
	 * Process Payment.
	 *
	 * Process the payment. Override this in your gateway. When implemented, this should.
	 * return the success and redirect in an array. e.g:
	 *
	 *        return array(
	 *            'result'   => 'success',
	 *            'redirect' => $this->get_return_url( $order )
	 *        );
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = new WC_Order( $order_id );

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true )
		);
	}


	public function requestJibitOrder( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( $order->get_payment_method() !== $this->id ) {
			return;
		}

		$currency = get_woocommerce_currency();

		if ( ! in_array( $currency, array( 'IRT', 'IRR' ) ) ) {
			return;
		}

		$amount = absint( $order->get_total() );

		if ( $currency === 'IRT' ) {
			$amount *= 10;
		}

		$token = Jibit_API::getCachedToken( $this->merchant_id, $this->merchant_password );
		if ( ! $token[ 'succeed' ] ) {
			$this->log( 'Get cached token result: ' . $token[ 'error' ] . ' - ' . wc_print_r( $token[ 'request' ], true ) );

			return false;
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


		if ( $jibitOrder[ 'succeed' ] ) {
			update_post_meta( $order_id, 'jibit_order_id', $jibitOrder[ 'order_id' ] );
			update_post_meta( $order_id, 'jibit_order_redirect_url', $jibitOrder[ 'redirect_url' ] );
		}

	}

	/**
	 * Loads payment form in receipt page
	 *
	 * @param $order_id
	 *
	 * @return void
	 */
	public function redirectToJibit( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order->get_payment_method() !== $this->id ) {
			return;
		}

		$jibitOrderId = get_post_meta( $order_id, 'jibit_order_id', true );
		$paymentUrl   = get_post_meta( $order_id, 'jibit_order_redirect_url', true );

		if ( ! $jibitOrderId ) {
			return;
		}

		$returnUrl = wc_get_checkout_url();


		$template = locate_template( 'woocommerce/jibit/payment-form.php', false );

		if ( ! $template ) {
			$template = WC_JIBIT_PAYMENT_GATEWAY_DIR . '/templates/payment-form.php';
		}


		require $template;
	}

	/**
	 * Callback for Jibit payment return url
	 *
	 * @return void
	 */
	public function jibitCallback() {
		global $woocommerce;

		if ( empty( $_GET[ 'wc_order' ] ) || ! is_numeric( $_GET[ 'wc_order' ] ) ) {
			return;
		}

		$order = wc_get_order( $_GET[ 'wc_order' ] );

		if ( ! $order ) {
			return;
		}

		$orderJibitId = get_post_meta( $order->get_id(), 'jibit_order_id', true );

		if (
			empty( $_GET[ 'orderId' ] )
			|| ! is_string( $_GET[ 'orderId' ] )
			|| $orderJibitId !== $_GET[ 'orderId' ]
		) {
			wc_add_notice( $this->settings[ 'failed_payment_message' ], 'error' );
			wp_redirect( $woocommerce->cart->get_checkout_url() );
			exit;
		}

		if ( empty( $_GET[ 'status' ] ) || $_GET[ 'status' ] !== WC_JIBIT_ORDER_PURCHASE_BY_USER ) {
			$message = wjpgGetMessageFromRedirectStatus( $_GET[ 'status' ], $this->settings );
			wc_add_notice( $message, 'error' );
			wp_redirect( $woocommerce->cart->get_checkout_url() );
			exit;
		}

		if (
			$order->get_status() !== 'pending'
		) {
			wc_add_notice( __( 'This order has been already paid.', 'jwpg' ), 'error' );
			wp_redirect( $woocommerce->cart->get_checkout_url() );
			exit;
		}

		$token = Jibit_API::getCachedToken( $this->merchant_id, $this->merchant_password );
		if ( ! $token[ 'succeed' ] ) {
			$this->log( 'Get cached token result: ' . $token[ 'error' ] . ' - ' . wc_print_r( $token[ 'request' ], true ) );
			wc_add_notice( $this->settings[ 'failed_payment_message' ], 'error' );
			wp_redirect( $woocommerce->cart->get_checkout_url() );
			exit;
		}
		$token = $token[ 'token' ];


		$jibitOrderId = $_GET[ 'orderId' ];
		$amount       = absint( $order->get_total() );
		if ( get_woocommerce_currency() === 'IRT' ) {
			$amount *= 10;
		}

		$verify = Jibit_API::verifyOrder( $jibitOrderId, $token );

		if ( absint( $verify[ 'result' ][ 'amount' ] ) !== absint( $amount ) ) {
			wc_add_notice( __( 'Invalid order info.', 'jwpg' ), 'error' );
			wp_redirect( $woocommerce->cart->get_checkout_url() );
			exit;
		}

		if ( ! $verify[ 'verified' ] ) {
			wc_add_notice( $this->settings[ 'unverified_payment_message' ], 'error' );
			wp_redirect( $woocommerce->cart->get_checkout_url() );
			exit;
		}

		$order->payment_complete( $jibitOrderId );
		$woocommerce->cart->empty_cart();

		$note = sprintf( __( 'Payment was successful. Order ID: "%s"', 'jwpg' ), $jibitOrderId );
		$order->add_order_note( $note, true );

		wc_add_notice( $this->settings[ 'successful_payment_message' ], 'success' );
		wp_redirect( add_query_arg( 'wc_status', 'success', $this->get_return_url( $order ) ) );
		exit;
	}

	public function log( $message, $level = 'info' ) {
		$enabled = ! empty( $this->settings[ 'enable_logs' ] ) && $this->settings[ 'enable_logs' ] === 'yes';
		if ( $enabled ) {
			if ( empty( self::$log ) ) {
				self::$log = wc_get_logger();
			}
			self::$log->log( $level, $message, array( 'source' => 'jibit' ) );
		}

	}

}
