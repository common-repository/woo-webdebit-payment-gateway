<?php 

class WebDebit_Payment_Gateway extends WC_Payment_Gateway{

    private $order_status;

	public function __construct(){
		$this->id = 'webdebit_payment';
		$this->method_title = __('WebDebit Payment','woocommerce-webdebit-payment-gateway');
		$this->title = __('WebDebit Payment','woocommerce-webdebit-payment-gateway');
		$this->has_fields = true;
		$this->init_form_fields();
		$this->init_settings();
		$this->enabled = $this->get_option('enabled');
		$this->title = $this->get_option('title');
		$this->description = $this->get_option('description');
		$this->hide_text_box = $this->get_option('hide_text_box');
		$this->text_box_required = $this->get_option('text_box_required');
		$this->order_status = $this->get_option('order_status');
		$this->endpoint = $this->get_option('webdebit_endpoint');
		$this->clientid = $this->get_option('webdebit_clientid');
		$this->apikey = $this->get_option('webdebit_apikey');
		$this->apitoken = "";
		$this->bank_verify = $this->get_option('bank_verify_required');

		add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));

	}

	public function init_form_fields(){
		$this->form_fields = array(
			'enabled' => array(
				'title' 		=> __( 'Enable/Disable', 'woocommerce-webdebit-payment-gateway' ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Enable WebDebit Payment', 'woocommerce-webdebit-payment-gateway' ),
				'default' 		=> 'yes'
			),

			'title' => array(
				'title' 		=> __( 'Method Title', 'woocommerce-webdebit-payment-gateway' ),
				'type' 			=> 'text',
				'description' 	=> __( 'This controls the title', 'woocommerce-webdebit-payment-gateway' ),
				'default'		=> __( 'WebDebit™ - Pay by Check', 'woocommerce-webdebit-payment-gateway' ),
				'desc_tip'		=> true,
			),
			'description' => array(
				'title' 		=> __( 'Customer Message', 'woocommerce-webdebit-payment-gateway' ),
				'type' 			=> 'textarea',
				'css' 			=> 'width:500px;',
				'default' 		=> 'Pay using a personal or business check through the WebDebit™ Gateway.',
				'description' 	=> __( 'The message which you want it to appear to the customer in the checkout page.', 'woocommerce-webdebit-payment-gateway' ),
			),
			'text_box_required' => array(
				'title' 		=> __( 'Make the text field required', 'woocommerce-webdebit-payment-gateway' ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Make the text field required', 'woocommerce-webdebit-payment-gateway' ),
				'default' 		=> 'no'
			),
			'hide_text_box' => array(
				'title' 		=> __( 'Hide The Customer Message', 'woocommerce-webdebit-payment-gateway' ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Hide', 'woocommerce-webdebit-payment-gateway' ),
				'default' 		=> 'yes',
				'description' 	=> __( 'If you do not need to show the text box for customers at all, enable this option.', 'woocommerce-webdebit-payment-gateway' ),
			),
			'order_status' => array(
				'title' 		=> __( 'Order Status After The Checkout', 'woocommerce-webdebit-payment-gateway' ),
				'type' 			=> 'select',
				'options' 		=> wc_get_order_statuses(),
				'default' 		=> 'wc-on-hold',
				'description' 	=> __( 'The default order status if this gateway used in payment.', 'woocommerce-webdebit-payment-gateway' ),
			),
			'complete_status' => array(
				'title' 		=> __( 'Order Status After Pay', 'woocommerce-webdebit-payment-gateway' ),
				'type' 			=> 'select',
				'options' 		=> wc_get_order_statuses(),
				'default' 		=> 'wc-completed',
				'description' 	=> __( 'The default order status after payment by this gateway.', 'woocommerce-webdebit-payment-gateway' ),
			),
			'webdebit_endpoint' => array(
				'title' 		=> __( 'WebDebit API Endpoint', 'woocommerce-webdebit-payment-gateway' ),
				'type' 			=> 'text',
				'default'		=> 'https://pay.webdebit.com/api/',
				'description' 	=> __( 'You can get the API Information from WebDebit API Setting.  <br><a href="http://webdebit.test/merchant/user_setting">http://webdebit.test/merchant/user_setting</a>', 'woocommerce-webdebit-payment-gateway' ),
				//'desc_tip'		=> true,
			),
			'webdebit_clientid' => array(
				'title' 		=> __( 'WebDebit Client ID', 'woocommerce-webdebit-payment-gateway' ),
				'type' 			=> 'text',
				//'description' 	=> __( 'This controls the title', 'woocommerce-webdebit-payment-gateway' ),
				//'desc_tip'		=> true,
			),
			'webdebit_apikey' => array(
				'title' 		=> __( 'WebDebit Secret Key', 'woocommerce-webdebit-payment-gateway' ),
				'type' 			=> 'text',
				//'description' 	=> __( 'This controls the title', 'woocommerce-webdebit-payment-gateway' ),
				//'desc_tip'		=> true,
			),
			'bank_verify_required' => array(
				'title' 		=> __( 'Make the bank verify required', 'woocommerce-webdebit-payment-gateway' ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Make the bank verify required', 'woocommerce-webdebit-payment-gateway' ),
				'default' 		=> 'no'
			),
			'disable_woocommerce_email' => array(
				'title' 		=> __( 'Disable WooCommerce Default Order Email', 'woocommerce-webdebit-payment-gateway' ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Disabled', 'woocommerce-webdebit-payment-gateway' ),
				'default' 		=> 'no',
				'description' 	=> __( 'If you do not need to send the WooCommerce order email for customers at all, enable this option.', 'woocommerce-webdebit-payment-gateway' ),
			),
			'enable_custom_thanks_url' => array(
				'title' 		=> __( 'Enable Custom Thanks URL', 'woocommerce-webdebit-payment-gateway' ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Enabled', 'woocommerce-webdebit-payment-gateway' ),
				'default' 		=> 'no',
				'description' 	=> __( 'If you want to set the thanks url to custom url for customers at all, enable this option.', 'woocommerce-webdebit-payment-gateway' ),
			),
			'custom_thanks_url' => array(
				'title' 		=> __( 'Custom Thanks URL', 'woocommerce-webdebit-payment-gateway' ),
				'type' 			=> 'text',
				'default'		=> 'https://',
				'description' 	=> __( '', 'woocommerce-webdebit-payment-gateway' ),
			),
		);
	}
	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_options() {
		?>
		<h3><?php _e( 'WebDebit Payment Settings', 'woocommerce-webdebit-payment-gateway' ); ?></h3>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<table class="form-table">
							<?php $this->generate_settings_html();?>
						</table><!--/.form-table-->
					</div>
					<div id="postbox-container-1" class="postbox-container">

                    </div>
				</div>
				<div class="clear"></div>
				<style type="text/css">
				.wpruby_button{
					background-color:#4CAF50 !important;
					border-color:#4CAF50 !important;
					color:#ffffff !important;
					width:100%;
					padding:5px !important;
					text-align:center;
					height:35px !important;
					font-size:12pt !important;
				}
				</style>
				<?php
	}

	public function validate_fields() {
		///////////////////////////////////////
		$endpoint = $this->endpoint;
		$clientid = $this->clientid;
		$apikey = $this->apikey;

		$data = array("client_id" => $clientid, "secret_key" => $apikey);
		$args = array(
			'body' => $data,
			'timeout' => '5',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'cookies' => array()
		);
		$response = wp_remote_post( $endpoint.'get_token', $args );

		if($response['body']!=""){
			$res = json_decode($response['body'],true);
			if(isset($res['result']) && $res['result']==1){
				$this->apitoken = $res['access_token'];
			}else{
				wc_add_notice( __('Access Token expired. Please try again.','woocommerce-custom-payment-gateway'), 'error');
				return false;
			}
		}else{
			wc_add_notice( __('Access Token expired. Please try again.','woocommerce-custom-payment-gateway'), 'error');
			return false;
		}

		////////////////////////////////////////////

	    if($this->text_box_required === 'no'){
	        return true;
        }

	    $textbox_value = (isset($_POST['webdebit_payment-admin-note']))? sanitize_text_field($_POST['webdebit_payment-admin-note']): '';
		if($textbox_value === ''){
			wc_add_notice( __('Please, complete the payment information.','woocommerce-custom-payment-gateway'), 'error');
			return false;
        }

		return true;
	}

	public function process_payment( $order_id ) {
		global $woocommerce;

		/*return array(
			'result' => 'success',
		);*/

		//$server_url = urlencode(get_site_url());
		$server_url = get_site_url();

		$order = new WC_Order( $order_id );
		// Mark as on-hold (we're awaiting the cheque)
		$order->update_status($this->order_status, __( 'Awaiting payment', 'woocommerce-webdebit-payment-gateway' ));
		// Reduce stock levels
		wc_reduce_stock_levels( $order_id );
		if(isset($_POST[ $this->id.'-admin-note']) && sanitize_text_field($_POST[ $this->id.'-admin-note'])!=''){
			$order->add_order_note(sanitize_text_field($_POST[ $this->id.'-admin-note']),1);
		}
		/////////////////////////

		if ( is_a( $order_id, 'WC_Order' ) ) {
			$order    = $order_id;
			$order_id = $order->get_id();
		} else {
			$order = wc_get_order( $order_id );
		}
		//error_log($order);
		$order_array = json_decode($order, true);
		$invoice_data = array(
			"date_issue"=> date('m/d/Y'),
			"date_due"=> date('m/d/Y', strtotime('+1 days')),
			"invoice_title"=> "Invoice",
			"invoice_description"=> $order_array['payment_method_title'],
			"invoice_number"=> $order_array['id'],
			"sub_total"=> "0.00",
			"tax_total"=> $order_array['total_tax'],
			"discount_total"=> $order_array['discount_total'],
			"shipping_total"=> $order_array['shipping_total'],
			"total"=> $order_array['total'],
			"invoice_note"=> $order_array['customer_note'],
			"company"=> $order_array['billing']['company'],
			"contact_name"=> $order_array['billing']['first_name']." ".$order_array['billing']['last_name'],
			"address"=> $order_array['billing']['address_1'],
			"address2"=> $order_array['billing']['address_2'],
			"city"=> $order_array['billing']['city'],
			"st"=> $order_array['billing']['state'],
			"zip"=> $order_array['billing']['postcode'],
			"phone"=> $order_array['billing']['phone'],
			"email"=> $order_array['billing']['email'],
			"date"=> "",
			"checknum"=> "",
			"amount"=> "0.00",
			"routing"=> "",
			"account"=> "",
			"bank_name"=> "",
			"bank_city"=> "",
			"bank_state"=> "",
			"status"=> "draft",
			"verification_required"=> $this->bank_verify=="yes"?"1":"0",
			"routingnum_verified"=> "0",
			"accountnum_verified"=> "0",
			"balance_verified"=> "0",
			"created_by" => "woocommerce",
			"ref_url" => $server_url,
			"ref_info" => $order_array['id'],
		);

		$invoice_item = array();
		$sub_total = 0;
		foreach ( $order->get_items() as $item ) {
			if ( ! $item->is_type( 'line_item' ) ) {
				continue;
			}
			//error_log($item);
			$item = json_decode($item, true);

			$price = $item['subtotal']/$item['quantity'];
			$tmp_array = array(
				"description" => $item['name'],
				"qty" => $item['quantity'],
				"price" => $price,
				"amount" => $item['total'],
				"tax" => $item['total_tax'],
				"discount" => "0.00"
			);
			$sub_total += $item['subtotal'];
			array_push($invoice_item, $tmp_array);
		}

		$token = $this->apitoken;

		$invoice_data['sub_total'] = $sub_total;
		$data = array(
			"import_data" => array(
				array("Invoice"=>$invoice_data, "InvoiceItem"=>$invoice_item),
			),
			"from" => "woocommerce"
		);

		$args = array(
			'body' => $data,
			'timeout' => '5',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array('X-Access-Token'=>$token),
			'cookies' => array()
		);
		$response = wp_remote_post( $this->endpoint.'import_invoices', $args );
		$result = json_decode($response['body'],true);

		// Remove cart
		$woocommerce->cart->empty_cart();

		$invoice_id = $result['imported_ids'][0];
		//error_log($invoice_id);
		$url = "https://pay.webdebit.com/invoice/invoice_preview/".$invoice_id;

		/*if ( wp_redirect( $url ) ) {
			exit;
		}*/

		return array(
			'result' => 'success',
			'redirect' => $url
		);

		// Return thankyou redirect
		/*return array(
			'result' => 'success',
			'redirect' => $this->get_return_url( $order )
		);	*/
	}

	public function payment_fields(){
	    ?>
		<fieldset>
			<p class="form-row form-row-wide">
                <label for="<?php echo $this->id; ?>-admin-note"><?php echo ($this->description); ?> <?php if($this->text_box_required=='yes'){echo '<span class="required">*</span>';}?></label>
                <?php if($this->hide_text_box !== 'yes'){ ?>
				    <textarea id="<?php echo $this->id; ?>-admin-note" class="input-text" type="text" name="<?php echo $this->id; ?>-admin-note"></textarea>
                <?php } ?>
			</p>						
			<div class="clear"></div>
		</fieldset>
		<?php
	}
}