<?php
namespace WooInvoicePayment\Listeners;

use WooInvoicePayment\Repositories\UserRepository;
use WooInvoicePayment\Repositories\SettingsRepository;
use WooInvoicePayment\PaymentMethod\FieldsRequired;

/**
* Sets the session payment method (via ajax)
* 
* Fixes bug where payment method change in checkout does not update dynamically, 
* Billing fields are required for CC customers, but not terms customers
* If a customer toggles Terms\CC, the billing required fields do not update unless the page is reloaded
* This class is called through an ajax action update_session_payment_method when the user updates their payment method
* The session payment method is updated so on the next validation call, the appropriate fields are required
* 
* @see assets/js/woocommerce-terms-payment.js
*/
class SessionPaymentUpdated
{
	private $user_repo; 
	private $settings; 
	private $fields_required;

	public function __construct()
	{
		$this->user_repo = new UserRepository;
		$this->settings = new SettingsRepository;
		$this->fields_required = new FieldsRequired;
		$this->validate();
		$this->savePaymentMethod();
	}

	private function validate()
	{
		if ( !wp_verify_nonce($_POST['nonce'], 'woocommerce_invoice_payment') ) {
			return $this->respond('error', __('Incorrect or missing nonce.', WOOINVOICEPAYMENT_DOMAIN));
			die();
		}
		if ( !isset($_POST['payment_method']) ) {
			return $this->respond('error', __('Payment method not specified.', WOOINVOICEPAYMENT_DOMAIN));
			die();
		}
		if ( !$_POST['payment_method'] == 'invoice' && !$this->user_repo->customerAllowed() ) {
			return $this->respond('error', __('Current customer cannot pay with terms account.', WOOINVOICEPAYMENT_DOMAIN));
			die();
		}
	}

	private function savePaymentMethod()
	{
		$payment_method = sanitize_text_field($_POST['payment_method']);
		$old_payment_method = WC()->session->get('chosen_payment_method');
		$billing_fields = $this->fields_required->getBillingFields();
		WC()->session->set('chosen_payment_method', $payment_method);
		$data['hide_billing'] = ( $this->user_repo->customerAllowed() && $this->settings->hideBillingInCheckout() && $payment_method == 'invoice' ) ? true : false;
		$data['old_payment_method'] = $old_payment_method;
		$data['new_payment_method'] = $payment_method;
		$data['billing_fields'] = $billing_fields;
		$this->respond('success', sprintf('Session payment method updated to: %s', $payment_method), $data);
	}

	private function respond($status, $message, $data = [])
	{
		return wp_send_json(['status' => $status, 'message' => $message, 'data' => $data]);
	}
}