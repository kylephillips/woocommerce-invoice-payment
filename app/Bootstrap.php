<?php 
namespace WooInvoicePayment;

/**
* Primary Plugin class
*/
class Bootstrap 
{
	function __construct()
	{
		$this->defineGlobals();
		$this->pluginInit();
		add_action('init', [$this, 'addLocalization']);
		add_action('plugins_loaded', [$this, 'pluginsLoaded']);
		add_filter('woocommerce_payment_gateways', [$this, 'registerMethod']);
		add_filter('woocommerce_billing_fields' , [$this, 'removeRequiredBilling'], 20, 1 );
		add_filter('woocommerce_checkout_fields' , [$this, 'removeRequiredBilling']);
	}

	/**
	* Define Globals
	*/
	public function defineGlobals()
	{
		$plugin_directory = plugins_url() . '/' . basename(dirname(dirname(__FILE__)));
		define('WOOINVOICEPAYMENT_PLUGIN_DIRECTORY', $plugin_directory);
		define('WOOINVOICEPAYMENT_VERSION', '1.0.0');
		define('WOOINVOICEPAYMENT_DOMAIN', 'woocommerce-invoice-payment'); // Localization domain
	}

	/**
	* Register the payment methhod
	*/
	public function registerMethod($methods)
	{
		$methods[] = '\WooInvoicePayment\PaymentMethod\PaymentMethod'; 
		return $methods;
	}

	/**
	* Define the payment method
	*/
	public function pluginsLoaded()
	{
		if ( !class_exists('WC_Payment_Gateway') ) return;
		new PaymentMethod\PaymentMethod;
	}

	/**
	* General Theme Functions
	*/
	public function pluginInit()
	{
		new Activation\Dependencies;
		new Events\PublicEvents;
	}

	/**
	* Remove the required billing details if the customer has selected to pay by invoice
	*/
	public function removeRequiredBilling($fields)
	{
		if ( !is_checkout() ) return $address_fields;
		$payment_method = WC()->session->get('chosen_payment_method');
		if ( $payment_method !== 'invoice' ) return $fields;
		foreach ( $fields as $name => $field ){
			if ( str_contains('billing_', $name) ) unset($fields[$name]);
		}
		return $fields;
	}

	/**
	* Localization Domain
	*/
	public function addLocalization()
	{
		load_plugin_textdomain(
			WOOINVOICEPAYMENT_DOMAIN, 
			false, 
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages' );
	}
}