<?php
/*
   Plugin name: Paymento – Non-Custodial Crypto Payment Gateway for WooCommerce  
   Plugin URI: https://github.com/paymento/paymento-woocommerce-plugin
   Description: Accept Bitcoin, Ethereum, USDT, and more directly into your wallet with Paymento! A secure, non-custodial crypto payment gateway for WooCommerce—no intermediaries, no hidden fees. 
   Version: 1.0.0
   Author: Paymento.io
   Author URI: https://paymento.io
   Text Domain: paymento-crypto-gateway
   Domain Path: /languages
   License: GPL-2.0-or-later
   License URI: https://www.gnu.org/licenses/gpl-2.0.html
   Requires at least: 6.0
   Tested up to: 6.4
   Requires PHP: 8.0
   WC requires at least: 8.0
   WC tested up to: 8.4
   Requires Plugins: woocommerce
*/

/**
 * Main PAYMENTO Gateway Class.
 *
 * @class PAYMENTO_WC_Main
 * @version	1.0.0
 */

 if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Declare HPOS compatibility
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

class PAYMENTO_WC_Main {

	/**
	 * The single instance of the class.
	 *
	 * @var PAYMENTO_WC_Main
	 */
	protected static $_instance = null;

	/**
	 * Name of plugin in wordpress admin area
	 * @var
	 */
	private $name;

	/**
	 * Description of plugin in wordpress admin area
	 * @var
	 */
	private $description;

	/**
	 * Author of plugin in wordpress admin area
	 * @var
	 */
	private $author;



	/**
	 * PAYMENTO_WC_Main constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->init_hooks();
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		// Since we're already in plugins_loaded, call methods directly
		$this->includes();
		$this->check_woocommerce_dependency();
		
		// Load translations and plugin data on init hook (not too early)
		add_action( 'init', array( $this, 'localization' ) );
		add_action( 'init', array( $this, 'init_plugin_data' ) );
	}

	/**
	 * Main Gateway Instance.
	 *
	 * Ensures only one instance of Paymento Gateway is loaded or can be loaded.
	 *
	 * @static
	 * @return PAYMENTO_WC_Main Gateway - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	/**
	 * Define Paymento Constants.
	 */
	private function define_constants() {
		$this->define( 'PAYMENTOGW_URL', plugin_dir_url(__FILE__) );
		$this->define( 'PAYMENTOGW_PATH', plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/* Make plugin translatable
	*/
   public function localization() {
	   $plugin_rel_path = plugin_basename(PAYMENTOGW_PATH).'/languages';
	   load_plugin_textdomain('paymento-crypto-gateway', false, $plugin_rel_path);
   }

	/**
	 * Initialize plugin data that requires translations
	 */
	public function init_plugin_data() {
		$this->name         = __('PAYMENTO gateway for Woocommerce', 'paymento-crypto-gateway');
		$this->description  = __('Paymento electronic payment gateway for Woocommerce', 'paymento-crypto-gateway');
		$this->author       = __('Paymento Team', 'paymento-crypto-gateway');
	}

	/**
	 * Check if WooCommerce is active and show admin notice if not
	 */
	public function check_woocommerce_dependency() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			return;
		}
	}

	/**
	 * Admin notice for missing WooCommerce
	 */
	public function woocommerce_missing_notice() {
		?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'Paymento payment gateway requires WooCommerce to be installed and active.', 'paymento-crypto-gateway' ); ?></p>
		</div>
		<?php
	}


	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		// Include gateway class
		include_once( PAYMENTOGW_PATH . 'includes/class-gateway.php' );

		/**
		 * WooCommerce Blocks Support.
		 */
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			include_once( PAYMENTOGW_PATH . 'includes/blocks/class-paymento-blocks-support.php' );
		}
	}

}
function paymento_gateway_get_instance() {
	return PAYMENTO_WC_Main::instance();
}

// Initialize the plugin after all plugins are loaded
add_action('plugins_loaded', 'paymento_gateway_get_instance');



// Activation hook
register_activation_hook( __FILE__, 'paymento_gateway_activation' );

/**
 * Plugin activation function
 */
function paymento_gateway_activation() {
	// Check if WooCommerce is active
	if ( ! class_exists( 'WooCommerce' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( 
			__( 'Paymento payment gateway requires WooCommerce to be installed and active.', 'paymento-crypto-gateway' ),
			__( 'Plugin Activation Error', 'paymento-crypto-gateway' ),
			array( 'back_link' => true )
		);
	}
}