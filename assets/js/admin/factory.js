var WooInvoicePaymentAdmin = WooInvoicePaymentAdmin || {};

jQuery(document).ready(function(){
	new WooInvoicePaymentAdmin.Factory;
});

/**
* Primary factory class
*/
WooInvoicePaymentAdmin.Factory = function()
{
	var self = this;
	var $ = jQuery;

	self.build = function()
	{
		new WooInvoicePaymentAdmin.Settings;
		self.initializeSelectWoo();
	};

	self.initializeSelectWoo = function()
	{
		if ( $('.invoice-payment-select-woo').length < 1 ) return;
		$('.invoice-payment-select-woo').select2({
			multiple: true
		});
	}

	return self.build();
}