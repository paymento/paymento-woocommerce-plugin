<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! defined( 'PAYMENTOGW_URL' ) ) {
    define( 'PAYMENTOGW_URL', plugin_dir_url(dirname(__FILE__)) ); // Correct path
}

// Check if WooCommerce Payment Gateway class exists
if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	return;
}

/**
 * Paymento Payment Gateway Class
 *
 * @class WC_PAYMENTO_Gateway
 * @extends WC_Payment_Gateway
 * @version 1.0.0
 * @package Paymento
 */
class WC_PAYMENTO_Gateway extends WC_Payment_Gateway {

	private $api_key;
	private $secret_key;
	private $confirmation;
	private $debug = false;
	private $logger;


	/**
	 * Loads the class, runs on init
	 *
	 * @return void
	 */
	public static function load() {
		add_action( 'rest_api_init', array( __CLASS__, 'wk_register_custom_routes' ) );
	}


	public function __construct()
	{

		$this->id = 'paymento_gateway';
		$this->icon = PAYMENTOGW_URL.'assets/images/paymento-badge.png';
		$this->has_fields = false; // No checkout form fields needed - redirects to external payment page
		$this->method_title = 'Paymento';
		$this->method_description = 'Paymento non-custodial crypto payment gateway for Woocommerce';
		
		// Declare support for WooCommerce features
		$this->supports = array(
			'products',
			'refunds',
		);

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Set translatable strings after textdomain is loaded
		add_action('init', array($this, 'init_translatable_strings'), 20);

		// Define user set variables
		$this->title = $this->get_option('title');
		$this->description = $this->get_option('description');
		$this->api_key = $this->get_option('api_key');
		$this->secret_key = $this->get_option('secret_key');
		$this->confirmation = $this->get_option('confirmation');
		$this->debug = $this->get_option('debug') === 'yes';

		

		// Actions
		add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'), 100, 0);
		add_action('woocommerce_receipt_'.$this->id, array($this, 'send_to_bank'));
		add_action('woocommerce_api_'.strtolower(get_class($this)), array($this, 'return_from_bank'));
		add_filter('woocommerce_get_order_item_totals', array($this, 'show_transaction_in_order'), 10, 2 );
		add_filter( 'woocommerce_available_payment_gateways', array($this, 'filter_woocommerce_available_payment_gateways'), 10, 1 ); 

		add_action('wp_enqueue_scripts', array($this,'register_script'));
		add_action('paymento_result_action', array($this,'paymento_result_action_callback'), 20, 2);
		// add_action( 'init', array($this,'register_shipped_order_status') );
		// add_filter( 'wc_order_statuses', array($this,'custom_order_status'));

		//add_action('admin_footer',  array($this,'paymento_custom_admin_js'));
		add_action('admin_enqueue_scripts', array($this, 'paymento_admin_enqueue'));

	}

	/**
	 * Initialize translatable strings after textdomain is loaded
	 */
	public function init_translatable_strings() {
		$this->method_title = __('Paymento', 'paymento-crypto-gateway');
		$this->method_description = __('Paymento non-custodial crypto payment gateway for Woocommerce', 'paymento-crypto-gateway');
	}

	function paymento_admin_enqueue($hook) {
		if ($hook !== 'woocommerce_page_wc-settings') return;
	
		wp_enqueue_script(
			'paymento-admin-js',
			PAYMENTOGW_URL . 'assets/js/paymento.js',
			array('jquery'),
			'1.1.7',
			true
		);
	
		// Ensure 'paymento_vars' is passed properly
		wp_localize_script('paymento-admin-js', 'paymento_vars', array(
			'api_key'  => esc_attr($this->get_option('api_key')),
			'rest_url' => esc_url(get_rest_url()),
		));
	}	
	

	public static	function wk_register_custom_routes() {

		register_rest_route( 'paymento', '/health', array(
			'methods' => 'GET',
			'callback' => array(__CLASS__,'wk_get_health_callback') ,
			'permission_callback' => '__return_true'
			));
			register_rest_route('paymento', '/merchant', array(
				'methods' => 'GET',
				'callback' => array(__CLASS__, 'wk_get_merchant_callback'),
				'permission_callback' => '__return_true',
			));
		register_rest_route('paymento', '/result', array(
			'methods' => 'POST',
			'callback' => array(__CLASS__, 'wk_get_post_callback'),
			'permission_callback' => '__return_true', // Keep this open since signature validation is inside the function
			));

	}

