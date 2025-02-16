<?php
/*
  $Id: paypal_notify.php,v 1.1.1.1 2004/03/04 23:38:01 ccwjr Exp $
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Paypal IPN v0.981 for Milestone 2
  Copyright (c) 2003 Pablo Pasqualino
  pablo_osc@osmosisdc.com
  http://www.osmosisdc.com

  Released under the GNU General Public License
*/

  include('includes/application_top.php');
  include(DIR_FS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_PROCESS);

// load paypal_ipn payment module
  $payment = 'paypalipn';
  require(DIR_FS_CLASSES . 'payment.php');
  $payment_modules = new payment($payment);

  if (phpversion() <= '4.0.6') {
    $_POST = $HTTP_POST_VARS;
  }

  require(DIR_FS_CLASSES . 'order.php');
  $order = new order($_POST['item_number']);

  $req = 'cmd=_notify-validate';

  foreach ($_POST as $key => $value) {
    $req .= '&' . $key . '=' . urlencode($value);
    $$key = $value;
  }

  $response_verified = '';
  $paypal_response = '';

  if (MODULE_PAYMENT_PAYPALIPN_TEST_MODE=='True') {

    if ($item_number) {
      $paypal_response = $_POST[ipnstatus];

	echo 'TEST IPN Processed for order #'.$item_number;
    } else {

	echo 'You need to specify an order #';

    };

  } elseif (MODULE_PAYMENT_PAYPALIPN_CURL=='True') { // IF CURL IS ON, SEND DATA USING CURL (SECURE MODE, TO https://)

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://www.paypal.com/cgi-bin/webscr");
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
    $paypal_response = curl_exec($ch);
    curl_close ($ch);

  } else { // ELSE, SEND IT WITH HEADERS (STANDARD MODE, TO http://)

    $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
    $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $header .= "Content-Length: " . strlen ($req) . "\r\n\r\n";
    $fp = fsockopen ("www.paypal.com", 80, $errno, $errstr, 30);

    fputs ($fp, $header . $req);
    while (!feof($fp)) {
      $paypal_response .= fgets($fp, 1024);
    };

    fclose ($fp);

  };

  if (ereg('VERIFIED',$paypal_response)) {
    $response_verified = 1;
    $ipn_result = 'VERIFIED';
  } else if (ereg('INVALID',$paypal_response)) {
    $response_invalid = 1;
    $ipn_result = 'INVALID';
  } else {
    echo 'Error: no valid $paypal_response received.';
  };

  if ($txn_id && ($response_verified==1 || $response_invalid==1)) {

    $txn_check = tep_db_query("select txn_id from " . TABLE_PAYPALIPN_TXN . " where txn_id='$txn_id'");
    if (tep_db_num_rows($txn_check)==0) { // If txn no previously registered, we should register it

      $sql_data_array = array('txn_id' => $txn_id,
                              'ipn_result' => $ipn_result,
                              'receiver_email' => $receiver_email,
                              'business' => $business,
                              'item_name' => $item_name,
                              'item_number' => $item_number,
                              'quantity' => $quantity,
                              'invoice' => $invoice,
                              'custom' => $custom,
                              'option_name1' => $option_name1,
                              'option_selection1' => $option_selection1,
                              'option_name2' => $option_name2,
                              'option_selection2' => $option_selection2,
                              'num_cart_items' => $num_cart_items,
                              'payment_status' => $payment_status,
                              'pending_reason' => $pending_reason,
                              'payment_date' => $payment_date,
                              'settle_amount' => $settle_amount,
                              'settle_currency' => $settle_currency,
                              'exchange_rate' => $exchange_rate,
                              'payment_gross' => $payment_gross,
                              'payment_fee' => $payment_fee,
                              'mc_gross' => $mc_gross,
                              'mc_fee' => $mc_fee,
                              'mc_currency' => $mc_currency,
                              'tax' => $tax,
                              'txn_type' => $txn_type,
                              'for_auction' => $for_auction,
                              'memo' => $memo,
                              'first_name' => $first_name,
                              'last_name' => $last_name,
                              'address_street' => $address_street,
                              'address_city' => $address_city,
                              'address_state' => $address_state,
                              'address_zip' => $address_zip,
                              'address_country' => $address_country,
                              'address_status' => $address_status,
                              'payer_email' => $payer_email,
                              'payer_id' => $payer_id,
                              'payer_status' => $payer_status,
                              'payment_type' => $payment_type,
                              'notify_version' => $notify_version,
                              'verify_sign' => $verify_sign);

      tep_db_perform(TABLE_PAYPALIPN_TXN,$sql_data_array);

    } else { // else we update it to the new status

      $sql_data_array = array('payment_status' => $payment_status,
                              'pending_reason' => $pending_reason,
                              'ipn_result' => $ipn_result,
                              'payer_email' => $payer_email,
                              'payer_id' => $payer_id,
                              'payer_status' => $payer_status,
                              'payment_type' => $payment_type);

      tep_db_perform(TABLE_PAYPALIPN_TXN,$sql_data_array,'update','txn_id=\''.$txn_id.'\'');

    };

  };

  if ($response_verified==1) {
    if (strtolower($receiver_email)==strtolower(MODULE_PAYMENT_PAYPALIPN_ID) || strtolower($business)==strtolower(MODULE_PAYMENT_PAYPALIPN_ID)) {
      if ($payment_status=='Completed') {
        if (MODULE_PAYMENT_PAYPALIPN_UPDATE_STOCK_BEFORE_PAYMENT=='False') {
          for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
// Stock Update - Joao Correia
            if (STOCK_LIMITED == 'true') {
              if (DOWNLOAD_ENABLED == 'true') {
                $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
                                    FROM " . TABLE_PRODUCTS . " p
                                    LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                     ON p.products_id=pa.products_id
                                    LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                     ON pa.products_attributes_id=pad.products_attributes_id
                                    WHERE p.products_id = '" . tep_get_prid($order->products[$i]['id']) . "'";
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
            }
          }
        }

        if (is_numeric(MODULE_PAYMENT_PAYPALIPN_ORDER_STATUS_ID) && (MODULE_PAYMENT_PAYPALIPN_ORDER_STATUS_ID > 0) ) {
          $order_status = MODULE_PAYMENT_PAYPALIPN_ORDER_STATUS_ID;
        } else {
          $order_status = DEFAULT_ORDERS_STATUS_ID;
        };

        $sql_data_array = array('orders_status' => $order_status);

        tep_db_perform(TABLE_ORDERS,$sql_data_array,'update','orders_id='.$item_number);

        $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
        $sql_data_array = array('orders_id' => $item_number,
                                'orders_status_id' => $order_status,
                                'date_added' => 'now()',
                                'customer_notified' => $customer_notification);
        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

// lets start with the email confirmation
        for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
          if (sizeof($order->products[$i]['attributes']) > 0) {
            $attributes_exist = '1';
            $products_ordered_attributes = "\n";
            for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
              $products_ordered_attributes .= '  '. $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
              if ($order->products[$i]['attributes'][$j]['price'] != '0') $products_ordered_attributes .= ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')' . "\n";
            }
          }

         		  //code by bhavik start
	$displayattributesinmail = str_replace("<br>","\n",$order->products[$i]['roomattributes'][1]);
	$displayattributesinmail = str_replace('</br>',"\n",$displayattributesinmail);
	
	$displayattributesinmail = str_replace('Total of room','Subtotal',$displayattributesinmail);
	/*$displayattributesinmail = str_replace('#','Number',$displayattributesinmail);*/
	$displayattributesinmail = str_replace('adults','Adults',$displayattributesinmail);
	$displayattributesinmail = str_replace('childs','Children',$displayattributesinmail);
	$displayattributesinmail = str_replace('room','Room',$displayattributesinmail);
	

	$display_departure_time_location = "\n- Departure Time & Location1: ".$order->products[$i]['dateattributes'][0].' '.$order->products[$i]['dateattributes'][1].' '.$order->products[$i]['dateattributes'][2];

          $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . $displayattributesinmail . $display_departure_time_location . "\n";
    //code by bhavik end
			 $products_ordered_attributes = '';
        }

        $email_order = STORE_NAME . "\n" .
                       EMAIL_SEPARATOR . "\n" .
                       EMAIL_TEXT_ORDER_NUMBER . ' ' . $item_number . "\n" .
                       EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $item_number, 'SSL', false) . "\n" .
                       EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";
        if ($order->info['comments']) {
          $email_order .= tep_db_output($order->info['comments']) . "\n\n";
        }
        $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
                        EMAIL_SEPARATOR . "\n" .
                        $products_ordered .
                        EMAIL_SEPARATOR . "\n";

        for ($i=0, $n=sizeof($order->totals); $i<$n; $i++) {
          $email_order .= strip_tags($order->totals[$i]['title']) . ' ' . strip_tags($order->totals[$i]['text']) . "\n";
        }

        if ($order->content_type != 'virtual') {
          $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                          EMAIL_SEPARATOR . "\n";
          if ($order->delivery['company']) { $email_order .= $order->delivery['company'] . "\n"; };
          $email_order .= $order->delivery['name'] . "\n" .
                          $order->delivery['street_address'] . "\n";
          if ($order->delivery['suburb']) { $email_order .= $order->delivery['suburb'] . "\n"; };
          $email_order .= $order->delivery['city'] . ', ' . $order->delivery['postcode'] . "\n";
          if ($order->delivery['state']) { $email_order .= $order->delivery['state'] . ', '; };
          $email_order .= $order->delivery['country'] . "\n";
        }

        $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                        EMAIL_SEPARATOR . "\n";
          if ($order->billing['company']) { $email_order .= $order->billing['company'] . "\n"; };
          $email_order .= $order->billing['name'] . "\n" .
                          $order->billing['street_address'] . "\n";
          if ($order->billing['suburb']) { $email_order .= $order->billing['suburb'] . "\n"; };
          $email_order .= $order->billing['city'] . ', ' . $order->billing['postcode'] . "\n";
          if ($order->billing['state']) { $email_order .= $order->billing['state'] . ', '; };
          $email_order .= $order->billing['country'] . "\n\n";

        if (is_object($$payment)) {
          $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                          EMAIL_SEPARATOR . "\n";
          $payment_class = $$payment;
          $email_order .= $payment_class->title . "\n\n";
          if ($payment_class->email_footer) {
            $email_order .= $payment_class->email_footer . "\n\n";
          }
        }
		
		$reservation_receipt_email = sprintf(EMAIL_TEXT_SUBJECT,$item_number,$item_number);

        tep_mail($order->customer['name'],$order->customer['email_address'], $reservation_receipt_email, nl2br($email_order), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, '');

        if (SEND_EXTRA_ORDER_EMAILS_TO != '') { // send emails to other people
          tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, $reservation_receipt_email,  nl2br($email_order), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, '');
        };

        tep_db_query("delete from " .TABLE_CUSTOMERS_BASKET. " where customers_id=".$order->customer['id']);
        tep_db_query("delete from " .TABLE_CUSTOMERS_BASKET_ATTRIBUTES. " where customers_id=".$order->customer['id']);

      };

    };

  };
?>