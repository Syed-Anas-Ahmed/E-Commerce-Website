 <?php
  $file=dirname( dirname( dirname( dirname( __FILE__ )))) . '/wp-load.php';
  require_once( $file );
 $request_id=sanitize_text_field($_REQUEST['ID']);
if (!empty($request_id)) {
  global $wpdb;
  $table='wp_hblpay_tokenization';
  $date=date_create();
  $datefinal=date_format($date,"Y-m-d h:i:sa");
  $wpdb->delete( $table, array( 'id' => $request_id ) );
//   $wpdb->update($table, array(
//   "updated_at" => $datefinal,
//   "is_deleted" => '1',
//   "is_enabled" => '0',
// ),
//   array("id" => $id)
// );
  $url = site_url().'/my-account/card-management';
  wp_redirect( $url );
  exit;
}

 ?>
