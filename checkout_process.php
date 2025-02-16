<?php
/*
$Id: checkout_process.php,v 1.1.1.2 2004/03/04 23:37:57 ccwjr Exp $

osCommerce, Open Source E-Commerce Solutions
http://www.oscommerce.com

Copyright (c) 2003 osCommerce

Released under the GNU General Public License
*/


include('includes/application_top.php');

require_once(DIR_FS_FUNCTIONS . 'timezone.php');

include(DIR_FS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_PROCESS);


//错误跳转{
// if the customer is not logged on, redirect them to the login page
if(!tep_session_is_registered('customer_id')) {   
	$navigation->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));   
	tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));  
}
if(!tep_session_is_registered('sendto')) {   
	tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL')); 
}
if( (tep_not_null(MODULE_PAYMENT_INSTALLED)) && (!tep_session_is_registered('payment')) ) { 
	tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL')); 
}
if(isset($cart->cartID) && tep_session_is_registered('cartID') && $cart->cartID != $cartID) {  
	tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}// avoid hack attempts during the checkout procedure by checking the internal cartID

// howard added check guest can not null
if(!tep_not_null(preg_replace('/[[:space:]]+/','',$_SESSION['GuestEngXing0'][0]))){
	$messageStack->add_session('global', db_to_html("顾客名称不能为空！"), 'error');
	tep_redirect(tep_href_link('checkout_info.php', '', 'SSL'));
}
//}

// load selected payment module
require(DIR_FS_CLASSES . 'payment.php');
if ($credit_covers) $payment=''; //ICW added for CREDIT CLASS
$payment_modules = new payment($payment);

// Need to be included before Authorizenet ADC Direct Connection
// load the selected shipping module
require(DIR_FS_CLASSES . 'shipping.php');
$shipping_modules = new shipping($shipping);
require(DIR_FS_CLASSES . 'order.php');

// by lwkai add 2012-06-01 根据订单ID取得行程信息与价格 返回 字符串 以便发邮件
require(DIR_FS_CLASSES . 'order_info_to_mail.php');
// by lwkai end 

$order = new order;
// Howard added for New Gruop Buy check start {
for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
	if(!(int)check_process_for_group_buy((int)$order->products[$i]['id'])){
		$messageStack->add_session('global', db_to_html("您购买的团购行程已经卖完，请选择其它行程！"), 'error');
		tep_redirect(tep_href_link('shopping_cart.php'));
	}
	//transfer-service check
	if($order->products[$i]['is_transfer'] == '1'){
		$info = tep_transfer_decode_info($order->products[$i]['transfer_info']);
		$msg = tep_transfer_validate(intval($order->products[$i]['id']) ,$info);
		if($msg !='') {
			$messageStack->add_session('global', db_to_html($msg), 'error');
			tep_redirect(tep_href_link('shopping_cart.php'));
		}
	}
	//
}
// Howard added for New Gruop Buy check end }

if(!class_exists('order_total')) {
	include_once DIR_FS_CLASSES . 'order_total.php';
	$order_total_modules = new order_total();
}

$order_totals = $order_total_modules->process();


// load the before_process function from the payment modules.
// Authorize.net/QuickCommerce/PlugnPlay processing - this called moved to a later point
// This is maintained for compatiblity with all other modules
// update for incorrect logic - start - 06/09/06 - datazen
/* old code commented out
if (!MODULE_PAYMENT_AUTHORIZENET_STATUS)
{
$payment_modules->before_process();
}
*/
/*
if((MODULE_PAYMENT_AUTHORIZENET_STATUS == 'True') ||
(MODULE_PAYMENT_QUICKCOMMERCE_STATUS =='True') ||
(MODULE_PAYMENT_PLUGNPAY_STATUS =='True')) {
} elseif((MODULE_PAYMENT_PAYPAL_STATUS == 'True') && ($payment == 'paypal')) {
$payment_modules->before_process();
include(DIR_FS_MODULES . 'payment/paypal/catalog/checkout_process.inc.php');
} else {
$payment_modules->before_process();
}
*/
if(((defined('MODULE_PAYMENT_AUTHORIZENET_STATUS') && MODULE_PAYMENT_AUTHORIZENET_STATUS == 'True') && ($_SESSION['payment'] == 'authorizenet')) ||
((defined('MODULE_PAYMENT_CREMERCHANT_AUTHORIZENET_STATUS') && MODULE_PAYMENT_CREMERCHANT_AUTHORIZENET_STATUS == 'True') && ($_SESSION['payment'] == 'CREMerchant_authorizenet')) ||
((defined('MODULE_PAYMENT_CREGATEWAY_STATUS') && MODULE_PAYMENT_CREGATEWAY_STATUS == 'True') && (MODULE_PAYMENT_CREGATEWAY_BRANDED == 'False') && ($_SESSION['payment'] == 'cregateway')) ||
((defined('MODULE_PAYMENT_QUICKCOMMERCE_STATUS') && MODULE_PAYMENT_QUICKCOMMERCE_STATUS =='True') && ($_SESSION['payment'] == 'quickcommerce')) ||
((defined('MODULE_PAYMENT_PLUGNPAY_STATUS') && MODULE_PAYMENT_PLUGNPAY_STATUS =='True')  && ($_SESSION['payment'] == 'plugnpay'))){
	//don't load before process
} elseif((defined('MODULE_PAYMENT_PAYPAL_STATUS') && MODULE_PAYMENT_PAYPAL_STATUS == 'True') && ($_SESSION['payment'] == 'paypal')) {
	$payment_modules->before_process();
	include(DIR_FS_MODULES . 'payment/paypal/catalog/checkout_process.inc.php');
} else {
	$payment_modules->before_process();
}
// update for incorrect logic - end

// BOF: WebMakers.com Added: Downloads Controller {
$customers_name = tep_get_customer_name($customer_id);
if(!tep_not_null($order->customer['email_address'])){
	$order->customer['email_address'] = tep_get_customers_email($customer_id);
	$order->customer['firstname'] = tep_get_customer_name($customer_id);
}

$_customers_ad_click_id = $customers_ad_click_id;
$_customers_advertiser = $customers_advertiser?$customers_advertiser:(isset($_COOKIE['url_from'])?$_COOKIE['url_from']:'');
if($_COOKIE['login_id']){	//客服人员下的单不计广告id，由客人的电脑点击时更新
  $_customers_ad_click_id = 0;
  $_customers_advertiser = '';
}
// die($_customers_advertiser);
$sql_data_array = array('customers_id' => $customer_id,
						//'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                        'customers_name' => $customers_name,
						'customers_company' => $order->customer['company'],
						'customers_street_address' => $order->customer['street_address'],
						'customers_suburb' => $order->customer['suburb'],
						'customers_city' => $order->customer['city'],
						'customers_postcode' => $order->customer['postcode'],
						'customers_state' => $order->customer['state'],
						'customers_country' => $order->customer['country']['title'],
						'customers_telephone' => $order->customer['telephone'],
						'customers_email_address' => $order->customer['email_address'],
						'customers_address_format_id' => $order->customer['format_id'],
						// 'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
						'delivery_company' => $order->delivery['company'],
						'delivery_street_address' => $order->delivery['street_address'],
						'delivery_suburb' => $order->delivery['suburb'],
						'delivery_city' => $order->delivery['city'],
						'delivery_postcode' => $order->delivery['postcode'],
						'delivery_state' => $order->delivery['state'],
						'delivery_country' => $order->delivery['country']['title'],
						'delivery_address_format_id' => (int)$order->delivery['format_id'],
						// 'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
						'billing_company' => $order->billing['company'],
						'billing_street_address' => $order->billing['street_address'],
						'billing_suburb' => $order->billing['suburb'],
						'billing_city' => $order->billing['city'],
						'billing_postcode' => $order->billing['postcode'],
						'billing_state' => $order->billing['state'],
						'billing_country' => $order->billing['country']['title'],
						'billing_address_format_id' => $order->billing['format_id'],
						'payment_method' => html_to_db ($order->info['payment_method']),
						// BOF: Lango Added for print order mod
						'payment_info' => html_to_db ($GLOBALS['payment_info']),
						// EOF: Lango Added for print order mod
						//'cc_type' => $order->info['cc_type'],
						//'cc_owner' => html_to_db($order->info['cc_owner']),
						// 'cc_number' => scs_cc_encrypt($order->info['cc_number']),
						'cc_expires' => $order->info['cc_expires'],
						'customers_advertiser' => $_customers_advertiser,
						'customers_ad_click_id' => (int)$_customers_ad_click_id,
						//echeck fields
						/*
						'accountholder' => $order->info['accountholder'],
						'address' => $order->info['address'],
						'address2' => $order->info['address2'],
						'phone' => $order->info['phone'],
						'bank' => $order->info['bank'],
						'bankcity' => $order->info['bankcity'],
						'bankphone' => $order->info['bankphone'],
						'checknumber' => $order->info['checknumber'],
						'accountnumber' => $order->info['accountnumber'],
						'routingnumber' => $order->info['routingnumber'],
						*/
						//echeck fields
						// 'cc_cvv' => $order->info['cc_cvv'],
						'order_cost' => $order->info['order_cost'],
						'date_purchased' => 'now()',
						'last_modified' => 'now()',
						'orders_status' => $order->info['order_status'],
						//             'orders_status' => DEFAULT_ORDERS_STATUS_ID,
						// the following field has been moved from orders to order_status_history
						// 'comments' => $order->info['comments'],
						'currency' => $order->info['currency'],
						'currency_value' => $order->info['currency_value'],
						'us_to_cny_rate' => get_value_usd_to_cny(),
						'is_top' => '1');

//amit added to tracke cc name as billing start

if(defined(MODULE_PAYMENT_AUTHORIZENET_STATUS) && MODULE_PAYMENT_AUTHORIZENET_STATUS == 'True' && $payment == 'authorizenet' && $order->info['cc_owner'] != '') {
	$customer_billing_sql_data = array('delivery_name' => html_to_db($order->info['cc_owner']),
									   'billing_name' => html_to_db($order->info['cc_owner']));

}else{
	$customer_billing_sql_data = array('delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
									   'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname']);
}


//amit added to track cc name as billing name
$sql_data_array = array_merge((array)$sql_data_array, (array)$customer_billing_sql_data);
// EOF: WebMakers.com Added: Downloads Controller }
//tep_db_perform(TABLE_ORDERS, html_to_db ($sql_data_array));
//print_r($_SESSION);
//print_r($sql_data_array);exit;
tep_db_perform(TABLE_ORDERS, $sql_data_array);
$insert_id = tep_db_insert_id();
//记录客服ID到订单
$tmp_a='';
if(!$Admin->parent_orders_id) $tmp_a=servers_sales_track::add_login_id_to_order($insert_id);

// if($Admin->parent_orders_id)
// 	return;

//insert into flight and eticket
//结伴同游
$payment_name = $order->info['payment_method']; //结伴同游下单人付款方式
$i_need_pay = 0;
$o_t_c_ids = '';
$have_travel_companion_for_order = false;	//判断当前订单有没有结伴同游的行程
//echo 'PPPPPPP'."\n";
//print_r($order->products);
//echo 'PPPPPPP'."\n";
//exit;