	public static function wk_get_post_callback($request) {
        $gateway = new self();
        $headers = $request->get_headers();
        $body = $request->get_body();
        $params = $request->get_json_params();

        if (!$gateway->validate_webhook_signature($body, $headers)) {
            $gateway->log('Invalid webhook signature');
            return new WP_REST_Response('Invalid signature', 400);
        }

        $gateway->process_webhook_payload($params);

        return new WP_REST_Response('Webhook processed successfully', 200);
    }

    private function validate_webhook_signature($payload, $headers) {
        if (!isset($headers['x_hmac_sha256_signature'])) {
            $this->log('Missing HMAC signature in headers');
            return false;
        }

        $received_signature = strtolower($headers['x_hmac_sha256_signature'][0]);
        $expected_signature = strtolower(hash_hmac('sha256', $payload, $this->get_option('secret_key')));

        $this->log('Received signature (lowercase): ' . $received_signature);
        $this->log('Expected signature (lowercase): ' . $expected_signature);

        return hash_equals($expected_signature, $received_signature);
    }

    private function process_webhook_payload($result) {

        if (!isset($result['OrderId']) || !isset($result['OrderStatus'])) {
            $this->log('Missing required webhook data');
            return;
        }

        $order_id = absint($result['OrderId']);
        $order_status = absint($result['OrderStatus']);
        
        if ($order_id <= 0) {
            $this->log('Invalid order ID in webhook');
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            $this->log('Order not found: ' . $order_id);
            return;
        }

        $this->log('Processing order ' . $order_id . ' with status ' . $order_status);

        switch ($order_status) {
            case 7: // Paid
                $this->process_successful_payment($order, $result);
                break;
            case 3: // Waiting to confirm
                $order->update_status('on-hold', __('Payment waiting for confirmation', 'paymento-crypto-gateway'));
                break;
            case 9: // Reject
                $order->update_status('failed', __('Payment was rejected', 'paymento-crypto-gateway'));
                break;
            default:
                $this->log('Unhandled order status: ' . $order_status);
                break;
        }
    }

    private function process_successful_payment($order, $result) {
        $this->log('Processing successful payment for order: ' . $order->get_id());
        
        $payment_token = $order->get_meta('paymento-payment-token');

        // Verify the payment
        $verified = $this->verify_payment($payment_token);

        if ($verified) {
            $this->log('Payment verified for order: ' . $order->get_id());
            wc_reduce_stock_levels($order->get_id());
            $order->payment_complete();
            $order->add_order_note(__('Payment completed via Paymento webhook', 'paymento-crypto-gateway'));
        } else {
            $this->log('Payment verification failed for order: ' . $order->get_id());
            $order->update_status('on-hold', __('Payment received but verification failed', 'paymento-crypto-gateway'));
        }
    }

	private function verify_payment($token) {
		$this->log('Verifying payment for token: ' . $token);
	
		$args = array(
			'body' => json_encode(array('token' => $token)),
			'headers' => array(
				'Content-Type' => 'application/json',
				'Api-Key' => $this->get_option('api_key')
			),
			'timeout' => 30
		);
	
		$response = wp_remote_post('https://api.paymento.io/v1/payment/verify', $args);
	
		if (is_wp_error($response)) {
			$this->log('Payment verification failed: ' . $response->get_error_message());
			return false;
		}
	
		$body = wp_remote_retrieve_body($response);
		$result = json_decode($body, true);
		
		
		return isset($result['success']) && 
			   $result['success'] && 
			   isset($result['body']['token']) && 
			   $result['body']['token'] === $token;
	}

