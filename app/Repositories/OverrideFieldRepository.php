<?php
namespace WooInvoicePayment\Repositories;

class OverrideFieldRepository
{
	public function outputSettings($options = [])
	{
		$out = '<div class="woocommerce-invoice-payment-repeater override-fields">';
		if ( !empty($options) || $options[0] !== 'none') :
		foreach ( $options as $key => $option ) :
			$out .= $this->outputFields($option, $key);
		endforeach;
		endif;
		$out .= '</div>';
		return $out;
	}

	/**
	* Output the override fields, with saved values if available
	* 
	* @todo - output select with first, last, email, company, and other. If other, show text field for meta key. Update plugin to use values rather than other settings. Remove other settings
	*/
	public function outputFields($option = null, $key = 0)
	{
		$fields = [
			'first_name' => __('First Name', WOOINVOICEPAYMENT_DOMAIN),
			'last_name' => __('Last Name', WOOINVOICEPAYMENT_DOMAIN),
			'email' => __('Email', WOOINVOICEPAYMENT_DOMAIN),
			'company' => __('Company', WOOINVOICEPAYMENT_DOMAIN),
			'custom' => __('Other (Enter User Meta Key)', WOOINVOICEPAYMENT_DOMAIN)
		];
		$out = '<div class="woocommerce-invoice-payment-repeater-row">';
		$out .= '<div class="field">';
		$out .= '<div class="order"></div>';
		
		$out .= '<div class="col">';
		$out .= '<select name="woocommerce_invoice_override_billing_meta[' . $key . '][name]" class="override-billing-field-select">';
		foreach ( $fields as $meta_key => $label ) :
			$out .= '<option value="' . $meta_key . '"';
			if ( $option && isset($option['name']) && $option['name'] == $meta_key ) $out .= ' selected';
			$out .= '>' . $label . '</option>';
		endforeach;
		$out .= '</select>';
		$out .= '</div>';

		$out .= '<div class="col custom-key-field">';
		$out .= '<input type="text" name="woocommerce_invoice_override_billing_meta[' . $key . '][custom]"';
		if ( $option && isset($option['custom']) ) $out .= ' value="' . $option['custom'] . '"';
		$out .= ' placeholder="' . __('User Meta Key', WOOINVOICEPAYMENT_DOMAIN) . '">';
		$out .= '</div>';

		$out .= '<div class="col disable-field">';
		$out .= '<label><input type="checkbox" value="yes" name="woocommerce_invoice_override_billing_meta[' . $key . '][disable]" ';
		if ( $option && isset($option['disable']) && $option['disable'] == 'yes' ) $out .= 'checked';
		$out .= '>' . __('Disable in Checkout', WOOINVOICEPAYMENT_DOMAIN) . '</label>';
		$out .= '</div>';
		
		$out .= '<div class="woocommerce-invoice-payment-repeater-remove-row"><a href="#" class="button">' . __('&times;', WOOINVOICEPAYMENT_DOMAIN) . '</a></div>';
		$out .= '</div><!-- .field -->';
		$out .= '</div><!-- .woocommerce-invoice-payment-repeater-row -->';
		return apply_filters('woocommerce_invoice_payment_override_billing_fields_output', $out, $option, $key);
	}
}