<?php
namespace WooInvoicePayment\Shipping;

/**
* Handles the disabling of taxes if the plugin option is selected
*/
class DisableShipping extends ShippingBase
{
	public function __construct()
	{
		parent::__construct();
		add_filter('woocommerce_cart_ready_to_calc_shipping', [$this, 'disable']);
		add_action('woocommerce_review_order_before_order_total', [$this, 'message'], 10);
		add_filter('woocommerce_cart_needs_shipping_address', [$this, 'shippingAddressFields'], 10);
	}

	/**
	* Disable shipping
	* @return bool
	*/
	public function disable($enabled)
	{
		if ( is_admin() ) return $enabled;
		if ( !$this->invoiceSelected() ) return $enabled;
		return false;
	}

	/**
	* Show/Hide Shipping Address Fields
	* @return bool
	*/
	public function shippingAddressFields($enabled)
	{
		if ( !$this->invoiceSelected() ) return $enabled;
		return $this->settings->shippingFields();
	}

	/**
	* The shipping message html - displays in order totals
	* @return str
	*/
	public function message()
	{
		if ( !$this->invoiceSelected() ) return;
		if ( $this->settings->hideShippingSubtotals() ) return;
		$message = $this->settings->shippingMessage();
		if ( $message ) $message = '<tr><th>' . __('Shipping', WOOINVOICEPAYMENT_DOMAIN) . '</th><td>' . $message . '</td></tr>';
		echo apply_filters('woocommerce_invoice_payment_shipping_message', $message);
	}
}