    private function log($message) {
        if ($this->debug) {
            if (empty($this->logger)) {
                $this->logger = wc_get_logger();
            }
            $this->logger->debug($message, array('source' => 'paymento'));
        }
    }

	
	public static function wk_get_health_callback ($request){
		$args = array(
			// Increase the timeout from the default of 5 to 10 seconds
			'timeout'    => 10,
		
			// Overwrite the default: "WordPress/5.8;www.mysite.tld" header:
			'user-agent' => 'My special WordPress installation',
		
			// Add a couple of custom HTTP headers
			'headers'    => array(
				 'X-Custom-Id' => 'ABC123',
				 'X-Secret-Thing' => 'secret',
			),
		
			// Skip validating the HTTP servers SSL cert;
			'sslverify' => false,
		);
		
		$response = wp_remote_get('https://api.paymento.io/v1/ping/', $args);

		if (is_wp_error($response)) {
			return new WP_REST_Response(array('error' => $response->get_error_message()), 500);
		}

		$body = wp_remote_retrieve_body($response);
		return new WP_REST_Response(json_decode($body, true));
	}

	public static function wk_get_merchant_callback($request) {
		// Check permissions
		if (!current_user_can('manage_woocommerce')) {
			return new WP_REST_Response(array('error' => 'Insufficient permissions'), 403);
		}

		// Retrieve the API key from headers
		$api_key = $request->get_header('Api-Key');
	
		// Check if API key is provided
		if (empty($api_key)) {
			return new WP_REST_Response(array('error' => 'Missing API key'), 401);
		}
	
		// Sanitize the API key
		$api_key = sanitize_text_field($api_key);
	
		// Make the external API request
		$args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'Api-Key' => $api_key,
			),
			'sslverify' => false,
		);
	
		$response = wp_remote_get('https://api.paymento.io/v1/ping/merchant/', $args);
	
		// Check for errors in the external API request
		if (is_wp_error($response)) {
			return new WP_REST_Response(array('error' => 'External API request failed'), 500);
		}
	
		// Decode the response body
		$body = json_decode($response['body'], true);
	
		// Update IPN settings (if needed)
		$body_settings = array(
			"IPN_Url" => get_site_url() . "/wp-json/paymento/result",
			"IPN_Method" => 1
		);
	
		$setting_args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'Api-Key' => $api_key,
			),
			'body' => json_encode($body_settings),
			'sslverify' => false,
		);
	
		$settings_response = wp_remote_post('https://api.paymento.io/v1/payment/settings/', $setting_args);
	
		// Return the merchant API response
		return new WP_REST_Response($body);
	}
	

	public function paymento_result_action_callback($result, $headers) {
		if ( isset($result['OrderId']) ) {
			$order_id = absint( $result['OrderId'] );
		}
		if ( isset($order_id) && !empty($order_id) ) {
			$order = wc_get_order($order_id);
			if ($order->get_status() !== 'completed') {

				// Get data from bank
				$OrderId = isset($result['OrderId']) ? $result['OrderId'] : '';
				$OrderStatus = isset($result['OrderStatus']) ? $result['OrderStatus'] : '';
				
				if( $OrderStatus == 7 ) {
					// BOOM! Payment completed!
					// $this->update_option( 'debug', '7' . json_encode($result));

					$order = wc_get_order($order_id);
					$payment_token = $order->get_meta('paymento-payment-token');

					$payload = array(
						'token' => $payment_token,
					);
					
					$args = array(
						'body' => json_encode($payload),
						'headers' => array(
							'Content-Type' => 'application/json',
							'Api-Key' => $this->get_option('api_key')
						),
						'timeout' => 30
					);
					
					$response = wp_remote_post('https://api.paymento.io/v1/payment/verify', $args);
					
					if (is_wp_error($response)) {
						// Translators: %1$s is the error message.
						$message = sprintf(__('Payment Verification failed: %1$s', 'paymento-crypto-gateway'), $response->get_error_message());
						$order->add_order_note($message, 1);
						wc_add_notice(__('Payment error:', 'paymento-crypto-gateway') . $message, 'error');
						wp_redirect(wc_get_checkout_url(), 301);
						return new WP_REST_Response('error');
					}
					
					$body = wp_remote_retrieve_body($response);
					$result = json_decode($body, true);
					
					if($result["success"] && $result["body"]["token"] == $payment_token){
						// $this->update_option( 'debug', 'verify result: true' . $order_id);
						wc_reduce_stock_levels($order_id);
						// Translators: %1$s is a line break, %2$s is the payment token.
						$message = sprintf(__('call: Payment was successful %1$s token: %2$s', 'paymento-crypto-gateway'),'<br />', $payment_token);
						$order->add_order_note($message, 1);
						$order->add_payment_token($payment_token);
						$order->update_status( 'processing' );

						$order->payment_complete();
						return new WP_REST_Response('good');
 
						// $successful_page = add_query_arg( 'wc_status', 'success', $this->get_return_url( $order ) );
						// wp_redirect( $successful_page );
						// exit();
						
					}else{			
						$message = sprintf(
							__('Payment Verification was unsuccessful.', 'paymento-crypto-gateway'),
							'<br />',
							$payment_token
						);
						$order->add_order_note($message, 1);
						wc_add_notice( __('Payment error:', 'paymento-crypto-gateway') . $message, 'error' );
						wp_redirect(wc_get_checkout_url(), 301);
						return new WP_REST_Response('good');
					}
				} else {
					// OOPS! Something wrong
					$error_message =  "Paymento failed payment";
					wc_add_notice( __('Payment error:', 'paymento-crypto-gateway') . $error_message, 'error' );
					wp_redirect( wc_get_checkout_url() );
					return new WP_REST_Response('good');
				}
			}
		}
	}

	public function register_script() {
		wp_register_style(
			'new_style', 
			PAYMENTOGW_URL . 'assets/css/style.css', 
			array(), 
			'1.0.0', 
			'all'  // Correct media parameter
		);
		wp_enqueue_style('new_style');
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields()
	{
		$this->form_fields = array(
			'enabled' => array(
				'title' => __('Enable/Disable', 'paymento-crypto-gateway'),
				'type' => 'checkbox',
				'label' => __('Enable Paymento Payments', 'paymento-crypto-gateway'),
				'default' => 'yes',
			),
			'status' => array(
				'title'   => 'Ping Status',
				'type' => 'title',
				'description' => sprintf('<span id="paymento_helth_check">Loading</span>'),
		),
		'Merchant' => array(
			'title'   => 'Merchant Name',
			'type' => 'title',
			'description' => sprintf('<span id="paymento_merchant_name">Loading</span>'),
		),
			'title' => array(
				'title' => __('Title', 'paymento-crypto-gateway'),
				'type' => 'text',
				'description' => __('This controls the title which the user sees during checkout.', 'paymento-crypto-gateway'),
				'default' => __('paymento gateway for Woocommerce', 'paymento-crypto-gateway'),
				'desc_tip' => true,
			),
			'description' => array(
				'title' => __('Description', 'paymento-crypto-gateway'),
				'type' => 'text',
				'description' => __('Payment method description that the customer will see on your checkout.', 'paymento-crypto-gateway'),
				'default' => __('Official paymento electronic payment gateway', 'paymento-crypto-gateway'),
				'desc_tip' => true,
			),
			'api_key' => array(
				'title' => __('API Key', 'paymento-crypto-gateway'),
				'type' => 'text',
				'description' => __('merchant access token', 'paymento-crypto-gateway'),
			),
			'secret_key' => array(
				'title' => __('Secret Key', 'paymento-crypto-gateway'),
				'type' => 'text',
				'description' => __('merchant secret key', 'paymento-crypto-gateway'),
			),
			'confirmation' => array(
				'title' => __('Confirmation Type', 'paymento-crypto-gateway'),
				// 'description' => __('merchant confirmation type', 'paymento'),
				'type' => 'select',
				'options' => array(
					'0' => 'Redirect Immediately and Hold Invoice (Recommended)',
					'1' => 'Wait for Payment Confirmation',
        ),
				'default' => '0'
			),
			'debug' => array(
                'title'       => __('Debug Log', 'paymento-crypto-gateway'),
                'type'        => 'checkbox',
                'label'       => __('Enable logging', 'paymento-crypto-gateway'),
                'default'     => 'no',
                'description' => __('Log Paymento events, such as webhook requests', 'paymento-crypto-gateway'),
            ),
		);
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function process_payment($order_id)
	{
		$order = wc_get_order($order_id);
		
		// Check if API key is configured
		if ( empty( $this->get_option('api_key') ) ) {
			wc_add_notice( __('Payment error: Paymento API key not configured. Please contact the store administrator.', 'paymento-crypto-gateway'), 'error' );
			return array(
				'result' => 'failure',
				'messages' => __('Payment error: Paymento API key not configured.', 'paymento-crypto-gateway')
			);
		}
		
		return array(
			'result' => 'success',
			'redirect' => $order->get_checkout_payment_url(true), // true = SSL
		);
	}


	public function get_payment_token($order_id){
		$order = wc_get_order( $order_id );
		if (!$order) {
			throw new Exception(__('Invalid order', 'paymento-crypto-gateway'));
		}
		
		$callback_url = add_query_arg('wc_order', $order_id, WC()->api_request_url('WC_PAYMENTO_Gateway'));
		$confirmation =  $this->get_option('confirmation');
		$currency = $order->get_order_currency();
		$cart_hash = $order->get_cart_hash();
		$total = $order->get_total();
		$billing_phone  = $order->get_billing_phone();
		if ( strtolower($currency) == strtolower('USD') ){
			$currency = 'USD';
		}else if( strtolower($currency) == strtolower('EUR')){
			$currency = 'EUR';
		}

		$payload = array(
			"fiatAmount" => $total,
			"fiatCurrency" => $currency,
			"ReturnUrl" => $callback_url,
			"orderId" => $order_id,
			"speed" => ($confirmation == 1) ? 0 : 1,
			"cryptoAmount" => array(),
			"additionalData" => array()
		);
		
		$args = array(
			'body' => wp_json_encode($payload),
			'headers' => array(
				'Content-Type' => 'application/json',
				'Api-Key' => esc_attr($this->get_option('api_key'))
			),
			'timeout' => 30
		);
		
		$response = wp_remote_post('https://api.paymento.io/v1/payment/request', $args);
		
		if (is_wp_error($response)) {
			// Handle error
			$error_message = esc_html($response->get_error_message());
			$this->log('Payment request failed: ' . $error_message);
			wc_add_notice( __('Payment error: Unable to connect to payment service. Please try again.', 'paymento-crypto-gateway'), 'error' );
			wp_redirect(wc_get_checkout_url());
			exit;
		}
		
		$body = wp_remote_retrieve_body($response);
		$result = json_decode($body, true);
		
		// Validate API response
		if ( !$result || !isset($result['body']) || empty($result['body']) ) {
			$this->log('Invalid API response: ' . $body);
			wc_add_notice( __('Payment error: Invalid response from payment service. Please try again.', 'paymento-crypto-gateway'), 'error' );
			wp_redirect(wc_get_checkout_url());
			exit;
		}
		
		$order = wc_get_order($order_id);
		$payment_token = sanitize_text_field($result['body']);
		$order->update_meta_data('paymento-payment-token', $payment_token);
		$order->save();
		
		$send_to_bank_url = add_query_arg('token', urlencode($payment_token), 'https://app.paymento.io/gateway');
		wp_redirect($send_to_bank_url, 301);
		exit;
	}
	
	/**
	 * Make ready for send to bank.
	 */
	public function send_to_bank($order_id)
	{
		esc_html_e('Thank you for your payment. redirecting to bank...', 'paymento-crypto-gateway');
		$this->get_payment_token($order_id);
	}

	public function return_from_bank() {

		if ( isset($_GET['wc_order']) ) {
			$order_id = absint( $_GET['wc_order'] );
		} else {
			wp_die( __('Invalid order ID', 'paymento-crypto-gateway') );
		}
		$confirmation_type = $this->get_option('confirmation');

		if ( isset($order_id) && !empty($order_id) ) {
			$order = wc_get_order($order_id);
			if (!$order) {
				wp_die( __('Order not found', 'paymento-crypto-gateway') );
			}
			
			if ($order->get_status() !== 'completed' && $order->get_status() !== 'processing') {

				// Get data from Paymento
				$OrderId = isset($_REQUEST['OrderId']) ? absint($_REQUEST['OrderId']) : 0;
				$OrderStatus = isset($_REQUEST['status']) ? absint($_REQUEST['status']) : 0;
				$payment_token = $order->get_meta('paymento-payment-token');

				if( $OrderStatus == 7 && ( $confirmation_type == 1) ) {
					// BOOM! Payment completed!
						WC()->cart->empty_cart();
						WC()->session->delete_session( 'paymento_order_id' );
						// Translators: %1$s is the order ID, %2$s is the payment status.
						$message = sprintf(__('Order ID: %1$s, Payment Status: %2$s', 'paymento-crypto-gateway'), $order_id, $payment_status);
						$order->add_payment_token($payment_token);
						$order->add_order_note($message, 1);
						$order->payment_complete();
						$successful_page = add_query_arg( 'wc_status', 'success', $this->get_return_url( $order ) );
						wp_redirect( $successful_page );
						exit();
				}
				elseif( $OrderStatus == 7 && $confirmation_type == 0 ) {
					// BOOM! Payment completed!
						WC()->cart->empty_cart();
						WC()->session->delete_session( 'paymento_order_id' );
						// Translators: %1$s is the payment token.
						$message = sprintf(__('Payment token: %1$s', 'paymento-crypto-gateway'),$payment_token);
						$order->add_payment_token($payment_token);
						$order->add_order_note($message, 1);
						$order->payment_complete();

						$order->update_status( 'on-hold' );
						$successful_page = add_query_arg( 'wc_status', 'success', $this->get_return_url( $order ) );
						wp_redirect( $successful_page );
						exit();
				} elseif( $OrderStatus == 3 ) {
					// BOOM! Payment completed!
						$message = sprintf(
							__('rfb: Payment Waiting To Confirm', 'paymento-crypto-gateway'));
						$order->add_payment_token($payment_token);
						$order->add_order_note($message, 1);
						$successful_page = add_query_arg( 'wc_status', 'success', $this->get_return_url( $order ) );
						wp_redirect( $successful_page );
						exit();	
				} else {
					// OOPS! Something wrong
					$error_message =  "rfb: Paymento failed payment";
					wc_add_notice( __('Payment error:', 'paymento-crypto-gateway') . $error_message, 'error' );
					wp_redirect( wc_get_checkout_url() ,301);
					exit();
				}
			}else if($order->get_status() == 'completed' || $order->get_status() == 'processing'){
				$successful_page = add_query_arg( 'wc_status', 'success', $this->get_return_url( $order ) );
				wp_redirect( $successful_page );
				exit();
			}
		}
	}

	public function get_error_message( $token ) {
		switch ($token) {
			case 'soap':
				return __('SOAP Client does not loaded in your server', 'paymento-crypto-gateway');
				break;
			case 'bank_connection':
				return __('Connection to bank failed.', 'paymento-crypto-gateway');
				break;
			default:
				return __('Unknown error', 'paymento-crypto-gateway');
		}
	}

	public function show_transaction_in_order($total_rows, $order) {
		$gateway = $order->get_payment_method();
		if ($gateway === $this->id) {
			$trace_number = $order->get_meta('paymento_payment_id');
			$total_rows['trace_number'] = array(
				'label' => __( 'Tracking Code:', 'paymento-crypto-gateway' ),
				'value' => $trace_number
			);
		}
		return $total_rows;
	}

	public function filter_woocommerce_available_payment_gateways($available_gateways) {
		return $available_gateways;
	}


}

/**
 * Add Paymento Gateway to WooCommerce
 *
 * @param array $gateways
 * @return array
 */
if ( ! function_exists( 'add_paymento_gateway_to_wc' ) ) {
	function add_paymento_gateway_to_wc( $gateways ) {
		if ( class_exists( 'WC_PAYMENTO_Gateway' ) ) {
			$gateways[] = 'WC_PAYMENTO_Gateway';
		}
		return $gateways;
	}
}

// Register the payment gateway with WooCommerce
add_filter( 'woocommerce_payment_gateways', 'add_paymento_gateway_to_wc' );



WC_PAYMENTO_Gateway::load();