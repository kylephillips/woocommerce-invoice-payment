<?php
namespace WooInvoicePayment\Repositories;

class UserRepository
{
	/**
	* Get roles allowed to use their own shipping account
	* @return array
	*/
	public function allowedRoles()
	{
		$option = get_option('woocommerce_invoice_settings');
		return ( isset($option['customer_roles']) && !empty($option['customer_roles']) ) ? $option['customer_roles'] : [];
	}

	/**
	* Is the customer is allowed to use their own shipper
	* @param obj|int - Either a user object or user id
	* @return bool
	*/
	public function customerAllowed($user = null)
	{
		if ( !$user ) $user = wp_get_current_user();
		if ( is_int($user) ) $user = get_user_by('id', $user);
        $user_roles = $user->roles;
        $allowed_roles = $this->allowedRoles();
        if ( empty($allowed_roles) ) return true;
        $allowed = false;
        foreach ( $allowed_roles as $role ) {
            if ( in_array($role, $user_roles) ) $allowed = true;
        }
        return apply_filters('woocommerce_invoice_payment_user_allowed', $allowed, $user);
	}
}