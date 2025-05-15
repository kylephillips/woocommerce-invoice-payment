var WooInvoicePaymentAdmin = WooInvoicePaymentAdmin || {};

/**
* Settings
*/
WooInvoicePaymentAdmin.ShippingOptionsField = function()
{
	var self = this;
	var $ = jQuery;

	self.selectors = {
		wrapper : '.woocommerce-invoice-payment-repeater-wrapper',
		table : '.woocommerce-invoice-payment-repeater',
		row : '.woocommerce-invoice-payment-repeater-row',
		addNewButton : '[data-woocommerce-invoice-payment-repeater-add-new]',
		removeButton : '.woocommerce-invoice-payment-repeater-remove-row a'
	}

	self.bindEvents = function()
	{
		$(document).ready(function(){
			self.enableSortable();
		});
		$(document).on('click', self.selectors.addNewButton, function(e){
			e.preventDefault();
			self.addNewLocation();
		});
		$(document).on('click', self.selectors.removeButton, function(e){
			e.preventDefault();
			self.removeLocation($(this));
		});
	}

	/**
	* Add a new location row
	*/
	self.addNewLocation = function()
	{
		var fields = woocommerce_invoice_payment.shipping_options_fields;
		$(self.selectors.table).append(fields);
		setTimeout(function(){
			self.reindexInputs();
		}, 50);
	}

	/**
	* Remove a location row
	*/
	self.removeLocation = function(button)
	{
		var row = $(button).parents(self.selectors.row).remove();
		setTimeout(function(){
			self.reindexInputs();
		}, 50);
	}

	/**
	* Reindex all the inputs with the new order
	*/
	self.reindexInputs = function()
	{
		var rows = $(self.selectors.row);
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
		$('.woocommerce-invoice-payment-repeater').sortable('refresh');
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
    					self.reindexInputs();
    				}, 100
    			);
    		},
		});
	}

	return self.bindEvents();
}