<?php
namespace WooInvoicePayment\Repositories;

class SettingsRepository
{
	/**
	* Are billing fields disabled for invoice payments?
	* @return bool
	*/
	public function hideBillingInCheckout()
	{
		$option = get_option('woocommerce_invoice_settings');
		return ( isset($option['hide_billing_checkout']) && $option['hide_billing_checkout'] == 'yes' ) ? true : false;
	}

	/**
	* Are tax calculations disabled for invoice payments?
	* @return bool
	*/
	public function taxesDisabled()
	{
		$option = get_option('woocommerce_invoice_settings');
		return ( isset($option['disable_taxes']) && $option['disable_taxes'] == 'yes' ) ? true : false;
	}

	/**
	* Should taxes be removed completely from subtotals? (removes label here)
	* @return bool
	*/
	public function hideTaxSubtotals()
	{
		$option = get_option('woocommerce_invoice_settings');
		return ( isset($option['hide_tax_subtotal']) && $option['hide_tax_subtotal'] == 'yes' ) ? true : false;
	}

	/**
	* Optional message shown if taxes are disabled for invoice payments. Filtered in DisableTaxes:message()
	* @return str
	*/
	public function taxTotalMessage()
	{
		$option = get_option('woocommerce_invoice_settings');
		$message = ( isset($option['tax_disabled_totals_message']) && $option['tax_disabled_totals_message'] !== '' ) 
			? sanitize_text_field($option['tax_disabled_totals_message']) : null;
		return ( isset($option['disable_taxes']) && $option['disable_taxes'] == 'yes' && $message ) ? $message : null;
	}

	/**
	* Are shipping calculations disabled for invoice payments?
	* @return bool
	*/
	public function shippingDisabled()
	{
		$option = get_option('woocommerce_invoice_settings');
		return ( isset($option['disable_shipping']) && $option['disable_shipping'] == 'yes' ) ? true : false;
	}

	/**
	* Should shipping be removed completely from subtotals? (removes label here)
	* @return bool
	*/
	public function hideShippingSubtotals()
	{
		$option = get_option('woocommerce_invoice_settings');
		return ( isset($option['hide_shipping_subtotal']) && $option['hide_shipping_subtotal'] == 'yes' ) ? true : false;
	}

	/**
	* Optional message shown if shipping is disabled for invoice payments. Filtered in DisableShipping:message()
	* @return str
	*/
	public function shippingMessage()
	{
		$option = get_option('woocommerce_invoice_settings');
		$message = ( isset($option['shipping_disabled_message']) && $option['shipping_disabled_message'] !== '' ) 
			? sanitize_text_field($option['shipping_disabled_message']) : null;
		return ( isset($option['disable_shipping']) && $option['disable_shipping'] == 'yes' && $message ) ? $message : null;
	}

	/**
	* Should shipping address fields be shown/hidden if the invoice method is selected?
	* @return bool
	*/
	public function shippingFields()
	{
		$option = get_option('woocommerce_invoice_settings');
		return ( isset($option['show_shipping_fields']) && $option['show_shipping_fields'] == 'yes' ) ? true : false;
	}

	/**
	* Is the custom field of shipping method required if invoice method is selected?
	* @return bool
	*/
	public function shippingSelectionRequired()
	{
		$option = get_option('woocommerce_invoice_settings');
		return ( isset($option['require_shipping_selection']) && $option['require_shipping_selection'] == 'yes' ) ? true : false;
	}

	/**
	* Get the available shipping options
	* @return bool
	*/
	public function shippingOptions()
	{
		$option = get_option('woocommerce_invoice_settings');
		return ( isset($option['shipping_options']) ) ? $option['shipping_options'] : false;
	}

	/**
	* Get the billing meta/field overrides
	*/
	public function billingMetaOverrides()
	{
		$option = get_option('woocommerce_invoice_settings');
		return ( isset($option['override_billing_meta']) ) ? $option['override_billing_meta'] : false;
	}

	/**
	* Get the billing fields to hide/disable
	*/
	public function disableBillingFields()
	{
		$option = get_option('woocommerce_invoice_settings');
		return ( isset($option['disable_billing_fields']) ) ? $option['disable_billing_fields'] : false;
	}
}