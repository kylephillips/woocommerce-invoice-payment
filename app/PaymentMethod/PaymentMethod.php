<?php
namespace WooInvoicePayment\PaymentMethod;

use WooInvoicePayment\Repositories\UserRepository;

/**
* Add Invoice payment method
*/
class PaymentMethod extends \WC_Payment_Gateway
{
	public $title;
	public $description;
	public $instructions;
	public $form_fields;
	public $user_repo;


	public function __construct()
	{
		$this->user_repo = new UserRepository;
		$this->id = 'invoice';
		$this->method_title = __('Pay with Invoice', WOOINVOICEPAYMENT_DOMAIN);
		$this->method_description = __( 'Allows customers to be invoiced for payment.', WOOINVOICEPAYMENT_DOMAIN );
		$this->has_fields = false;

		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title        = apply_filters('woocommerce_invoice_payment_title', $this->get_option( 'title' ));
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions' );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
		add_action( 'woocommerce_thankyou_cheque', [$this, 'thankyou_page']);

		// Customer Emails.
		add_action( 'woocommerce_email_before_order_table', [$this, 'email_instructions'], 10, 3 );
	}

	/**
	 * Initialize Gateway Settings Form Fields.
	 */
	public function init_form_fields() 
	{
		if ( !function_exists('get_editable_roles') ) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }
		$all_roles = get_editable_roles();
		$all_roles = get_editable_roles();
        $roles = [];
        foreach ( $all_roles as $name => $label ){
            $roles[$name] = $label['name'];
        }
		$this->form_fields = [
			'enabled'      => [
				'title'   => __( 'Enable/Disable', WOOINVOICEPAYMENT_DOMAIN ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable invoice payments', WOOINVOICEPAYMENT_DOMAIN ),
				'default' => 'no',
			],
			'title'        => [
				'title'       => __( 'Title', WOOINVOICEPAYMENT_DOMAIN ),
				'type'        => 'safe_text',
				'description' => __( 'This controls the title which the user sees during checkout.', WOOINVOICEPAYMENT_DOMAIN ),
				'default'     => _x( 'Invoice my account', 'Invoice my account', WOOINVOICEPAYMENT_DOMAIN ),
				'desc_tip'    => true,
			],
			'description'  => [
				'title'       => __( 'Description', WOOINVOICEPAYMENT_DOMAIN ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your checkout.', WOOINVOICEPAYMENT_DOMAIN),
				'default'     => __( 'Charge this order to my account and receive an invoice', WOOINVOICEPAYMENT_DOMAIN ),
				'desc_tip'    => true,
			],
			'instructions' => [
				'title'       => __( 'Instructions', WOOINVOICEPAYMENT_DOMAIN ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be added to the thank you page and emails.', WOOINVOICEPAYMENT_DOMAIN),
				'default'     => '',
				'desc_tip'    => true,
			],
			'override_billing_meta' => [
                'title'       => __( 'Override Billing Fields', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'repeater_override_billing_meta',
                'default'     => 'none',
                'description' => __( 'Override billing fields with custom user meta and disable editing if invoice payment is selected.', WOOINVOICEPAYMENT_DOMAIN ),
                'class' => 'invoice-payment-repeater',
                'desc_tip'    => false
            ],
			'disable_name_fields' => [
                'title'       => __( 'Disable Name Fields', WOOINVOICEPAYMENT_DOMAIN ),
                'label'       => __( 'Disable the first and last name fields when invoice payment is selected.', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'checkbox',
                'default'     => 'no'
            ],
            'first_name_field' => [
                'title'       => __( 'First Name Custom Field Meta Key', WOOINVOICEPAYMENT_DOMAIN ),
                'description'       => __( 'If terms is selected, and name fields are disabled, enter a custom field to pull the value from. Leave blank to use the stored WordPress user value. If the custom user meta is unavailable, the WordPress value will be used. If neither are available, the field will not be disabled.', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'text',
                'class'	=> 'disable-name-fields'
            ],
            'last_name_field' => [
                'title'       => __( 'Last Name Custom Field Meta Key', WOOINVOICEPAYMENT_DOMAIN ),
                'description'       => __( 'If terms is selected, and name fields are disabled, enter a custom field to pull the value from. Leave blank to use the stored WordPress user value. If the custom user meta is unavailable, the WordPress value will be used. If neither are available, the field will not be disabled.', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'text',
                'class'	=> 'disable-name-fields'
            ],
            'disable_email_field' => [
                'title'       => __( 'Disable Email Field', WOOINVOICEPAYMENT_DOMAIN ),
                'label'       => __( 'Disable the email field when invoice payment is selected.', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'checkbox',
                'default'     => 'no'
            ],
            'email_field' => [
                'title'       => __( 'Email Custom Field Meta Key', WOOINVOICEPAYMENT_DOMAIN ),
                'description'       => __( 'If terms is selected, and email is disabled, enter a custom field to pull the value from. Leave blank to use the stored WordPress user value. If the custom user meta is unavailable, the WordPress value will be used. If neither are available, the field will not be disabled.', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'text',
                'class'	=> 'disable-email-field'
            ],
			'hide_billing_checkout' => [
                'title'       => __( 'Hide Billing at Checkout', WOOINVOICEPAYMENT_DOMAIN ),
                'label'       => __( 'Hide the billing address at checkout and remove requirement.', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'checkbox',
                'default'     => 'no',
                'description' => __( 'If a billing address is not required when using an invoiced account, select this option to remove the fields from checkout.', WOOINVOICEPAYMENT_DOMAIN ),
            ],
            'disable_taxes' => [
                'title'       => __( 'Disable Taxes', WOOINVOICEPAYMENT_DOMAIN ),
                'label'       => __( 'Disable taxes on orders when paying by invoice.', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'checkbox',
                'default'     => 'no'
            ],
            'hide_tax_subtotal' => [
                'title'       => __( 'Hide taxes in subtotals', WOOINVOICEPAYMENT_DOMAIN ),
                'label'       => __( 'Remove the taxes line item in checkout subtotals.', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'checkbox',
                'class'	=> 'tax-disabled-totals-message',
                'default'     => 'no'
            ],
            'tax_disabled_totals_message' => [
                'title'       => __( 'Label for tax totals if disabled', WOOINVOICEPAYMENT_DOMAIN ),
                'description'       => __( 'Defaults to 0.00.', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'text',
                'class'	=> 'tax-disabled-totals-message'
            ],
            'disable_shipping' => [
                'title'       => __( 'Disable Shipping', WOOINVOICEPAYMENT_DOMAIN ),
                'label'       => __( 'Disable shipping methods on orders when paying by invoice.', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'checkbox',
                'default'     => 'no'
            ],
            'hide_shipping_subtotal' => [
                'title'       => __( 'Hide shipping in subtotals.', WOOINVOICEPAYMENT_DOMAIN ),
                'label'       => __( 'Remove the shipping line item in checkout subtotals.', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'checkbox',
                'class'	=> 'shipping-disabled-message',
                'default'     => 'no'
            ],
            'shipping_disabled_message' => [
                'title'       => __( 'Label for shipping if disabled', WOOINVOICEPAYMENT_DOMAIN ),
                'description'       => __( 'Displays where shipping options would normally show. Leave blank to hide.', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'text',
                'class'	=> 'shipping-disabled-message'
            ],
            'require_shipping_selection' => [
                'title'       => __( 'Require Shipping Selection', WOOINVOICEPAYMENT_DOMAIN ),
                'label'       => __( 'Require a shipping method selection when shipping is disabled.', WOOINVOICEPAYMENT_DOMAIN ),
                'description' => __( 'The label for disabled shipping will display above the field if this option is checked. This will force customer seleciton of a shipping method, but no payment or calculations up front.', WOOINVOICEPAYMENT_DOMAIN),
                'type'        => 'checkbox',
                'default'     => 'no',
                'class'	=> 'shipping-disabled-message'
            ],
            'shipping_options' => [
                'title'       => __( 'Shipping Options', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'repeater_shipping_options',
                'default'     => 'none',
                'description' => __( 'Add the shipping options available to select from.', WOOINVOICEPAYMENT_DOMAIN ),
                'class' => 'invoice-payment-repeater',
                'desc_tip'    => false
            ],
            'show_shipping_fields' => [
                'title'       => __( 'Show the shipping fields', WOOINVOICEPAYMENT_DOMAIN ),
                'label'       => __( 'Show the shipping fields, even if shipping is disabled.', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'checkbox',
                'default'     => 'no',
                'class'	=> 'shipping-disabled-message'
            ],
            'customer_roles_title' => [
                'title'       => __( 'Customer Roles', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'title',
                'description' => __( 'Limit the customers with the assigned role to have the option of paying by invoice', WOOINVOICEPAYMENT_DOMAIN ) ,
                'class'       => 'customer-shipping-section-title-section-title',
            ],
            'customer_roles' => [
                'title'       => __( 'Allowed Roles', WOOINVOICEPAYMENT_DOMAIN ),
                'type'        => 'multiselect',
                'default'     => 'none',
                'description' => __( 'If no role is selected, all will have this option.', WOOINVOICEPAYMENT_DOMAIN ),
                'class' => 'invoice-payment-select-woo',
                'desc_tip'    => true,
                'options'     => $roles,
            ],
		];
	}

	/**
	 * Output for the order received page.
	 */
	public function thankyou_page()
	{
		if ( $this->instructions ) echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @access public
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin Sent to admin.
	 * @param bool     $plain_text Email format: plain text or HTML.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false )
	{
		/**
		 * Filter the email instructions order status.
		 *
		 * @since 7.4
		 * @param string $terms The order status.
		 * @param object $order The order object.
		 */
		if ( $this->instructions && ! $sent_to_admin && 'cheque' === $order->get_payment_method() && $order->has_status( apply_filters( 'woocommerce_invoice_email_instructions_order_status', 'on-hold', $order ) ) ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
		}
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id )
	{
		$order = wc_get_order( $order_id );
		$order->payment_complete();
		WC()->cart->empty_cart();
		return [
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		];
	}

	public function is_available()
	{
		if ( !$this->user_repo->customerAllowed() ) return false;
		if ( WC()->cart && 0 < $this->get_order_total() && 0 < $this->max_amount && $this->max_amount < $this->get_order_total() ) {
			$is_available = false;
		}
		return true;
	}

	/**
    * Validate the billing meta overrides
    */
    public function validate_repeater_override_billing_meta_field($field_key, $data)
    {
        if ( !$data ) return;
        foreach ( $data as $key => $option ) :
        	if ( $data[$key]['name'] == '' ) continue;
        	$data[$key]['name'] = sanitize_text_field($data[$key]['name']);
        	$data[$key]['custom'] = ( isset($data[$key]['custom']) && $data[$key]['custom'] !== '' ) 
        		? sanitize_text_field($data[$key]['custom']) : null;
        	$data[$key]['disable'] = ( isset($data[$key]['disable']) && $data[$key]['disable'] == 'yes' ) ? 'yes' : null;
        endforeach;
        return $data;
    }

	/**
    * Validate the locations
    */
    public function validate_repeater_shipping_options_field($field_key, $data)
    {
        if ( !$data ) return;
        foreach ( $data as $key => $option ) :
        	if ( $data[$key]['name'] == '' ) continue;
        	$data[$key]['name'] = sanitize_text_field($data[$key]['name']);
        	$data[$key]['local_pickup'] = ( isset($data[$key]['local_pickup']) && $data[$key]['local_pickup'] == 'yes' ) ? 'yes' : null;
        endforeach;
        return $data;
    }
       
}