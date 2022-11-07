
<?php
/**
 * Plugin Name: HBLPAY Payment Gateway for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/hblpay-payment-gateway-for-woocommerce
 * Description: Collect payment from multiple payment gateway on your store.
 * Author: HBL
 * Author URI: https://hbl.com
 * Version: 2.0.1
 * Requires at least: 5.8.0
 * Requires PHP: 7.4.0
 * Tested up to: 6.0.1
 * WC requires at least: 5.1
 * WC tested up to: 6.1.1
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
ob_start();
					//----------------**setting version requirements** defing minimum version for WC---------------------------//
define( 'WC_HBLPAY_MIN_WC_VER', '5.1' );
//--------function declared here and called in main init class----------------//
function woocommerce_hblpay_missing_wc_notice() {
	/* translators: 1. URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'HBLPAY requires WooCommerce to be installed and active. You can download %s here.', 'hblpay-payment-gateway-for-woocommerce' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}
function woocommerce_hblpay_wc_not_supported() {
	/* translators: $1. Minimum WooCommerce version. $2. Current WooCommerce version. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'HBLPAY requires WooCommerce %1$s or greater to be installed and active. WooCommerce %2$s is no longer supported.', 'hblpay-payment-gateway-for-woocommerce' ), WC_HBLPAY_MIN_WC_VER, WC_VERSION ) . '</strong></p></div>';
}
						//---------------**end**---------------------------------------//

 //This action hook registers our PHP class as a WooCommerce payment gateway

//Receiving Data On Return URL Start
add_action( 'rest_api_init', 'HBLPAYPGW_mark_status_completed');
function HBLPAYPGW_mark_status_completed()
{
	register_rest_route('hblpay_response/v1','checkout',array(
		'methods' => WP_REST_SERVER::READABLE,
		'callback' => 'hblpay_response_result',
		'permission_callback' => '__return_true'
	));
}

//-----------------hook for page creation at the time of plugin activation for cancel order---------------MZ
 register_activation_hook(__FILE__, 'HBLPAYPGW_add_page_cancel_order');
 //function for page creation-------------------------MZ
function HBLPAYPGW_add_page_cancel_order()
{
   $post_details = array(
  'post_title'    => 'Order Cancelled',
  'post_content'  => '',
  'post_status'   => 'publish',
  'post_author'   => 1,
  'post_type' => 'page'
   );
   wp_insert_post( $post_details );
}
//-----------------on deactivation of  plugin delete a page----------------//
function HBLPAYPGW_delete_page_cancel_order() {

    $page = get_page_by_path( 'order-cancelled' );
    wp_delete_post($page->ID);

}
register_deactivation_hook( __FILE__, 'HBLPAYPGW_delete_page_cancel_order' );
//---------------end of page hook--------------------------//

//-----------------------------hook for table creation-----------------------------------//
register_activation_hook(__FILE__,'HBLPAYPGW_table_creation');

//creation of table at the time of plugin activation
function HBLPAYPGW_table_creation()
{
	global $wpdb;
	 $charset_collate = $wpdb->get_charset_collate();
	 require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	 //* Create the teams table
	 $table_name = $wpdb->prefix . 'hblpay_tokenization';
	 $sql = "CREATE TABLE $table_name (
	 id INTEGER NOT NULL AUTO_INCREMENT,
	 user_id INTEGER NOT NULL,
	 email TEXT NOT NULL,
	 token TEXT NOT NULL,
	 mask TEXT NOT NULL,
	 payment_type TEXT NOT NULL,
	 last_trans_date DATE NOT NULL DEFAULT CURRENT_DATE,
 	 token_expiry DATE NOT NULL DEFAULT CURRENT_DATE,
 	 created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 	 updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	 created_by INTEGER NOT NULL,
	 updated_by INTEGER NOT NULL,
	 is_enabled INTEGER NOT NULL,
	 is_deleted INTEGER NOT NULL,
	 PRIMARY KEY (id)
	 ) $charset_collate;";
	 dbDelta( $sql );
}
//end of creation
//drop table atm of plugin activation
 function HBLPAYPGW_table_drop() {
			 global $wpdb;
			 $table_name = $wpdb->prefix . 'hblpay_tokenization';
			 $sql = "DROP TABLE IF EXISTS $table_name";
			 $wpdb->query($sql);
	 }
//register_deactivation_hook( __FILE__, 'table_drop');
//--------------------------------end of table hook-------------------------------------//

//hook for page card management creation at the time of plugin activation---------------MZ
register_activation_hook(__FILE__, 'HBLPAYPGW_add_page_user_management');
//function for page creation-------------------------MZ
function HBLPAYPGW_add_page_user_management()
{
	//$cards=get_card_token(get_current_user_id());
   $post_details = array(
  'post_title'    => 'Card Management',
  'post_content'  => '',
  'post_status'   => 'publish',
  'post_author'   => 1,
  'post_type' => 'page'
   );
   wp_insert_post( $post_details );
}
//-----------------on deactivation of  plugin delete a page----------------//
function HBLPAYPGW_delete_page_card_management() {

    $page = get_page_by_path( 'card-management' );
    wp_delete_post($page->ID);

}
register_deactivation_hook( __FILE__, 'HBLPAYPGW_delete_page_card_management' );
//-------------------end --------------------------------------------//
 function HBLPAYPGW_wp_enqueue_scripts_callback(){
	  //wp_enqueue_style('mycustom-style', '/wp-content/plugins/hblpay-gateway/css/select2.min.css');
	 // wp_enqueue_script('mycustom-script', '/wp-content/plugins/hblpay-gateway/js/select2.min.js', array('jquery'));
     wp_enqueue_script('mycustom', plugins_url( "/js/mycustom.js", __FILE__ ),array('jquery'));
}
//provoke auto-updates
add_filter( 'auto_update_plugin', '__return_true' );

function hblpay_response_result()
{

	try{

	if(isset($_GET['data']))
	{

		$redirection_url = '';
		$admin_settings = WC()->payment_gateways->payment_gateways()['hblpay']->settings;
		$dec_key = $admin_settings['private_key'];
		$dec_key = str_replace("-----BEGIN RSA PRIVATE KEY-----","-----BEGIN RSA PRIVATE KEY-----\n",$dec_key);
		$dec_key = str_replace("-----END RSA PRIVATE KEY-----","\n-----END RSA PRIVATE KEY-----",$dec_key);


		$encryptedData = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
		$encryptedData = str_replace("data=", "", $encryptedData);
		$url_params = decryptData($encryptedData, $dec_key);

		if($url_params === '')
		{
			$redirection_url = '/checkout/order-cancelled?data='.encodeString('Your Order is cancelled. Please Try again later.');
			// wp_safe_redirect( $admin_settings['return_url'] . $redirection_url, 301 );
			wp_safe_redirect( home_url() . $redirection_url, 301 );
			exit;
		}

		parse_str($url_params,$paramArray);
	//	error_log("test\n" . json_encode($paramArray, JSON_PRETTY_PRINT));


		$order_message = '';
		$order = wc_get_order($paramArray['ORDER_REF_NUMBER']);

		$user_id=$order->get_customer_id();
		$order_data = $order->get_data();
			// error_log('full param  '. var_dump($paramArray));
		 // error_log('USER_ID:::'.$user_id.'::::token::::'.$paramArray['CS_RESP_TOKEN'].':::: mask::::'.$paramArray['CARD_NUM_MASKED']);

		$orstat = $order_data['status'];

		if($orstat != 'pending')
		{
			$paramArray['RESPONSE_MESSAGE'] = 'You are not authorized to process this order';
			$redirection_url = '/checkout/order-cancelled?cusdata='.encodeString('RESPONSE_MESSAGE='.$paramArray['RESPONSE_MESSAGE'] . '&ORDER_REF_NUMBER='. $paramArray['ORDER_REF_NUMBER'] .'&RESPONSE_CODE='.$paramArray['RESPONSE_CODE']);

			wp_safe_redirect( home_url() . $redirection_url, 301 );
			exit;
		}
			error_log(print_r($paramArray,1));
		if($paramArray['RESPONSE_CODE'] == '100' || $paramArray['RESPONSE_CODE'] == '0' || $paramArray['RESPONSE_CODE'] == '300' || $paramArray['RESPONSE_CODE'] == '00')
        {

            if($paramArray['ORDER_REF_NUMBER'])
            {
                HBLPAYPGW_mark_order_completed($paramArray['ORDER_REF_NUMBER'], $paramArray['RESPONSE_MESSAGE']);
								error_log('::::::::::::::::UPDATING ORDER STATUS TO COMPLETED/Processing::::::::::::::::::ORDERID:::::'.$paramArray['ORDER_REF_NUMBER']);
            }

            $redirection_url = '/checkout/order-received?cusdata='.encodeString('RESPONSE_MESSAGE='.$paramArray['RESPONSE_MESSAGE'] . '&ORDER_REF_NUMBER='. $paramArray['ORDER_REF_NUMBER'] .'&RESPONSE_CODE='.$paramArray['RESPONSE_CODE']);

            //check if user wants to save card or not

		$save_card_or_not = get_post_meta( $paramArray['ORDER_REF_NUMBER'], 'save_card', true );

if($user_id != null){
		if($save_card_or_not == '1'){
					error_log('::::::::::::::::Saving Card information:::::::ORDERID:::::'.$paramArray['ORDER_REF_NUMBER']);
					//if there is token in return url--add or update card request-----------MZ

					if($paramArray['CS_RESP_TOKEN'] && $paramArray['CARD_NUM_MASKED'] != NULL ){

					//check if card exists already then update it (calloffunction)-----------------MZ

					$add_update_card=HBLPAYPGW_get_card_details ($user_id,$paramArray['PAYMENT_TYPE'],$paramArray['CARD_NUM_MASKED']);

						//adding new card details (calloffunction)-----------------MZ

						if(is_null($add_update_card)){

								HBLPAYPGW_add_token_db($user_id,$paramArray['ORDER_REF_NUMBER'],$paramArray['CS_RESP_TOKEN'],$paramArray['CARD_NUM_MASKED'],$paramArray['PAYMENT_TYPE']);
								error_log('::::::::::::::::Adding NEW card:::::::ORDERID:::::'.$paramArray['ORDER_REF_NUMBER']);
								}

						//if card exits then update token (calloffunction)------------------MZ

						else{

							  HBLPAYPGW_update_token_db($user_id,$paramArray['ORDER_REF_NUMBER'],$paramArray['CS_RESP_TOKEN'],$paramArray['CARD_NUM_MASKED']);
								error_log('::::::::::::::::UPDATING Card details:::::::ORDERID:::::'.$paramArray['ORDER_REF_NUMBER']);
								}

							}//END OF IF FOR NULL CHECK

						}//end of save card or not
					}//end of session id
        }
		else if ($paramArray['RESPONSE_CODE'] == '481')
		{
			HBLPAYPGW_mark_order_hold($paramArray['ORDER_REF_NUMBER'], $paramArray['RESPONSE_MESSAGE']);
			error_log('::::::::::::::::UPDATING ORDER STATUS TO ON HOLD::::::::::::::::::ORDERID:::::'.$paramArray['ORDER_REF_NUMBER']);

			$redirection_url = '/checkout/order-received?cusdata='.encodeString('RESPONSE_MESSAGE='.$paramArray['RESPONSE_MESSAGE'] . '&ORDER_REF_NUMBER='. $paramArray['ORDER_REF_NUMBER'] .'&RESPONSE_CODE='.$paramArray['RESPONSE_CODE']);
		}

		else if ($paramArray['RESPONSE_CODE'] == '' || $paramArray['RESPONSE_CODE'] == '112')
		{
			if(isset($paramArray['ORDER_REF_NUMBER']) && $paramArray['ORDER_REF_NUMBER'] != null && $paramArray['ORDER_REF_NUMBER'] != '')
			{
				HBLPAYPGW_mark_order_cancelled($paramArray['ORDER_REF_NUMBER'], 'Order is Cancelled');
				error_log('::::::::::::::::UPDATING ORDER STATUS TO CANCELLED:::::::ORDERID:::::'.$paramArray['ORDER_REF_NUMBER']);
			}
			$redirection_url = '/checkout/order-cancelled?cusdata='.encodeString('RESPONSE_MESSAGE='.$paramArray['RESPONSE_MESSAGE'] . '&ORDER_REF_NUMBER='. $paramArray['ORDER_REF_NUMBER'] .'&RESPONSE_CODE='.$paramArray['RESPONSE_CODE']);
		}
        else
        {
            // if(isset($paramArray['ORDER_REF_NUMBER']) && $paramArray['ORDER_REF_NUMBER'] != null && $paramArray['ORDER_REF_NUMBER'] != '')
            // {
              	HBLPAYPGW_mark_order_failed($paramArray['ORDER_REF_NUMBER'], $paramArray['RESPONSE_MESSAGE']);
            // }

            $redirection_url ='/order-cancelled?cusdata='.encodeString('RESPONSE_MESSAGE='.$paramArray['RESPONSE_MESSAGE'] . '&ORDER_REF_NUMBER='. $paramArray['ORDER_REF_NUMBER'] .'&RESPONSE_CODE='.$paramArray['RESPONSE_CODE'] );

        }


		$decrypted_string = $url_params;

		 // wp_safe_redirect( $admin_settings['return_url'] . $redirection_url, 301 );
		 wp_safe_redirect( home_url() . $redirection_url, 301 );
		 exit;

		return 'success';
	}
}
	catch (Exception $e)
	{
		error_log("::::ORDERID:::::.'.$order_id:::Web Exception Raised...".$e->getMessage().':::Error code: ' . $e->getCode().':::Error Line:' . $e->getLine());
	}
}

//First hook that adds the menu item to my-account WooCommerce menu--------MZ
function HBLPAYPGW_user_card_management( $menu_links ){

	$new = array( 'card-management' => 'My Saved Cards' );
	$menu_links = array_slice( $menu_links, 0, 1, true )
	+ $new
	+ array_slice( $menu_links, 1, NULL, true );

	return $menu_links;
}
add_filter ( 'woocommerce_account_menu_items', 'HBLPAYPGW_user_card_management' );

// Second Filter to Redirect the WooCommerce endpoint to custom URL----------MZ
function HBLPAYPGW_user_card_management_hook_endpoint( $url, $endpoint, $value, $permalink ){

	if( $endpoint === 'card-management' ) {
		$url = site_url().'/my-account/card-management';
	}
	return $url;
}
add_filter( 'woocommerce_get_endpoint_url', 'HBLPAYPGW_user_card_management_hook_endpoint', 10, 4 );
do_action( 'woocommerce_account_navigation' );

//content for car management page--------------------MZ
function HBLPAYPGW_content_for_card_management( $content ) {
	if ( strcmp( 'card-management', get_post_field( 'post_name' ) ) === 0 ) {

		$select  = HBLPAYPGW_get_card_token( get_current_user_id());
		if(!$select){
			echo '<h4>You do not have any saved card(s).</h4>';
		}
		else{
		echo'	<h3>Saved Card(s)</h3>
		<table>
		<tr>
		<th>Card Type</th>
	    <th>Mask Card Number</th>
	    <th>Last Transaction Date</th>
	    <th>Action</th>
	  </tr>';
		foreach ( $select  as $card ) {
			$mask= $card->mask;
			$payment_type=$card->payment_type;
			$last_txn_date=$card->last_trans_date;
			$card_id=$card->id;
	echo'
	<tr>';
	if($payment_type == 'MASTER'){
	echo'<td><img src="';
	echo plugins_url( '/assets/images/master.png', __FILE__ ); echo '"';
			 echo ' alt="Alternate File Name"/>';
	echo'</td>';
}
else{
	echo'<td><img src="';
	echo plugins_url( '/assets/images/visa.png', __FILE__ ); echo '"';
		echo' alt="Alternate File Name"/>';
	echo'</td>';
}
	echo'<td>';esc_html_e( $mask );
	echo'</td>
	<td>';esc_html_e($last_txn_date);
	   echo '</td>
	    <td><a name="delete" href="'.plugins_url( 'delete_card.php?ID=', __FILE__ );
			echo esc_html_e($card_id). '"><img src="';
			echo plugins_url( '/assets/images/bin.png', __FILE__ ); echo '";
			 alt="Alternate File Name"/></a></td>
</form>
</td>
	  </tr>
	<br>';
     }
		 echo '</table>';
	 }

}
		return $content;
}//end of function placing content
add_filter( 'the_content', 'HBLPAYPGW_content_for_card_management' );

//content for cancelled order_page
function HBLPAYPGW_content_for_cancelled_order( $content ) {

	$encodedStr = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
	$str_params = strpos($encodedStr, 'cusdata=');
	//echo $str_params;
	if($str_params === 0)
	{

		$encodedStr = str_replace("cusdata=", "", $encodedStr);
		$items = decodeString($encodedStr);
		parse_str($items, $paramArray);
//echo $paramArray['RESPONSE_CODE'];
			return $paramArray['RESPONSE_MESSAGE'] .' Your Order number is '.$paramArray['ORDER_REF_NUMBER'].'.'.'<form action="'.home_url().'"><input type="submit" value="Return To Home" /></form>';
}
		return $content;
}//end of function placing content
add_filter( 'the_content', 'HBLPAYPGW_content_for_cancelled_order' );

add_filter( 'woocommerce_endpoint_order-received_title', 'HBLPAYPGW_thank_you_title' );
function HBLPAYPGW_thank_you_title($old_title)
{
	$encodedStr = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

	$str_params = strpos($encodedStr, 'cusdata=');

	if($str_params === 0)
	{
		$encodedStr = str_replace("cusdata=", "", $encodedStr);
		$items = decodeString($encodedStr);
		parse_str($items, $paramArray);
//echo $paramArray['RESPONSE_CODE'];

		if($paramArray['RESPONSE_CODE'] == '')
		{
			return 'Order cancelled';
		}
		else
		{
			return 'Order received';

		}
	}
	else
	{
		return 'Order received';
	}
}

add_filter( 'woocommerce_thankyou_order_received_text', 'HBLPAYPGW_thank_you_title_modify', 20, 2 );
function HBLPAYPGW_thank_you_title_modify()
{
	$encodedStr = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
	$str_params = strpos($encodedStr, 'cusdata=');
	//echo $str_params;
	if($str_params === 0)
	{

		$encodedStr = str_replace("cusdata=", "", $encodedStr);
		$items = decodeString($encodedStr);
		parse_str($items, $paramArray);

		if($paramArray['RESPONSE_CODE'] === '')
		{
			return $paramArray['RESPONSE_MESSAGE'] . '<form action="'.home_url().'"><input type="submit" value="Return To Home" /></form>';
		}
		else
		{
			//when payment is successfull order received page message***
			return $paramArray['RESPONSE_MESSAGE'] . 'Your order number is ' .$paramArray['ORDER_REF_NUMBER'] . '<form action="'.home_url().'"><input type="submit" value="Return To Home" /></form>';
		}
	}
	else
	{
		return 'Thank you. Your order has been received.' . 'Your order number is ' .$paramArray['ORDER_REF_NUMBER'] . '<form action="'.home_url().'"><input type="submit" value="Return To Home" /></form>';
	}
}


function HBLPAYPGW_mark_order_completed( $order_id, $note )
{
    $order = wc_get_order( $order_id );

	$order_data = $order->get_data();
	$order_status = $order_data['status'];

	if($order_status === 'pending')
	{
		$order->update_status( 'processing' );
		$order->add_order_note($note);
	}
}

function HBLPAYPGW_mark_order_cancelled( $order_id, $note )
{
    $order = wc_get_order( $order_id );

	$order_data = $order->get_data();
	$order_status = $order_data['status'];

	if($order_status === 'pending')
	{
		$order->update_status( 'cancelled' );
		$order->add_order_note($note);
	}
}

function HBLPAYPGW_mark_order_hold( $order_id, $note )
{
    $order = wc_get_order( $order_id );

	$order_data = $order->get_data();
	$order_status = $order_data['status'];

	if($order_status === 'pending')
	{
		$order->update_status( 'on-hold' );
		$order->add_order_note($note);
	}
	// else
	// {
		// $order->add_order_note('You are not authorized to process this order');
	// }
}
//**new status for failed order
function HBLPAYPGW_mark_order_failed( $order_id, $note )
{
    $order = wc_get_order( $order_id );

	$order_data = $order->get_data();
	$order_status = $order_data['status'];

	if($order_status === 'pending')
	{
		$order->update_status( 'failed' );
		$order->add_order_note($note);
	}
}


//Receiving Data On Return URL End

// add_action( 'woocommerce_checkout_order_essed', 'is_express_delivery',  1, 1  );
// function is_express_delivery( $order_id )
// {

   // $order = new WC_Order( $order_id );
   // //something else

// }

add_filter( 'woocommerce_payment_gateways', 'hblpay_add_gateway_class' );
function hblpay_add_gateway_class( $gateways )
{
	$gateways[] = 'WC_HblPay_Gateway'; // your class name is here
	return $gateways;
}


/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'hblpay_init_gateway_class' );
function hblpay_init_gateway_class()
{
	//notification if woocommerce is missing--calling
	if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', 'woocommerce_hblpay_missing_wc_notice' );
			return;
		}

	//using define to compare WC require version
	if ( version_compare( WC_VERSION, WC_HBLPAY_MIN_WC_VER, '<' ) ) {
		add_action( 'admin_notices', 'woocommerce_hblpay_wc_not_supported' );
		return;
	}

	class WC_HblPay_Gateway extends WC_Payment_Gateway
	{

 		/**
 		 * Class constructor, more about it in Step 3
 		 */
 		public function __construct()
		{
			$this->id = 'hblpay'; // payment gateway plugin ID
			$this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
			$this->has_fields = true; // in case you need a custom credit card form
			$this->method_title = 'HBLPAY Payment Gateway For Woocommerce';
			$this->method_description = 'HBLPay Payment Gateway is simple checkout platform that enables ecommerce merchants to accept online payments from its VISA, Master and Union Pay credit/debit cards customers.'; // will be displayed on the options page
			// gateways can support subscriptions, refunds, saved payment methods,
			// but in this tutorial we begin with simple payments
			$this->supports = array(
				'products',
				'subscriptions'
			);

			// Method with all the options fields
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();
			$this->title = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->enabled = $this->get_option( 'enabled' );
			$this->private_key = $this->get_option( 'private_key' );
			$this->publishable_key = $this->get_option( 'gateway_public_key' );
			$this->tokenization = $this->get_option( 'enable_tokenization' );

			// $this->testmode = 'yes' === $this->get_option( 'testmode' );
			// $this->private_key = $this->testmode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
			// $this->publishable_key = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'gateway_public_key' );

			// This action hook saves the settings
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			  add_action( 'woocommerce_checkout_update_order_meta', array($this,'add_save_card_to_table'));
			// We need custom JavaScript to obtain a token
			//add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

			// You can also register a webhook here
			 add_action( 'rest_api_init', 'HBLPAYPGW_mark_status_completed');
			 	//hook dropdown for saved cards
			// add_action('woocommerce_review_order_after_submit', array($this,'select_cards_custom'));
			 //hook to save save card option in post_meta() table

			 // custom checkout field validation
		//	 add_action( 'woocommerce_after_checkout_validation', 'validate_fields' );
			 //add_action( 'wp_enqueue_scripts', 'enqueue_select2_jquery' );
			 add_action( 'wp_enqueue_scripts', 'HBLPAYPGW_wp_enqueue_scripts_callback' );
			 // Conditional Show hide checkout fields based on chosen payment methods
			// add_action( 'woocommerce_after_checkout_billing_form', array($this,'conditionally_show_hide_save_card'));
			// add_action( 'init', 'user_card_management_add_endpoint' );
 		}




		/**
 		 * Plugin options, we deal with it in Step 3 too
 		 */
		public function init_form_fields()
		{
				$this->form_fields = array(
				'enabled' => array(
					'title'       => 'Enable/Disable',
					'label'       => 'Enable HBLPay Gateway',
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no'
				),
				'is_live' => array(
					'title'       => 'Live Environment',
					'label'       => 'Enable Live Environment',
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no'
				),
				'use_proxy' => array(
					'title'       => 'Use Proxy',
					'label'       => 'Enable Proxy',
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no'
				),
				'proxy' => array(
					'title'       => 'Proxy',
					'type'        => 'text'
				),
				'title' => array(
					'title'       => 'Title',
					'type'        => 'text',
					'description' => 'This controls the title which the user sees during checkout.',
					'default'     => 'HBL Pay',
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => 'Description',
					'type'        => 'textarea',
					'description' => 'This controls the description which the user sees during checkout.',
					'default'     => 'Pay through HBL payment gateway and do whatever you want',
				),
				'client_name' => array(
					'title'       => 'Client Name',
					'type'        => 'text'
				),
				'channel' => array(
					'title'       => 'Channel',
					'type'        => 'text'
				),
				'password' => array(
					'title'       => 'Password',
					'type'        => 'password'
				),
				// 'return_url' => array(
					// 'title'       => 'URL',
					// 'type'        => 'text'
				// ),

				'gateway_public_key' => array(
					'title'       => 'Gateway Public Key',
					'type'        => 'text'
				),

				'private_key' => array(
					'title'       => 'Store Private Key',
					'type'        => 'password'
				),
				'selected_method' => array(
					'title'       => 'Selected Payment Gateway',
					'type'        => 'select',
					'options'       => array(
						'0'          => __("None", "woocommerce"),
						'1001'  => __("Union Pay", "woocommerce"),
						'1002'  => __("Cybersource", "woocommerce"),
						)
				),
				'enable_tokenization' => array(
					'title'       => 'Enable Tokenization',
					'type'        => 'checkbox',
					'label'       => '',
					'description' => '',
					'default'     => 'no'
				)

			);
		}