//quickcommerce
if(defined(MODULE_PAYMENT_QUICKCOMMERCE_STATUS) && MODULE_PAYMENT_QUICKCOMMERCE_STATUS) {
	include(DIR_FS_MODULES . 'quickcommerce_direct.php');
	$payment_modules->before_process();
}
//***********************************
if(defined(MODULE_PAYMENT_PLUGNPAY_STATUS) && MODULE_PAYMENT_PLUGNPAY_STATUS) {
	include(DIR_FS_MODULES . 'plugnpay_api.php');
	$payment_modules->before_process();
}



$total_credit_applied = 0;
for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
	// this is for credit update-start
	if($order_totals[$i]['code'] == 'ot_easy_discount' && $order_totals[$i]['title'] == TITLE_CREDIT_APPLIED){
		$customer_current_credit_bal = tep_get_customer_credits_balance($customer_id);
		$customer_new_credit_bal = $customer_current_credit_bal - $order_totals[$i]['value'];

		tep_db_query("update ".TABLE_CUSTOMERS." set customers_credit_issued_amt = '".$customer_new_credit_bal."' where customers_id = '".(int)$customer_id."'");
		$sql_data_credits_array = array('customers_id' => (int)$customer_id,
										'orders_id' => $insert_id,
										'credit_bal' => ($order_totals[$i]['value']*(-1)),
										'credit_comment' => 'Credit Applied',
										'date_added' => 'now()'
										);
		tep_db_perform(TABLE_CUSTOMERS_CREDITS, html_to_db($sql_data_credits_array));
		$total_credit_applied = $total_credit_applied + $order_totals[$i]['value'];
	}
	//this is for credit update-end

	if($order_totals[$i]['code'] == 'ot_tax'){
		$display_email_tax = $order_totals[$i]['text'];
	}
	if($order_totals[$i]['code'] == 'ot_coupon'){
		//优惠券，不再重复显示
		//$display_discount_coupons = $order_totals[$i]['text'];
		//$display_discount_coupons_title = $order_totals[$i]['title'];
		// 凡是用了优惠券的订单都不可以赠送积分！
		$not_points_toadd = true;
	}

	$sql_data_array = array('orders_id' => $insert_id,
							'title' => $order_totals[$i]['title'],
							'text' => $order_totals[$i]['text'],
							'value' => $order_totals[$i]['value'],
							'class' => $order_totals[$i]['code'],
							'sort_order' => $order_totals[$i]['sort_order']);
	tep_db_perform(TABLE_ORDERS_TOTAL, html_to_db ($sql_data_array));
}

#### Points/Rewards Module V2.1rc2a balance customer points BOF #### {
if ((USE_POINTS_SYSTEM == 'true') && (USE_REDEEM_SYSTEM == 'true')) {
	// customer pending points added
	if ($order->info['total'] > 0 && $not_points_toadd !== true) {
		$points_toadd = get_points_toadd($order);
		$points_comment = 'TEXT_DEFAULT_COMMENT';
		$points_type = 'SP';
		if ((get_redemption_awards($customer_shopping_points_spending) == true) && ($points_toadd >0)) {
			tep_add_pending_points($customer_id, $insert_id, $points_toadd, $points_comment, $points_type);
		}
	}
	if ($customer_shopping_points_spending) {	//兑换积分
		tep_redeemed_points($customer_id, $insert_id, $customer_shopping_points_spending);
	}
}
#### Points/Rewards Module V2.1rc2a balance customer points EOF ####}*/

$customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
//credit applied default status first set - start
if($total_credit_applied > 0){
	$credit_applied_comment = '$'.number_format($total_credit_applied, 2).' of '.STORE_NAME.' Credit has been applied to this order. For details please view  customer\'s credit order history.';
	$sql_data_array = array('orders_id' => $insert_id,
							'orders_status_id' => '100098', //Credit Applied
							'date_added' => 'now()',
							'customer_notified' => $customer_notification,
							'updated_by' => CUSTOMER_SERVICE_SYSTEM_ACCOUNT_ID,
							'comments' => $credit_applied_comment);
	tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

	$settlement_final_date = GetTime(5, false, true);  //timezone , settlemetnt_time formate, daytimesaving  -- PST TIME
	$settlement_time = GetTime(5, true, true);
	if((int)$settlement_time >= 0 && (int)$settlement_time <= 170000){
		$settlement_final_date = $settlement_final_date;
	}else{
		$settlement_final_date = date('Y-m-d H:i:s', strtotime($settlement_final_date . ' + 1 day'));
	}

	$reference_comments = NEW_PAYMENT_METHOD_T4F_CREDIT;

	$sql_data_settlement_array = array(
								'orders_id' =>  (int)$insert_id,
								'order_value' => $total_credit_applied,
								'orders_payment_method' => NEW_PAYMENT_METHOD_T4F_CREDIT,
								'reference_comments' => $reference_comments,
								'settlement_date' => $settlement_final_date,
								'date_added' => GetTime(5, false, true),
								'updated_by' => CUSTOMER_SERVICE_SYSTEM_ACCOUNT_ID
	);
	tep_db_perform(TABLE_ORDERS_SETTLEMENT_INFORMATION, $sql_data_settlement_array);
}
//credit applied default status first set - end
if(defined(MODULE_PAYMENT_AUTHORIZENET_STATUS) && MODULE_PAYMENT_AUTHORIZENET_STATUS == 'True' && $payment == 'authorizenet') {
	
}else{
	$sql_data_array = array('orders_id' => $insert_id,
							'orders_status_id' => $order->info['order_status'],
							'date_added' => 'now()',
							'customer_notified' => $customer_notification,
							'updated_by' => CUSTOMER_SERVICE_SYSTEM_ACCOUNT_ID,
							'comments' => $order->info['comments']);

	if(tep_not_null($_SESSION['SingleName'])){
		if(tep_not_null($sql_data_array['comments'])){
			$sql_data_array['comments'] .= "\n";
		}
		$sql_data_array['comments'] .= db_to_html('同意单人部分配房！');
	}
	tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, html_to_db ($sql_data_array));
}


// initialized for the email confirmation
$products_ordered = '';
$subtotal = 0;
$total_tax = 0;
//insert into flight and eticket
$payment_name = $order->info['payment_method']; //travel companion
$i_need_pay = 0;
$o_t_c_ids = '';
//insert into flight and eticket
$total_array_insert_id_from_i = array();

