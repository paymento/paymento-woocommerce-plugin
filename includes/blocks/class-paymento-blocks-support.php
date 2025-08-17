<?php
/**
 * WooCommerce Blocks Support for Paymento Gateway
 *
 * @package Paymento
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class to handle WooCommerce Blocks integration
 */
class Paymento_Blocks_Support {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'woocommerce_blocks_loaded', array( $this, 'register_payment_method_type' ) );
    }
    
    /**
     * Register the payment method type for blocks
     */
    public function register_payment_method_type() {
        if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
            return;
        }
        
        require_once 'class-paymento-payment-block.php';
        
        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
                $payment_method_registry->register( new Paymento_Payment_Block() );
            }
        );
    }
}

new Paymento_Blocks_Support();
