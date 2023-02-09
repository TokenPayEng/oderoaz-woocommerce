<?php
/*
 * Plugin Name: Odero Az Payment Gateway for WooCommerce
 * Description: Accept online payment with Odero Az.
 * Author: Token Azerbaijan LLC
 * Author URI: http://odero.az
 * Version: 1.0.0
 */

define( 'WC_ODERO_AZ_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

add_action('plugins_loaded', 'woocommerce_gateway_name_init', 0);
function woocommerce_gateway_name_init() {
    if ( !class_exists( 'WC_Payment_Gateway' ) ) return;

    /**
     * Gateway class in includes directory
     */
    include_once( 'includes/wc-gateway-odero-az.php' );

    /**
     * Add the Gateway to WooCommerce
     **/
    add_filter('woocommerce_payment_gateways', 'add_gateway');
    function add_gateway($methods) {
        $methods[] = 'WC_Gateway_Odero_Az';
        return $methods;
    }
}