for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
	// Stock Update - Joao Correia {
	if (STOCK_LIMITED == 'true') {
		if (DOWNLOAD_ENABLED == 'true') {
			$stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
                            FROM " . TABLE_PRODUCTS . " p,
                            " . TABLE_PRODUCTS_ATTRIBUTES . " pa,
                            " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                             WHERE p.products_id = '" . tep_get_prid($order->products[$i]['id']) . "'
                             and p.products_id=pa.products_id
                             and pad.products_attributes_id=pa.products_attributes_id ";

			// Will work with only one option for downloadable products
			// otherwise, we have to build the query dynamically with a loop
			$products_attributes = $order->products[$i]['attributes'];
			if (is_array($products_attributes)) {
				$stock_query_raw .= " AND pa.options_id = '" . $products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . $products_attributes[0]['value_id'] . "'";
			}
			$stock_query = tep_db_query($stock_query_raw);
		} else {
			$stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
		}
		if (tep_db_num_rows($stock_query) > 0) {
			$stock_values = tep_db_fetch_array($stock_query);
			// do not decrement quantities if products_attributes_filename exists
			if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
				$stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
			} else {
				$stock_left = $stock_values['products_quantity'];
			}
			tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $stock_left . "' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
			if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
				tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
			}
			MCache::update_product(tep_get_prid($order->products[$i]['id']));//MCache update
		}
	} //}

	// Update products_ordered (for bestsellers list)
	tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
	MCache::update_product(tep_get_prid($order->products[$i]['id']));//MCache update
	$get_currency_value = tep_db_query("select value from ".TABLE_CURRENCIES." where code = '".$order->products[$i]['operate_currency_code']."'");
	if($row_currency_value = tep_db_fetch_array($get_currency_value)){
		$operate_currency_exchange_value = $row_currency_value['value'];
	}else{
		$operate_currency_exchange_value = '';
	}
	$products_id=tep_get_prid($order->products[$i]['id']);
	$product_date_day_month = '';
	if($order->products[$i]['dateattributes'][0] != ''){
		$product_date_day_month = @convert_datetime($order->products[$i]['dateattributes'][0]).' ';
	}

	$productsId = tep_get_prid($order->products[$i]['id']);
	$new_group_buy_type = get_specials_type($order->products[$i]['is_new_group_buy'], $productsId );

	$sql_data_array = array('orders_id' => $insert_id,
							'products_id' => $productsId,
							'products_model' => $order->products[$i]['model'],
							'products_name' => $order->products[$i]['name'],
							'products_price' => $order->products[$i]['price'],
							'group_buy_discount' => $order->products[$i]['group_buy_discount'],
							'final_price' => $order->products[$i]['final_price'],
							'final_price_cost' => $order->products[$i]['final_price_cost'],
							'products_tax' => $order->products[$i]['tax'],
							'products_quantity' => $order->products[$i]['qty'],
							'products_departure_date' => $order->products[$i]['dateattributes'][0],
							'products_departure_time' => $product_date_day_month.$order->products[$i]['dateattributes'][1],
							'products_departure_location' => $order->products[$i]['dateattributes'][2],
							'products_departure_location_sent_to_provider_confirm' => '1',
							'products_room_price' => $order->products[$i]['roomattributes'][0],
							'products_room_info' => $order->products[$i]['roomattributes'][1],
							'total_room_adult_child_info' => $order->products[$i]['roomattributes'][3],
							'operate_currency_exchange_code' => $order->products[$i]['operate_currency_code'],
							'operate_currency_exchange_value' => $operate_currency_exchange_value,
							'is_diy_tours_book' => $order->products[$i]['is_diy_tours_book'],
							'hotel_pickup_info' => html_to_db($_SESSION['is_hotel_pickup_info_'.$i.'_'.(int)$order->products[$i]['id']]),
							'is_new_group_buy' => $order->products[$i]['is_new_group_buy'],
							'new_group_buy_type' => $new_group_buy_type,
							'no_sel_date_for_group_buy' => $order->products[$i]['no_sel_date_for_group_buy'],
							'hotel_extension_info' => $order->products[$i]['hotel_extension_info'],
							'is_hotel' => tep_check_product_is_hotel($order->products[$i]['id']),
							'extra_values' => $order->products[$i]['extra_values'],
							'is_transfer'=> $order->products[$i]['is_transfer'],
							'products_price_last_modified'=> tep_db_get_field_value('products_price_last_modified','products',' products_id="'.(int)$productsId.'" '),
							'add_date' => date('Y-m-d H:i:s')
							);
	//tep_db_perform(TABLE_ORDERS_PRODUCTS, html_to_db ($sql_data_array));
	tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
	$order_products_id = tep_db_insert_id();
	$total_array_insert_id_from_i[$i] = $order_products_id; //store insert ids in array from i loop
	//transfer-service {
	//写入transfer_info到数据库
	if($order->products[$i]['is_transfer'] == '1'){
		//读取自购物车的数据已经是gb2312编码
		$info = tep_transfer_decode_info( $order->products[$i]['transfer_info']);
		foreach($info['routes'] as $route){
			if(is_numeric($route['pickup_id']) && is_numeric($route['dropoff_id']) && intval($route['guest_total']) > 0) {
				$flight_arrival_time  = date('Y-m-d H:i:s' , strtotime($route['flight_arrival_time']));
				$flight_pick_time  = date('Y-m-d H:i:s' , strtotime($route['flight_pick_time']));

				$sql_data_array = array(
										'orders_products_id' => $order_products_id,
										'orders_id' => $insert_id,
										'flight_number'=>$route['flight_number'],
										'flight_departure'=>$route['flight_departure'],
										'flight_arrival_time'=>$flight_arrival_time,
										'pickup_id'=>$route['pickup_id'],
										'pickup_address'=>$route['pickup_address'],
										'pickup_zipcode'=>$route['pickup_zipcode'],
										'dropoff_id'=>$route['dropoff_id'],
										'dropoff_address'=>$route['dropoff_address'],
										'dropoff_zipcode'=>$route['dropoff_zipcode'],
										'guest_total'=>$route['guest_total'],
										'baggage_total'=>$route['baggage_total'],
										'comment'=>$route['comment'],
										'created_date'=>date('Y-m-d H:i:s' ,time()),
										'flight_pick_time'=>$flight_pick_time
										);
				tep_db_perform(TABLE_ORDERS_PRODUCTS_TRANSFER, $sql_data_array);
			}
		}
	}
	//}

	//写航班信息 start{
	$arrival_date = "";	$departure_date = "";
	foreach($_SESSION as $key=>$val)
	{
		if(strstr($key,'arrival_date'.$i)) $arrival_date = $val;
		if(strstr($key,'departure_date'.$i)) $departure_date = $val;
	}

	$sql_data_array = array('orders_id' => $insert_id,
							'products_id' => $order->products[$i]['id'],
							'airline_name' => $order->info['airline_name'][$i],
							'flight_no' => $order->info['flight_no'][$i],
							'airline_name_departure' => $order->info['airline_name_departure'][$i],
							'flight_no_departure' => $order->info['flight_no_departure'][$i],
							'airport_name' => $order->info['airport_name'][$i],
							'airport_name_departure' => $order->info['airport_name_departure'][$i],
							'arrival_date' => tep_db_input($arrival_date),
							'arrival_time' => $order->info['arrival_time'][$i],
							'departure_date' => tep_db_input($departure_date),
							'departure_time' => $order->info['departure_time'][$i],
							'orders_products_id' => $order_products_id);
							//print_r($sql_data_array);
							//exit();
	tep_db_perform(TABLE_ORDERS_PRODUCTS_FLIGHT, html_to_db($sql_data_array));
	$orders_flight_id = tep_db_insert_id();
	
	//记录航班更新历史
	tep_add_orders_product_flight_history(html_to_db($sql_data_array),$order_products_id,0,$customer_id,1);
	//写航班信息 end}
	if($order->products[$i]['dateattributes'][0] !=''){
		$display_departure_day_str = @convert_datetime($order->products[$i]['dateattributes'][0]);
		$depature_full_address =  $order->products[$i]['dateattributes'][0].' '.$display_departure_day_str.' '.$order->products[$i]['dateattributes'][1].' &nbsp; '.$order->products[$i]['dateattributes'][2];
	}else{
		$depature_full_address =  $order->products[$i]['dateattributes'][0].' '.$order->products[$i]['dateattributes'][1].' &nbsp; '.$order->products[$i]['dateattributes'][2];
	}
	//写上车地址历史记录 start{
	//if(tep_not_null($order->products[$i]['dateattributes'][2])){

		tep_add_departure_location_history($order_products_id, $depature_full_address);
	//}
	//写上车地址历史记录 end}

	$h=0;
	$guest_name='';
	$guest_body_weight='';
	$guest_body_height='';
	$is_travel_companion = false;
	$GuestEmailArray = array();
	$PayerMeArray = array();
	//amit added add extra customer information start
	$guest_genger_str = '';
	$need_add_extra_checkout_fields = false;
	if(preg_match("/,".(int)$order->products[$i]['id'].",/i", ",".TXT_ADD_EXTRA_FIELDS_CHECKOUT_IDS.",") || in_array($order->products[$i]['agency_id'],explode(',','12,48')) || $order->products[$i]['is_birth_info'] == '1') {
		$need_add_extra_checkout_fields = true;
	}
	//amit added add extra customer information end

	foreach($_SESSION as $key=>$val){
		if(strstr($key,'GuestEngXing')){
			if($_SESSION['GuestEngXing'.$h][$i] != ''){
				//howard added 结伴同游
				if($_SESSION['GuestEmail'.$h][$i] != ''){
					$is_travel_companion = true;
					$have_travel_companion_for_order = true;
				}
				//howard added 结伴同游
				$guest_name_english = "";
				if(strlen($_SESSION['GuestEngXing'.$h][$i])>0){
					$guest_name_english = $_SESSION['GuestEngXing'.$h][$i];
				}
				if(strlen($_SESSION['GuestEngName'.$h][$i])>0){
					$guest_name_english .= ','.$_SESSION['GuestEngName'.$h][$i];
				}
				$guest_name_english = '['.$guest_name_english.']';

				$guest_child_age_input = '';
				if($_SESSION['guestchildage'.$h][$i] != ''){
					$guest_child_age_input = '||'.$_SESSION['guestchildage'.$h][$i];
				}else if($need_add_extra_checkout_fields == true && $_SESSION['guestdob'.$h][$i] != ''){
					$guest_child_age_input = '||'.$_SESSION['guestdob'.$h][$i];
				}
				$single_gender ='';
				if($_SESSION['guestname'.$h][$i]==$_SESSION['SingleName'][$i] && tep_not_null($_SESSION['SingleName'][$i]) ){
					$tmp_g = tep_not_null($_SESSION['SingleGender'][$i]) ? $_SESSION['SingleGender'][$i] : "m";
					$single_gender ='('.$tmp_g.')';
				}
				//如果中文名为空就复制英文的姓名到中文名
				if(!tep_not_null(str_replace(' ','',$_SESSION['guestname'.$h][$i]))){
					$_SESSION['guestname'.$h][$i] = $_SESSION['GuestEngXing'.$h][$i];
					$_SESSION['guestsurname'.$h][$i] = $_SESSION['GuestEngName'.$h][$i];
				}
				if(!tep_not_null(str_replace(' ','',$_SESSION['GuestEngXing'.$h][$i]))){
					$messageStack->add_session('global', db_to_html("顾客名称填写不完整！"), 'error');
					tep_redirect(tep_href_link('checkout_info.php', '', 'SSL'));
				}

				$guest_name .= $_SESSION['guestname'.$h][$i].' '.$_SESSION['guestsurname'.$h][$i].' '.$guest_name_english.$guest_child_age_input.$single_gender."<::>";

				//结伴同游
				if($is_travel_companion == true){
					$GuestEmailArray[$h]['mail'] = $_SESSION['GuestEmail'.$h][$i];
					$GuestEmailArray[$h]['name'] = $_SESSION['guestname'.$h][$i].' '.$_SESSION['guestsurname'.$h][$i].' '.$guest_name_english.$guest_child_age_input;

					$PayerMeArray[$h] = $_SESSION['PayerMe'.$h][$i];
				}
			}

			if($_SESSION['guestbodyweight'.$h][$i]  != ''){
				$guest_body_weight .= $_SESSION['guestbodyweight'.$h][$i]." ".$_SESSION['guestweighttype'.$h][$i]."<::>";
			}

			if($_SESSION['guestbodyheight'.$h][$i]  != '' && $need_add_extra_checkout_fields_height == true) {
				$guest_body_height .= stripslashes($_SESSION['guestbodyheight'.$h][$i])."<::>";
			}
			if($_SESSION['guestgender'.$h][$i]  != '' && ($need_add_extra_checkout_fields == true || $order->products[$i]['is_gender_info'] == '1')){
				$guest_genger_str .= $_SESSION['guestgender'.$h][$i]."<::>";
			}
			$h++;
		}
	}

	
	//替换“可不填”为“未知”
	$guest_name = str_replace(JS_MAY_NOT_ENTER_TEXT,JS_UNKNOWN_STRING,$guest_name);

	$sql_data_array = array('orders_id' => $insert_id,
							'products_id' => (int)$order->products[$i]['id'],
							'guest_name' => tep_db_input($guest_name),
							'guest_body_weight' => tep_db_input($guest_body_weight),
							'guest_gender' => tep_db_input($guest_genger_str),
							'guest_body_height' => tep_db_input($guest_body_height),
							'depature_full_address' => db_to_html(tep_db_input($depature_full_address)),
							'orders_products_id' => $order_products_id,
							'agree_single_occupancy_pair_up'=> (int)$cart->contents[(int)$order->products[$i]['id']]['roomattributes'][6]
							);
	tep_db_perform(TABLE_ORDERS_PRODUCTS_ETICKET, html_to_db ($sql_data_array));
	$orders_eticket_id = tep_db_insert_id();

	//结伴同游
	if($is_travel_companion==true && (int)count($GuestEmailArray)){
		foreach($GuestEmailArray as $key => $val){
			$date_of_birth='';
			$is_child='false';
			$guest_long_name = $val['name'];
			$guest_long_name = str_replace(JS_MAY_NOT_ENTER_TEXT,JS_UNKNOWN_STRING,$guest_long_name);
			if(strstr($val['name'],'||')){
				$tmp_array = explode('||',$val['name']);
				$guest_long_name = $tmp_array[0];
				$date_of_birth = $tmp_array[1];
				if(tep_not_null($date_of_birth)){
					$is_child='true';
				}
			}
			$payables = str_replace(',','',$order->products[$i]['adult_average']);
			if($is_child=='true'){ $payables = str_replace(',','',$order->products[$i]['child_average']); }
			//谁付款
			$payment_customers_id = 0;
			if((int)$PayerMeArray[$key]){	$payment_customers_id = $customer_id;	}

			if(form_email_get_customers_id($val['mail'])==$customer_id || $payment_customers_id == $customer_id){
				//added to apply 2% for specific payment method
				if($payment =='banktransfer' || $payment =='transfer'  || $payment =='moneyorder' || $payment =='cashdeposit' ){
					$discount_per_set_for_pay_method = 0;//用线下支付方式优惠百分多少
					$payables = $payables - (($payables * $discount_per_set_for_pay_method) / 100);
				}
				//added to apply 2% for specific payment method
				$i_need_pay += $payables;	//下单人需付的款 如果使用积分则扣除积分或优惠券，目前未作处理，如果电子邮件为空者则下单人需要负责帮其付款
			}
			//设置订单最后付款期限
			$max_day_num = (int)TRAVEL_COMPANION_MAX_PAY_DAY;
			$expired_date = date("Y-m-d H:i:s", strtotime('+'.$max_day_num.' day'));
			$use_working_date = TRAVEL_COMPANION_MAX_PAY_DAY_USE_WORKING_DATE;
			if(strtolower($use_working_date)=='true'){
				$expired_date = get_date_working_date(date('Y-m-d H:i:s'),$max_day_num);
			}

			$customers_id = form_email_get_customers_id($val['mail']);

			$sql_data_array = array('orders_id' => $insert_id,
									'products_id' => $order->products[$i]['id'],
									'customers_id' => $customers_id,
									'guest_name' => tep_db_input($guest_long_name),
									'is_child' => $is_child,
									'date_of_birth' => tep_db_input($date_of_birth),
									'payables' => $payables,
									'orders_travel_companion_status' => '0',
									'payment_customers_id' => $payment_customers_id,
									'last_modified' => date('Y-m-d H:i:s'),
									'expired_date' => $expired_date,
									'orders_products_id' => $order_products_id
									
									);
			if((int)$customers_id == (int)$customer_id){	//加入下单人的付款方式
				$payment_name = strip_tags($payment_name);
				$payment_name = str_replace('&nbsp;','',$payment_name);
				$sql_data_array['payment_name'] = $payment_name;
				$sql_data_array['payment'] = $payment;
			}
			tep_db_perform(TABLE_ORDERS_TRAVEL_COMPANION, html_to_db ($sql_data_array));
			$o_t_c_id = tep_db_insert_id();
			if(form_email_get_customers_id($val['mail'])==$customer_id || $payment_customers_id == $customer_id){
				$o_t_c_ids .= $o_t_c_id.',';
				$orders_travel_companion_ids_str .= '&orders_travel_companion_ids%5B%5D='.$o_t_c_id;
			}
			if(!strstr($to_email_address,$val['mail']) && form_email_get_customers_id($val['mail'])!=$customer_id){
				$to_email_address .= $val['mail'].',';
			}
		}
		//给结伴同游成员发邮件
		$to_email_address = substr($to_email_address,0,strlen($to_email_address)-1);
		//if(strlen($to_email_address)>1){
			
			/*  by lwkai 2012-06-19 注释开始 {  发邮件转移动 934
			 
			$email_subject = db_to_html("走四方结伴同游--请支付费用！订单号：".(int)$insert_id."日期:".date('YmdHis')." ");
			$email_text = db_to_html('尊敬的顾客您好：')."\n\n";
			$to_name = $to_email_address;
			
			$links = tep_href_link('orders_travel_companion_info.php','order_id='.(int)$insert_id,'SSL');
			$links = str_replace('/admin/','/',$links);
			
			$email_text .= db_to_html('您参与的结伴同游，发起人已下了订单，请打开下面的连接支付订单款项，走四方在此订单完全支付成功后会尽快确认订单！') . "\n";
			$email_text .= db_to_html('<a href="'.$links.'" target="_blank">' . $links . '</a>注：如果点击打不开连接，请复制该地址到浏览器地址栏打开。' . "\n\n");
			// 结伴同游 给申请结伴人 发送已下单邮件  by  lwkai 2012-06-01 add start {
			$s_mail = new c_order_info_to_mail($insert_id);
			$email_text .= db_to_html($s_mail->getString());
			unset($s_mail);
			// 结伴同游 给申请结伴人 发送已下单邮件  by  lwkai 2012-06-01 add end } 
			/ *
			if($currency == 'CNY'){ $email_text .='<b>'.TEXT_RMB_CHECK_OUT_MSN.'</b><br>\n';} // 添加RMB提示语
			$email_text .= db_to_html("如果您已经完成付款请忽略此邮件，如果您还未付款，请尽快去确认付款，以免影响大家的行程，谢谢！\n");
			$email_text .= "<b>".JIEBANG_CART_NOTE_MSN."</b>\n";
			* /			
			$email_text .= db_to_html("此邮件为系统自动发出，请勿直接回复！\n\n");
			$email_text .= db_to_html(CONFORMATION_EMAIL_FOOTER)."\n\n";

			$from_email_name = STORE_OWNER;
			//$from_email_address = STORE_OWNER_EMAIL_ADDRESS;
			$from_email_address = 'automail@usitrip.com';

			$var_num = (int)count($_SESSION['need_send_email']);
			$_SESSION['need_send_email'][$var_num]['to_name'] = $to_name;
			$_SESSION['need_send_email'][$var_num]['to_email_address'] = $to_email_address;
			$_SESSION['need_send_email'][$var_num]['email_subject'] = $email_subject;
			$_SESSION['need_send_email'][$var_num]['email_text'] = $email_text;
			$_SESSION['need_send_email'][$var_num]['from_email_name'] = $from_email_name;
			$_SESSION['need_send_email'][$var_num]['from_email_address'] = $from_email_address;
			$_SESSION['need_send_email'][$var_num]['action_type'] = 'true';
			//echo $to_email_address;
			//exit;
			// by lwkai 2012-06-19 注释结束 }  */
		//}
	}else{
		$i_need_pay += $order->products[$i]['final_price'];
	}

	//更新products_remaining_seats
	//查询剩余座位
	$sql = tep_db_query('SELECT remaining_seats_num FROM `products_remaining_seats` WHERE products_id ="'.tep_db_prepare_input($order->products[$i]['id']).'" AND departure_date="'.$order->products[$i]['dateattributes'][0].'"');
	if((int)tep_db_num_rows($sql)>'0'){
		$row = tep_db_fetch_array($sql);
		$now_seats = $row['remaining_seats_num']-$order->products[$i]['roomattributes'][2];
		tep_db_query('UPDATE products_remaining_seats SET `remaining_seats_num` ="'.$now_seats.'",`update_date`=now() WHERE products_id="'.tep_db_prepare_input($order->products[$i]['id']).'" AND departure_date="'.$order->products[$i]['dateattributes'][0].'"');
	}
	//更新剩余座位结束

	//howard added updated travel companion orders start
	tep_update_travel_companion_orders($customer_id, 0, tep_get_prid($order->products[$i]['id']), $insert_id );
	//howard added updated travel companion orders end

	//howard added if date $order->products[$i]['dateattributes'][0] <= today send mail to howard.zhou@usitrip.com
	if(check_date(substr($order->products[$i]['dateattributes'][0],0,10))==false){
		$error_conten = "订单号orders_id: $insert_id ，产品订单号orders_products_id: $order_products_id ，数据库表orders_products。\n";
		$error_conten .= "错误的出发日期:".$order->products[$i]['dateattributes'][0];
		write_error_log($error_conten);
	}

	//amit added for cost cal history
	$sql_data_array_original_insert = array(
	'orders_products_id' => $order_products_id,
	'products_model' => $order->products[$i]['model'],
	'products_name' => $order->products[$i]['name'],
	'retail' => $order->products[$i]['final_price'],
	'cost' => $order->products[$i]['final_price_cost'],
	'last_updated_date' => 'now()'
	);
	tep_db_perform(TABLE_ORDERS_PRODUCTS_UPDATE_HISTORY, $sql_data_array_original_insert);
	//amit added for cost cal history

	$order_total_modules->update_credit_account($i);//ICW ADDED FOR CREDIT CLASS SYSTEM

	//------insert customer choosen option to order--------
	$attributes_exist = '0';
	$products_ordered_attributes = '';
	// 取得产品的扩展价格
	$products_ordered_attributes_price = 0;
	if (isset($order->products[$i]['attributes'])) {
		$attributes_exist = '1';
		for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
			if (DOWNLOAD_ENABLED == 'true') {
				$attributes_query = "select povtpo.products_options_values_id, popt.products_options_id, popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
                               from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa,
                                " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad, " .TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " povtpo
                                where pa.products_id = '" . $order->products[$i]['id'] . "'
                                and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                and popt.products_options_id = pa.options_id
                                and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                and pa.products_attributes_id = pad.products_attributes_id
                                and povtpo.products_options_id = pa.options_id
                                and popt.language_id = '" . $languages_id . "'
                                and poval.language_id = '" . $languages_id . "'";
				$attributes = tep_db_query($attributes_query);
			} else {
				$attributes = tep_db_query("select pa.options_id, popt.products_options_id, povtpo.products_options_values_id  from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " .TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " povtpo
          where pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
          and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
          and popt.products_options_id = pa.options_id
          and povtpo.products_options_id = pa.options_id ");

			}
			$attributes_values = tep_db_fetch_array($attributes);

			$sql_data_array = array('orders_id' => $insert_id,
									'orders_products_id' => $order_products_id,
									'products_options' => $order->products[$i]['attributes'][$j]['option'],
									'products_options_id' => $order->products[$i]['attributes'][$j]['option_id'], //$attributes_values['products_options_id'],
									'products_options_values' => $order->products[$i]['attributes'][$j]['value'],
									'products_options_values_id' => $order->products[$i]['attributes'][$j]['value_id'], //$attributes_values['products_options_values_id'],
									'options_values_price' => $order->products[$i]['attributes'][$j]['price'],
									'options_values_price_cost' => $order->products[$i]['attributes'][$j]['price_cost'],
									'price_prefix' => $order->products[$i]['attributes'][$j]['prefix']);
			//print_r($sql_data_array);exit;

			//tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, html_to_db ($sql_data_array));
			tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

			if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
				$sql_data_array = array('orders_id' => $insert_id,
										'orders_products_id' => $order_products_id,
										'orders_products_filename' => $attributes_values['products_attributes_filename'],
										'download_maxdays' => $attributes_values['products_attributes_maxdays'],
										'download_count' => $attributes_values['products_attributes_maxcount']);
										tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, html_to_db ($sql_data_array));
			}
			
			$temp_s = $order->products[$i]['attributes'][$j]['option'] . ' ' . $order->products[$i]['attributes'][$j]['value'] . ' ' . $order->products[$i]['attributes'][$j]['prefix'] . ' ' . $currencies->display_price($order->products[$i]['attributes'][$j]['price'], tep_get_tax_rate($products[$i]['tax_class_id']), 1);
			$products_ordered_attributes_price = $products_ordered_attributes_price + $order->products[$i]['attributes'][$j]['price'];
			$temp_s = trim($temp_s);
			if (tep_not_null($temp_s) == true) {
				$products_ordered_attributes .= '　　' . $temp_s . "\n";
			}
			
		}
		
	}
	$products_ordered_attributes = trim($products_ordered_attributes,"\n");
	//------insert customer choosen option eof ----
	//Hotel Extension - start  hotel-extension {
	//echo "<pre>" ;print_r($order->products[$i]);
	if(tep_not_null($order->products[$i]['hotel_extension_info']) && $order->products[$i]['is_hotel'] == 1){
		$hotel_extension_info = explode('|=|', $order->products[$i]['hotel_extension_info']);
		$hotel_attribute = getProductAttribute($order->products[$i]['attributes'],HOTEL_EXT_ATTRIBUTE_OPTION_ID);
		if($hotel_attribute =='2'){
			$hotel_checkout_date = tep_get_date_db($hotel_extension_info[7]);
		}else{
			$hotel_checkout_date = tep_get_date_db($hotel_extension_info[1]);
		}
		tep_db_query("update ".TABLE_ORDERS_PRODUCTS." set hotel_checkout_date='".$hotel_checkout_date."', is_early = '".$hotel_attribute."' where orders_products_id = '".$order_products_id."'");
	}
	//}

	$total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
	$total_tax += tep_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
	$total_cost += $total_products_price;

	$displayattributesinmail = str_replace("<br>","\n　　",$order->products[$i]['roomattributes'][1]);
	$displayattributesinmail = str_replace('</br>',"\n　　",$displayattributesinmail);
	$displayattributesinmail = trim($displayattributesinmail,"\n");
	$displayattributesinmail = str_replace('Total of room','Subtotal',$displayattributesinmail);
	/*$displayattributesinmail = str_replace('#','Number',$displayattributesinmail);*/
	$displayattributesinmail = str_replace('adults','Adults',$displayattributesinmail);
	$displayattributesinmail = str_replace('childs','Children',$displayattributesinmail);
	$displayattributesinmail = str_replace('room','Room',$displayattributesinmail);

	$displayattributesinmail = trim($displayattributesinmail,"\n");
	//$EMAIL_TEXT_DEPARTURE_TIME_AND_LOCATION = EMAIL_TEXT_DEPARTURE_TIME_AND_LOCATION;
	$EMAIL_TEXT_DEPARTURE_TIME_AND_LOCATION = db_to_html('　　出发地点');
	// Show start
	//if((int)is_show((int)$order->products[$i]['id'])){ // 原来的SHOW团判断
	if ($order->products[$i]['products_type'] == 7){
		$EMAIL_TEXT_DEPARTURE_TIME_AND_LOCATION = db_to_html('　　演出时间/地点：');//PERFORMANCE_TIME;
	}
	// Show end
	if($order->products[$i]['is_hotel'] == 1){
		$display_departure_time_location = "\n- ".TEXT_HOTEL_CHECK_IN_DATE.": ".$order->products[$i]['dateattributes'][0].' '.$order->products[$i]['dateattributes'][1].' '.$order->products[$i]['dateattributes'][2];
	}else if($order->products[$i]['is_hotel'] == 3){
		list($pick_uplocation, $return_location) = explode('|=|',$order->products[$i]['dateattributes'][2]);
		list($return_final_location,$return_date_two) = explode('=|=',$return_location);
		$display_departure_time_location = "\n".$EMAIL_TEXT_DEPARTURE_TIME_AND_LOCATION.": ".$order->products[$i]['dateattributes'][1].' '.$pick_uplocation."\n- ".TXT_RETURN_DEPARTURE_TIME_LOCATION.": ".date('Y-m-d',strtotime(substr($return_date_two,0,10)))." ".str_replace('||',' ',$return_final_location);
	}else if($order->products[$i]['is_transfer'] == '1'){
		//接送服务信息
		$transfer_info_arr = tep_transfer_decode_info($order->products[$i]['transfer_info']);
		$display_departure_time_location = "\n- ".db_to_html('接送信息')."-\n ".db_to_html(tep_transfer_display_route($transfer_info_arr,true));
	}else{
		$display_departure_time_location = "\n".$EMAIL_TEXT_DEPARTURE_TIME_AND_LOCATION.": ".$order->products[$i]['dateattributes'][1].' '.$order->products[$i]['dateattributes'][2];
	}
	
	if(!tep_not_null($order->products[$i]['dateattributes'][1]) && !tep_not_null($order->products[$i]['dateattributes'][2])){
		$display_departure_time_location = '';
	}
	$products_ordered .= "\n" . ($i + 1);
	if ($order->products[$i]['is_hotel'] == 1) {
		$products_ordered .= db_to_html('&nbsp;、酒店名称：') . db_to_html($order->products[$i]['name']);
		if ((int)$order->products[$i]['attributes'][0]['value_id'] == 2) {
			$products_ordered .= db_to_html(" (参团后加订的酒店)\n");
		} else {
			$products_ordered .= db_to_html(" (参团前加订的酒店)\n");
		}
		$products_ordered .= db_to_html("　　酒店编号：" . $order->products[$i]['model']) . "\n";
		// 取酒店产品的入住日期与退房日期
		$date_sql = "SELECT `products_departure_date`,`hotel_checkout_date` FROM `orders_products` 
				WHERE orders_id='" . $insert_id . "' and products_id='" . (int)$order->products[$i]['id'] . "' and is_hotel=1";
		$date_result = tep_db_query($date_sql);
		if (tep_db_num_rows($date_result) > 0) {
			$date_data = tep_db_fetch_array($date_result);
			$products_ordered .= db_to_html("　　入住日期：" . tep_date_short($date_data["products_departure_date"])) . "\n";
			$products_ordered .= db_to_html("　　退房日期：" . tep_date_short($date_data['hotel_checkout_date'])) . "\n";
		}
	} else {
		$products_ordered .= db_to_html('&nbsp;、线路名称：') . db_to_html($order->products[$i]['name']) ."\n";
		$products_ordered .= db_to_html('　　旅游团号：') . $order->products[$i]['model'] ."\n";
		$products_ordered .= db_to_html('　　出发日期：') . date('m/d/Y',strtotime($order->products[$i]['dateattributes'][0])) ."\n";
	}
	//file_put_contents("/var/www/html/test.usitrip/456.txt",print_r($order->products[$i],true) . "\n",FILE_APPEND);
	
	//$products_ordered .= db_to_html('小计：') . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty'])."\n";
	$temp = "";
	$products_ordered_attributes = trim($products_ordered_attributes,'　');
	$temp = trim($products_ordered_attributes,"\n");
	$temp = trim($temp);
	$temp = str_replace(array('$0.00','&#65509;0.00'),array('',''),$temp);
	if (tep_not_null($temp) == true) {
		$products_ordered .= db_to_html('　　' . $temp) . "\n";
	}
	/* $displayattributesinmail 房间信息
	$display_departure_time_location 演出时间与乘车地点
	*/
	$temp =  trim(trim(trim($displayattributesinmail,"\n"),"　") . "\n" . trim($display_departure_time_location,"\n"));
	$temp = trim($temp);
	if (tep_not_null($temp) == true) {
		$products_ordered .= db_to_html('　　' . $temp) . "\n";
	}
	
	//团购信息 start
	//Group Ordering start/*
	if($order->products[$i]['group_buy_discount']>0){
		$products_ordered .= db_to_html('　　') . ($order->products[$i]['is_diy_tours_book']==2 ? TXT_FEATURED_DEAL_DISCOUNT : TITLE_GROUP_BUY)." -".$currencies->display_price($order->products[$i]['group_buy_discount'],$order->products[$i]['tax'],$order->products[$i]['qty'])."\n";
	}
	//Group Ordering end
	//团购信息 end
	
	//特殊积分提示信息 start
	$n_multiple_points_notes = get_n_multiple_points_notes((int)$order->products[$i]['id']);
	if(tep_not_null($n_multiple_points_notes)){
		$products_ordered .= db_to_html('　　') . '<b>' . db_to_html($n_multiple_points_notes) . "</b>\n";
	}
	//特殊积分提示信息 end
	
	
	// 取用户名称
	$temp_sql = "select guest_name from orders_product_eticket where orders_id='" . $insert_id . "' and products_id='" . (int)$order->products[$i]['id'] . "'";
	$order_sql = tep_db_query($temp_sql);
	$order_names = tep_db_fetch_array($order_sql);
	$order_names_arr = explode('<::>',$order_names['guest_name']);
	for($guest_i = 0, $guest_count = count($order_names_arr); $guest_i < $guest_count - 1; $guest_i++) {
		if (preg_match("/[^\[]+\[([^\]]+)\]/", $order_names_arr[$guest_i],$matchs)) {
			$products_ordered .= db_to_html('　　游客' . ($guest_i + 1) . '：' . $matchs[1]) . "\n";
		}
	}
	
	//　添加上每个产品的小计
	$products_price = $order->products[$i]['roomattributes'][0] + $products_ordered_attributes_price ;
	$products_ordered .= db_to_html('　　小计：' . $currencies->display_price($products_price)) . "\n";


	//短信提醒 {
	if(0){	//暂时关闭此部分的短信 Howard 以排除信用卡的问题
		//下单成功并填有飞机到达信息，且飞机到达时间与出团时间相差一至三天，则短信提醒旅客延住
		$tour_start_time = strtotime($order->products[$i]['dateattributes'][0]);
		$tmp_arrival_date = query_date($arrival_date);
		$tmp_arrival_time = query_time($order->info['arrival_time'][$i]);
		if(tep_not_null($tmp_arrival_date) && $tmp_arrival_date != '0000-00-00' && tep_not_null($tmp_arrival_time) && $tmp_arrival_time != '00:00'){
			$flight_arrival_time = strtotime($tmp_arrival_date);
			$differ_days = ($tour_start_time - $flight_arrival_time)/24/3600;
			//提前1天至3天到的旅客才发送酒店延住信息
			if($differ_days == '1' || $differ_days == '2' || $differ_days == '3'){
				send_before_extension_sms($insert_id);
			}
		}

		//下单成功并填有飞机离开信息，且飞机离开时间在旅行结束后第二天早上8:00以后，第五天早上8:00之前的，则发送短信提醒旅客延住
		$tmp_departure_date = query_date($departure_date);
		$tmp_departure_time = query_time($order->info['departure_time'][$i]);
		if(tep_not_null($tmp_departure_date) && $tmp_departure_date != '0000-00-00' && tep_not_null($tmp_departure_time) && $tmp_departure_time != '00:00'){
			$products_id = tep_get_prid($order->products[$i]['id']);
			$products_sql="SELECT products_durations FROM products WHERE products_id = '".$products_id."'";
			$products_query=tep_db_query($products_sql);
			$products_result=tep_db_fetch_array($products_query);
			//旅游开始和结束时间
			$tour_start_time = strtotime($order->products[$i]['dateattributes'][0]);
			$tour_end_time = $tour_start_time+(($products_result['products_durations']-1)*3600*24);
			//飞机离开时间
			$flight_departure_time = $tmp_departure_date.' '.$tmp_departure_time;
			$flight_departure_time = strtotime($flight_departure_time);
			//发短信提醒延住的飞机离开时间要求
			$interval_start_time = $tour_end_time+32*3600;
			$interval_end_time = $tour_end_time+104*3600;
			//飞机离开时间在旅行结束后第二天早上8:00以后，第五天早上8:00之前的，发送短信提醒旅客延住
			if($interval_start_time < $flight_departure_time && $flight_departure_time < $interval_end_time){
				send_after_extension_sms($insert_id);
			}
		}
	}
	//}短信提醒
	
}


