<?php


// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

include_once('qpayment-plugin.php');

// drop a custom database table
global $wpdb;
//select * from wp_options where option_value='woocommerce_qisst_pay_settings'

$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}options WHERE option_name = 'woocommerce_qisst_pay_settings'", OBJECT );

$plugin_version = qpayment8911_plugin_version();

$qisstpay_req_1911=json_encode(['qisstpay_merchant_settings'=>$results, 'status' => 'DELETED',"action"=>"deleted","plugin_version"=>$plugin_version]);

// $url         = 'https://qisstpay.com/api/plugin-status-update';
// $args        = [
//     'method'   => 'POST',
//     'timeout'  => 45,
//     'blocking' => true,
//     'headers'  => [
//         'Content-Type'  => 'application/json',
//         'Accept'        => 'application/json',
//     ],
//     'body'     => $qisstpay_req_1911,
// ];
// $response    = wp_remote_post($url, $args);

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
    'body' => $qisstpay_req_1911
];

$response    = wp_remote_post($url, $args);

return json_decode(wp_remote_retrieve_body($response), true);

// // if uninstall.php is not called by WordPress, die
// if (!defined('WP_UNINSTALL_PLUGIN')) {
//     die;
// }

// include_once('qpayment-plugin.php');
 
// // drop a custom database table
// global $wpdb;
// //select * from wp_options where option_value='woocommerce_qisst_pay_settings'

// $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}options WHERE option_name = 'woocommerce_qisst_pay_settings'", OBJECT );

// $plugin_version = plugin_version();

// $ch = curl_init();
// curl_setopt($ch, CURLOPT_URL, "https://sandbox.qisstpay.com/api/plugin-status-update");
// curl_setopt($ch, CURLOPT_POST, 1);// set post data to true
// curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode(['qisstpay_merchant_settings'=>$results,"action"=>"deleted","plugin_version"=>$plugin_version]));   // post data
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// $json = curl_exec($ch);
// curl_close ($ch);    