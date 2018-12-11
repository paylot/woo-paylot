<?php
/*
	Plugin Name:            Paylot WooCommerce Crypto-payment Gateway
	Plugin URI:             https://www.paylot.co
	Description:            A WooCommerce crypto-payment gateway for WordPress
	Version:                1.0
	Author:                 Onyekelu Chukwuebuka
	License:                GPL-2.0+
	License URI:            http://www.gnu.org/licenses/gpl-2.0.txt
	WC requires at least:   4.7
	WC tested up to:        4.9.8
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}




define( 'WC_PAYLOT_MAIN_FILE', __FILE__ );
define( 'WC_PAYLOT_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );

function pyl_wc_paylot_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	if ( class_exists( 'WC_Payment_Gateway_CC' ) ) {

		require_once dirname( __FILE__ ) . '/includes/class-paylot.php';

	} else{

		require_once dirname( __FILE__ ) . '/includes/class-paylot-deprecated.php';

	}

	add_filter( 'woocommerce_payment_gateways', 'pyl_wc_add_paylot_gateway', 99 );

}
add_action( 'plugins_loaded', 'pyl_wc_paylot_init', 99 );


/**
* Add Settings link to the plugin entry in the plugins menu
**/
function pyl_woo_paylot_plugin_action_links( $links ) {

    $settings_link = array(
    	'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paylot' ) . '" title="View Paylot WooCommerce Settings">Settings</a>'
    );

    return array_merge( $links, $settings_link );

}
add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), 'pyl_woo_paylot_plugin_action_links' );


/**
* Add Paylot Gateway to WC
**/
function pyl_wc_add_paylot_gateway( $methods ) {

	
		$methods[] = 'pyl_WC_Paylot_Gateway';




	return $methods;

}