//写入销售跟踪记录
//tep_add_salestrack_when_checkout($insert_id,false);
//添加销售跟踪。
if($tmp_a)
tep_add_salestrack_when_checkout_at_check_proceess($insert_id,$tmp_a);

// 给结伴同游参与人 发送邮件 by lwkai 2012-06-20 add
if (strlen($to_email_address) > 1){
	if(!class_exists('companion_under_order_mail')) {
		if (class_exists('send_mail_ready') == false){
			require_once DIR_FS_CLASSES . 'send_mail_ready.php';
		}
		require_once(DIR_FS_CLASSES . 'companion_under_order_mail.php');
	
	}
	new companion_under_order_mail($to_email_address, $insert_id);
}
// 结伴同游给参与者发邮件结束

// 写优惠码跟踪记录
$order_total_modules->apply_credit();//ICW ADDED FOR CREDIT CLASS SYSTEM

//结伴同游结帐的子订单id整理
$o_t_c_ids = substr($o_t_c_ids,0,(strlen($o_t_c_ids)-1));

//************ amit moved authorized.net payment after insert order start
//信用卡付款 start{
if(defined(MODULE_PAYMENT_AUTHORIZENET_STATUS) && MODULE_PAYMENT_AUTHORIZENET_STATUS == 'True' && $payment == 'authorizenet') {
	//记录信用卡交易日志{
	$authorizenetLogs = DIR_FS_CATALOG.'/tmp/authorizenet_logs.txt';
	$logNotes = 'Date:'.date("Y-m-d H:i:s")." | ";
	$logNotes .= 'OrdersID:'.$insert_id." | ";
	$logNotes .= "\n";
	if($handle = fopen($authorizenetLogs, 'ab')){
		fwrite($handle, $logNotes);
		fclose($handle);
	}
	//记录信用卡交易日志}

	//check safe previouse entry need to delete
	if( ((int)$last_place_order_authorized_id != (int)$insert_id ) && ((int)($last_place_order_authorized_id)!= 0)){
		$secure_check_customer_order_query= tep_db_query("select orders_id from " . TABLE_ORDERS . " o, orders_status s where o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and (o.orders_status='100060' or o.orders_status='100061') and orders_id = '".(int)$last_place_order_authorized_id."' and o.customers_id='".$customer_id."' ");
		if(tep_db_num_rows($secure_check_customer_order_query)>0){
			tep_db_query("delete from " . TABLE_ORDERS . " where orders_id = '".(int)$last_place_order_authorized_id."' ");
			tep_db_query("delete from " . TABLE_ORDERS_TOTAL . " where orders_id = '".(int)$last_place_order_authorized_id."' ");
			tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS_FLIGHT . " where orders_id = '".(int)$last_place_order_authorized_id."' ");
			tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS_ETICKET . " where orders_id = '".(int)$last_place_order_authorized_id."' ");

			$select_dele_order_products_id_sql = "select orders_products_id from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '".(int)$last_place_order_authorized_id."'";
			$select_dele_order_products_id_row = tep_db_query($select_dele_order_products_id_sql);
			while ($select_dele_order_products_id = tep_db_fetch_array($select_dele_order_products_id_row)) {
				tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS_UPDATE_HISTORY . " where orders_products_id = '".$select_dele_order_products_id['orders_products_id']."' ");
			}

			tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '".(int)$last_place_order_authorized_id."' ");
			tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '".(int)$last_place_order_authorized_id."' ");
			tep_db_query("delete from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '".(int)$last_place_order_authorized_id."' ");
		}
	}

	if (!tep_session_is_registered('last_place_order_authorized_id')) tep_session_register('last_place_order_authorized_id');

	$last_place_order_authorized_id =  (int)$insert_id;
	//check sage previosue entry need to delete


	include(DIR_FS_MODULES . 'authorizenet_direct.php');
	$before_process = $payment_modules->before_process();
	//amit added for add pending status later start
	$sql_data_array = array('orders_id' => $insert_id,
	'orders_status_id' => $order->info['order_status'],
	'date_added' => 'now()',
	'customer_notified' => $customer_notification,
	'comments' => $order->info['comments']);
	tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, html_to_db ($sql_data_array));
	//amit added for add pending status later end
	//amit added for add pending status later end
	$last_active_charged_transation = $response_auth_trans_id;
	//amit added automatically notify provider status and update own order status start
	//amit added automatically notify provider status and update own order status start
	if($is_travel_companion != true && !tep_not_null($order->info['comments'])){ // check not a travel companion order and no comment
		$send_auto_notify_email_prodr = false;
		$is_all_providers_have_not_account=0;
		//amit added for auto charged start
		$is_all_providers_auto_charged_count = 0;
		//amit added for auto charged end
		$total_product_cont_nums = sizeof($order->products);
		//send auto notification to provider for confirmed new order start
		for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {

			$check_product_id_providers = tep_get_prid($order->products[$i]['id']);

			$display_providers_comment=get_if_display_provider_status_history($check_product_id_providers, $insert_id);
			$is_double_book_bool_array_check = is_double_booked((int)$insert_id, $check_product_id_providers);

			if($display_providers_comment=='1'  && $is_double_book_bool_array_check[0] == false ){

				//send email
				$selct_op_id_f_pd_ord_sql = "select orders_products_id from ".TABLE_ORDERS_PRODUCTS." where products_id='".(int)$check_product_id_providers."' and orders_id='".(int)$insert_id."' ";

				$selct_op_id_f_pd_ord_row =tep_db_query($selct_op_id_f_pd_ord_sql);
				$selct_op_id_f_pd_ord_row=tep_db_fetch_array($selct_op_id_f_pd_ord_row);

				$ord_prod_id_provider = $selct_op_id_f_pd_ord_row['orders_products_id'];

				$provider_order_status_id = 1;
				//hotel-extension {
				$default_provider_comment = '';
				if($order->products[$i]['is_hotel'] == 1){
					$hotel_extension_info = explode('|=|', $order->products[$i]['hotel_extension_info']);
					if($order->products[$i]['attributes'][0]['option_id']==HOTEL_EXT_ATTRIBUTE_OPTION_ID && $order->products[$i]['attributes'][0]['value_id']=='2'){
						$hotel_checkout_date = tep_get_date_db($hotel_extension_info[7]);
					}else if($order->products[$i]['attributes'][0]['option_id']==HOTEL_EXT_ATTRIBUTE_OPTION_ID){
						$hotel_checkout_date = tep_get_date_db($hotel_extension_info[1]);
					}
					$default_provider_comment = 'Check-in: '.tep_date_short($order->products[$i]['dateattributes'][0]).chr(13).chr(10).'Check-out: '.tep_date_short($hotel_checkout_date);
					if(check_date($hotel_checkout_date) && check_date($order->products[$i]['dateattributes'][0])){
						$DepartureDate .=' <span>'.db_to_html('Total: '.date1SubDate2($hotel_checkout_date,$order->products[$i]['dateattributes'][0]).' night').'</span>';
					}
				}
				//}

				$sql_data_array = array('orders_products_id' => $ord_prod_id_provider,
										'provider_order_status_id' => $provider_order_status_id,
										'provider_comment' => '',
										'provider_status_update_date' => 'now()',
										'popc_updated_by' => CUSTOMER_SERVICE_SYSTEM_ACCOUNT_ID, //96= service email
										'notify_usi4trip' => 1);
				tep_db_perform(TABLE_PROVIDER_ORDER_PRODUCTS_STATUS_HISTORY, $sql_data_array);

				$qry_providers="SELECT pl.*, concat_ws(' ', pl.providers_firstname, pl.providers_lastname) as providers_name, p.provider_tour_code, pd.products_name, ta.agency_name, op.products_departure_date, op.products_departure_time, op.customer_invoice_no FROM ".TABLE_PROVIDERS_LOGIN." pl, ".TABLE_TRAVEL_AGENCY." ta, ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_DESCRIPTION." pd, ".TABLE_ORDERS_PRODUCTS." op WHERE pl.providers_agency_id=ta.agency_id AND p.agency_id=ta.agency_id AND p.products_id=pd.products_id AND pl.providers_agency_id=p.agency_id AND p.products_id=op.products_id AND op.orders_id='".$insert_id."' AND p.products_id='".$check_product_id_providers."' AND pl.providers_status=1";
				$mail_to="";
				$qry_providers_detail=$qry_providers." AND pl.providers_email_notification = 1 ";
				$res_providers_detail=tep_db_query($qry_providers_detail);

				$orders_link=tep_href_link('providers/'.FILENAME_PROVIDERS_ORDERS, 'oID='.$ord_prod_id_provider.'&action=edit_order', 'SSL');
				$orders_prod_status=tep_get_provider_order_status_name($provider_order_status_id);
				$mail_subject=sprintf(EMAIL_ORDERS_PRODUCTS_STATUS_CHANGED_SUBJECT, $insert_id, $orders_prod_status);

				if(tep_db_num_rows($res_providers_detail)>0){//Send to sub users
					while($row_providers_detail=tep_db_fetch_array($res_providers_detail)){

						$mail_to=$row_providers_detail['providers_email_address'];

						if(trim($row_providers_detail['providers_firstname'])==""){
							$to_name = $row_providers_detail['agency_name'];
						}else{
							$to_name = $row_providers_detail['providers_firstname'];
						}

						$mail_body=sprintf(EMAIL_ORDERS_PRODUCTS_STATUS_CHANGED_BODY, db_to_html(TXT_PROVIDER_STATUS_MAIL_FROM), $insert_id, tep_db_prepare_input($row_providers_detail['provider_tour_code'])." (".tep_db_prepare_input($row_providers_detail['products_name']).")", $insert_id, tep_date_short($row_providers_detail['products_departure_date'])." ".$row_providers_detail['products_departure_time'], $orders_prod_status, ($row_providers_detail['customer_invoice_no']!="")?"\n Invoice#: ".$row_providers_detail['customer_invoice_no']:"", $provider_comment, $orders_link, $orders_link, db_to_html(TXT_PROVIDER_STATUS_MAIL_FROM));
						if($_SERVER['HTTP_HOST']=='208.109.123.18' || $_SERVER['HTTP_HOST']=='cn.usitrip.com' || $_SERVER['HTTP_HOST']=='tw.usitrip.com' || $mail_to=="xmzhh2000@126.com"){

							//howard add use session+ajax send email

							$var_num = (int)count($_SESSION['need_send_email']);
							$_SESSION['need_send_email'][$var_num]['to_name'] = $to_name;
							$_SESSION['need_send_email'][$var_num]['to_email_address'] = $mail_to;
							$_SESSION['need_send_email'][$var_num]['email_subject'] = $mail_subject;
							$_SESSION['need_send_email'][$var_num]['email_text'] = $email_text;	// can is html or text
							$_SESSION['need_send_email'][$var_num]['from_email_name'] = db_to_html(STORE_OWNER);
							//$_SESSION['need_send_email'][0]['from_email_address'] = STORE_OWNER_EMAIL_ADDRESS;
							$_SESSION['need_send_email'][$var_num]['from_email_address'] = 'automail@usitrip.com';
							$_SESSION['need_send_email'][$var_num]['action_type'] = EMAIL_USE_HTML;
							//howard add use session+ajax send email end

							//tep_mail($to_name, $mail_to, $mail_subject, $mail_body, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
						}
					}

					//send email
					$send_auto_notify_email_prodr = true;
					$is_all_providers_have_not_account++;
					//amit added for auto charged start
					$get_provider_charged_request_array = tep_get_tour_agency_information($check_product_id_providers, $order->products[$i]['final_price']);
					if($get_provider_charged_request_array['provider_auto_charged_package'] == '1' && $get_provider_charged_request_array['products_vacation_package'] == '1'){
						$is_all_providers_auto_charged_count++;
					}else if($get_provider_charged_request_array['provider_auto_charged'] == '1' && $get_provider_charged_request_array['products_vacation_package'] == '0'){
						$is_all_providers_auto_charged_count++;
					}
					//amit added for auto charged end
				}
			}
		}


		if($send_auto_notify_email_prodr == true){

			if($is_all_providers_have_not_account == $total_product_cont_nums){
				//amit added for auto charged start
				if($response_auth_trans_id != 0 && $response_auth_trans_id != '' && $is_all_providers_auto_charged_count == $total_product_cont_nums) {
					$auto_charged_request_response = auto_charged_authorized_net_order(); //auto charged order
					if($auto_charged_request_response[0] == '1'){ // only if success response

						tep_db_query("update " . TABLE_ORDERS . " set orders_status = '100006', last_modified = now() where orders_id = '" . (int)$insert_id . "'");

						$auto_chrg_comment_ref = 'Auto Charged Order By System<br><br>'.$x_Amount.'<br>'.$auto_charged_request_response[50].'<br>Transaction ID: '.$auto_charged_request_response[6];
						$sql_data_array = array('orders_id' => $insert_id,
						'orders_status_id' => '100006', //Charge Captured (I)
						'date_added' => 'now()',
						'customer_notified' => '0',
						'updated_by' => CUSTOMER_SERVICE_SYSTEM_ACCOUNT_ID,
						'comments' => $auto_chrg_comment_ref);
						tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

						$response_auth_trans_id = ''; //added to avoide multiple charged same order

						//added charged capture information record
						/*
						$settlement_final_date = gmdate('Y-m-d H:i:s', time()-28800);
						$settlement_time = gmdate('His', time()-28800);
						if((int)$settlement_time >= 0 && (int)$settlement_time <= 170000){
						$settlement_final_date = $settlement_final_date;
						}else{
						$settlement_final_date = gmdate('Y-m-d H:i:s', strtotime("+1 day")-28800);
						}
						*/
						$settlement_final_date = GetTime(5, false, true);  //timezone , settlemetnt_time formate, daytimesaving  -- PST TIME
						$settlement_time = GetTime(5, true, true);
						if((int)$settlement_time >= 0 && (int)$settlement_time <= 170000){
							$settlement_final_date = $settlement_final_date;
						}else{
							$settlement_final_date = date('Y-m-d H:i:s', strtotime($settlement_final_date . ' + 1 day'));
						}

						$reference_comments = 'Auto Charged Order By System'.chr(13).chr(10).$x_Amount.chr(13).chr(10).$auto_charged_request_response[50].chr(13).chr(10).'Transaction ID: '.$auto_charged_request_response[6];

						$sql_data_settlement_array = array(
													'orders_id' =>  (int)$insert_id,
													'order_value' => $x_Amount,
													'orders_payment_method' => 'Credit Card',
													'reference_comments' => $reference_comments,
													'settlement_date' => $settlement_final_date,
													'settlement_date_short' => substr($settlement_final_date,0,10),
													'date_added' => 'now()',
													'updated_by' => CUSTOMER_SERVICE_SYSTEM_ACCOUNT_ID
													);
						tep_db_perform(TABLE_ORDERS_SETTLEMENT_INFORMATION, $sql_data_settlement_array);
						//added charged capture information record

					}
				}
				//amit added for auto charged end
				//100072 = Book Issued by System
				tep_db_query("update " . TABLE_ORDERS . " set orders_status = '100072', last_modified = now() where orders_id = '" . (int)$insert_id . "'");
				$sql_data_array = array('orders_id' => $insert_id,
				'orders_status_id' => '100072',
				'date_added' => 'now()',
				'customer_notified' => '0',
				'updated_by' => CUSTOMER_SERVICE_SYSTEM_ACCOUNT_ID,
				'comments' => 'Booking Issued by System');
				tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

			}else{
				//100074 = Partially Booking Issued by System
				tep_db_query("update " . TABLE_ORDERS . " set orders_status = '100074', last_modified = now() where orders_id = '" . (int)$insert_id . "'");
				$sql_data_array = array('orders_id' => $insert_id,
										'orders_status_id' => '100074',
										'date_added' => 'now()',
										'customer_notified' => '0',
										'updated_by' => CUSTOMER_SERVICE_SYSTEM_ACCOUNT_ID,
										'comments' => 'Partially Booking Issued by System');
				tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

				//1 = Pending
				tep_db_query("update " . TABLE_ORDERS . " set orders_status = '1', last_modified = now() where orders_id = '" . (int)$insert_id . "'");
				$sql_data_array = array('orders_id' => $insert_id,
										'orders_status_id' => '1',
										'date_added' => 'now()',
										'customer_notified' => '0',
										'updated_by' => CUSTOMER_SERVICE_SYSTEM_ACCOUNT_ID,
										'comments' => '');
				tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
			}
		}

	} //end of check not travel companion order
	//send auto notification to provider for confirmed new order end
	if((int)$before_process && $i_need_pay>0 && tep_not_null($o_t_c_ids)){	//如果交易成功以及是结伴同游的话需要更新该同游订单的客户交款状态

		if(tep_not_null($o_t_c_ids)){
			tep_db_query('UPDATE `orders_travel_companion` SET `orders_travel_companion_status` = "1", last_modified="'.date('Y-m-d H:i:s').'", payment="'.$payment.'" WHERE orders_id="'.$insert_id.'" AND orders_travel_companion_id in('.$o_t_c_ids.') ');

		}
	}

}
//信用卡付款 end}
//************ amit moved authorized.net payment after insert order end

