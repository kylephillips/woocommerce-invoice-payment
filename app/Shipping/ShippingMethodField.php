<?php
namespace WooInvoicePayment\Shipping;

/**
* Handles the disabling of taxes if the plugin option is selected
*/
class ShippingMethodField extends ShippingBase
{
	public function __construct()
	{
		parent::__construct();
		add_action('woocommerce_review_order_before_order_total', [$this, 'outputField'], 10);
		add_action('woocommerce_after_checkout_validation', [$this, 'validate'], 10, 2);
	}

	public function outputField()
	{
		if ( !$this->invoiceSelected() ) return;
		if ( !$this->settings->shippingSelectionRequired() ) return;
		$methods = $this->shippingMethods();
		$message = '<p>' . $this->settings->shippingMessage() . '</p>';
		echo '<tr class="woocommerce-invoice-payment-shipping-selection"><td colspan="2">';
		echo apply_filters('woocommerce_invoice_payment_shipping_choice_description', $message);
		if ( $methods ) :
			$first_option = apply_filters('woocommerce_invoice_payment_shipping_select_label', __('Select a shipping method', WOOINVOICEPAYMENT_DOMAIN));
			array_unshift($methods, $first_option);
			woocommerce_form_field('woocommerce_invoice_payment_shipping_choice', [
				'type' => 'select',
				'required' => true,
				'options' => $methods
			]);
		endif;
		echo '</td></tr>';
	}

	/**
	* Require the custom field
	*/
	public function validate($data, $errors)
	{
		if ( !$this->invoiceSelected() ) return;
		if ( !$this->settings->shippingSelectionRequired() ) return;
		if ( is_cart() ) return;
		$error = apply_filters('woocommerce_invoice_payment_no_shipping_method_error', __('Please select a shipping method.' , WOOINVOICEPAYMENT_DOMAIN));
		if ( !isset( $_POST['woocommerce_invoice_payment_shipping_choice'] ) || $_POST['woocommerce_invoice_payment_shipping_choice'] == '' || $_POST['woocommerce_invoice_payment_shipping_choice'] == 0 ){
			$errors->add( 'woocommerce_invoice_payment_shipping_choice', $error );
			// return wc_add_notice($error, "error");
		}
	}

	/**
	* Get available shipping methods/rates
	*/
	private function shippingMethods()
	{
		$methods = [];
		$packages = \WC()->shipping()->get_packages();
		foreach ( $packages as $i => $package ) : foreach ( $package['rates'] as $key => $rate ) :
			if ( !array_key_exists($key, $methods) ) $methods[$rate->label] = $rate->label;
		endforeach; endforeach;
		return apply_filters('woocommerce_invoice_payment_shipping_methods', $methods);
	}

	/**
	* Get all shipping zones
	*/
	private function shippingZones()
	{
		return apply_filters('woocommerce_invoice_payment_shipping_zones', \WC_Shipping_Zones::get_zones());
	}
}