//show cards if available and select from them-----------MZ
	 public function payment_fields(){
		$admin_settings = WC()->payment_gateways->payment_gateways()['hblpay']->settings;
		//error_log("settings..".$admin_settings['enable_tokenization']);
		if($admin_settings['enable_tokenization'] === 'no'){
			if( $this->description ){
			 echo wp_kses_post( wpautop( wptexturize( $this->description ) ) );
		 }
		}
		 if($admin_settings['enable_tokenization'] === 'yes'){
			 	$card_exist=HBLPAYPGW_get_card_token(get_current_user_id());
				if(get_current_user_id() != null){
			?>

			<div class="save_card_div" >
				<select name="select_cards" class="" id="myselection" style="width:100%">
					<option value="new_card" data-foo="new_card">Add New Card</option>
					<?php  foreach ( $card_exist  as $card ) {
					?><option value="<?php echo esc_html_e($card->id);?>"data-foo="<?php echo esc_html_e($card->payment_type); ?>" name="saved_cards">
						<?php echo esc_html_e($card->mask); ?></option>
				<?php } ?>
			 </select>

			<div class="checkbox_div" style="margin-top:7px">
				<input style="margin-right:8px;margin-bottom=-2px" type="checkbox" name="save_card" value="1" checked>Save My Card
			</div>
			 </div>

		<?php
		if( $this->description ){
			echo wp_kses_post( wpautop( wptexturize( $this->description ) ) );
		}
		//		}
			}
		}
}
	 function add_save_card_to_table($order_id){
			update_post_meta( $order_id, 'save_card', sanitize_text_field( $_POST ['save_card'] ) );
		}


		// public function payment_scripts()
		// {
		//
		// }

		/*
 		 * Fields validation, more in Step 5
		 */
	 function validate_fields()
		{

				if( is_checkout() && ! is_wc_endpoint_url() ){
					global $woocommerce;
					$admin_settings = WC()->payment_gateways->payment_gateways()['hblpay']->settings;
				if($admin_settings['enable_tokenization'] === 'yes'){
					if(get_current_user_id() != null){
					if (  empty(sanitize_text_field( $_POST['select_cards'] )) ){
							wc_add_notice( 'Please select at least one card', 'error' );
							return false;
						}
					}
				}
					if( empty(sanitize_text_field( $_POST[ 'billing_first_name' ])) ) {
					wc_add_notice(  'First name is required!', 'error' );
					return false;
					}

					if( empty( sanitize_text_field($_POST[ 'billing_last_name' ]) )) {
					wc_add_notice(  'Last name is required!', 'error' );
					return false;
					}

					if( empty( sanitize_text_field($_POST[ 'billing_country' ])) ) {
					wc_add_notice(  'Billing Country is required!', 'error' );
					return false;
					}

					if( sanitize_text_field(empty( $_POST[ 'billing_address_1' ])) ) {
					wc_add_notice(  'Billing Address Line 1 is required!', 'error' );
					return false;
					}

					if( sanitize_text_field(empty( $_POST[ 'billing_city' ]) )) {
					wc_add_notice(  'Billing City is required!', 'error' );
					return false;
					}

			// if( empty( $_POST[ 'billing_state' ]) ) {
			// wc_add_notice(  'Billing State is required!', 'error' );
			// return false;
			// }

			// if( empty( $_POST[ 'billing_postcode' ]) ) {
			// wc_add_notice(  'Billing Postal Code is required!', 'error' );
			// return false;
			// }

			if( empty( sanitize_text_field($_POST[ 'billing_phone' ])) ) {
			wc_add_notice(  'Billing Phone is required!', 'error' );
			return false;
			}

			if( sanitize_text_field(empty( $_POST[ 'billing_email' ])) ) {
			wc_add_notice(  'Billing Email is required!', 'error' );
			return false;
			}


			// if( empty( $_POST[ 'shipping_first_name' ]) ) {
			// wc_add_notice(  'Shipping First Name is required!', 'error' );
			// return false;
			// }

			// if( empty( $_POST[ 'shipping_last_name' ]) ) {
			// wc_add_notice(  'Shipping Last Name is required!', 'error' );
			// return false;
			// }

			// if( empty( $_POST[ 'shipping_country' ]) ) {
			// wc_add_notice(  'Shipping Country is required!', 'error' );
			// return false;
			// }

			// if( empty( $_POST[ 'shipping_address_1' ]) ) {
			// wc_add_notice(  'Shipping Address Line 1 is required!', 'error' );
			// return false;
			// }

			// if( empty( $_POST[ 'shipping_city' ]) ) {
			// wc_add_notice(  'Shipping City is required!', 'error' );
			// return false;
			// }

			// if( empty( $_POST[ 'shipping_state' ]) ) {
			// wc_add_notice(  'Shipping State is required!', 'error' );
			// return false;
			// }

			// if( empty( $_POST[ 'shipping_postcode' ]) ) {
			// wc_add_notice(  'Shipping Postal Code is required!', 'error' );
			// return false;
			// }

			return true;
			}
		}

		public function process_payment($order_id)
		{
			try{
			error_log('::::::::::::::::process_payment STARTED::::::::::::::::::'.$order_id);
			global $woocommerce;
			$order = wc_get_order($order_id);
			$customer_id = $order->get_user_id();
			$requestData = $this->getRequestObject($order, $order_id);


			$jsondata = json_encode($requestData);
			error_log($jsondata);
			$order->update_status('pending');
			//$woocommerce->cart->empty_cart();

			$api_url = '';
			$page_url = '';
			$admin_settings = WC()->payment_gateways->payment_gateways()['hblpay']->settings;

			if($admin_settings['is_live'] === 'yes')
			{
				$api_url = 'https://digitalbankingportal.hbl.com/HostedCheckout/api/checkout';
				$page_url = 'https://digitalbankingportal.hbl.com/HostedCheckout/Site/index.html#/checkout?data=';
			}
			else
			{
				$api_url = 'https://testpaymentapi.hbl.com/HBLPay/api/checkout';
				$page_url = 'https://testpaymentapi.hbl.com/HBLPay/Site/index.html#/checkout?data=';
				// $api_url = 'http://localhost:63896/api/Checkout';
				// $page_url = 'http://localhost:1113/index.html#/checkout?data=';
			}
			error_log('::::::::::::::::Before Api call::::::::::ORDERID::::::::'.$order_id);
			try{

				$f_response = callAPI('POST', $api_url, $jsondata,$order_id);

			}
			 catch (Exception $e)
			{
					error_log("::::ORDERID:::::.'.$order_id:::Web Exception Raised...".$e->getMessage().':::Error code: ' . $e->getCode().':::Error Line:' . $e->getLine());
			}
			 if(!$f_response){
				 error_log('::::::::::::::::NO RESPONSE RECEIEVED DUE TO EXECEPTION:::::::::ORDERID:::::::::'.$order_id);
				 return false;
			 }

			error_log('::::::::::::::::After Api Call Encrypted data receieved successfully:::::::::ORDERID:::::::::'.$order_id);

			$jsonData = json_decode($f_response,true);

			if($jsonData){
			error_log(':::::::::::::::Decrypted data successfully::::::::ORDERID::::::::::'.$order_id);
			}

			 $f_url = $page_url . encodeString($jsonData['Data']['SESSION_ID']);

			 if(!$f_url){
				error_log('::::::::::::::::Redirection Stopped ::::::::::ORDERID::::::::'.$order_id);
				return false;
			}

			error_log('::::::::::::::::Redirecting to URL with Session ID::::::::::ORDERID::::::::'.$f_url);

			if(!isset($jsonData['Data']['SESSION_ID']))
			{
				error_log('::::::::::::::::Redirection not successfull-- NO Session ID::::::::::ORDERID::::::::'.$order_id);
				return false;
			}

			error_log('::::::::::::::::process_payment ENDED WITH SUCCESS URL:::::::::::::::::::ORDERID::::::::::'.$order_id. $f_url);

			return array(
				'result'   => 'success',
				'redirect' => $f_url,
			);
		 }
		 catch (Exception $e)
		{
				error_log("::::ORDERID:::::.'.$order_id:::Web Exception Raised...".$e->getMessage().':::Error code: ' . $e->getCode().':::Error Line:' . $e->getLine());
		}

		}

		public function getRequestObject($order, $order_id)
		{
			try{
				error_log('::::::::::::::::getRequestObject STARTED::::::::::::::::::ORDERID::::'.$order_id );
			global $post;

			$shippingPackageName = '';
			$shippingPackageCost = '0';
			foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj )
			{
			  $shippingPackageName = $shipping_item_obj->get_name();
			  $shippingPackageCost = strval($shipping_item_obj->get_total());
			}

			$shippingName = 'none';
			$ddays = 0;
			$shippingcost = $shippingPackageCost;
			if(strlen($shippingPackageName) > 0)
			{
				$shippingName = $shippingPackageName;
			}
			else
			{
				$shippingName = 'none';
			}

			$orderTotalDiscount = 0;
			foreach ($order->get_items() as $item_id => $item)
			{
				$orderTotalDiscount += (string)((int)$saving_price * (int)$product_quantity);
			}

			$DISCOUNT_ON_TOTAL = $orderTotalDiscount;
			$SUBTOTAL = $order->get_total();

			$bill_to_forename = $order->get_billing_first_name();
			$BillToSurName = $order->get_billing_last_name();
			$bill_to_email =  $order->get_billing_email();
			$BillToPhone = $order->get_billing_phone();
			$bill_to_address_line1 = $order->get_billing_address_1();
			if($order->get_billing_address_2() != '')
			{
				$bill_to_address_line1 .= $bill_to_address_line1.' '.$order->get_billing_address_2();
			}

			$BillToCity = $order->get_billing_city();
		//	error_log('$BillToCity::::::' . $order->get_billing_city() . '::::::$BillToCity');
			//$token_array = WC_Payment_Tokens::get_order_tokens($order_data['id']);
			//error_log('tokenn::::::'.print_r(WC_Payment_Tokens::get_order_tokens($order_data['id'])));
			$bill_to_address_state = $order->get_billing_state();
			$BillToCountry = $order->get_billing_country();
			$billing_postcode=sanitize_text_field($_POST[ 'billing_postcode' ]);
			if(isset($billing_postcode)){
				$bill_to_address_postal_code = $billing_postcode;
			//	error_log("bill_potsal".$bill_to_address_postal_code);
			}
			else {
				$bill_to_address_postal_code = '';
			}


				$ShipToForeName = $order->get_shipping_first_name();
				$ShipToSurName = $order->get_shipping_last_name();
				$ShipToEmail = $order->get_billing_email();
				$ShipToPhone = $order->get_billing_phone();
				$ShipToAddressLine1 = $order->get_shipping_address_1();
				if($order->get_shipping_address_2() != '')
				{
					$ShipToAddressLine1 .= $ShipToAddressLine1.' '.$order->get_shipping_address_2();
				}

				$ShipToCity = $order->get_shipping_city();
				$ShipToState = $order->get_shipping_state();
				$ShipToCountry = $order->get_shipping_country();
				$ShipToPostalCode = $order->get_shipping_postcode();
//error_log("ship_postal".$ShipToPostalCode);


						// $ShipToForeName = $order->get_billing_first_name();
						// $ShipToSurName = $order->get_billing_last_name();
						// $ShipToEmail = $order->get_billing_email();
						// $ShipToPhone = $order->get_billing_phone();
						// $ShipToAddressLine1 = $order->get_billing_address_1();
						// if($order->get_billing_address_2() != '')
						// {
						// 	$ShipToAddressLine1 .= $ShipToAddressLine1.' '.$order->get_shipping_address_2();
						// }

						// $ShipToCity = $order->get_billing_city();
						// $ShipToState = $order->get_billing_state();
						// $ShipToCountry = $order->get_billing_country();
						// $ShipToPostalCode = $order->get_billing_postcode();


			$admin_settings = WC()->payment_gateways->payment_gateways()['hblpay']->settings;

			$USER_ID = $admin_settings['client_name'];
			$PASSWORD = $admin_settings['password'];
			$CHANNEL = $admin_settings['channel'];
			$RETURN_URL = home_url() . '/wp-json/hblpay_response/v1/checkout';
			$CANCEL_URL = home_url() . '/wp-json/hblpay_response/v1/checkout';
			//$RETURN_URL = $admin_settings['return_url'] . '/wp-json/hblpay_response/v1/checkout';
			//$CANCEL_URL = $admin_settings['return_url'] . '/wp-json/hblpay_response/v1/checkout';
			$Default_Payment_Gateway = $admin_settings['selected_method'];



			/////////-------Section MDD3 and MDD4 Start-------/////////
			global $woocommerce;
			$cart_items = $woocommerce->cart->get_cart();
			$cb_items = array();

			 if(!empty($cart_items))
			 {
				 if(count($cart_items) == 1)
				 {
					 $x = 0;
					 foreach($cart_items  as $values)
					 {
						 if($values[ 'product_id' ])
						 {
							 $product = new WC_Product( $values['product_id']);
							 $products_cats = $product->get_category_ids();
						 }
						 else
						 {
							 $product = new WC_Product( $values['variation_id']);
							 $products_cats = $product->get_category_ids();
						 }

						 $_product = $values['data']->post;

						 $cb_items[ 'merchant_defined_data4' ] = cyber_clean($_product->post_title);

						if( is_array($products_cats) && ! empty($products_cats))
						{
							$c = array();
							foreach ($products_cats as $value)
							{
								if( $term = get_term_by( 'id', $value, 'product_cat' ) )
								{
									$c[] = $term->name;
								}
							}

							$products_cats = implode( ',', $c );
						}
						else
						{
							$products_cats = '';
						}

						$cb_items['merchant_defined_data3'] = cyber_clean($products_cats);

						 $x++;
					 }
				 }
				 else
				 {
					$x = 0;
					foreach( $cart_items  as $values )
					{

						if( $values[ 'product_id' ] )
						{
							$product = new WC_Product( $values['product_id']);
							$products_cats = $product->get_category_ids();
						}
						else
						{
							$product = new WC_Product( $values['variation_id']);
							$products_cats = $product->get_category_ids();
						}

						$_product = $values['data']->post;

						$mdd_product['name'][] = $_product->post_title;

						if( ! empty($products_cats) && is_array($products_cats))
						{
							foreach ( $products_cats as $key => $value)
							{
								$mdd_product[ 'cats' ][]  = $value;
							}
						}
						else
						{
							$mdd_product[ 'cats' ]  = $products_cats;
						}

						$x++;
					}

					if(!empty($mdd_product[ 'name' ]) && is_array($mdd_product[ 'name' ]))
					{
						$cb_items['merchant_defined_data4'] = cyber_clean(implode( ',', $mdd_product[ 'name' ]));
					}

					if( ! empty($mdd_product[ 'cats' ]) && is_array($mdd_product[ 'cats' ] ))
					{
						$c = array();

						foreach ($mdd_product[ 'cats' ] as  $value)
						{
							if( $term = get_term_by( 'id', $value, 'product_cat' ) )
							{
								$c[] = $term->name;
							}
						}
						$cb_items[ 'merchant_defined_data3' ] = cyber_clean(implode( ',', $c));
					}

				 }

			 }
			/////////-------Section MDD3 and MDD4 END-------/////////
//-------------------use of cards and send token----------------------MZ//////
$admin_settings = WC()->payment_gateways->payment_gateways()['hblpay']->settings;
 if($admin_settings['enable_tokenization'] === 'yes'){
			$consumer_id = get_current_user_id();
			if(empty($consumer_id)){//for logout users
				$bill_to_email=$bill_to_email;
			}
			else{
			$selected_card=sanitize_text_field($_POST['select_cards']);
			//if user wants to add new card--no token
			if($selected_card == 'new_card'){
				$token_valid ='';
				$payment_type ='';
				$bill_to_email=$bill_to_email;//to check against token of saved card---- if not saved card go with email in input
			}
			//if user wants to add from saved cards -- send token
			else{
			//get saved card token
			global $wpdb;
			$results = $wpdb->get_row( "SELECT * FROM wp_hblpay_tokenization WHERE user_id = '$consumer_id' AND id= $selected_card");
			$token_valid =$results->token;
			$payment_type =$results->payment_type;
			$bill_to_email=$results->email;//to check against token of saved card from DB--disable email field
			//updating last trans date of saved cards
			HBLPAYPGW_update_lasttrans_of_saved_card($consumer_id,$selected_card);
		 }
	 }//for loggedin user
	 }
	 else{
		 	$bill_to_email=$bill_to_email;
	 }
//---------------end of use of save cards--------------------//

$admin_settings = WC()->payment_gateways->payment_gateways()['hblpay']->settings;
 if($admin_settings['enable_tokenization'] === 'yes'){
	 //error_log("token");
//-------------if guest user or create account at checkout Behaviour-------------------------//
	 			if((empty($selected_card) && !empty($consumer_id))  || (empty($selected_card) && empty($consumer_id))){
	 						$type_value=$Default_Payment_Gateway;
	 			}
//------------if user is logged in properly behaviour--------------------------//
	 			else{
					if($selected_card == 'new_card'){
							$type_value= $Default_Payment_Gateway;
					}
					else{
						$type_value= '1002';
					}
}
}
else{
	 //error_log("no token");
	$type_value=$Default_Payment_Gateway;
}
		//	 error_log("test type\n" . json_encode($payment_type, JSON_PRETTY_PRINT));
	//-----------------------------------------End ---------------------------------------/////////
			$previous_customer = ( $consumer_id == 0 ? 'NO' : 'YES' );

			$mdd1 = 'WC';
			$mdd2 = 'YES';
			$mdd3 = $cb_items[ 'merchant_defined_data3' ];
			$mdd4 = $cb_items[ 'merchant_defined_data4' ];
			$mdd5 = '';
			if($previous_customer)
			{
				$mdd5 = $consumer_id;
			}

			$mdd6 = $shippingPackageName;
			$mdd7 = $woocommerce->cart->get_cart_contents_count();
			$mdd8 = $woocommerce->customer->get_billing_country();

			//---------------MERCHANT MANUAL CONFIGURATION SECTION (start)---------------//
			// $mdd9 = $admin_settings['merchant_data_9'];
			// $mdd10 = $admin_settings['merchant_data_10'];
			// $mdd11 = $admin_settings['merchant_data_11'];
			// $mdd12 = $admin_settings['merchant_data_12'];
			// $mdd13 = $admin_settings['merchant_data_13'];
			// $mdd14 = $admin_settings['merchant_data_14'];
			// $mdd15 = $admin_settings['merchant_data_15'];
			// $mdd16 = $admin_settings['merchant_data_16'];
			// $mdd17 = $admin_settings['merchant_data_17'];
			// $mdd18 = $admin_settings['merchant_data_18'];
			// $mdd19 = $admin_settings['merchant_data_19'];
			//---------------MERCHANT MANUAL CONFIGURATION SECTION (end)---------------//

			$mdd20 = __('NO', 'woocommerce');

			$mddallowedlength = 100;

			$currency_code = $order->get_currency();

			$customer_id = $order->get_user_id();
			$enc_key = $admin_settings['gateway_public_key'];
			$enc_key = str_replace("-----BEGIN PUBLIC KEY-----","-----BEGIN PUBLIC KEY-----\n",$enc_key);
			$enc_key = str_replace("-----END PUBLIC KEY-----","\n-----END PUBLIC KEY-----",$enc_key);

if($ShipToForeName == '' && $ShipToSurName == '' &&  $ShipToAddressLine1 == '' && $ShipToCity == ''
	&& $ShipToState == '' && $ShipToCountry == '' && $ShipToPostalCode == '' ) {

// $main_data = array (
// 	'Data1' => $USER_ID,
// 	'Data2' => encryptData($PASSWORD,$enc_key),
// 	'Data3' => "Aeskey",
// 	'Data4' => "$custom_data"
// );

			$custom_data = array (//all fields will be plain text
				 'USER_ID' => $USER_ID,
				 'PASSWORD' => encryptData($PASSWORD,$enc_key),//password will be plain text from now.
				'CHANNEL' => encryptData($CHANNEL,$enc_key),
				'RETURN_URL' => encryptData($RETURN_URL,$enc_key),
				'CANCEL_URL' => encryptData($CANCEL_URL,$enc_key),
				'TYPE_ID' => encryptData($type_value,$enc_key),
				'SHIPPING_DETAIL' => array (
					'NAME' => encryptData($shippingName,$enc_key),
					'DELIEVERY_DAYS' => encryptData($ddays,$enc_key),//modify if shipping days applicable
					'SHIPPING_COST' => encryptData($shippingcost,$enc_key)
				),
				'ORDER' => array (
					'DISCOUNT_ON_TOTAL' => encryptData($DISCOUNT_ON_TOTAL,$enc_key),
					'SUBTOTAL' => encryptData($SUBTOTAL,$enc_key)
				),
				'ADDITIONAL_DATA' => array (
					'BILL_TO_FORENAME' => encryptData($bill_to_forename,$enc_key),
					'BILL_TO_SURNAME' => encryptData($BillToSurName,$enc_key),
					'BILL_TO_EMAIL' => encryptData($bill_to_email,$enc_key),
					'BILL_TO_PHONE' => encryptData($BillToPhone,$enc_key),
					'BILL_TO_ADDRESS_LINE' => encryptData(TrimString(clean_special_chars($bill_to_address_line1),50),$enc_key),
					'BILL_TO_ADDRESS_CITY' => encryptData(TrimString(clean_special_chars($BillToCity),50),$enc_key),
					'BILL_TO_ADDRESS_STATE' => encryptData($bill_to_address_state,$enc_key),
					'BILL_TO_ADDRESS_COUNTRY' => encryptData($BillToCountry,$enc_key),
					'BILL_TO_ADDRESS_POSTAL_CODE' => encryptData($bill_to_address_postal_code,$enc_key),

					//'SHIP_TO_PHONE' => encryptData($ShipToPhone,$enc_key),

					'CURRENCY' => encryptData($currency_code,$enc_key),
					'REFERENCE_NUMBER' => encryptData($order_id,$enc_key),
					'PAYMENT_TOKEN'=> $token_valid,
					'PAYMENT_TYPE'=> encryptData($payment_type,$enc_key),
					'CUSTOMER_ID' => encryptData($customer_id,$enc_key),
					'MerchantFields' => array (
						'MDD1' => encryptData(TrimString($mdd1,$mddallowedlength),$enc_key),
						'MDD2' => encryptData(TrimString($mdd2,$mddallowedlength),$enc_key),
						'MDD3' => encryptData(TrimString($mdd3,$mddallowedlength),$enc_key),
						'MDD4' => encryptData(TrimString($mdd4,$mddallowedlength),$enc_key),
						'MDD5' => encryptData(TrimString($mdd5,$mddallowedlength),$enc_key),
						'MDD6' => encryptData(TrimString($mdd6,$mddallowedlength),$enc_key),
						'MDD7' => encryptData(TrimString($mdd7,$mddallowedlength),$enc_key),
						'MDD8' => encryptData(TrimString($mdd8,$mddallowedlength),$enc_key),
						'MDD20' => encryptData(TrimString($mdd20,$mddallowedlength),$enc_key)
					)
				)
			 );
 }
 else{

	$custom_data = array (
				 'USER_ID' => $USER_ID,
				 'PASSWORD' => encryptData($PASSWORD,$enc_key),
				'CHANNEL' => encryptData($CHANNEL,$enc_key),
				'RETURN_URL' => encryptData($RETURN_URL,$enc_key),
				'CANCEL_URL' => encryptData($CANCEL_URL,$enc_key),
				'TYPE_ID' => encryptData($type_value,$enc_key),
				'SHIPPING_DETAIL' => array (
					'NAME' => encryptData($shippingName,$enc_key),
					'DELIEVERY_DAYS' => encryptData($ddays,$enc_key),//modify if shipping days applicable
					'SHIPPING_COST' => encryptData($shippingcost,$enc_key)
				),
				'ORDER' => array (
					'DISCOUNT_ON_TOTAL' => encryptData($DISCOUNT_ON_TOTAL,$enc_key),
					'SUBTOTAL' => encryptData($SUBTOTAL,$enc_key)
				),
				'ADDITIONAL_DATA' => array (
					'BILL_TO_FORENAME' => encryptData($bill_to_forename,$enc_key),
					'BILL_TO_SURNAME' => encryptData($BillToSurName,$enc_key),
					'BILL_TO_EMAIL' => encryptData($bill_to_email,$enc_key),
					'BILL_TO_PHONE' => encryptData($BillToPhone,$enc_key),
					'BILL_TO_ADDRESS_LINE' => encryptData(TrimString(clean_special_chars($bill_to_address_line1),50),$enc_key),
					'BILL_TO_ADDRESS_CITY' => encryptData(TrimString(clean_special_chars($BillToCity),50),$enc_key),
					'BILL_TO_ADDRESS_STATE' => encryptData($bill_to_address_state,$enc_key),
					'BILL_TO_ADDRESS_COUNTRY' => encryptData($BillToCountry,$enc_key),
					'BILL_TO_ADDRESS_POSTAL_CODE' => encryptData($bill_to_address_postal_code,$enc_key),

					'SHIP_TO_FORENAME' => encryptData($ShipToForeName,$enc_key),
					'SHIP_TO_SURNAME' => encryptData($ShipToSurName,$enc_key),
					'SHIP_TO_PHONE' => encryptData($ShipToPhone,$enc_key),
					'SHIP_TO_ADDRESS_LINE' => encryptData(TrimString(clean_special_chars($ShipToAddressLine1),50),$enc_key),
					'SHIP_TO_ADDRESS_CITY' => encryptData(TrimString(clean_special_chars($ShipToCity),29),$enc_key),
					'SHIP_TO_ADDRESS_STATE' => encryptData($ShipToState,$enc_key),
					'SHIP_TO_ADDRESS_COUNTRY' => encryptData($ShipToCountry,$enc_key),
					'SHIP_TO_ADDRESS_POSTAL_CODE' => encryptData($ShipToPostalCode,$enc_key),

					'CURRENCY' => encryptData($currency_code,$enc_key),
					'REFERENCE_NUMBER' => encryptData($order_id,$enc_key),
					'PAYMENT_TOKEN'=> $token_valid,
					'PAYMENT_TYPE'=> encryptData($payment_type,$enc_key),
					'CUSTOMER_ID' => encryptData($customer_id,$enc_key),
					'MerchantFields' => array (
						'MDD1' => encryptData(TrimString($mdd1,$mddallowedlength),$enc_key),
						'MDD2' => encryptData(TrimString($mdd2,$mddallowedlength),$enc_key),
						'MDD3' => encryptData(TrimString($mdd3,$mddallowedlength),$enc_key),
						'MDD4' => encryptData(TrimString($mdd4,$mddallowedlength),$enc_key),
						'MDD5' => encryptData(TrimString($mdd5,$mddallowedlength),$enc_key),
						'MDD6' => encryptData(TrimString($mdd6,$mddallowedlength),$enc_key),
						'MDD7' => encryptData(TrimString($mdd7,$mddallowedlength),$enc_key),
						'MDD8' => encryptData(TrimString($mdd8,$mddallowedlength),$enc_key),
						// 'MDD9' => encryptData(TrimString($mdd9,$mddallowedlength),$enc_key),
						// 'MDD10' => encryptData(TrimString($mdd10,$mddallowedlength),$enc_key),
						// 'MDD11' => encryptData(TrimString($mdd11,$mddallowedlength),$enc_key),
						// 'MDD12' => encryptData(TrimString($mdd12,$mddallowedlength),$enc_key),
						// 'MDD13' => encryptData(TrimString($mdd13,$mddallowedlength),$enc_key),
						// 'MDD14' => encryptData(TrimString($mdd14,$mddallowedlength),$enc_key),
						// 'MDD15' => encryptData(TrimString($mdd15,$mddallowedlength),$enc_key),
						// 'MDD16' => encryptData(TrimString($mdd16,$mddallowedlength),$enc_key),
						// 'MDD17' => encryptData(TrimString($mdd17,$mddallowedlength),$enc_key),
						// 'MDD18' => encryptData(TrimString($mdd18,$mddallowedlength),$enc_key),
						// 'MDD19' => encryptData(TrimString($mdd19,$mddallowedlength),$enc_key),
						'MDD20' => encryptData(TrimString($mdd20,$mddallowedlength),$enc_key)
					)
				)
			 );

}
			$custom_data['ORDER'] =  array (
					'DISCOUNT_ON_TOTAL' => encryptData($DISCOUNT_ON_TOTAL,$enc_key),
					'SUBTOTAL' => encryptData($SUBTOTAL,$enc_key),
					'OrderSummaryDescription' => array()
				);

			$OrderSummaryDescription = array();

			$parent_category = 'Uncategorized';
			$child_category = 'Uncategorized';

			$orderTotalDiscount = 0;
			foreach ($order->get_items() as $item_id => $item)
			{
				$product = $item->get_product();

				$terms = get_the_terms ( $product->get_id(), 'product_cat' );

				foreach ( $terms as $term )
				{
					if($term->parent == 0)
					{
						$parent_category = $term->name;
					}
					else if($term->parent != 0)
					{
						$child_category = $term->name;
					}

					if(count($terms) == 1)
					{
						$child_category = $parent_category;
					}
				}

				$regular_price = (float)$product->get_regular_price();

				//error_log("regular price...".$regular_price);
				$sale_price = (float)$product->get_price();
				//error_log("sale price...".$sale_price);
				$saving_price = $regular_price - $sale_price;

				$product_quantity = $item->get_quantity();
				$orderTotalDiscount += (string)((int)$saving_price * (int)$product_quantity);
				//**--------------new sale code changed to remove double price**-----------------------------------//
				if( $product->is_on_sale() ) {
					$old_price = $product->get_regular_price();
					}
				else{
				$old_price = '';
				}

				$product_name = $item->get_name();
				$product_total_price = $item['subtotal'];
				$product_unit_price = (string)($product_total_price / $product_quantity);
				$OrderSummaryDescription[] = [
						'ITEM_NAME' => encryptData(TrimString(RemoveBlackListCharacters($product_name),100),$enc_key),
						'QUANTITY' => encryptData(intval($product_quantity),$enc_key),
						'UNIT_PRICE' => encryptData($product_unit_price,$enc_key),
						'OLD_PRICE' => encryptData($old_price,$enc_key),
						'CATEGORY' => encryptData(TrimString(RemoveBlackListCharacters($parent_category),100),$enc_key),
						'SUB_CATEGORY' => encryptData(TrimString(RemoveBlackListCharacters($child_category),100),$enc_key),
						];
			}

			$custom_data['ORDER']['OrderSummaryDescription'] = $OrderSummaryDescription;
			error_log('::::::::::::::::getRequestObject ENDED::::::::ORDERID:::::.'.$order_id.'.:::::'.print_r($custom_data,1));
			error_log('::::::::::::::::Billing First Name::::::::::::::::::'.$bill_to_forename.'::::ORDERID:::::.'.$order_id);
			error_log('::::::::::::::::Billing Last Name::::::::::::::::::'.$BillToSurName.'::::ORDERID:::::.'.$order_id);
			return $custom_data;
		}
		catch (Exception $e)
			{
					error_log("::::ORDERID:::::.'.$order_id:::Web Exception Raised...".$e->getMessage().':::Error code: ' . $e->getCode().':::Error Line:' . $e->getLine());
			}
		}
 	}

	function callAPI($method, $url, $data,$order_id)
	{
		error_log('::::::::::::::::callAPI Started::::::::::ORDERID::::::::'.$order_id);
		 //error_log(print_r($data,1));
		 $admin_settings = WC()->payment_gateways->payment_gateways()['hblpay']->settings;
			 $curl = curl_init();
		 switch ($method)
		 {
			 case "POST":
				 curl_setopt($curl, CURLOPT_POST, 1);
				 if ($data)
					 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				 break;
			 case "PUT":
				 curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
				 if ($data)
				 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				 break;
			 default:
				 if ($data)
					 $url = sprintf("%s?%s", $url, http_build_query($data));
		 }

		 curl_setopt($curl, CURLOPT_URL, $url);
		 curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		 curl_setopt($curl, CURLOPT_FAILONERROR, true);
		 //PROTOCOL_ERROR
		 curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		 error_log('URL : '.$url);

		 if($admin_settings['is_live'] === 'yes')
		 {
			 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		 }

		 if($admin_settings['use_proxy'] === 'yes')
		 {
			 $proxy = $admin_settings['proxy'];
			 curl_setopt($curl, CURLOPT_PROXY, $proxy);
		 }

		 $result = curl_exec($curl);

		 if (curl_errno($curl)) {

			 $error_msg = curl_error($curl);

			 }

			 if (isset($error_msg)) {
				 throw new Exception($error_msg);
			 }
		 // if(!$result)
		 // {
		 // 	error_log("Internet Connection Failure");
		 // 	return false;
		 // }
		 curl_close($curl);
		 error_log('::::::::::::::::closing CURL connection::::::::ORDERID::::'.$order_id);

		 error_log('::::::::::::::::callAPI ENDED::::::::::::::::::ORDERID::::'.$order_id);
		 return $result;
	}

	function encryptData($plainData, $publicPEMKey)
	{
		$plainData=utf8_encode($plainData);
		$partialEncrypted = '';
		$encryptionOk = openssl_public_encrypt($plainData, $partialEncrypted, $publicPEMKey,OPENSSL_PKCS1_PADDING);
		if(!$encryptionOk){
			throw new Exception("Something went wrong with Encryption");
		}
		return base64_encode($partialEncrypted);
	}

	function encodeString($plainData)
	{
		$plainData=utf8_encode($plainData);
		return base64_encode($plainData);
	}

	function decodeString($encodedData)
	{
		$encodedData = base64_decode($encodedData);
		return utf8_decode($encodedData);
	}

	function decryptData($data, $privatePEMKey)
	{
		$DECRYPT_BLOCK_SIZE = 512;
		$decrypted = '';

		$data = str_split(base64_decode($data), $DECRYPT_BLOCK_SIZE);
		foreach($data as $chunk)
		{
			$partial = '';

			$decryptionOK = openssl_private_decrypt($chunk, $partial, $privatePEMKey, OPENSSL_PKCS1_PADDING);

			if($decryptionOK === false)
			{
				$decrypted = '';
				return $decrypted;
			}
			$decrypted .= $partial;
		}

		return utf8_decode($decrypted);
	}

	function cyber_clean($string)
	{
		$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
		$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
		return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
	}

	function clean_special_chars($string)
	{
		$string = preg_replace('/[^A-Za-z0-9\-]/', ' ', $string);
		return preg_replace('/-+/', '-', $string);
	}

	function TrimString($value,$length)
	{
		$value = substr($value, 0, $length);
		return $value;
	}

	function RemoveBlackListCharacters($str)
	{
		$chr = array("/","sleep","wait","insert","update","delete","$","~","`","'","truncate","drop","alter","modify","--");
		$res = str_replace($chr,"",$str);
		return $res;
	}
	//insert token and mask in table
	function HBLPAYPGW_add_token_db($user_id,$order_id,$token,$mask,$payment_type){
		$admin_settings = WC()->payment_gateways->payment_gateways()['hblpay']->settings;
		$enc_key = $admin_settings['gateway_public_key'];
		$enc_key = str_replace("-----BEGIN PUBLIC KEY-----","-----BEGIN PUBLIC KEY-----\n",$enc_key);
		$enc_key = str_replace("-----END PUBLIC KEY-----","\n-----END PUBLIC KEY-----",$enc_key);
		$encrypted_token=encryptData($token,$enc_key);
		//date_default_timezone_set('Asia/Karachi');
		$date=date_create();
		date_modify($date,"+1 year");
		$datefinal=date_format($date,"Y-m-d");
		$order = wc_get_order( $order_id );
		global $wpdb;
		$wpdb->insert("wp_hblpay_tokenization", array(
		"user_id" => $user_id,
		"email" => $order->get_billing_email(),
		"token" => $encrypted_token,
		"token_expiry" => $datefinal,
		"mask" => $mask,
		"payment_type" => $payment_type,
		"last_trans_date" => date("Y-m-d"),
		"created_at" => date( "Y-m-d h:i:s", time()),
		"updated_at" => date( "Y-m-d h:i:s", time()),
		"created_by" => $user_id,
		"updated_by" => $user_id,
		"is_enabled" => 1,
		"is_deleted" => 0
	));

	return true;
}
//if card is duplicate update token----------MZ
function HBLPAYPGW_update_token_db($user_id,$order_id,$token,$mask){
	$admin_settings = WC()->payment_gateways->payment_gateways()['hblpay']->settings;
	$enc_key = $admin_settings['gateway_public_key'];
	$enc_key = str_replace("-----BEGIN PUBLIC KEY-----","-----BEGIN PUBLIC KEY-----\n",$enc_key);
	$enc_key = str_replace("-----END PUBLIC KEY-----","\n-----END PUBLIC KEY-----",$enc_key);
	$encrypted_token=encryptData($token,$enc_key);
		//date_default_timezone_set('Asia/Karachi');
		$date=date_create();
		date_modify($date,"+1 year");
		$datefinal=date_format($date,"Y-m-d");
		$order = wc_get_order( $order_id );
		global $wpdb;
			$wpdb->update("wp_hblpay_tokenization", array(
			"last_trans_date" => date( "Y-m-d"),
			"updated_at" => date( "Y-m-d h:i:s", time() ),
			"token_expiry" => $datefinal,
			"token" =>$encrypted_token,
			"email" =>$order->get_billing_email(),
			"is_enabled" => '1',
		),
		array("user_id" => $user_id,"mask" => $mask,'is_deleted' => '0')
	 );
return true;
}
//save last tarnsaction of saved cards-----------MZ
function HBLPAYPGW_update_lasttrans_of_saved_card($user_id,$id){
	global $wpdb;
		$encrypted_token=encryptData($token,$enc_key);
		//date_default_timezone_set('Asia/Karachi');
		$date=date_create();
		date_modify($date,"+1 year");
		$datefinal=date_format($date,"Y-m-d");
			$wpdb->update("wp_hblpay_tokenization", array(
			"last_trans_date" => date( "Y-m-d", time() ),
			"updated_at" => date( "Y-m-d h:i:s", time() ),
			"token_expiry" => $datefinal,
		),
		array("user_id" => $user_id,"id" => $id, 'is_deleted' => '0')
	 );
return true;
}

//get saved card-------------MZ
    function HBLPAYPGW_get_card_token($user_id)
    {
			global $wpdb;
			$results = $wpdb->get_results( "SELECT * FROM wp_hblpay_tokenization WHERE user_id = '$user_id' AND is_enabled='1' AND is_deleted='0' AND token_expiry >= NOW()");
			 return $results;
    }
//get details for duplicate cards-------MZ
		function HBLPAYPGW_get_card_details ($user_id,$payment_type,$mask){
			global $wpdb;
		  $posts = $wpdb->get_row("SELECT * FROM wp_hblpay_tokenization WHERE user_id = '$user_id'
		   AND payment_type='$payment_type'  AND mask='$mask' AND is_deleted= '0' ");
			 //error_log("print_rslts ".print_r($posts,1));
				if(empty($posts)){
					return null;
				}
				else{
				return $posts;
				}
		}


}

 ?>