// lets start with the email confirmation
if (!tep_session_is_registered('noaccount')){
	
	$_url = tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $insert_id, 'SSL', false);
	if($_COOKIE['login_id']){	//如果是客服下的单就在通知客人时发送自动登录格式的url，以减少客人的登录步骤！
		$_url = $autoLogin->make_url($_url, $customer_id);
	}
	$email_order = "尊敬的 ".$order->customer['firstname'] . " 先生/女士： \n".
	'您好！非常感谢您预订美国走四方(Usitrip.com)的旅游产品！'."\n\n" ;
	if(in_array($payment,array('paypal','alipay_direct_pay','paypal_nvp_samples'))){	//信用卡支付/Paypal支付/支付宝支付 的内容
		$email_order .=
		'<strong>您的订单<a href="'.$_url.'" target="_blank">' . $insert_id . "</a>已收到。</strong>\n" .
		'我们会在收到您的付款后， 3-4个工作日内发送行程确认单到您的注册邮箱（收到该行程确认单，表明您的行程已订购成功），请留意查收邮件。'."\n\n";
	}else{
		$_payment_class = $$payment;
		$email_order .= '订单号: <strong><a href="'.$_url.'" target="_blank">'.$insert_id.'</a></strong>，您已经选择<strong>'.html_to_db($_payment_class->title).'</strong>支付，';
		if(in_array($payment,array('transfer','banktransfer','cashdeposit'))){	//银行转账 银行电汇(美国) 银行转账/现金存款(美国)
			$email_order .=	'请在汇款后及时通知走四方客服人员您的汇款详细信息，以便尽快帮您确认行程预订。'."\n";
		}
		if($payment=='moneyorder'){	//支票
			$email_order .=	'请在发送支票扫描件后及时通知走四方客服人员，以便尽快帮您确认行程预订。'."\n";
		}
		$email_order .= '（温馨提示：请尽快支付，在您成功支付、收到行程确认单前，您的座位未被保留）'."\n\n";
	}	
	$email_order = db_to_html($email_order);

}else{
	$email_order = db_to_html(STORE_NAME) . "\n" .
	EMAIL_SEPARATOR . "\n" .
	EMAIL_TEXT_ORDER_NUMBER . ' ' . $insert_id . "\n" .
	EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n";
}

