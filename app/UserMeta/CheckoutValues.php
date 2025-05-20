<?php
namespace WooInvoicePayment\UserMeta;

use WooInvoicePayment\UserMeta\Values;

class CheckoutValues
{
	/**
	* Values Repository
	*/
	private $values;

	public function __construct()
	{
		$this->values = new Values;
		add_filter('woocommerce_checkout_get_value', [$this, 'populateEmail'], 10, 2);
		add_filter('woocommerce_checkout_get_value', [$this, 'populateName'], 10, 2);
	}

	/**
	* Prepopulate the checkout email field if necessary
	*/
	public function populateEmail($input, $key)
	{
		if ( !\WC()->session ) return $input;
		if ( \WC()->session->get('chosen_payment_method') !== 'invoice' ) return $input;

		if ( $key !== 'billing_email' ) return $input;
		$value = $this->values->getValue('email');
		return ( $value ) ? $value : $input;
	}

	/**
	* Prepopulate the checkout name fields if necessary
	*/
	public function populateName($input, $key)
	{
		if ( !\WC()->session ) return $input;
		if ( \WC()->session->get('chosen_payment_method') !== 'invoice' ) return $input;

		if ( $key == 'billing_first_name' ) :
			$value = $this->values->getValue('first_name');
			return ( $value ) ? $value : $input;
		endif;

		if ( $key == 'billing_last_name' ) :
			$value = $this->values->getValue('last_name');
			return ( $value ) ? $value : $input;
		endif;

		return $input;
	}
}