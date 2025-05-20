<?php
namespace WooInvoicePayment\UserMeta;

use WooInvoicePayment\Repositories\SettingsRepository;

class Values
{
	/**
	* Settings Repository
	*/
	private $repo;

	public function __construct()
	{
		$this->repo = new SettingsRepository;
	}

	public function getValue($meta_key)
	{
		if ( $meta_key == 'first_name' || $meta_key == 'last_name' ) :
			if ( !$this->repo->disableNameFields() ) return $this->getDefaultValue($meta_key);
			$key = $this->repo->userMetaKey($meta_key);
			if ( !$key ) return false;
			$value = get_user_meta(get_current_user_id(), $key, true);
			return ( $value ) ? sanitize_text_field($value) : false;
		endif;

		if ( $meta_key == 'email' ) :
			if ( !$this->repo->disableEmailField() ) return $this->getDefaultValue($meta_key);
			$key = $this->repo->userMetaKey($meta_key);
			if ( !$key ) return false;
			$value = get_user_meta(get_current_user_id(), $key, true);
			return ( $value ) ? sanitize_email($value) : false;
		endif;

		return false;
	}

	public function getDefaultValue($meta_key)
	{
		$user = new \WC_Customer(get_current_user_id());
		if ( $meta_key == 'first_name' ) return ( $user->get_billing_first_name() ) ? $user->get_billing_first_name() : $user->get_first_name();
		if ( $meta_key == 'last_name' ) return ( $user->get_billing_last_name() ) ? $user->get_billing_last_name() : $user->get_last_name();
		if ( $meta_key == 'email' ) return ( $user->get_billing_email() ) ? $user->get_billing_email() : $user->get_email();
		return false;
	}

	/**
	* Should a custom field be forced?
	*/
	public function forceCustomValue($meta_key, $payment_method)
	{
		if ( $payment_method !== 'invoice' ) return false;
		if ( $meta_key == 'first_name' || $meta_key == 'last_name' ) :
			if ( !$this->repo->disableNameFields() ) return false;
			$value = $this->getValue($meta_key);
			return ( $value ) ? true : false;
		endif;
		if ( $meta_key == 'email' ) :
			if ( !$this->repo->disableEmailField() ) return false;
			$value = $this->getValue($meta_key);
			return ( $value ) ? true : false;
		endif;
	}
}