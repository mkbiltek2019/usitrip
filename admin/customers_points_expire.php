<?php
/*
  $Id: customers_points_expire.php, V2.1rc2a 2008/SEP/29 11:05:12 dsa_ Exp $
  created by Ben Zukrel, Deep Silver Accessories
  http://www.deep-silver.com

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2005 osCommerce

  Released under the GNU General Public License
*/

  include_once('includes/application_top.php');
  
if ((USE_POINTS_SYSTEM == 'true') && tep_not_null(POINTS_AUTO_EXPIRES)){
/*  tep_db_query("update " . TABLE_CUSTOMERS . " set customers_shopping_points = null, customers_points_expires = null where customers_points_expires < CURDATE()");

  if (tep_not_null(POINTS_EXPIRES_REMIND)){
  
    include_once(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CUSTOMERS_POINTS_PENDING);

    $customer_query = tep_db_query("select customers_gender, customers_lastname, customers_firstname, customers_email_address, customers_shopping_points, customers_points_expires from " . TABLE_CUSTOMERS . " where (CURDATE() + '". (int)POINTS_EXPIRES_REMIND ."') = customers_points_expires");
    while($customer = tep_db_fetch_array($customer_query)){
    $customers_email_address = $customer['customers_email_address'];
    $gender = $customer['customers_gender'];
    $first_name = $customer['customers_firstname'];
    $last_name = $customer['customers_lastname'];
    $name = $first_name . ' ' . $last_name;

    if (ACCOUNT_GENDER == 'true') {
      if ($gender == 'm') {
        $greet = sprintf(EMAIL_GREET_MR, $last_name);
      } else {
        $greet = sprintf(EMAIL_GREET_MS, $last_name);
      }
    } else {

    $greet = sprintf(EMAIL_GREET_NONE, $first_name);
    }
    $can_use = "\n\n" . EMAIL_TEXT_SUCCESS_POINTS;

    $email_text = $greet  . "\n" . EMAIL_EXPIRE_INTRO . "\n" . sprintf(EMAIL_EXPIRE_DET, number_format($customer['customers_shopping_points'],POINTS_DECIMAL_PLACES), tep_date_short($customer['customers_points_expires'])) . "\n" . EMAIL_EXPIRE_TEXT . "\n\n" . sprintf(EMAIL_TEXT_POINTS_URL, tep_catalog_href_link(FILENAME_CATALOG_MY_POINTS, '', 'SSL')) . "\n\n" . sprintf(EMAIL_TEXT_POINTS_URL_HELP, tep_catalog_href_link(FILENAME_CATALOG_MY_POINTS_HELP, '', 'NONSSL')) . $can_use . "\n" . EMAIL_CONTACT . "\n" . EMAIL_SEPARATOR . "\n" . '<b>' . STORE_NAME . '</b>.' . "\n";

    tep_mail($name, $customer['customers_email_address'], EMAIL_EXPIRE_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
    }
    echo 'Done!<br />You may close this window now. ';
  }
*/}
