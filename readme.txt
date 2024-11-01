=== Woo DHL Auto Complete ===
Contributors: mnording10
Tags: DHL freight, DHL Tracking, DHL Fullfilment, Auto complete, order completion
Requires at least: 4.9.5
Tested up to: 5.2
Requires PHP: 5.6
Stable tag: trunk
License: MIT
License URI: https://opensource.org/licenses/MIT

Using DHL Tracking API to automatically move orders to Complete

== Description ==
Adds an order status for defining orders as \"awaiting shipments\". The plugin then checks for when the order has been handled by DHL and activates accordingly.

**Prerequisite**
This plugins relies on that you are entering your order ID from woocommerce into the sender-reference part of the shipment.

== FAQ ==
Do i need a MyACT Account for this plugin to work?
Yes

How do i get Access to the API?
You email se.ecom@dhl.com mentioning your myAct account and that you want ACT Webservice access.

== Installation ==
Upload to wp-content/plugins
Activate through admin panel

Sign up for an account at https://activetracing.dhl.com/

Send a e-mail to se.ecom@dhl.com or call SE ECOM 0771 345 345 and request accest to The ACT Webservice and specify the account previously registered at myACT

Setup a scheduled job to access {www.Yoursite.com}/wp-admin/admin-ajax.php?action=dhl_auto_complete every 6 hours.

== Changelog ==
1.2.0 - Now showing orders that are pending shipment in My-Account!
1.1.0 - Added check for woocommerce activation and cleanup for de-activation of plugin.
1.0.0 - Initial release
