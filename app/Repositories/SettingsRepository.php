<?php
namespace WooInvoicePayment\Repositories;

class SettingsRepository
{
	public function hideBillingInCheckout()
	{
		$option = get_option('woocommerce_invoice_settings');
		return ( isset($option['hide_billing_checkout']) && $option['hide_billing_checkout'] == 'yes' ) ? true : false;
	}

	public function taxesDisabled()
	{
		$option = get_option('woocommerce_invoice_settings');
		return ( isset($option['disable_taxes']) && $option['disable_taxes'] == 'yes' ) ? true : false;
	}

	public function taxTotalMessage()
	{
		$option = get_option('woocommerce_invoice_settings');
		$message = ( isset($option['tax_disabled_totals_message']) && $option['tax_disabled_totals_message'] !== '' ) 
			? sanitize_text_field($option['tax_disabled_totals_message']) : null;
		return ( isset($option['disable_taxes']) && $option['disable_taxes'] == 'yes' && $message ) ? $message : null;
	}
}