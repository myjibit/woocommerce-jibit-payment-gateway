<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

	public static $jibitApi = 'https://pg.jibit.mobi';

	/**
	 * @param $username string
	 * @param $password string
	 *
	 * @return array
	 */
	public static function getToken( $username, $password ) {
		$data = wp_remote_post(
			self::$jibitApi . '/authenticate',
			array(
				'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ),
				'body'    => json_encode( array(
					'username' => $username,
					'password' => $password
				) ),
			)
		);
		if ( is_wp_error( $data ) || ! wjpgValidateHttpStatusCode( $data[ 'response' ][ 'code' ] ) ) {
			return array(
				'succeed' => false,
				'error'   => self::error(
					'Couldn\'t validate request token body.',
					$data
				),
				'request' => $data
			);
		}
		$body = json_decode( $data[ 'body' ], true );
		if ( ! $body ) {
			return array(
				'succeed' => false,
				'error'   => self::error(
					'Couldn\'t parse body for request token.',
					$data
				),
				'request' => $data
			);
		}

		if ( $body[ 'errorCode' ] > 0 ) {
			return array(
				'succeed' => false,
				'error'   => self::error(
					array( 'Jibit didn\'t create token. error code: %s', (string) $body[ 'errorCode' ] ),
					$data
				),
				'request' => $data
			);
		}

		return array(
			'succeed'       => true,
			'token'         => $body[ 'result' ][ 'token' ],
			'refresh_token' => $body[ 'result' ][ 'refreshToken' ]
		);
	}

	/**
	 * @param $token
	 * @param $refreshToken
	 *
	 * @return array
	 */
	public static function refreshToken( $token, $refreshToken ) {
		$data = wp_remote_post(
			self::$jibitApi . '/authenticate/refresh',
			array(
				'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ),
				'body'    => json_encode( array(
					'token'        => $token,
					'refreshToken' => $refreshToken
				) ),
			)
		);
		if ( is_wp_error( $data ) || ! wjpgValidateHttpStatusCode( $data[ 'response' ][ 'code' ] ) ) {
			return array(
				'succeed' => false,
				'error'   => self::error(
					'Couldn\'t validate request refresh token body.',
					$data
				),
				'request' => $data
			);
		}
		$body = json_decode( $data[ 'body' ], true );
		if ( ! $body ) {
			return array(
				'succeed' => false,
				'error'   => self::error(
					'Couldn\'t parse body for request refresh token.',
					$data
				),
				'request' => $data
			);
		}

		if ( $body[ 'errorCode' ] > 0 ) {
			return array(
				'succeed' => false,
				'error'   => self::error(
					array( 'Jibit didn\'t create refresh token. error code: %s', (string) $body[ 'errorCode' ] ),
					$data
				),
				'request' => $data
			);
		}

		return array(
			'succeed'       => true,
			'token'         => $body[ 'result' ][ 'token' ],
			'refresh_token' => $body[ 'result' ][ 'refreshToken' ]
		);
	}

	/**
	 * @param $username
	 * @param $password
	 *
	 * @return bool|mixed
	 */
	public static function getCachedToken( $username, $password ) {
		$tokenOptionName        = 'jibit_wc_pay_token';
		$refreshTokenOptionName = 'jibit_wc_pay_refresh_token';
		$token                  = get_option( $tokenOptionName, array() );
		$refreshToken           = get_option( $refreshTokenOptionName, false );
		$time                   = time();

		if ( $token && (int) $token[ 'expires_in' ] > $time ) {
			return array(
				'succeed' => true,
				'token'   => $token[ 'token' ],
			);
		}
		if ( $refreshToken ) {
			$newToken = self::refreshToken( $token[ 'token' ], $refreshToken );
			if ( ! $newToken[ 'succeed' ] ) {
				return array(
					'succeed' => false,
					'error'   => $newToken[ 'error' ],
					'request' => $newToken[ 'request' ]
				);
			}
			update_option( $tokenOptionName, array(
				'token'      => $newToken[ 'token' ],
				'expires_in' => $time + ( 23 * HOUR_IN_SECONDS )
			) );
			update_option( $refreshTokenOptionName, $newToken[ 'refresh_token' ] );

			return array(
				'succeed' => true,
				'token'   => $newToken[ 'token' ],
			);
		}
		$token = self::getToken( $username, $password );
		if ( ! $token[ 'succeed' ] ) {
			return array(
				'succeed' => false,
				'error'   => $token[ 'error' ],
				'request' => $token[ 'request' ]
			);
		}
		update_option( $tokenOptionName, array(
			'token'      => $token[ 'token' ],
			'expires_in' => $time + ( 23 * HOUR_IN_SECONDS )
		) );
		update_option( $refreshTokenOptionName, $token[ 'refresh_token' ] );

		return array(
			'succeed' => true,
			'token'   => $token[ 'token' ]
		);
	}

	/**
	 * Deletes token and refresh token
	 */
	public static function deleteToken() {
		$tokenOptionName        = 'jibit_wc_pay_token';
		$refreshTokenOptionName = 'jibit_wc_pay_refresh_token';

		return array(
			'token'         => delete_option( $tokenOptionName ),
			'refresh_token' => delete_option( $refreshTokenOptionName )
		);
	}

	public static function error( $message, $request ) {
		$output = '<div style="direction: ltr; text-align: left;">';


		$output .= '<p>' . ( is_array( $message ) ? call_user_func_array( 'sprintf', $message ) : $message ) . '</p>';

		$output .= '<p>' . sprintf( 'Status code: %s', $request[ 'response' ][ 'code' ] ) . '</p>';
		$output .= '<p>' . sprintf( 'Response body: %s', $request[ 'body' ] ) . '</p>';

		return $output . '</div>';
	}

	/**
	 * Request an order to pay from Jibit. Returns an array which includes order_id and succeed properties.
	 *
	 * @param $order
	 *
	 * @return array
	 */
	public static function requestOrder( $order, $token ) {
		$order[ 'userIdentity' ] = wjpgConvert2EnglishNumbers( $order[ 'userIdentity' ] );
		preg_match( '/^((00|\+)?(98)|0)?(9\d{9})$/', $order[ 'userIdentity' ], $matches );
		$order[ 'userIdentity' ] = "0" . $matches[ 4 ];
		$data                    = wp_remote_post(
			self::$jibitApi . '/order/initiate',
			array(
				'headers' => array(
					'Content-Type'  => 'application/json; charset=utf-8',
					'Authorization' => 'Bearer ' . $token,
				),
				'body'    => json_encode( $order ),
				'method'  => 'POST'
			)
		);
		$responseStatusCode      = $data[ 'response' ][ 'code' ];
		if ( is_wp_error( $data ) || ! wjpgValidateHttpStatusCode( $responseStatusCode ) ) {
			if ( $responseStatusCode === 401 ) {
				self::deleteToken();
			}

			return array(
				'succeed' => false,
				'error'   => self::error(
					'Couldn\'t validate request payment status code.',
					$data
				),
				'request' => $data
			);
		}

		$body = json_decode( $data[ 'body' ], true );

		if ( ! $body ) {
			return array(
				'succeed' => false,
				'error'   => self::error(
					'Couldn\'t parse request payment body.',
					$data
				),
				'request' => $data
			);
		}

		if ( $body[ 'errorCode' ] > 0 ) {
			return array(
				'succeed' => false,
				'error'   => self::error(
					array( 'Request order from jibit contains error. error code: %s', (string) $body[ 'errorCode' ] ),
					$data
				),
				'request' => $data
			);
		}

		return array(
			'succeed'      => true,
			'order_id'     => $body[ 'result' ][ 'orderId' ],
			'redirect_url' => $body[ 'result' ][ 'redirectUrl' ]
		);
	}

	/**
	 * Verifies an order id. Returns in array which includes "verified" property with boolean type.
	 *
	 * @param $orderId
	 * @param $token
	 *
	 * @return array
	 */
	public static function verifyOrder( $orderId, $token ) {
		$data = wp_remote_post(
			self::$jibitApi . "/order/verify/{$orderId}",
			array(
				'headers' => array(
					'Content-Type'  => 'application/json; charset=utf-8',
					'Authorization' => 'Bearer ' . $token,
				),
				'method'  => 'POST'
			)
		);

		if ( is_wp_error( $data ) || ! wjpgValidateHttpStatusCode( $data[ 'response' ][ 'code' ] ) ) {
			return array(
				'verified' => false,
				'error'    => $data
			);
		}

		$body = json_decode( $data[ 'body' ], true );

		if ( ! $body ) {
			return array(
				'verified' => false
			);
		}

		if ( ! isset( $body[ 'errorCode' ] ) || $body[ 'errorCode' ] > 0 ) {
			return array(
				'verified' => false
			);
		}

		return array(
			'verified' => true,
			'result'   => $body[ 'result' ]
		);
	}

	/**
	 * Verifies an order id. Returns in array which includes "verified" property with boolean type.
	 *
	 * @param $orderId
	 * @param $token
	 *
	 * @return array
	 */
	public static function inquiryOrder( $orderId, $token ) {
		$data = wp_remote_get(
			self::$jibitApi . "/order/inquiry/{$orderId}",
			array(
				'headers' => array(
					'Content-Type'  => 'application/json; charset=utf-8',
					'Authorization' => 'Bearer ' . $token,
				),
				'method'  => 'GET'
			)
		);

		if ( is_wp_error( $data ) || ! wjpgValidateHttpStatusCode( $data[ 'response' ][ 'code' ] ) ) {
			return array(
				'verified' => false,
				'error'    => $data
			);
		}

		$body = json_decode( $data[ 'body' ], true );

		if ( ! $body ) {
			return array(
				'verified' => false
			);
		}

		if ( ! isset( $body[ 'errorCode' ] ) || $body[ 'errorCode' ] > 0 ) {
			return array(
				'verified' => false
			);
		}

		return array(
			'verified' => true,
			'result'   => $body[ 'result' ]
		);
	}

}

