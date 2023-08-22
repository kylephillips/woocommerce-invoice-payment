<?php
/*
Plugin Name: WooCommerce Invoice Payment
Plugin URI: https://github.com/kylephillips
Description: Enables a new payment method that allows customers to pay via terms/invoice towards their account.
Version: 1.0.0
Author: Kyle Phillips
Author URI: https://github.com/kylephillips
License: GPL
*/
$loader = require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/app/Bootstrap.php');

$woo_invoice_payment = new WooInvoicePayment\Bootstrap;