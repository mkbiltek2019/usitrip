<?php
/*
  $Id: checkout_update.inc.php,v 2.8 2004/09/11 devosc Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  DevosC, Developing open source Code
  http://www.devosc.com

  Copyright (c) 2003 osCommerce
  Copyright (c) 2004 DevosC.com

  Released under the GNU General Public License
*/

  $PayPal_osC_Order->setAccountHistoryInfoURL(tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $PayPal_osC_Order->orderID, 'SSL', false));
  $PayPal_osC_Order->setCheckoutProcessLanguageFile(DIR_WS_LANGUAGES . $PayPal_osC_Order->language . '/' . FILENAME_CHECKOUT_PROCESS);
  $PayPal_osC_Order->updateProducts($order,$currencies);
  $PayPal_osC_Order->notifyCustomer($order);
  $PayPal_osC_Order->updateOrderStatus(MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID);
  $PayPal_osC_Order->removeOrdersSession();
?>
