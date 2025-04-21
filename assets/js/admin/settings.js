var WooInvoicePaymentAdmin = WooInvoicePaymentAdmin || {};

/**
* Settings
*/
WooInvoicePaymentAdmin.Settings = function()
{
	var self = this;
	var $ = jQuery;

	self.bindEvents = function()
	{
		$(document).ready(function(){
			self.toggleTaxDependentFields()
		});
		$(document).on('change', 'input[name="woocommerce_invoice_disable_taxes"]', function(){
			self.toggleTaxDependentFields();
		});
	}

	self.toggleTaxDependentFields = function()
	{
		var checked = $('input[name="woocommerce_invoice_disable_taxes"]').is(':checked');
		if ( checked ) {
			$('.tax-disabled-totals-message').parents('tr').show();
			return;
		}
		$('.tax-disabled-totals-message').parents('tr').hide();
	}

	return self.bindEvents();
}