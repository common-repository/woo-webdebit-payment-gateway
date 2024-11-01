<?php
/* @wordpress-plugin
 * Plugin Name:       WebDebit Pay By Check Gateway
 * Plugin URI:        https://webdebit.com/
 * Description:       Design your own payment gateway by drag and drop.
 * Version:           1.0.5
 * WC requires at least: 3.0
 * WC tested up to: 3.8
 * Author:            Boston Commerce, Inc.
 * Author URI:        https://bostoncommerce.com
 * Text Domain:       woocommerce-webdebit-payment-gateway
 * Domain Path: /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
if(webdebit_custom_payment_is_woocommerce_active()){
	add_filter('woocommerce_payment_gateways', 'add_webdebit_payment_gateway');
	function add_webdebit_payment_gateway( $gateways ){
		$gateways[] = 'WebDebit_Payment_Gateway';
		return $gateways; 
	}

	add_action('plugins_loaded', 'init_webdebit_payment_gateway');
	function init_webdebit_payment_gateway(){
		require 'class-woocommerce-webdebit-payment-gateway.php';
	}

	add_action( 'plugins_loaded', 'webdebit_payment_load_plugin_textdomain' );
	function webdebit_payment_load_plugin_textdomain() {
	  load_plugin_textdomain( 'woocommerce-webdebit-payment-gateway', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	add_action( 'woocommerce_email', 'unhook_those_pesky_emails');
	function unhook_those_pesky_emails( $email_class ) {
		/**
		 * Hooks for sending emails during store events
		 **/

		$disable_woocommerce_email = 'no';
		$webdebit_payment_settings = (array) get_option('woocommerce_webdebit_payment_settings', array());
		if(isset($webdebit_payment_settings['disable_woocommerce_email'])){
			$disable_woocommerce_email = $webdebit_payment_settings['disable_woocommerce_email'];
		}

		if($disable_woocommerce_email == 'yes'){
			//remove_action( 'woocommerce_low_stock_notification', array( $email_class, 'low_stock' ) );
			//remove_action( 'woocommerce_no_stock_notification', array( $email_class, 'no_stock' ) );
			//remove_action( 'woocommerce_product_on_backorder_notification', array( $email_class, 'backorder' ) );

			// New order emails
			remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_pending_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );

			// Processing order emails
			remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_On_Hold_Order'], 'trigger' ) );

			// Completed order emails
			remove_action( 'woocommerce_order_status_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ) );

			// Note emails
			remove_action( 'woocommerce_new_customer_note_notification', array( $email_class->emails['WC_Email_Customer_Note'], 'trigger' ) );
		}else{
			//add_action( 'woocommerce_low_stock_notification', array( $email_class, 'low_stock' ) );
			//add_action( 'woocommerce_no_stock_notification', array( $email_class, 'no_stock' ) );
			//add_action( 'woocommerce_product_on_backorder_notification', array( $email_class, 'backorder' ) );

			// New order emails
			add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			add_action( 'woocommerce_order_status_pending_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			add_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			add_action( 'woocommerce_order_status_failed_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			add_action( 'woocommerce_order_status_failed_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			add_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );

			// Processing order emails
			add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
			add_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_On_Hold_Order'], 'trigger' ) );

			// Completed order emails
			add_action( 'woocommerce_order_status_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ) );

			// Note emails
			add_action( 'woocommerce_new_customer_note_notification', array( $email_class->emails['WC_Email_Customer_Note'], 'trigger' ) );
		}
	}

	add_filter( 'query_vars', 'webdebit_query_vars');
	function webdebit_query_vars( $query_vars ) {
		$query_vars[] = 'webdebit_order_id';
		$query_vars[] = 'webdebit_valid';
		return $query_vars;
	}

	add_action( 'parse_request', 'webdebit_parse_request');
	function webdebit_parse_request( &$wp ) {
		if ( array_key_exists( 'webdebit_order_id', $wp->query_vars ) && array_key_exists( 'webdebit_valid', $wp->query_vars ) ) {
			$order_id = $wp->query_vars['webdebit_order_id'];
			$valid = $wp->query_vars['webdebit_valid'];
			$f_valid = md5(strval($order_id*3+7))==$valid?1:0;

			$webdebit_payment_settings = (array) get_option('woocommerce_webdebit_payment_settings', array());

			$enable_custom_thanks_url = isset($webdebit_payment_settings['enable_custom_thanks_url'])?$webdebit_payment_settings['enable_custom_thanks_url']:'no';
			$custom_thanks_url = isset($webdebit_payment_settings['custom_thanks_url'])?$webdebit_payment_settings['custom_thanks_url']:'';
			//error_log("---------------------".$enable_custom_thanks_url.$custom_thanks_url);

			if(isset($webdebit_payment_settings['complete_status']) && $f_valid == 1){
				$order_status = $webdebit_payment_settings['complete_status'];
				$order = new WC_Order( $order_id );
				$order->update_status($order_status, __( 'Completed payment', 'woocommerce-webdebit-payment-gateway' ));

				if($enable_custom_thanks_url=='yes' && $custom_thanks_url!='' && $custom_thanks_url!='https://'){
					wp_redirect( $custom_thanks_url );
					exit;
				}else{
					include 'webdebit-thanks.php';
					exit();
				}

			}
		}
	}

}


/**
 * @return bool
 */
function webdebit_custom_payment_is_woocommerce_active()
{
	$active_plugins = (array) get_option('active_plugins', array());

	if (is_multisite()) {
		$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	}

	return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
}