<?php
namespace WooInvoicePayment\Repositories;

use WooInvoicePayment\Repositories\SettingsRepository;

class BillingFieldsRepository
{
	private $settings;

	public function __construct()
	{
		$this->settings = new SettingsRepository;
	}

	public function getHiddenFields()
	{
		$fields = $this->settings->disableBillingFields();
		$hidden = [];
		foreach ( $fields as $field ){
			$hidden[] = $field['name'];
		}
		return $hidden;
	}
}