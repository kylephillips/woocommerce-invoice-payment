<?php
namespace WooInvoicePayment\Shipping;

use WooInvoicePayment\Repositories\SettingsRepository;

class ShippingBase
{
	/**
	* Settings repository
	* @var obj
	*/
	protected $settings;

	/**
	* Is shipping disabled for the invoice payment method?
	* @var bool
	*/
	protected $shipping_disabled;

	public function __construct()
	{
		$this->settings = new SettingsRepository;
		$this->setDisabled();
	}

	/**
	* Set the admin-defined option
	*/
	protected function setDisabled()
	{
		$this->shipping_disabled = $this->settings->shippingDisabled();
	}

	/**
	* Is the invoice payment method selected?
	* @return bool
	*/
	protected function invoiceSelected()
	{
		$chosen_payment_method = WC()->session->get('chosen_payment_method');
		return ( $chosen_payment_method == 'invoice' && $this->shipping_disabled ) ? true : false;
	}
}