<?php
namespace WooInvoicePayment\Repositories;

class SettingsRepository
{
	public function hideBillingInCheckout()
	{
		$option = get_option('woocommerce_invoice_settings');
		return ( isset($option['hide_billing_checkout']) && $option['hide_billing_checkout'] == 'yes' ) ? true : false;
	}
}