=== Paymento – Non-Custodial Crypto Payment Gateway for WooCommerce ===  
Contributors: paymento  
Tags: crypto payments, bitcoin, ethereum, payment gateway, non-custodial
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.0
WC requires at least: 8.0
WC tested up to: 11.0
Stable tag: 1.3.1
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

**From your WordPress dashboard**

1. Go to Plugins > Add New.
2. Search for "Paymento".
3. Click "Install Now", then "Activate".

**Manual installation**

1. Download the plugin zip file.
2. Go to Plugins > Add New and click "Upload Plugin".
3. Choose the zip file and click "Install Now".
4. Click "Activate Plugin".

WooCommerce must be installed and active before activating this plugin. After activating, follow the Configuration steps below.

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
The plugin makes exactly four types of request to the Paymento API, each with a specific trigger. No request is made on ordinary page views.

- **Submitting Payment Requests**
  - Endpoint: `https://api.paymento.io/v1/payment/request`
  - Triggered when a customer places an order and chooses Paymento at checkout.
  - Data sent: order total, currency, order ID, the return URL on your store, and your API Key.
  - Paymento returns a payment token used to generate the invoice.

- **Payment Verification**
  - Endpoint: `https://api.paymento.io/v1/payment/verify`
  - Triggered only when Paymento notifies your store of a paid order via webhook (IPN).
  - Data sent: the payment token for that order, and your API Key.
  - Used to confirm the notification is genuine before the order is completed.

- **Fetching Merchant Information**
  - Endpoint: `https://api.paymento.io/v1/ping/merchant/`
  - Triggered only when an administrator saves the gateway settings in WooCommerce.
  - Data sent: your API Key. Returns your registered merchant name and account status, which are stored locally and shown on the settings screen.

- **Setting Callback URLs**
  - Endpoint: `https://api.paymento.io/v1/payment/settings/`
  - Triggered only when an administrator saves the gateway settings, after the merchant check above succeeds.
  - Data sent: your store's webhook (IPN) URL and your API Key, so Paymento knows where to send payment notifications.

### 📌 **Customer Redirect**

After an order is placed, the customer's browser is redirected to `https://app.paymento.io/gateway` with the payment token for that order, where they complete the payment. No other customer data is included in the redirect.

### 📌 **Data Received**

Paymento sends payment status notifications to `/wp-json/paymento/result` on your store. Every notification is verified with an HMAC SHA256 signature using your Secret Key before it is processed.


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

= 1.3.1 - 2026-08-09 =
**🛡️ Security Release — update immediately**

**🛡️ Fixed:**
* ✅ **Orders could be marked paid without payment** - The URL customers return to after paying accepted a payment status as a query parameter and trusted it. Anyone who knew the address of a store using this plugin could complete their own unpaid order. The return URL is now only a redirect: it reads the order's existing status and never changes it.
* ✅ **Return URL restricted to Paymento orders** - The endpoint did not check which gateway an order was placed with, so it could be aimed at orders paid by other methods. It now rejects any order not placed through Paymento.

Orders are now completed solely by Paymento's payment notification (IPN), which is verified with an HMAC SHA256 signature and confirmed against the Paymento API before an order changes state. That check was already in place and was not affected.

**What this means for you:** if your store has been live with an earlier version, review recent orders that were marked paid but have no matching payment in your Paymento dashboard, particularly any order completed without a corresponding transaction.

= 1.3.0 - 2026-08-08 =
**⚡ Performance & Security Update**

**⚡ Performance:**
* ✅ **Drastically fewer API requests** - The plugin previously contacted the Paymento API twice on nearly every request to your site, including ordinary front-end page views, AJAX calls and scheduled tasks. Each API call now has a single, deliberate trigger.
* ✅ **Faster page loads** - Removing those blocking requests from the gateway's startup path speeds up front-end pages, the cart and checkout.
* ✅ **Merchant status is cached** - Your merchant name and account status are checked when you save the gateway settings and stored locally, along with the time of the last check.

**🛡️ Security:**
* ✅ **Removed an unauthenticated endpoint** - `/wp-json/paymento/health` was publicly accessible and allowed any visitor to make your store contact the Paymento API. It has been removed.
* ✅ **Removed a redundant endpoint** - `/wp-json/paymento/merchant` rewrote your webhook settings on every request and is no longer registered.
* ✅ **TLS certificate validation enabled** - API requests no longer skip certificate checks, so your API Key is never sent over an unverified connection.

**🔧 Technical Improvements:**
* Payment verification now runs only in response to a webhook notification, from a single code path
* The webhook handler reuses the existing gateway instance instead of creating a new one
* Admin script loads only on the Paymento settings screen
* Removed unused legacy code

**⚠️ Note for developers:** If you were calling `/wp-json/paymento/health` or `/wp-json/paymento/merchant`, those routes no longer exist. The webhook endpoint `/wp-json/paymento/result` is unchanged.

= 1.2.1 - 2025-01-17 =
**🔧 Bug Fixes & Improvements**

**✨ Fixed:**
* ✅ **Merchant Name Display** - Fixed authentication issues preventing merchant info from loading in admin settings
* ✅ **Admin Authentication** - Replaced JavaScript-based API calls with server-side PHP implementation
* ✅ **Better Error Handling** - Improved error messages for API connection issues
* ✅ **Automatic IPN Setup** - Webhook URL configuration now happens automatically when merchant info loads

**🔧 Technical Improvements:**
* Eliminated REST API authentication conflicts in admin area
* Server-side merchant information retrieval for better reliability
* Cleaner JavaScript code with reduced complexity
* Enhanced error reporting for troubleshooting

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

= 1.3.1 =
🛡️ **Security release — update immediately.** Fixes a flaw that allowed an order to be marked paid without any payment being made. Orders are now completed only by Paymento's signature-verified payment notification. After updating, review any recent orders marked paid that have no matching transaction in your Paymento dashboard.

= 1.3.0 =
⚡ **Important update for all users.** Removes a large volume of unnecessary requests to the Paymento API that could slow your store and cause rate limiting, and closes a publicly accessible endpoint. After updating, open WooCommerce > Settings > Payments > Paymento and click "Save changes" once to re-confirm your connection.

= 1.2.1 =
🔧 **Bug Fix Update!** Fixes merchant name display issues in admin settings. Now uses reliable server-side authentication instead of JavaScript. Recommended update for better admin experience.

= 1.2.0 =
🚀 **Major Update!** Adds WooCommerce Blocks support, HPOS compatibility, enhanced security, and fixes checkout loading issues. Update recommended for all users.

= 1.0.0 =
🎉 First stable release of Paymento for WooCommerce! Start accepting Bitcoin, Ethereum, and USDT payments directly into your wallet with zero custody risk.

== License ==

This plugin is released under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html) license.

