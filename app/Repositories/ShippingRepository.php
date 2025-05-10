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
		$local_pickup = ( class_exists('\WooLocalPickupExpanded\Bootstrap') ) ? true : false;
		$local_pickup_checked = ( $local_pickup && isset($option['local_pickup']) && $option['local_pickup'] == 'yes' ) ? true : false;
		$out = '<div class="woocommerce-invoice-payment-repeater-row';
		if ( $local_pickup ) $out .= ' has-local-pickup';
		$out .= '">';
		$out .= '<div class="field">';
		$out .= '<div class="order"></div>';
		$out .= '<input type="text" placeholder="' . __('Option Name', WOOINVOICEPAYMENT_DOMAIN) . '" required name="woocommerce_invoice_shipping_options[' . $key . '][name]"';
		if ( $option && isset($option['name']) ) $out .= ' value="' . $option['name'] . '"';
		$out .= '>';

		if ( $local_pickup ) :
		$out .= '<label><input type="checkbox" value="yes" name="woocommerce_invoice_shipping_options[' . $key . '][local_pickup]" ';
		if ( $local_pickup_checked ) $out .= 'checked';
		$out .= '>' . __('Local Pickup', WOOINVOICEPAYMENT_DOMAIN) . '</label>';
		endif;

		$out .= '<div class="woocommerce-invoice-payment-repeater-remove-row"><a href="#" class="button">' . __('&times;', WOOINVOICEPAYMENT_DOMAIN) . '</a></div>';
		$out .= '</div><!-- .field -->';
		$out .= '</div><!-- .woocommerce-invoice-payment-repeater-row -->';
		return apply_filters('woocommerce_invoice_payment_shipping_options_fields_output', $out, $option, $key);
	}
}