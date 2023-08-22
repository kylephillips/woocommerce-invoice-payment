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

	public function init()
	{
		
	}

	/**
	* General Theme Functions
	*/
	public function pluginInit()
	{
		new Activation\Dependencies;
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