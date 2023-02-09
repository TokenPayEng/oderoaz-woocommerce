<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * WC_Gateway_Odero_Az class.
 *
 * @extends WC_Payment_Gateway
 */
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
     * Create init cpp request
     */
//    public function init_payment_page() {
//        $request = array(
//            'price' => 100,
//            'paidPrice' => 100,
//            'walletPrice' => 0,
//            'installment' => 1,
//            'currency' => Currency::AZN,
//            'paymentGroup' => PaymentGroup::PRODUCT,
//            'conversationId' => '456d1297-908e-4bd6-a13b-4be31a6e47d5',
//            'cardUserKey' => 'eee24372-1735-4bc1-a534-023f1e02a03e',
//            'callbackUrl' => 'https://www.your-website.com/tokenpay-checkout-callback',
//            'buyerId' => 1,
//            'items' => array(
//                array(
//                    'externalId' => uniqid(),
//                    'name' => 'Item 1',
//                    'price' => 30,
//                    'subMerchantId' => 1,
//                    'subMerchantPrice' => 27
//                ),
//                array(
//                    'externalId' => uniqid(),
//                    'name' => 'Item 2',
//                    'price' => 50,
//                    'subMerchantId' => 2,
//                    'subMerchantPrice' => 42
//                ),
//                array(
//                    'externalId' => uniqid(),
//                    'name' => 'Item 3',
//                    'price' => 20,
//                    'subMerchantId' => 3,
//                    'subMerchantPrice' => 18
//                )
//            )
//        );
//
//        $response = FunctionalTestConfig::tokenpay()->payment()->initCheckoutPayment($request);
//
//        print_r($response);
//    }
}