<?php
namespace WooInvoicePayment\PaymentMethod;

use WooInvoicePayment\UserMeta\Values;
use WooInvoicePayment\Repositories\SettingsRepository;
use WooInvoicePayment\Repositories\BillingFieldsRepository;

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

	/**
	* Billing Fields Repository
	* @var obj
	*/
	private $billing_fields_repo;

	public function __construct()
	{
		$this->settings = new SettingsRepository;
		$this->values = new Values;
		$this->billing_fields_repo = new BillingFieldsRepository;
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
		$hidden_fields = $this->billing_fields_repo->getHiddenFields();
		foreach ( $fields as $name => $field ){
			if ( in_array($name, $hidden_fields) ) unset($fields[$name]); 
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
		$hidden_fields = $this->billing_fields_repo->getHiddenFields();
		$checkout = new \WC_Checkout;
		$fields = $checkout->get_checkout_fields('billing');
		$fields_html = '';
		foreach ( $fields as $key => $field ) {
			if ( in_array($key, $hidden_fields) ) continue;
			$value = ( $key == 'billing_country' ) ? 'US' : $checkout->get_value( $key );
			$field['return'] = true;
			$fields_html .= woocommerce_form_field( $key, $field, $value );
		}
		return $fields_html;
	}
}