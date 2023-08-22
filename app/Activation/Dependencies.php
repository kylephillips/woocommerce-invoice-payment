<?php 
namespace WooInvoicePayment\Activation;

class Dependencies 
{
	public function __construct()
	{
		add_action( 'wp_enqueue_scripts', [$this, 'scripts']);
		add_action( 'wp_enqueue_scripts', [$this, 'styles']);
		add_action( 'admin_enqueue_scripts', [$this, 'adminScripts']);
		add_action( 'admin_enqueue_scripts', [$this, 'adminStyles']);
	}

	/**
	* Public Scripts
	*/
	public function scripts()
	{
		wp_enqueue_script(
			'woocommerce-invoice-payment',
			WOOINVOICEPAYMENT_PLUGIN_DIRECTORY . '/assets/js/scripts.min.js',
			[],
			WOOINVOICEPAYMENT_VERSION,
			true
		);
		$localized_data = [
		];
		wp_localize_script(
			'woocommerce-invoice-payment',
			'woocommerce_invoice_payment',
			$localized_data
		);
	}

	/**
	* Public Styles
	*/
	public function styles()
	{
		wp_enqueue_style(
			'woocommerce-invoice-payment',
			WOOINVOICEPAYMENT_PLUGIN_DIRECTORY . '/assets/css/woocommerce-invoice-payment.css',
			[],
			WOOINVOICEPAYMENT_VERSION
		);
	}

	/**
	* Admin Scripts
	*/
	public function adminScripts()
	{
		wp_enqueue_script(
			'woocommerce-invoice-payment',
			WOOINVOICEPAYMENT_PLUGIN_DIRECTORY . '/assets/js/admin.scripts.min.js',
			[],
			WOOINVOICEPAYMENT_VERSION,
			true
		);
		$localized_data = [
		];
		wp_localize_script(
			'woocommerce-invoice-payment',
			'woocommerce_invoice_payment',
			$localized_data
		);
	}

	/**
	* Admin Styles
	*/
	public function adminStyles()
	{
		wp_enqueue_style(
			'woocommerce-customer-shipping-admin',
			WOOINVOICEPAYMENT_PLUGIN_DIRECTORY . '/assets/css/woocommerce-invoice-payment-admin.css',
			[],
			WOOINVOICEPAYMENT_VERSION
		);
	}
}