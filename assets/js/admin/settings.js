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
			self.toggleTaxDependentFields();
			self.toggleShippingDependentFields();
		});
		$(document).on('change', 'input[name="woocommerce_invoice_disable_taxes"]', function(){
			self.toggleTaxDependentFields();
		});
		$(document).on('change', 'input[name="woocommerce_invoice_disable_shipping"]', function(){
			self.toggleShippingDependentFields();
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

	self.toggleShippingDependentFields = function()
	{
		var checked = $('input[name="woocommerce_invoice_disable_shipping"]').is(':checked');
		if ( checked ) {
			$('.shipping-disabled-message').parents('tr').show();
			return;
		}
		$('.shipping-disabled-message').parents('tr').hide();
	}

	return self.bindEvents();
}