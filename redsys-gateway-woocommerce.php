<?php
/*
Plugin Name: RedSys Gateway for WooCommerce
Plugin URI: http://www.codection.com
Description: This plugins allows to users to include RedSys / Servired / Sermepa in their WooCommerce installations
Author: codection
Version: 0.9
Author URI: https://codection.com
*/

add_action( 'plugins_loaded', 'redsys_plugins_loaded' );
add_action( 'init', 'redsys_inicio' );

function redsys_inicio() {
	load_plugin_textdomain( "redsys_gw_woo", false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

function redsys_plugins_loaded() {
	include_once ('class-wc-redsys-gateway.php');
	
	add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_gateway_redsys_gateway' );	
	add_action( 'woocommerce_api_wc_gateway_redsys', 'redsys_ipn_response' );
}

function redsys_ipn_response () {		
	$post_filtered = filter_input_array( INPUT_POST );
	
	if ( $post_filtered['Ds_Response'] == '0000' ):
		$order_id = substr( $post_filtered['Ds_Order'], 0, 8 );		
		$order = new WC_Order( $order_id );
		
		if ( $order->status == 'completed' )
			exit;

		$order->update_status('completed');		
		$order->add_order_note( sprintf( __( 'RedSys/Servired order completed, code %s', "redsys_gw_woo" ), $post_filtered['Ds_AuthorisationCode'] ) );
	else:
		$order = new WC_Order( $post_filtered['Ds_Order'] );

		$order->update_status('cancelled');
		
		$order->add_order_note( sprintf( __( 'RedSys/Servired payment error, code %s', "redsys_gw_woo" ), $post_filtered['Ds_ErrorCode'] ) );	
	endif;
}

function woocommerce_add_gateway_redsys_gateway($methods) {
	$methods[] = 'WC_Redsys_Gateway';
	return $methods;
}

function redsys_enqueue($hook) {
	if ( 'woocommerce_page_wc-settings' != $hook ) {
        return;
    }

    wp_enqueue_script( 'jquery-validate', plugin_dir_url( __FILE__ ) . 'js/jquery.validate.min.js', array( "jquery" ), "1.13.1", true );
}
add_action( 'admin_enqueue_scripts', 'redsys_enqueue' );