//是否发送留言的内容到邮件
$send_comments = false;	
if ($send_comments == true && $order->info['comments']) {
	$email_order .= EMAIL_SEPARATOR . "\n" .tep_db_output($order->info['comments']) . "\n";
}
//是否发送产品列表信息到邮件
$send_reservation_list = true; 
if($send_reservation_list == true){
	$email_order .= EMAIL_SEPARATOR . "\n" .
	db_to_html('<strong>订单详情：<a href="'.$_url.'" target="_blank">'.$insert_id.'</a>（您可以使用预订时注册的Email登录您的账户来查询订单详情）</strong>') . //"\n" .
	str_replace('$0.00','',$products_ordered) ;	//移除金额为0的字样
	
	//替换小计 总计的中文字
	function mail_replace_str($str){
		$str = html_to_db($str);
		$str = str_replace(array('小计:','总计:'),array('共计:','总金额:'),$str);
		$str = db_to_html($str);
		return $str;
	}
	
	for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
	
		//print_r($order_totals);exit;
		//if(strip_tags($order_totals[$i]['title']) == 'Sub-Total:')
		if($order_totals[$i]['code'] == 'ot_subtotal')
		{
			if($i==0){ 
				$email_order .= EMAIL_SEPARATOR . "\n"; 
			}
			$grand_payment = str_replace('Sub-Total:','Grand Total:',strip_tags($order_totals[$i]['title']));
			$grand_payment = mail_replace_str($grand_payment);
			$email_order .= $grand_payment . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
			if($display_email_tax != '')
				$email_order .= 'Tax:  '. $display_email_tax . "\n";
			if($display_discount_coupons_title != '')
				$email_order .= $display_discount_coupons_title.' '.$display_discount_coupons. "\n";
	
		}
		//elseif(strip_tags($order_totals[$i]['title']) == 'Total:')
		elseif($order_totals[$i]['code']=='ot_total')
		{
			if($i==0){ $email_order .= EMAIL_SEPARATOR . "\n"; }
			$grand_payment = str_replace('Total:','Payment: ',strip_tags($order_totals[$i]['title']));
			$grand_payment = mail_replace_str($grand_payment);
			$email_order .= $grand_payment . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
	
			//if paypent is transfer or banktransfer calculate the value of concessions %2
		}else{	//其它优惠或附加费
	
			$email_order .= strip_tags(mail_replace_str($order_totals[$i]['title'])).' '.strip_tags($order_totals[$i]['text'])."\n";
	
		}
		//strip_tags($order_totals[$i]['title'])
	}
	// 判断是否是结伴同游的订单 如果是结伴 加上个人所需付款
	if (strlen($to_email_address) > 1) {
		if (class_exists('companions_personal_pay') == false) {
			require_once DIR_FS_CLASSES . 'companions_personal_pay.php';
		}
		$cp_pay = new companions_personal_pay($insert_id);
		$email_order .= db_to_html($cp_pay->getCustomersPay($order->customer['email_address']) . "\n<span style='color:red'>注：上述费用不包括小孩费用</span>\n");
		$child = $cp_pay->getChildPay();
		if (tep_not_null($child) == true) {
			$email_order .= db_to_html($child) . "\n";
		}
	}
}
//是否发送航班信息到邮件
$send_flightinfo = false; 
if ($send_flightinfo == true && $order->info['flightinfo']) {
	$email_order .= EMAIL_SEPARATOR . "\n" .tep_db_output($order->info['flightinfo']) . "\n";
}
//是否发送参团凭证邮箱地址到邮件
$send_eticket_delivery_address = false;
if ($send_eticket_delivery_address == true && $order->content_type != 'virtual') {
	// panda 结伴同游订单,增加所有结伴者得邮箱地址在预订单邮件里面 start
	if ($is_travel_companion){
		$email_order .= EMAIL_SEPARATOR . "\n" .
		"<strong>" . EMAIL_TEXT_DELIVERY_ADDRESS . "</strong>\n" .
		$order->customer['email_address'] . ',' . $to_email_address ."\n";
	}else{
		$email_order .= EMAIL_SEPARATOR . "\n" .
		"<strong>" . EMAIL_TEXT_DELIVERY_ADDRESS . "</strong>\n" .
		$order->customer['email_address'] ."\n";
	}
	// panda 结伴同游订单,增加所有结伴者得邮箱地址在预订单邮件里面 end
}
//是否发送支付方式的资料到邮件
$send_payment_info = true;
if($send_payment_info == true){
	//不显示信用卡地址
	//$email_order .= EMAIL_SEPARATOR . "\n <strong>" . EMAIL_TEXT_BILLING_ADDRESS . "</strong>" . db_to_html(tep_address_label($customer_id, $billto, 0, '', "\n")) . "\n";
	if (is_object($$payment)) {
		$payment_str ='';
		$payment_str .= EMAIL_SEPARATOR . "\n".
		"<strong>".EMAIL_TEXT_PAYMENT_METHOD . "</strong> " ;
		$payment_class = $$payment;
		$payment_str .= $payment_class->title . "\n";
		if ($payment_class->email_footer) {
			$payment_str .= $payment_class->email_footer . "\n";
		}
	
		//$email_order .= preg_replace("/(\n+)/","\n",strip_tags($payment_str,'<img><ul><li><table><tr><td><a><strong><b>'));
		$email_order .= preg_replace("/(\n+)/","\n",$payment_str);
		//$email_order .= preg_replace("/(\n+)|(\<br\>\n)|(\n\<br\>)/i","\n",$payment_str);
	}
	if($currency == 'CNY'){
		$email_order .=  "\n".TEXT_RMB_CHECK_OUT_MSN . "\n" ;
	}
}

