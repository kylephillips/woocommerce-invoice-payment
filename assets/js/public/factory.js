var WooInvoicePayment = WooInvoicePayment || {};

jQuery(document).ready(function(){
	new WooInvoicePayment.Factory;
});

/**
* Primary factory class
*/
WooInvoicePayment.Factory = function()
{
	var self = this;
	var $ = jQuery;

	self.build = function()
	{
		new WooInvoicePayment.Checkout
	}

	return self.build();
}