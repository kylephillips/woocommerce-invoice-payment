<?php
namespace WooInvoicePayment\Taxes;

use WooInvoicePayment\Repositories\SettingsRepository;

/**
* Handles the disabling of taxes if the plugin option is selected
*/
class DisableTaxes
{
	/**
	* Settings repository
	* @var obj
	*/
	private $settings;

	/**
	* Are taxes disabled for the invoice payment method?
	* @var bool
	*/
	private $taxes_disabled;

	public function __construct()
	{
		$this->settings = new SettingsRepository;
		$this->setDisabled();
		add_filter('wc_avatax_checkout_ready_for_calculation', [$this, 'disableAvatax']);
		add_action('woocommerce_after_calculate_totals', [$this, 'disableInCart'], 1, 10);
		add_filter('woocommerce_cart_totals_taxes_total_html', [$this, 'taxTotalHtml']);
	}

	/**
	* Set the admin-defined option
	*/
	private function setDisabled()
	{
		$this->taxes_disabled = $this->settings->taxesDisabled();
	}

	public function disableAvatax()
	{
		return ( $this->invoiceSelected() ) ? false : true;
	}


	public function disableInCart($cart)
	{
		if ( is_admin() ) return;
		$chosen_payment_method = WC()->session->get('chosen_payment_method');
		if ( !$this->invoiceSelected() ) {
			WC()->customer->set_is_vat_exempt(false);
			return;
		}
		WC()->customer->set_is_vat_exempt(true);
	}

	/**
	* Is the invoice payment method selected?
	* @return bool
	*/
	private function invoiceSelected()
	{
		$chosen_payment_method = WC()->session->get('chosen_payment_method');
		return ( $chosen_payment_method == 'invoice' && $this->taxes_disabled ) ? true : false;
	}

	/**
	* The tax total html - displays in order totals
	* @return str
	*/
	public function taxTotalHtml($html)
	{
		if ( !$this->invoiceSelected() ) return $html;
		$message = $this->settings->taxTotalMessage();
		$message = ( $message ) ? $message : $html;
		return apply_filters('woocommerce_invoice_payment_taxes_message', $message);
	}
}