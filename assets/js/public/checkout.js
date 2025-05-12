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
	self.doing_ajax = false;

	self.selectors = {
		paymentMethodRadio : 'input[name="payment_method"]',
		shippingMethodSelect : 'select[name="woocommerce_invoice_payment_shipping_choice"]'
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
		$(document.body).on('payment_method_selected', function(){
			self.toggleBillingTerms();
			setTimeout(function(){
				self.updateSessionPaymentMethod();
			}, 50);
		});
		$(document.body).on('updated_checkout', function(){
			self.toggleLocalPickup();
			self.toggleTaxSubtotal();
			self.toggleBodyClass();
		});
		$(document).on('change', self.selectors.shippingMethodSelect, function(){
			setTimeout(function(){
				self.updateSessionPaymentMethod();
			}, 50);
		});
		$(document).on('change', self.selectors.paymentMethodRadio, function(){
			self.toggleBillingTerms();
			setTimeout(function(){
				self.updateSessionPaymentMethod();
				$(document.body).trigger('update_checkout');
			}, 50);
		});
		$(document.body).on('checkout_error', function(){
			self.validateShippingMethod();
		});
		$(document).on('change', self.selectors.shippingMethodSelect, function(){
			self.validateShippingMethod();
			self.toggleLocalPickup();
		});
	}

	/**
	* Toggle the taxes line item in the subtotals based on admin setting
	*/
	self.toggleTaxSubtotal = function()
	{
		var payment_method = $(self.selectors.paymentMethodRadio + ':checked').val();
		if ( typeof payment_method === 'undefined' || payment_method === '' ) return;
		if ( payment_method == 'invoice' && woocommerce_invoice_payment.hide_tax_subtotal ){
			$('.tax-total').hide();
			$('.tax-rate').hide();
			return;
		}
		$('.tax-total').show();
		$('.tax-rate').show();
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
	* Toggle the body class has-invoice-payment
	*/
	self.toggleBodyClass = function()
	{
		var payment_method = $(self.selectors.paymentMethodRadio + ':checked').val();
		if ( payment_method == 'invoice' ){
			$('body').addClass('has-invoice-payment-method');
			return;
		}
		$('body').removeClass('has-invoice-payment-method');
	}

	/**
	* Update the session payment method on change
	* This updates the required/validation for the selected payment method
	* The response includes the billing fields, since we do not require them for invoicing
	*/
	self.updateSessionPaymentMethod = function()
	{
		self.doing_ajax = true;
		var payment_method = $(self.selectors.paymentMethodRadio + ':checked').val();
		if ( typeof payment_method === 'undefined' || payment_method === '' ) return;
		var shipping_method = $(self.selectors.shippingMethodSelect).val();
		$.ajax({
			url: woocommerce_invoice_payment.ajaxurl,
			type: 'post',
			datatype: 'json',
			data: {
				action: 'update_session_payment_method',
				nonce: woocommerce_invoice_payment.nonce,
				payment_method: payment_method,
				shipping_method: shipping_method
			},
			success: function(d){
				self.doing_ajax = false;
				if ( woocommerce_invoice_payment.hide_billing_fields !== '1' ) return;
				var payment_method = $(self.selectors.paymentMethodRadio + ':checked').val();
				var billing_fields = ( d.data.new_payment_method === 'invoice' ) ? '' : d.data.billing_fields;
				self.populateBillingFields(d.data.billing_fields);
				self.toggleBillingFields(d.data.hide_billing);
				setTimeout(function(){
					$(document.body).trigger('country_to_state_changed'); // Reset SelectWoo
					$(document.body).trigger('update_checkout');
				}, 500);
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
			$('.woocommerce-checkout').addClass('billing-fields-hidden');
			$(billingFields).hide();
			$(shipToDifferent).hide();
			$(document).trigger('woocommerce-invoice-payment-billing-fields-toggled', [hide]);
			return;
		}
		$('.woocommerce-checkout').removeClass('billing-fields-hidden');
		$(billingFields).show();
		$(shipToDifferent).show();
		$(document).trigger('woocommerce-invoice-payment-billing-fields-toggled', [hide]);
	}

	/**
	* Validate the shipping method custom field
	* @see woocommerce/assets/js/frontend/checkout.js
	*/
	self.validateShippingMethod = function()
	{
		var field = $(self.selectors.shippingMethodSelect);
		var row = $(field).parents('tr');
		var parent = $(field).parents('.form-row');
		if ( $(field).val() == '' || $(field).val() == 0 ){
			$(row).addClass('has-error');
			$(parent).addClass('woocommerce-invalid-required-field');
			return;
		}
		$(row).removeClass('has-error');
		$(parent).removeClass('woocommerce-invalid-required-field');
	}

	/**
	* Is invoice payment selected?
	*/
	self.invoicePaymentSelected = function()
	{
		return ( $(self.selectors.paymentMethodRadio + ':checked').val() === 'invoice' ) ? true : false;
	}

	/**
	* Is a local pickup option selected?
	*/
	self.localPickupSelected = function()
	{
		var selected = $(self.selectors.shippingMethodSelect).val();
		return ( typeof selected !== 'undefined' && selected.includes('_local_pickup_expanded') ) ? true : false;
	}

	/**
	* Toggle local pickup options if available (invoice payment and local pickup both selected)
	* Integration with Local Pickup Expanded
	*/
	self.toggleLocalPickup = function()
	{
		if ( !self.invoicePaymentSelected() ) return;
		var local_pickup_fields = $('.woocommerce-local-pickup-expanded-checkout');
		if ( self.localPickupSelected() ){
			$(local_pickup_fields).show();
			return;
		}
		$(local_pickup_fields).hide();
	}

	return self.bindEvents();
}