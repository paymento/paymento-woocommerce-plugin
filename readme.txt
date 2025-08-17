=== Paymento – Non-Custodial Crypto Payment Gateway for WooCommerce ===  
Contributors: paymento  
Tags: crypto payments, Bitcoin, Ethereum, payment gateway, crypto gateway, woocommerce, non-custodial, USDT
Requires at least: 6.0  
Tested up to: 6.8
Requires PHP: 8.0
WC requires at least: 8.0
WC tested up to: 8.4  
Stable tag: 1.2.0  
License: GPL-2.0-or-later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  
Text Domain: paymento-crypto-gateway


Accept Bitcoin, Ethereum, and USDT in WooCommerce with Paymento – a secure, non-custodial crypto payment gateway.


== Description ==  
Paymento – The Non-Custodial Crypto Payment Gateway for WooCommerce

Paymento allows businesses and individuals to accept cryptocurrency payments **directly into their own wallets**, eliminating third-party risks, custody issues, and unnecessary fees.  

**🌟 Key Features:**  
✔ **Direct Wallet Payments** – No intermediaries, full control over your funds.  
✔ **Multi-Chain Support** – Accept Bitcoin, Ethereum, USDT (ERC20 & TRC20), and more.  
✔ **Secure & Private** – Non-custodial solution with no private key access required.  
✔ **Easy WooCommerce Integration** – Install, configure, and start accepting crypto in minutes.  
✔ **WooCommerce Blocks Compatible** – Works with both classic and modern block-based checkout.  
✔ **HPOS Compatible** – Full support for High-Performance Order Storage.  
✔ **Low Transaction Fees** – Save costs compared to traditional payment gateways.  
✔ **Developer-Friendly API** – Expand functionality with simple API calls.  

== Requirements ==

- WordPress 6.0 or higher
- WooCommerce 8.0 or higher
- PHP 8.0 or higher
- A Paymento merchant account

== Installation ==

1. Download the plugin zip file from the GitHub repository.
2. Log in to your WordPress admin panel and navigate to Plugins > Add New.
3. Click on the "Upload Plugin" button at the top of the page.
4. Choose the downloaded zip file and click "Install Now".
5. After installation, click "Activate Plugin".

== Configuration ==

1. Go to WooCommerce > Settings > Payments.
2. Find "Paymento" in the list and click "Manage".
3. Enable the payment method by checking the "Enable/Disable" box.
4. Fill in the following required fields:
   - Title: The name of the payment method displayed to customers
   - Description: A brief description of the payment method displayed to customer, you can mention the assets that you want to accept.
   - API Key: Your Paymento API Key (available in your Paymento merchant dashboard)
   - Secret Key: Your Paymento Secret Key (available in your Paymento merchant dashboard)
5. Choose your preferred Confirmation Type:
   - Redirect Immediately and Hold Invoice (Recommended)
   - Wait for Payment Confirmation
6. Optionally enable Debug Log for troubleshooting.
7. Click "Save changes" to apply your settings.

== Usage ==

Once configured, the Paymento payment option will appear on your WooCommerce checkout page. Customers can select this option to pay with cryptocurrency.

== Order Process ==

1. Customer selects Paymento as the payment method and completes the order.
2. They are redirected to the Paymento payment page to complete the transaction.
3. After payment, the customer is redirected back to your store.
4. The order status is updated based on the payment status and your chosen confirmation type.


== External Services ==

This plugin connects to the **Paymento API** to process cryptocurrency payments. Paymento is a non-custodial payment gateway that enables WooCommerce stores to accept payments in Bitcoin, Ethereum, USDT, and other cryptocurrencies.

### 📌 **Data Sent to Paymento API**
The plugin interacts with the Paymento API for the following purposes:

- **Payment Verification**  
  - The plugin sends payment transaction details to `https://api.paymento.io/v1/payment/verify` to confirm if a payment was successfully made.  
  - This request is triggered when an order is placed or needs verification.  

- **Fetching Merchant Information**  
  - Calls `https://api.paymento.io/v1/ping/merchant/` to retrieve the store’s registered name and confirm API connectivity.  
  - This request is made when setting up the payment gateway in WooCommerce.  

- **Setting Callback URLs**  
  - The plugin sends data to `https://api.paymento.io/v1/payment/settings/` to configure callback URLs for payment notifications.  
  - This happens when the payment gateway is set up for the first time.  

- **Submitting Payment Requests**  
  - When a customer selects crypto payment, the plugin sends a request to `https://api.paymento.io/v1/payment/request` with order details.  
  - This allows Paymento to generate a payment invoice for the user.  

- **API Health Check**  
  - A request to `https://api.paymento.io/v1/ping/` ensures the API is operational before processing payments.  


