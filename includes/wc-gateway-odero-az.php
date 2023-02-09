<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * WC_Gateway_Odero_Az class.
 *
 * @extends WC_Payment_Gateway
 */

function generateSignature($baseUrl, $apiKey, $secretKey, $randomKey, $body = null) {
        $requestBody= "";
        if ($body) {
            $requestBody = json_encode($body);
        }

        $hashStr = urldecode($baseUrl) . $apiKey . $secretKey . $randomKey . $requestBody;
        return base64_encode(hash('sha256', $hashStr, true));
}

class WC_Gateway_Odero_Az extends WC_Payment_Gateway {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'odero-az';
        $this->icon = WC_ODERO_AZ_PLUGIN_URL. '/images/odero-az-logo.svg';
        $this->method_title = 'Odero Az Payment Gateway';
        $this->method_description = 'Accept credit and debit card payments using Odero Az Payment Gateway.';

        $this->supports = array(
            'products'
        );

        // Method with all the options fields
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();
        $this->title = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        $this->enabled = $this->get_option( 'enabled' );
        $this->testmode = 'yes' === $this->get_option( 'testmode' );
        $this->private_key = $this->testmode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
        $this->publishable_key = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'publishable_key' );

        // This action hook saves the settings
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

        // We need custom JavaScript to obtain a token
        add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

        add_action( 'woocommerce_api_odero-complete-payment', array( $this, 'webhook' ) );
    }

    /**
     * Plugin options
     */
    public function init_form_fields(){

        $this->form_fields = array(
            'enabled' => array(
                'title'       => 'Enable/Disable',
                'label'       => 'Enable Odero Az Payment Gateway',
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ),
            'title' => array(
                'title'       => 'Title',
                'type'        => 'text',
                'description' => 'This controls the title which the user sees during checkout.',
                'default'     => 'Pay with Odero',
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => 'Description',
                'type'        => 'textarea',
                'description' => 'This controls the description which the user sees during checkout.',
                'default'     => 'Ödənişlər artıq Odero ilə təhlükəsizdir!',
            ),
            'testmode' => array(
                'title'       => 'Test mode',
                'label'       => 'Enable Test Mode',
                'type'        => 'checkbox',
                'description' => 'Place the payment gateway in test mode using test API keys.',
                'default'     => 'yes',
                'desc_tip'    => true,
            ),
            'test_publishable_key' => array(
                'title'       => 'Test API Key',
                'type'        => 'text'
            ),
            'test_private_key' => array(
                'title'       => 'Test Secret Key',
                'type'        => 'password',
            ),
            'publishable_key' => array(
                'title'       => 'Live API Key',
                'type'        => 'text'
            ),
            'private_key' => array(
                'title'       => 'Live Secret Key',
                'type'        => 'password'
            )
        );
    }

    /**
     * Payment scripts
     */
    public function payment_scripts() {
    }

    /**
     * Processing the payments here
     */
    public function process_payment( $order_id ) {
        global $woocommerce;

        // we need it to get any order details
        $order = wc_get_order( $order_id );

        // let's decide what order item types we would like to get
        $types = array( 'line_item', 'fee', 'shipping', 'coupon' );

        $items = [];
        // iterating through each order item in the order
        foreach($order->get_items( $types ) as $item ) {
            // product only ( out of line_item | fee | shipping | coupon)
            if( $item->is_type( 'line_item' ) ) {

                // product price
                $item_total = $item->get_total();

                // product name
                $item_name = $item->get_name();
                $items[] = array('name' => $item_name, 'price' => $item_total);
            }
        }

        // odero needed variables
        $paymentURL = $this->testmode ? "https://sandbox-api-gateway.oderopay.com.tr/payment/v1/checkout-payments/init" : "https://api-gateway.oderopay.com.tr/payment/v1/checkout-payments/init";
        $requestBody = array(
            'price' => $order->get_total(),
            'paidPrice' => $order->get_total(),
            'currency' => Currency::TL,
            'paymentGroup' => PaymentGroup::PRODUCT,
            'callbackUrl' => 'NO_CALLBACK_URL',
            'items' => $items
        );
        $signature = generateSignature($paymentURL, $this->publishable_key, $this->private_key, '111', $requestBody);

        /**
         * Sending with old gold curl request
         */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, urldecode($paymentURL));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: application/json",
            "Content-Type: application/json",
            "x-api-key: ". $this->publishable_key,
            "x-rnd-key: 111",
            "x-signature: ". $signature,
            "x-auth-version: 1",
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $json_data = mb_substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close( $ch );

        if( !is_wp_error( $response ) ) {
            if( $code == 200 ) {
                $json = json_decode($json_data, true);

                $order->update_status( 'pending-payment',  __( 'Awaiting Odero payment completion. Check merchant panel in case it takes too long.', 'odero-az') );

                // Empty cart
                $woocommerce->cart->empty_cart();

                return array(
                    'result' => 'success',
                    'redirect' => $json['data']['pageUrl']
                );
            } else {
                wc_add_notice('Something went wrong. Please try again.', 'error');
                return;
            }
        } else {
            wc_add_notice('Connection error.', 'error');
            return;
        }
    }

    // TODO:
    public function webhook() {

        $order = wc_get_order( $_GET['id'] );
        $order->payment_complete();
        $order->reduce_order_stock();
        $order->add_order_note( 'Hey, your order is paid! Thank you!', true );

        update_option('webhook_debug', $_GET);
    }
}