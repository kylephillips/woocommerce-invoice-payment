<?php
namespace WooInvoicePayment\Events;

use WooInvoicePayment\Listeners\SessionPaymentUpdated;

class PublicEvents
{
	public function __construct()
	{
		add_action('wp_ajax_update_session_payment_method', [$this, 'paymentMethodUpdated']);
		add_action('wp_ajax_nopriv_update_session_payment_method', [$this, 'paymentMethodUpdated']);
	}

	/**
	* Payment method was changed
	*/
	public function paymentMethodUpdated()
	{
		new SessionPaymentUpdated;
	}
}