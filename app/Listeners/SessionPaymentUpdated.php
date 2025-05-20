<?php
namespace WooInvoicePayment\Listeners;

use WooInvoicePayment\Repositories\UserRepository;
use WooInvoicePayment\Repositories\SettingsRepository;
use WooInvoicePayment\PaymentMethod\FieldsRequired;
use WooInvoicePayment\UserMeta\Values;

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
	private $values;

	public function __construct()
	{
		$this->user_repo = new UserRepository;
		$this->settings = new SettingsRepository;
		$this->fields_required = new FieldsRequired;
		$this->values = new Values;
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

		$customer_details = $this->setCustomBillingFields($payment_method);

		$billing_fields = ( $payment_method == 'invoice' ) 
			? $this->fields_required->getInvoiceBillingFields()
			: $this->fields_required->getBillingFields(false);
		
		$shipping_fields = false;
		if ( class_exists('\WooLocalPickupExpanded\ShippingMethod\FieldsRequired') ) :
			$shipping_fields = ( new \WooLocalPickupExpanded\ShippingMethod\FieldsRequired )->getShippingFields();
		endif;
		
		WC()->session->set('chosen_payment_method', $payment_method);
		$data['hide_billing'] = ( $this->user_repo->customerAllowed() && $this->settings->hideBillingInCheckout() && $payment_method == 'invoice' ) ? true : false;
		$data['old_payment_method'] = $old_payment_method;
		$data['new_payment_method'] = $payment_method;
		$data['billing_fields'] = $billing_fields;
		$data['shipping_fields'] = $shipping_fields;
		$data['customer_details'] = $customer_details;
		$data['customer_fields_custom_force'] = $this->setForcedCustomFields($payment_method);

		// Local Pickup Expanded Integration for hiding/unrequiring shipping 
		$shipping_method = ( isset($_POST['shipping_method']) && $_POST['shipping_method'] !== '' ) 
			? sanitize_text_field($_POST['shipping_method']) : '';
		$shipping_required = ( str_contains($shipping_method, '_local_pickup_expanded') ) ? 'yes' : 'no';
		WC()->session->set('force_local_pickup_expanded', $shipping_required);
		WC()->session->set('invoice_payment_shipping_method', $shipping_method);
		$data['shipping_method'] = $shipping_method;

		$this->respond('success', sprintf('Session payment method updated to: %s', $payment_method), $data);
	}

	/**
	* Optional feature to pull invoice customer details from custom user meta
	* Disables editing of these fields if feature is enabled under plugin settings and invoice method is selected
	*/
	private function setCustomBillingFields($payment_method)
	{
		$fields = ['email', 'first_name', 'last_name'];
		$customer_details = [];
		foreach ( $fields as $field ) :
			$customer_details[$field] = ( $payment_method == 'invoice' ) ? $this->values->getValue($field) : $this->values->getDefaultValue($field);
			WC()->session->set('billing_' . $field, $customer_details[$field]);
		endforeach;
		return $customer_details;
	}

	/**
	* Set whether custom fields should be forced/disabled or not
	*/
	private function setForcedCustomFields($payment_method)
	{
		$fields = [
			'email' => false,
			'first_name' => false,
			'last_name' => false
		];
		if ( $payment_method !== 'invoice' ) return $fields;
		foreach ( $fields as $key => $value ) :
			$force = $this->values->forceCustomValue($key, $payment_method);
			$fields[$key] = $force;
		endforeach;
		return $fields;
	}

	private function respond($status, $message, $data = [])
	{
		return wp_send_json(['status' => $status, 'message' => $message, 'data' => $data]);
	}
}