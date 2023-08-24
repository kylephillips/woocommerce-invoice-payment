/**
* Hide/Show, Required/Unrequire billing fields for customers paying by invoice
* Updates session payment method on toggle so that we can remove billing address 
* requirements from terms payments without page reload
*/
var WooInvoicePayment = WooInvoicePayment || {};
WooInvoicePayment.Checkout = function()
{
	var self = this;
	var $ = jQuery;

	self.selectors = {
		paymentMethodRadio : 'input[name="payment_method"]',
		billingFields : '.woocommerce-billing-fields'
	}

	self.bindEvents = function()
	{
		$(document).ready(function(){
			self.toggleBillingTerms();
			setTimeout(function(){
				self.updateSessionPaymentMethod();
			}, 50);
		});
		$(document).on('payment_method_selected', function(){
			self.toggleBillingTerms();
			setTimeout(function(){
				self.updateSessionPaymentMethod();
			}, 50);
		});
		$(document).on('change', self.selectors.paymentMethodRadio, function(){
			self.toggleBillingTerms();
			setTimeout(function(){
				self.updateSessionPaymentMethod();
			}, 50);
		});
	}

	/**
	* Hide/Show the billing address for customers paying on terms accounts
	* (does not handle the required/validation aspect)
	*/
	self.toggleBillingTerms = function()
	{
		var payment_method = $(self.selectors.paymentMethodRadio + ':checked').val();
		if ( typeof payment_method === 'undefined' || payment_method === '' ) return;
		var billing_section = $('.woocommerce-billing-fields');
		if ( payment_method !== 'invoice' ) {
			$(billing_section).show();
			return;
		}
		$(billing_section).hide();
	}

	/**
	* Update the session payment method on change
	* This updates the required/validation for the selected payment method
	*/
	self.updateSessionPaymentMethod = function()
	{
		var payment_method = $(self.selectors.paymentMethodRadio + ':checked').val();
		if ( typeof payment_method === 'undefined' || payment_method === '' ) return;
		$.ajax({
			url: woocommerce_invoice_payment.ajaxurl,
			type: 'post',
			datatype: 'json',
			data: {
				action: 'update_session_payment_method',
				nonce: woocommerce_invoice_payment.nonce,
				payment_method: payment_method,
			},
			beforeSend: function(a,b){
				console.log(a);
				console.log(b);
			},
			success: function(d){
				console.log(d);
				var payment_method = $(self.selectors.paymentMethodRadio + ':checked').val();
				self.toggleBillingFields(d.data.hide_billing);
			},
			error: function(d){
				console.log(d);
			}
		});
	}

	/**
	* Toggle the billing fields if set to do so
	*/
	self.toggleBillingFields = function(hide)
	{
		if ( hide ){
			$(self.selectors.billingFields).hide();
			return;
		}
		$(self.selectors.billingFields).show();
	}

	return self.bindEvents();
}