<?php
/*
 * Plugin Name: Odero Az Payment Gateway for WooCommerce
 * Description: Accept online payment with Odero Az.
 * Author: Token Azerbaijan LLC
 * Author URI: http://odero.az
 * Version: 1.0.0
 */

add_action('plugins_loaded', 'woocommerce_gateway_name_init', 0);
function woocommerce_gateway_name_init() {
    if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
    /**
     * Localisation
     */
    load_plugin_textdomain('wc-gateway-name', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');

    /**
     * Gateway class
     */
    class WC_Gateway_Name extends WC_Payment_Gateway {

        // Go wild in here
    }

    /**
     * Add the Gateway to WooCommerce
     **/
    function woocommerce_add_gateway_name_gateway($methods) {
        $methods[] = 'WC_Gateway_Name';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_name_gateway' );
}