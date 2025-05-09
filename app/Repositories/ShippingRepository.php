<?php
namespace WooInvoicePayment\Repositories;

class ShippingRepository
{
	public function outputSettings($options = [])
	{
		if ( empty($options) || $options[0] == 'none') return;
		$out = '<div class="woocommerce-invoice-payment-repeater">';
		foreach ( $options as $key => $option ) :
			$out .= $this->outputFields($option, $key);
		endforeach;
		$out .= '</div>';
		return $out;
	}

	/**
	* Output the shipping options fields, with saved values if available
	*/
	public function outputFields($option = null, $key = 0)
	{
		$out = '<div class="woocommerce-invoice-payment-repeater-row">';
		$out .= '<div class="field">';
		$out .= '<div class="order"></div>';
		$out .= '<input type="text" placeholder="' . __('Option Name', WOOINVOICEPAYMENT_DOMAIN) . '" required name="woocommerce_invoice_shipping_options[' . $key . '][name]"';
		if ( $option && isset($option['name']) ) $out .= ' value="' . $option['name'] . '"';
		$out .= '>';
		$out .= '<div class="woocommerce-invoice-payment-repeater-remove-row"><a href="#" class="button">' . __('&times;', WOOINVOICEPAYMENT_DOMAIN) . '</a></div>';
		$out .= '</div><!-- .field -->';
		$out .= '</div><!-- .woocommerce-invoice-payment-repeater-row -->';
		return apply_filters('woocommerce_invoice_payment_shipping_options_fields_output', $out, $option, $key);
	}
}