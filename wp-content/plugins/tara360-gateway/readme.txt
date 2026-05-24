=== Tara360 Gateway ===
Contributors: tara360
Tags: woocommerce, payment, gateway, tara360, iran
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.1.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Tara payment gateway for WooCommerce.

== Description ==
This plugin adds online payment via the Tara (Tara360) payment gateway to WooCommerce. Users can easily complete their payments using the Tara app.

= Features =
* Fast and secure connection to the Tara payment gateway
* Supports Rial and Toman
* Logs full transaction reports in the database
* Customize success and failure payment messages

== Installation ==
1. Upload the plugin ZIP via “Plugins → Add New → Upload Plugin” and activate it.
2. Go to WooCommerce → Payments → Tara Payment Gateway.
3. Enter your Tara360 username, password, and gateway code, then save.

== Privacy & External Service ==
This plugin connects to the Tara360 payment API to process WooCommerce payments.

= External Service =
- Service: Tara360 Payment API
- API base: https://pay.tara360.ir/
- Terms of Service: https://tara360.ir/termscondition/
- Privacy Policy: https://tara360.ir/termscondition/

= Data Sent to Tara360 =
- Customer mobile number (entered at checkout)
- Order details: order ID/number, total amount, currency, line items (name, code/ID, quantity, unit price, optional category/group)
- Callback URL (your site’s payment return endpoint)
- IP address used for the payment request

= When Data Is Sent =
- **At checkout (token request):** when the customer selects Tara360 and a payment token is requested.
- **On return/verification:** when Tara360 redirects/calls back and the plugin verifies the transaction with the API.

= Data Stored on Your Site =
- Tara360 token/trace numbers, reference numbers (RRN), and transaction status saved to the WooCommerce order meta.
- A payment history row in a custom table (prefixed by your WordPress table prefix), used for audit/debugging.

= Tracking and Cookies =
This plugin does not track users, set analytics cookies, or send usage telemetry. It only sends the minimum data required to process the payment.

= Uninstall =
Deactivating the plugin stops any future data transfers. Removing the plugin does not automatically delete historical transaction data from orders or the custom table. You may delete those records manually if required by your policies.

== Screenshots ==
1. Mobile number input on the checkout page
2. Gateway settings in the WordPress admin panel

== Changelog ==
= 1.1.8 =
* Update the plugin to be compatible with the latest WooCommerce version.

== Changelog ==
= 1.1.7 =
* Plugin security improvements and some minor bugs fixed.

== Changelog ==
= 1.1.6 =
* Notify Tara when the plugin is deactivated.

== Changelog ==
= 1.1.5 =
* Some minor bug fixed and some performance improvements.

== Changelog ==
= 1.1.4 =
* Some minor bug fixed and some performance improvements.

== Changelog ==
= 1.1.3 =
* Some minor bug fixed and some performance improvements.

== Changelog ==
= 1.1.2 =
* Compliance and documentation improvements and some minor bug fixed.

== Changelog ==
= 1.1.1 =
* Compliance and documentation improvements.