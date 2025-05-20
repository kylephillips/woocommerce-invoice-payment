<?php
namespace WooInvoicePayment\Fields;

use WooInvoicePayment\Repositories\OverrideFieldRepository;

/**
* Define our custom Woo settings field type to save multiple locations
*/
class OverrideBillingFieldsRepeater
{
	private $repo;

	public function __construct()
	{
		$this->repo = new OverrideFieldRepository;
		add_filter( 'woocommerce_generate_repeater_override_billing_meta_html', [$this, 'field'], 10, 4 );
	}

	public function field($output, $key, $data, $method)
	{
		$field_key = $method->get_field_key( $key );
		$defaults  = [
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'repeater_override_billing_meta',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => [],
			'locations'           => [],
		];

		$data  = wp_parse_args( $data, $defaults );
		$value = (array) $method->get_option( $key, [] );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
			</th>
			<td class="forminp">
				<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
				<p class="description" style="margin-bottom:1rem;"><?php echo wp_kses_post( $data['description'] ); ?></p>
				<div class="woocommerce-invoice-payment-repeater-wrapper">
					<?php echo $this->repo->outputSettings($value); ?>
				</div>
				<a href="#" data-woocommerce-invoice-payment-repeater-add-new="override_billing_fields" class="button">
					<?php _e('Add Field', WOOINVOICEPAYMENT_DOMAIN); ?>
				</a>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}
}