$email_order .=  EMAIL_SEPARATOR."\n";
	


$email_order .=  "\n<b>".db_to_html('走四方旅游网法律声明：此邮件仅作走四方网预订的系统收据，不能将此作为签证等其他任何用途！若需要办理签证所需的邀请函，请联系我们在线专业顾问，谢谢！') . "</b>\n" ;
$email_order .=  db_to_html(CONFORMATION_EMAIL_FOOTER) . "\n" ;
$email_order .=  email_track_code('NewOrder',$order->customer['email_address'], $insert_id);



//加入newsletter广告
$banners = get_banners("Orders Email",true);
if(tep_not_null($banners)){
	for($i=0; $i<count($banners); $i++){
		if(tep_not_null($banners[$i]['FinalCode'])){
			$email_order .= $banners[$i]['FinalCode'];
		}else{
			$email_order .= '<div><a title="'.$banners[$i]['alt'].'" href="'.$banners[$i]['links'].'" target="_blank"><img border="0" src="'.$banners[$i]['src'].'" alt="'.$banners[$i]['alt'].'" /></a></div>';
		}
	}
}

// echo $email_order;
//exit();
//howard add use session+ajax send email
// 下单后发送的邮件 非 paypal alipay paypal 方式
if(!in_array($payment,array('paypal','alipay_direct_pay','paypal_nvp_samples')) && !$Admin->parent_orders_id){	//非在线付款的方式时在这里发订单邮件给客人，在原有订单中添加新产品时也不可发邮件给客人howard added by 2013-06-20
	$var_num = (int)count($_SESSION['need_send_email']);
	$_SESSION['need_send_email'][$var_num]['to_name'] = db_to_html($order->customer['firstname'] . ' ' . $order->customer['lastname']);
	$_SESSION['need_send_email'][$var_num]['to_email_address'] = $order->customer['email_address'];
	$_SESSION['need_send_email'][$var_num]['email_subject'] = sprintf(EMAIL_TEXT_SUBJECT,$payment_class->title,$insert_id);
	$_SESSION['need_send_email'][$var_num]['email_text'] = $email_order;
	$_SESSION['need_send_email'][$var_num]['from_email_name'] = db_to_html(STORE_OWNER);
	//$_SESSION['need_send_email'][0]['from_email_address'] = STORE_OWNER_EMAIL_ADDRESS;
	$_SESSION['need_send_email'][$var_num]['from_email_address'] = 'automail@usitrip.com';
	$_SESSION['need_send_email'][$var_num]['action_type'] = EMAIL_USE_HTML;
	
	//howard add use session+ajax send email end
	
	// send emails to other people
	if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
	
		//howard add use session+ajax send email
		$var_num = (int)count($_SESSION['need_send_email']);
		$_SESSION['need_send_email'][$var_num]['to_name'] = '';
		$_SESSION['need_send_email'][$var_num]['to_email_address'] = SEND_EXTRA_ORDER_EMAILS_TO;
		$_SESSION['need_send_email'][$var_num]['email_subject'] = sprintf(EMAIL_TEXT_SUBJECT,$payment_class->title,$insert_id);
		$_SESSION['need_send_email'][$var_num]['email_text'] = $email_order;
		$_SESSION['need_send_email'][$var_num]['from_email_name'] =  db_to_html(STORE_OWNER);
		//$_SESSION['need_send_email'][$var_num]['from_email_address'] = STORE_OWNER_EMAIL_ADDRESS;
		$_SESSION['need_send_email'][$var_num]['from_email_address'] = 'automail@usitrip.com';
		$_SESSION['need_send_email'][$var_num]['action_type'] = EMAIL_USE_HTML;
		//howard add use session+ajax send email end
	}
}
//amit added for auto canceled order start
//Howard updated outwith transfer and have comments
if($payment!=""){
	include('checkout_process_auto_canceled.php');
}
//amit added for auto canceled order end

