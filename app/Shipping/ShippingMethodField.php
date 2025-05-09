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
		add_action('woocommerce_checkout_update_order_meta', [$this, 'orderMeta'], 30, 1 );
	}

	public function outputField()
	{
		if ( !$this->invoiceSelected() ) return;
		if ( !$this->settings->shippingSelectionRequired() ) return;
		$options = $this->settings->shippingOptions();
		$message = '<p class="invoice-payment-shipping-message">' . $this->settings->shippingMessage() . '</p>';

		$el = apply_filters('woocommerce_invoice_payment_shipping_select_element', 'tr');
		echo ( $el == 'tr' ) 
			? '<tr class="woocommerce-invoice-payment-shipping-selection"><td colspan="2">'
			: '<' . $el . ' class="woocommerce-invoice-payment-shipping-selection">';

		echo apply_filters('woocommerce_invoice_payment_shipping_choice_description', $message);
		
		if ( $options ) :
			$choices = [];
			foreach ( $options as $option ) :
				$choices[] = $option['name'];
			endforeach;
			$first_option = apply_filters('woocommerce_invoice_payment_shipping_select_label', __('Select a shipping method', WOOINVOICEPAYMENT_DOMAIN));
			array_unshift($choices, $first_option);
			woocommerce_form_field('woocommerce_invoice_payment_shipping_choice', [
				'type' => 'select',
				'required' => true,
				'options' => $choices
			]);
		endif;
		echo ( $el == 'tr' ) ? '</td></tr>' : '</' . $el . '>';
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
		}
	}

	/**
	* Save custom field to order metadata
	* @param int - order_id
	* @return void
	*/
	public function orderMeta($order_id)
	{
		if ( isset($_POST['woocommerce_invoice_payment_shipping_choice']) ){
			update_post_meta( 
				$order_id, 
				'woocommerce_invoice_payment_shipping_choice', 
				sanitize_text_field($_POST['woocommerce_invoice_payment_shipping_choice']) 
			);
		}
	}
}