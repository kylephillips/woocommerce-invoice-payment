var WooInvoicePaymentAdmin = WooInvoicePaymentAdmin || {};

/**
* Settings
*/
WooInvoicePaymentAdmin.RepeaterField = function()
{
	var self = this;
	var $ = jQuery;

	self.selectors = {
		wrapper : '.woocommerce-invoice-payment-repeater-wrapper',
		table : '.woocommerce-invoice-payment-repeater',
		row : '.woocommerce-invoice-payment-repeater-row',
		addNewButton : 'data-woocommerce-invoice-payment-repeater-add-new',
		removeButton : '.woocommerce-invoice-payment-repeater-remove-row a'
	}

	self.bindEvents = function()
	{
		$(document).ready(function(){
			self.enableSortable();
		});
		$(document).on('click', '[' + self.selectors.addNewButton + ']', function(e){
			e.preventDefault();
			self.addNew($(this));
		});
		$(document).on('click', self.selectors.removeButton, function(e){
			e.preventDefault();
			self.removeRow($(this));
		});
	}

	/**
	* Add a new row
	*/
	self.addNew = function(button)
	{
		var field_type = $(button).attr(self.selectors.addNewButton);
		var fields = null;
		if ( field_type === 'shipping_option' ) fields = woocommerce_invoice_payment.shipping_options_fields;
		if ( field_type === 'override_billing_fields' ) fields = woocommerce_invoice_payment.override_billing_fields;
		var wrapper = $(button).siblings(self.selectors.wrapper);
		$(wrapper).find(self.selectors.table).append(fields);
		setTimeout(function(){
			self.reindexInputs(wrapper);
		}, 50);
	}

	/**
	* Remove a location row
	*/
	self.removeRow = function(button)
	{
		var row = $(button).parents(self.selectors.row).remove();
		var wrapper = $(button).parents(self.selectors.wrapper);
		setTimeout(function(){
			self.reindexInputs(wrapper);
		}, 50);
	}

	/**
	* Reindex all the inputs with the new order
	*/
	self.reindexInputs = function(wrapper)
	{
		var field_type = $(wrapper).siblings('[' + self.selectors.addNewButton + ']').attr(self.selectors.addNewButton);
		if ( field_type === 'shipping_option' ){
			self.reindexShippingOptions();
			return;
		}
		if ( field_type === 'override_billing_fields' ){
			self.reindexOverrideBillingFields();
			return;
		}
	}

	/**
	* Reindex Shipping Options Field
	*/
	self.reindexShippingOptions = function()
	{
		var wrapper = $('[' + self.selectors.addNewButton + '="shipping_option"]').siblings(self.selectors.wrapper);
		var rows = $(wrapper).find(self.selectors.row);
		var reg = /woocommerce_invoice_shipping_options\[\d\]/;
		$.each(rows, function(i){
			var newIndex = i;
			var inputs = $(this).find('input');
			var selects = $(this).find('select');
			$.each(inputs, function(){
				var oldName = $(this).attr('name');
				$(this).attr('name', oldName.replace(reg, 'woocommerce_invoice_shipping_options[' + newIndex + ']'));
			});
			$.each(selects, function(){
				var oldName = $(this).attr('name');
				$(this).attr('name', oldName.replace(reg, 'woocommerce_invoice_shipping_options[' + newIndex + ']'));
			});
		});
		$(wrapper).find('.woocommerce-invoice-payment-repeater').sortable('refresh');
	}

	/**
	* Reindex Override Billing Fields
	*/
	self.reindexOverrideBillingFields = function()
	{
		var wrapper = $('[' + self.selectors.addNewButton + '="override_billing_fields"]').siblings(self.selectors.wrapper);
		var rows = $(wrapper).find(self.selectors.row);
		var reg = /woocommerce_invoice_override_billing_meta\[\d\]/;
		$.each(rows, function(i){
			var newIndex = i;
			var inputs = $(this).find('input');
			var selects = $(this).find('select');
			$.each(inputs, function(){
				var oldName = $(this).attr('name');
				$(this).attr('name', oldName.replace(reg, 'woocommerce_invoice_override_billing_meta[' + newIndex + ']'));
			});
			$.each(selects, function(){
				var oldName = $(this).attr('name');
				$(this).attr('name', oldName.replace(reg, 'woocommerce_invoice_override_billing_meta[' + newIndex + ']'));
			});
		});
		$(wrapper).find('.woocommerce-invoice-payment-repeater').sortable('refresh');
	}

	self.enableSortable = function()
	{
		$('.woocommerce-invoice-payment-repeater').sortable({
			handle : '.order',
			start: function(e, ui){
        		ui.placeholder.height(ui.item.height());
    		},
    		stop: function(e, ui){
    			setTimeout(
    				function(){
    					var wrapper = $(ui.item).parents(self.selectors.wrapper);
    					self.reindexInputs(wrapper);
    				}, 100
    			);
    		},
		});
	}

	return self.bindEvents();
}