//使用手机短信通知客户 start
//另外两个process文件目前没有加这个功能
send_orders_sms($insert_id);
//使用手机短信通知客户 end

//LOG User Session Status {
$logUserSession = print_r($_SESSION,true);
//}

// Include OSC-AFFILIATE
require(DIR_FS_INCLUDES . 'affiliate_checkout_process.php');
//根据sofia要求，生成一次单后清除销售联盟变量
servers_sales_track::clear_ref_info();
// load the after_process function from the payment modules
$payment_modules->after_process();

$cart->reset(true);

// unregister session variables used during checkout
tep_session_unregister('sendto');
tep_session_unregister('billto');
tep_session_unregister('shipping');
tep_session_unregister('payment');
tep_session_unregister('comments');
tep_session_unregister('airline_name');
tep_session_unregister('flight_no');
tep_session_unregister('airline_name_departure');
tep_session_unregister('flight_no_departure');
tep_session_unregister('airport_name');
tep_session_unregister('airport_name_departure');
tep_session_unregister('arrival_date');
tep_session_unregister('arrival_time');
tep_session_unregister('departure_date');
tep_session_unregister('departure_time');
$guest_name = "";
foreach($_SESSION as $key=>$val)
{
	if(strstr($key,'GuestEmail') || strstr($key,'PayerMe'))
	{
		tep_session_unregister($key);
	}
	if(strstr($key,'guestname'))
	{
		tep_session_unregister($key);
	}
	if(strstr($key,'SingleName'))
	{
		tep_session_unregister($key);
	}
	if(strstr($key,'SingleGender'))
	{
		tep_session_unregister($key);
	}

	if(strstr($key,'guestsurname'))
	{
		tep_session_unregister($key);
	}

	if(strstr($key,'GuestEngXing'))
	{
		tep_session_unregister($key);
	}
	if(strstr($key,'GuestEngName'))
	{
		tep_session_unregister($key);
	}
	if(strstr($key,'guestchildage'))
	{
		tep_session_unregister($key);
	}
	if(strstr($key,'guestbodyweight'))
	{
		tep_session_unregister($key);
	}
	if(strstr($key,'guestweighttype'))
	{
		tep_session_unregister($key);
	}

	if(strstr($key,'arrival_date'.$i))
	{
		tep_session_unregister($key);
	}
	if(strstr($key,'departure_date'.$i))
	{
		tep_session_unregister($key);
	}

	if(strstr($key,'guestgender'.$i))
	{
		tep_session_unregister($key);
	}

	if(strstr($key,'guestdob'.$i))
	{
		tep_session_unregister($key);
	}
	if(strstr($key,'guestbodyheight'))
	{
		tep_session_unregister($key);
	}

}

if(tep_session_is_registered('credit_covers')) tep_session_unregister('credit_covers');
$order_total_modules->clear_posts();//ICW ADDED FOR CREDIT CLASS SYSTEM

#### Points/Rewards Module V2.1rc2a balance customer points EOF ####*/
if (tep_session_is_registered('customer_shopping_points')) tep_session_unregister('customer_shopping_points');
if (tep_session_is_registered('customer_shopping_points_spending')) tep_session_unregister('customer_shopping_points_spending');
if (tep_session_is_registered('customer_referral')) tep_session_unregister('customer_referral');
if (tep_session_is_registered('customer_apply_credit_bal')) tep_session_unregister('customer_apply_credit_bal');
if (tep_session_is_registered('customer_apply_credit_amt')) tep_session_unregister('customer_apply_credit_amt');
if (tep_session_is_registered('credit_applied_covers')) tep_session_unregister('credit_applied_covers');
#### Points/Rewards Module V2.1rc2a balance customer points EOF ####*/
if (tep_session_is_registered('last_place_order_authorized_id')) tep_session_unregister('last_place_order_authorized_id');

//LOG User Session Status {
//记录下单日志
$switchBokingLog = false;
if($switchBokingLog == true){
	$logUserSession = "\n==================================================\n".date("Y-m-d H:i:s",time())." ORDER_ID:".$insert_id." \n"
	.$_SERVER['HTTP_USER_AGENT']."\n"
	.$logUserSession
	."\n==================================================\n";
	$logUserSessionFile = 'tmp/'.date("Ymd",time()).'.log';
	$fp = @fopen($logUserSessionFile,'a');
	for($i=0;$i<500;$i++){
		@fwrite($fp,$logUserSession);
	}
	@fclose( $fp);
}
//}

//如果是结伴同游订单则直接到结伴同游结帐处结帐(限在线支付方式，Paypal的已经在Paypal的专用文件进行处理)
if(isset($i_need_pay) && $i_need_pay>0 && tep_not_null($orders_travel_companion_ids_str) && in_array($payment,array('netpay','a_chinabank','alipay_direct_pay','paypal_nvp_samples'))){

	tep_db_query('DELETE FROM `orders_session_info` WHERE `orders_id` = "'.(int)$insert_id.'" LIMIT 1 ');
	tep_redirect(tep_href_link('travel_companion_pay.php','order_id='.(int)$insert_id. $orders_travel_companion_ids_str, 'SSL'));
	//echo $i_need_pay;
	exit;
}

// BOF: Lango added for print order mod
tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, 'order_id='. $insert_id, 'SSL'));
// EOF: Lango added for print order mod
require(DIR_FS_INCLUDES . 'application_bottom.php');


?>
