<?php
namespace WooInvoicePayment\PaymentMethod;

/**
* Remove billing fields if the invoice payment method is selected
*/
class FieldsRequired
{
	public function __construct()
	{
		add_filter('woocommerce_billing_fields' , [$this, 'removeRequiredBilling']);
		add_filter('woocommerce_checkout_fields' , [$this, 'removeRequiredBilling']);
	}

	/**
	* Remove the required billing details if the customer has selected to pay by invoice
	*/
	public function removeRequiredBilling($fields)
	{
		if ( !is_checkout() ) return $fields;
		$payment_method = WC()->session->get('chosen_payment_method');
		if ( $payment_method !== 'invoice' ) return $fields;
		foreach ( $fields as $name => $field ){
			if ( str_contains('billing_', $name) ) unset($fields[$name]);
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