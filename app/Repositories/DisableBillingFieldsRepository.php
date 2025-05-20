<?php
namespace WooInvoicePayment\Repositories;

class DisableBillingFieldsRepository
{
	public function outputSettings($options = [])
	{
		$out = '<div class="woocommerce-invoice-payment-repeater disable-fields">';
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
		$checkout = new \WC_Checkout;
		$all_fields = $checkout->get_checkout_fields('billing');
		$fields = [];
		foreach ( $all_fields as $fkey => $field ) :
			$fields[$fkey] = $field['label'];
		endforeach;
		$fields['custom'] = __('Other (Enter User Meta Key)', WOOINVOICEPAYMENT_DOMAIN);
			
		$out = '<div class="woocommerce-invoice-payment-repeater-row">';
		$out .= '<div class="field">';
		$out .= '<div class="order"></div>';
		
		$out .= '<div class="col">';
		$out .= '<select name="woocommerce_invoice_disable_billing_fields[' . $key . '][name]" class="disable-billing-field-select">';
		foreach ( $fields as $meta_key => $label ) :
			$out .= '<option value="' . $meta_key . '"';
			if ( $option && isset($option['name']) && $option['name'] == $meta_key ) $out .= ' selected';
			$out .= '>' . $label . '</option>';
		endforeach;
		$out .= '</select>';
		$out .= '</div>';

		$out .= '<div class="col disable-billing-field-custom" style="display:none;">';
		$out .= '<input type="text" name="woocommerce_invoice_disable_billing_fields[' . $key . '][custom]"';
		if ( $option && isset($option['custom']) ) $out .= ' value="' . $option['custom'] . '"';
		$out .= ' placeholder="' . __('Field Key', WOOINVOICEPAYMENT_DOMAIN) . '">';
		$out .= '</div>';
		
		$out .= '<div class="woocommerce-invoice-payment-repeater-remove-row"><a href="#" class="button">' . __('&times;', WOOINVOICEPAYMENT_DOMAIN) . '</a></div>';
		$out .= '</div><!-- .field -->';
		$out .= '</div><!-- .woocommerce-invoice-payment-repeater-row -->';
		return apply_filters('woocommerce_invoice_payment_override_billing_fields_output', $out, $option, $key);
	}
}