### 🔗 **Third-Party Policies**
By using this plugin, your WooCommerce store communicates with **Paymento API**. You can review Paymento’s terms and policies here:  

- **Terms of Service**: [https://paymento.io/terms](https://paymento.io/terms)  
- **Privacy Policy**: [https://paymento.io/privacy](https://paymento.io/privacy)  

This ensures users are **fully aware** of the data being sent and why. 🚀  

== Frequently Asked Questions ==

= Does this plugin require an external API? =
Yes, this plugin connects to Paymento's API to process crypto payments securely.

= What are the costs associated with using Paymento? =
The Paymento plugin is completely free to install and use. However, there is a 0.5% transaction fee per payment processed. Paymento also provides $100 in free credit, allowing you to process up to 20,000 transactions at no cost. After reaching this limit, the standard 0.5% transaction fee applies.

= Does Paymento require my private key? =
No, Paymento never asks for your private key. Paymento is a non-custodial payment gateway, meaning you remain in full control of your funds. You only need to share your XPUB (Extended Public Key) and account address with Paymento to generate payment addresses and track transactions securely.

= Is this plugin compatible with WooCommerce Blocks? =
Yes! The plugin is fully compatible with both classic WooCommerce checkout and the modern WooCommerce Blocks checkout experience.

= Does this work with High-Performance Order Storage (HPOS)? =
Absolutely! The plugin is fully compatible with WooCommerce's HPOS feature for improved performance.

= What cryptocurrencies are supported? =
Paymento supports Bitcoin, Ethereum, USDT (both ERC20 and TRC20), and many other cryptocurrencies. Check the Paymento dashboard for the complete list.

== Troubleshooting ==

If you encounter any issues:

1. Enable Debug Log in the plugin settings.
2. Check the WooCommerce status report (WooCommerce > Status > Logs) for detailed error messages.
3. Ensure your API Key and Secret Key are correct.
4. Verify that your server can receive incoming webhooks from Paymento.

== Support ==

For support, please open an issue on the GitHub repository or contact Paymento support for gateway-specific questions.

== Contributing ==

Contributions to improve the plugin are welcome. Please fork the repository and submit a pull request with your changes.

== Changelog ==

= 1.2.0 - 2025-01-17 =
**🚀 Major Update - Modern Standards & Enhanced Compatibility**

**✨ New Features:**
* ✅ **WooCommerce Blocks Support** - Full compatibility with modern block-based checkout
* ✅ **HPOS Compatibility** - Support for High-Performance Order Storage (WooCommerce 8.2+)
* ✅ **Enhanced Security** - Improved validation, capability checks, and error handling
* ✅ **Better User Experience** - Fixed infinite loading issues in checkout
* ✅ **Translation Improvements** - Proper textdomain loading timing

**🔧 Technical Improvements:**
* Fixed payment gateway registration timing issues
* Enhanced input sanitization and validation
* Improved error handling with user-friendly messages
* Better API response validation
* WordPress coding standards compliance
* Modern plugin structure following NOWPayments pattern
* Fixed translation loading warnings

**🛡️ Security Enhancements:**
* Added proper capability checks for admin functions
* Enhanced webhook signature validation
* Improved order data access patterns (HPOS-ready)
* Better input sanitization throughout

**🐛 Bug Fixes:**
* Fixed infinite loading when selecting payment method
* Resolved translation domain loading warnings
* Fixed plugin registration timing conflicts
* Improved error messages for missing API credentials

**📋 Requirements Updated:**
* WordPress 6.0+ (was 5.0+)
* WooCommerce 8.0+ (was 3.0+)  
* PHP 8.0+ (was 7.0+)

= 1.0.0 - 2025-01-16 =
**🎉 Initial Release**

**✨ Basic Features:**
* Non-custodial crypto payment gateway integration
* Support for Bitcoin, Ethereum, USDT (ERC20 & TRC20)
* Direct wallet payments - no intermediaries
* Basic webhook integration for payment notifications
* Debug logging for troubleshooting
* Two confirmation types: Immediate redirect or wait for confirmation

**🔧 Initial Technical Implementation:**
* REST API endpoints for webhook handling
* Basic input validation
* Translation ready (paymento-crypto-gateway domain)
* Compatible with WooCommerce 3.0+

**🛡️ Basic Security:**
* HMAC SHA256 webhook signature validation
* Secure API communication with Paymento
* No private key requirements

== Upgrade Notice ==

= 1.2.0 =
🚀 **Major Update!** Adds WooCommerce Blocks support, HPOS compatibility, enhanced security, and fixes checkout loading issues. Update recommended for all users.

= 1.0.0 =
🎉 First stable release of Paymento for WooCommerce! Start accepting Bitcoin, Ethereum, and USDT payments directly into your wallet with zero custody risk.

== License ==

This plugin is released under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html) license.

