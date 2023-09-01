<?php
namespace WooInvoicePayment\PaymentMethod;

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

	public function __construct()
	{
		$this->settings = new SettingsRepository;
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
		foreach ( $fields as $name => $field ){
			if ( 
				str_contains($name, 'billing_') 
				&& $name !== 'billing'
				&& !str_contains($name, 'first_name') 
				&& !str_contains($name, 'last_name')
				&& !str_contains($name, 'email')
			) unset($fields[$name]);
		}
		return $fields;
	}

	/**
	* Get required fields (for bug fix when switching from invoice to another payment method)
	* Returned in AJAX response to replace missing fields
	*/
	public function getBillingFields()
	{
		remove_filter('woocommerce_billing_fields', [$this, 'removeRequiredBilling']);
		remove_filter('woocommerce_checkout_fields', [$this, 'removeRequiredBilling']);

		$checkout = new \WC_Checkout;
		$fields = $checkout->get_checkout_fields('billing');
		$fields_html = '';
		foreach ( $fields as $key => $field ) {
			$field['return'] = true;
			$fields_html .= woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
		}

		add_filter('woocommerce_billing_fields' , [$this, 'removeRequiredBilling']);
		add_filter('woocommerce_checkout_fields' , [$this, 'removeRequiredBilling']);
		return $fields_html;
	}
}