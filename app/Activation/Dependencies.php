<?php 
namespace WooInvoicePayment\Activation;

use WooInvoicePayment\Repositories\UserRepository;
use WooInvoicePayment\Repositories\SettingsRepository;
use WooInvoicePayment\Repositories\ShippingRepository;

/**
* Load our front-end dependencies and localize
*/
class Dependencies 
{
	/**
	* User Repository
	* @var obj UserRepository
	*/ 
	private $user_repo;

	/**
	* Shipping Repository
	* @var obj ShippingRepository
	*/ 
	private $shipping_repo;

	/**
	* Setting Repository
	* @var obj SettingsRepository
	*/ 
	private $settings;

	public function __construct()
	{
		$this->user_repo = new UserRepository;
		$this->shipping_repo = new ShippingRepository;
		$this->settings = new SettingsRepository;
		add_action('wp_enqueue_scripts', [$this, 'scripts']);
		add_action('wp_enqueue_scripts', [$this, 'styles']);
		add_action('admin_enqueue_scripts', [$this, 'adminScripts']);
		add_action('admin_enqueue_scripts', [$this, 'adminStyles']);
	}

	/**
	* Enqueue and Localize Public Scripts
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
		$hide_billing = ( $this->user_repo->customerAllowed() && $this->settings->hideBillingInCheckout() ) ? '1' : '0';
		$localized_data = [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce('woocommerce_invoice_payment'),
			'hide_billing_fields' => $hide_billing,
			'hide_tax_subtotal' => $this->settings->hideTaxSubtotals()
		];
		wp_localize_script(
			'woocommerce-invoice-payment',
			'woocommerce_invoice_payment',
			$localized_data
		);
	}

	/**
	* Enqueue Public Styles
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
	* Enqueue and Localize Admin Scripts
	*/
	public function adminScripts()
	{
		wp_enqueue_script(
			'woocommerce-invoice-payment',
			WOOINVOICEPAYMENT_PLUGIN_DIRECTORY . '/assets/js/admin.scripts.min.js',
			['jquery-ui-sortable',],
			WOOINVOICEPAYMENT_VERSION,
			true
		);
		$localized_data = [
			'shipping_options_fields' => $this->shipping_repo->outputFields()
		];
		wp_localize_script(
			'woocommerce-invoice-payment',
			'woocommerce_invoice_payment',
			$localized_data
		);

		// Dev - Livereload
		if ( str_contains($_SERVER['SERVER_NAME'], '.test') ) :
			wp_enqueue_script(
				'livereload',
				get_template_directory_uri() . '/node_modules/livereload-js/dist/livereload.js?snipver=1',
				[],
				THEME_VERSION,
				true
			);
		endif;
	}

	/**
	* Enqueue Admin Styles
	*/
	public function adminStyles()
	{
		wp_enqueue_style(
			'woocommerce-invoice-payment-admin',
			WOOINVOICEPAYMENT_PLUGIN_DIRECTORY . '/assets/css/woocommerce-invoice-payment-admin.css',
			[],
			WOOINVOICEPAYMENT_VERSION
		);
	}
}