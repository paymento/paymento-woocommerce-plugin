<?php
/**
 * Paymento Payment Block Integration
 *
 * @package Paymento
 */

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Paymento payment method integration for WooCommerce Blocks
 */
class Paymento_Payment_Block extends AbstractPaymentMethodType {

    /**
     * Payment method name/id/slug
     *
     * @var string
     */
    protected $name = 'paymento_gateway';

    /**
     * Initializes the payment method type
     */
    public function initialize() {
        $this->settings = get_option( 'woocommerce_paymento_gateway_settings', array() );
    }

    /**
     * Returns if this payment method should be active. If false, the scripts will not be enqueued.
     *
     * @return boolean
     */
    public function is_active() {
        $payment_gateways_class = WC()->payment_gateways();
        $payment_gateways       = $payment_gateways_class->payment_gateways();

        return isset( $payment_gateways[ $this->name ] ) && 'yes' === $payment_gateways[ $this->name ]->enabled;
    }

    /**
     * Returns an array of scripts/handles to be registered for this payment method
     *
     * @return array
     */
    public function get_payment_method_script_handles() {
        $script_path       = '/assets/js/frontend/blocks.js';
        $script_asset_path = PAYMENTOGW_PATH . 'assets/js/frontend/blocks.asset.php';
        $script_asset      = file_exists( $script_asset_path )
            ? require( $script_asset_path )
            : array(
                'dependencies' => array(),
                'version'      => filemtime( PAYMENTOGW_PATH . ltrim( $script_path, '/' ) ),
            );
        $script_url        = PAYMENTOGW_URL . ltrim( $script_path, '/' );

        wp_register_script(
            'wc-paymento-payments-blocks',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );

        if ( function_exists( 'wp_set_script_translations' ) ) {
            wp_set_script_translations( 'wc-paymento-payments-blocks', 'paymento-crypto-gateway', PAYMENTOGW_PATH . 'languages' );
        }

        return array( 'wc-paymento-payments-blocks' );
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script
     *
     * @return array
     */
    public function get_payment_method_data() {
        $gateway = $this->get_gateway();
        
        return array(
            'title'                => $gateway->get_title(),
            'description'          => $gateway->get_description(),
            'icon'                 => $gateway->get_icon(),
            'supports'             => array_filter( $gateway->supports, array( $gateway, 'supports' ) ),
            'placeOrderButtonLabel' => __( 'Pay with Crypto', 'paymento-crypto-gateway' ),
        );
    }

    /**
     * Get the gateway instance
     *
     * @return WC_PAYMENTO_Gateway
     */
    private function get_gateway() {
        $gateways = WC()->payment_gateways->payment_gateways();
        return $gateways[ $this->name ];
    }
}
