# GazChap's WooCommerce Purchase Order Payment Gateway

This plugin adds a new offline payment gateway to WooCommerce that allows your customers to request an invoice with a Purchase Order.

There are a number of options:

- You can set the plugin to ask the customer for a Purchase Order Number, and dictate whether this is mandatory or can be left blank.
- You can set the plugin to ask the customer for a postal address for the invoice.
- You can set the plugin to pre-fill this address with the customer\'s existing billing address (if they are logged in, and have one set in WooCommerce)

When an order is received, the plugin will add all of the submitted information on to the WooCommerce View Order screen.

Note: This plugin does not (currently, at least) generate the actual invoices - it is only used to collect the Purchase Order information.

## Requirements

[WordPress](https://wordpress.org). Tested up to version 4.9.4. Minimum version probably 4.0, but you should really be on a later version!
[WooCommerce](https://woocommerce.com). Tested with version 3.2.6 and 3.3.0, minimum version is 3.0.0.

## Installation

Install via the WordPress Plugin Directory, or download a release from this repository and install as you would a normal WordPress plugin.

## Usage

Once installed and activated, you need to enable the Payment Gateway in WooCommerce > Settings > Checkout (or via the plugin's Settings link on the WordPress Plugins page) - you can then set the various options for the plugin at the same time.

## License
Licensed under the [GNU General Public License v2.0](http://www.gnu.org/licenses/gpl-2.0.html)

