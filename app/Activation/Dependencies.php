<?php 
namespace WooInvoicePayment\Activation;

use WooInvoicePayment\Repositories\UserRepository;
use WooInvoicePayment\Repositories\SettingsRepository;

class Dependencies 
{
	private $user_repo;
	private $settings;

	public function __construct()
	{
		$this->user_repo = new UserRepository;
		$this->settings = new SettingsRepository;
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
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce('woocommerce_invoice_payment')
		];
		$localized_data['hide_checkout_billing'] = ( $this->user_repo->customerAllowed() && $this->settings->hideBillingInCheckout() && WC()->session->get('chosen_payment_method') == 'invoice' ) ? true : false;
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
		// var_dump(WC()->session->get('chosen_payment_method'));
		if ( $this->user_repo->customerAllowed() && $this->settings->hideBillingInCheckout() && WC()->session->get('chosen_payment_method') == 'invoice' ) :
			echo '<style>.woocommerce-checkout .woocommerce-billing-fields {display:none; !important}</style>';
		endif;
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