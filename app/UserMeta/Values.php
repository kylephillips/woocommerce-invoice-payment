<?php
namespace WooInvoicePayment\UserMeta;

use WooInvoicePayment\Repositories\SettingsRepository;

class Values
{
	/**
	* Settings Repository
	*/
	private $repo;

	/**
	* Field Overrides
	*/
	private $overrides;

	public function __construct()
	{
		$this->repo = new SettingsRepository;
		$this->overrides = $this->repo->billingMetaOverrides();	
	}

	public function getValue($meta_key)
	{
		$override = false;
		foreach ( $this->overrides as $field ) :
			if ( $field['name'] == $meta_key ) $override = $field;
		endforeach;
		if ( !$override ) return false;

		$value = get_user_meta(get_current_user_id(), $override['custom'], true);
		return ( $value ) ? sanitize_text_field($value) : false;
	}

	/**
	* Get default values
	*/
	public function getDefaultValue($meta_key)
	{
		$user = new \WC_Customer(get_current_user_id());
		if ( $meta_key == 'billing_first_name' ) return ( $user->get_billing_first_name() ) ? $user->get_billing_first_name() : $user->get_first_name();
		if ( $meta_key == 'billing_last_name' ) return ( $user->get_billing_last_name() ) ? $user->get_billing_last_name() : $user->get_last_name();
		if ( $meta_key == 'billing_email' ) return ( $user->get_billing_email() ) ? $user->get_billing_email() : $user->get_email();
		if ( $meta_key == 'billing_company' ) return ( $user->get_billing_company() ) ? $user->get_billing_company() : null;
		$value = $user->get_meta($meta_key);
		return $value;
	}

	/**
	* Should a custom field be forced? (Disable user input)
	*/
	public function forceCustomValue($meta_key, $payment_method)
	{
		if ( $payment_method !== 'invoice' ) return false;
		foreach ( $this->overrides as $field ) :
			if ( $field['name'] == $meta_key && $field['disable'] && $field['disable'] == 'yes' ) return true;
		endforeach;
		return false;
	}
}