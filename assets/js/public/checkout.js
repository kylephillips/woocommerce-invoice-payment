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
		paymentMethodRadio : 'input[name="payment_method"]'
	}

	self.bindEvents = function()
	{
		$(document).ready(function(){
			if ( woocommerce_invoice_payment.hide_billing_fields !== '1' ) return;
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
		if ( woocommerce_invoice_payment.hide_billing_fields !== '1' ){
			self.toggleBillingFields(false);
			return;
		}
		var payment_method = $(self.selectors.paymentMethodRadio + ':checked').val();
		if ( typeof payment_method === 'undefined' || payment_method === '' ) return;
		if ( payment_method !== 'invoice' ) {
			self.toggleBillingFields(false);
			return;
		}
		self.toggleBillingFields(true);
	}

	/**
	* Update the session payment method on change
	* This updates the required/validation for the selected payment method
	* The response includes the billing fields, since we do not require them for invoicing
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
			success: function(d){
				if ( woocommerce_invoice_payment.hide_billing_fields !== '1' ) return;
				var payment_method = $(self.selectors.paymentMethodRadio + ':checked').val();
				var billing_fields = ( d.data.new_payment_method === 'invoice' ) ? '' : d.data.billing_fields;
				self.populateBillingFields(d.data.billing_fields);
				self.toggleBillingFields(d.data.hide_billing);
				setTimeout(function(){
					$(document.body).trigger('country_to_state_changed'); // Reset SelectWoo
				}, 50);
			},
			error: function(d){
				console.log(d);
			}
		});
	}

	/**
	* Populate billing fields if not set 
	* (If invoice method is chosen on page load, and another method that requires
	* billing fields is chosen after page load, the fields are not shown)
	*/
	self.populateBillingFields = function(fields_html)
	{
		$('.woocommerce-billing-fields__field-wrapper').html(fields_html);
	}

	/**
	* Toggle the billing fields if set to do so
	*/
	self.toggleBillingFields = function(hide)
	{
		var billingFields = $('.woocommerce-billing-fields').find('.form-row, .address-book-selection').not('#billing_first_name_field').not('#billing_last_name_field').not('#billing_email_field');
		var shipToDifferent = $('#ship-to-different-address');
		if ( hide ){
			$(billingFields).hide();
			$(shipToDifferent).hide();
			return;
		}
		$(billingFields).show();
		$(shipToDifferent).show();
	}

	return self.bindEvents();
}