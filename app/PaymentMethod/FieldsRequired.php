<?php
namespace WooInvoicePayment\PaymentMethod;

use WooInvoicePayment\UserMeta\Values;
use WooInvoicePayment\Repositories\SettingsRepository;

/**
* Remove billing fields if the invoice payment method is selected
*/
class FieldsRequired
{
	/**
	* Settings Repository
	* @var obj
	*/
	private $settings;

	/**
	* User Meta Values Repository
	* @var obj
	*/
	private $values;

	public function __construct()
	{
		$this->settings = new SettingsRepository;
		$this->values = new Values;
		add_filter('woocommerce_billing_fields' , [$this, 'removeRequiredBilling']);
		add_filter('woocommerce_checkout_fields' , [$this, 'removeRequiredBilling']);
	}

	/**
	* Remove the required billing details if the customer has selected to pay by invoice
	*/
	public function removeRequiredBilling($fields)
	{
		if ( !is_checkout() || !$this->settings->hideBillingInCheckout() ) return $fields;
		$payment_method = WC()->session->get('chosen_payment_method');
		if ( $payment_method !== 'invoice' ) return $fields;
		$include = ['billing', 'billing_first_name', 'billing_last_name', 'billing_email', 'billing_country'];
		foreach ( $fields as $name => $field ){
			if ( in_array($name, $include) ) continue; 
			if ( str_contains($name, 'billing_') ) unset($fields[$name]);
		}
		return $fields;
	}

	/**
	* Get required fields (for bug fix when switching from invoice to another payment method)
	* Returned in AJAX response to replace missing fields
	*/
	public function getBillingFields($invoice_fields = false)
	{
		remove_filter('woocommerce_billing_fields', [$this, 'removeRequiredBilling']);
		remove_filter('woocommerce_checkout_fields', [$this, 'removeRequiredBilling']);
		
		$checkout = new \WC_Checkout;
		$fields = $checkout->get_checkout_fields('billing');
		$fields_html = '';
		foreach ( $fields as $key => $field ) {

			// Optional override
			$value = false;
			if ( $key == 'billing_first_name' ) $value = $this->values->getValue('first_name');
			if ( $key == 'billing_last_name' ) $value = $this->values->getValue('last_name');
			if ( $key == 'billing_email' ) $value = $this->values->getValue('email');
			$value = ( $value ) ? $value : $checkout->get_value($key);

			$field['return'] = true;
			$fields_html .= woocommerce_form_field( $key, $field, $value );
		}

		add_filter('woocommerce_billing_fields' , [$this, 'removeRequiredBilling']);
		add_filter('woocommerce_checkout_fields' , [$this, 'removeRequiredBilling']);
		return $fields_html;
	}

	/**
	* Get the invoice billing fields
	*/
	public function getInvoiceBillingFields()
	{
		$checkout = new \WC_Checkout;
		$fields = $checkout->get_checkout_fields('billing');
		$include = ['billing', 'billing_first_name', 'billing_last_name', 'billing_email', 'billing_country', 'billing_company'];
		$fields_html = '';
		foreach ( $fields as $key => $field ) {
			if ( !in_array($key, $include) ) continue;
			$value = ( $key == 'billing_country' ) ? 'US' : $checkout->get_value( $key );
			$field['return'] = true;
			$fields_html .= woocommerce_form_field( $key, $field, $value );
		}
		return $fields_html;
	}
}