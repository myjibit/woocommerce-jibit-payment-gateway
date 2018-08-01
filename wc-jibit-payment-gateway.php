<?php
/**
 * Plugin Name: WooCommerce Jibit Payment Gateway
 * Plugin URI:  http://jibit.ir
 * Description: This plugin adds Jibit payment gateway to WooCommerce.
 * Author:      Jibit Team
 * Author URI:  http://jibit.ir/woocommerce-plugin
 * Version:     1.0
 * Text Domain: wjpg
 * Domain Path: /languages
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_JIBIT_PAYMENT_GATEWAY_MAIN_FILE', __FILE__ );
define( 'WC_JIBIT_PAYMENT_GATEWAY_DIR', dirname( __FILE__ ) );
define( 'WC_JIBIT_PAYMENT_GATEWAY_URL', trailingslashit( plugin_dir_url( WC_JIBIT_PAYMENT_GATEWAY_MAIN_FILE ) ) );
define( 'WC_JIBIT_QRPG_URL', "https://qrpg.jibit.ir/" );
define( 'WC_JIBIT_SUCCESSFUL_ORDER', "SUCCESS" );
define( 'WC_JIBIT_REJECTED_ORDER', "REJECTED" );
define( 'WC_JIBIT_PENDING_ORDER', "PENDING" );

add_action( 'plugins_loaded', 'wjpgLoadLanguages' );

function wjpgLoadLanguages() {
	load_plugin_textdomain( 'wjpg', false, basename( WC_JIBIT_PAYMENT_GATEWAY_DIR ) . '/languages/' );
}

add_action( 'plugins_loaded', 'wjpgInit' );
function wjpgInit() {
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		require WC_JIBIT_PAYMENT_GATEWAY_DIR . '/init.php';
	}
}
