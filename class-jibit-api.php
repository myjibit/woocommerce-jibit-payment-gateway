<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Jibit_API' ) ) {
	return;
}

/**
 * Jibit API Service
 *
 * @class       Jibit_API
 * @author      Jibit Tech Team
 * @version     1.0
 * @since       1.0
 * @package     WooCommerce Jibit Payment Gateway
 */
class Jibit_API {

	public static $jibitApi = 'https://appserver.jibit.mobi/';

	/**
	 * Request an order to pay from Jibit. Returns an array which includes order_id and succeed properties.
	 *
	 * @param $order
	 *
	 * @return array
	 */
	public static function requestOrder( $order ) {
		$data = wp_remote_post(
			self::$jibitApi . 'order_service/order/',
			array(
				'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ),
				'body'    => json_encode( $order ),
				'method'  => 'POST'
			)
		);
		if ( is_wp_error( $data ) ) {
			return array(
				'succeed' => false,
				'error'   => $data
			);
		}
		$body = json_decode( $data[ 'body' ], true );

		if ( ! $body ) {
			return array(
				'succeed' => false
			);
		}

		if ( $body[ 'errors' ] !== null ) {
			return array(
				'succeed' => false,
				'error'   => new WP_Error( 'jibit-error', $body[ 'errors' ] )
			);
		}

		return array(
			'succeed'  => true,
			'order_id' => $body[ 'orderId' ]
		);
	}

	/**
	 * Verifies an order id. Returns in array which includes "verified" property which is boolean.
	 *
	 * @param $orderId
	 *
	 * @return array
	 */
	public static function verifyOrder( $orderId ) {
		$data = wp_remote_post(
			self::$jibitApi . "order_service/order/verify/{$orderId}",
			array(
				'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ),
				'method'  => 'POST'
			)
		);
		if ( is_wp_error( $data ) ) {
			return array(
				'verified' => false,
				'error'    => $data
			);
		}

		return array(
			'verified' => json_decode( $data[ 'body' ] )
		);
	}

}
