<?php
namespace WooInvoicePayment\OrderTotals;

use WooInvoicePayment\Repositories\SettingsRepository;

/**
* Update the order totals shipping and taxes based on the payment method and plugin settings
*/
class OrderTotals
{
	/**
	* Settings Repository
	*/
	private $repo;

	public function __construct()
	{
		$this->repo = new SettingsRepository;
		add_filter('woocommerce_get_order_item_totals', [$this, 'shipping'], 10, 3);
		add_filter('woocommerce_get_order_item_totals', [$this, 'taxes'], 10, 3);
	}

	public function shipping($total_rows, $order, $tax_display)
	{
		if ( $order->get_payment_method() !== 'invoice' ) return $total_rows;
		if ( !$this->repo->shippingDisabled() ) return $total_rows;
		$shipping_method = $order->get_meta('woocommerce_invoice_payment_shipping_choice');
		if ( !$shipping_method ) return $total_rows;
		if ( str_contains($shipping_method, '_local_pickup_expanded') ) :
			$total_rows['shipping_method'] = $this->localPickup($order, $shipping_method);
		else :
			$total_rows['shipping_method'] = [
				'label' => __('Selected Shipping Method:', WOOINVOICEPAYMENT_DOMAIN),
				'value' => $shipping_method
			];
		endif;
		return $total_rows;
	}

	private function localPickup($order, $method)
	{
		if ( !class_exists('\WooLocalPickupExpanded\Repositories\LocationRepository') ) return;
		$repo = new \WooLocalPickupExpanded\Repositories\LocationRepository;
		$location_key = $order->get_meta('local_pickup_expanded_selection');

		$label = str_replace('_local_pickup_expanded', '', $method);
		$value = $repo->locationFormatted(intval($location_key));
		return ['label' => $label, 'value' => $value];
	}

	public function taxes($total_rows, $order, $tax_display)
	{
		if ( $order->get_payment_method() !== 'invoice' ) return $total_rows;
		if ( !$this->repo->taxesDisabled() ) return $total_rows;
		$tax_message = apply_filters('woocommerce_invoice_payment_tax_totals_message', $this->repo->taxTotalMessage());
		if ( isset($total_rows['tax']) && !$tax_message ) unset($total_rows['tax']);
		$total_rows['tax'] = [
			'label' => $total_rows['tax']['label'],
			'value' => $tax_message
		];
		return $total_rows;
	}
}