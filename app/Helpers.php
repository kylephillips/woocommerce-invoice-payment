<?php
namespace WooInvoicePayment;

class Helpers
{
	/**
	* View
	*/
	public static function view($file, $template_variables = null, $echo = true)
	{
		if ( $template_variables && is_array($template_variables) ){
			foreach ( $template_variables as $var => $value ){
				${$var} = $value;
			}
		}
		$view = dirname(__FILE__) . '/Views/' . $file . '.php';
		ob_start();
		include ( $view );
		$output = ob_get_contents();
		ob_end_clean();
		if ( !$echo ) return $output;
		echo $output;
	}
}