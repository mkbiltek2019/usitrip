<?php
/*
  $Id: authorizenet.php,v 1.1.1.1 2003/03/22 16:56:03 nickle Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2002 osCommerce
  Released under the GNU General Public License

  Traditional Chinese language pack(Big5 code) for osCommerce 2.2 ms1
  Community: http://forum.kmd.com.tw 
  Author(s): Nickle Cheng (nickle@mail.kmd.com.tw)
  Released under the GNU General Public License ,too!!

*/

  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_TITLE', 'Authorize.net');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_DESCRIPTION', '信用卡測試：<br><br>CC#: 4111111111111111<br>到期日： Any');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_TYPE', '卡別：');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_OWNER', '持卡人：');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_NUMBER', '信用卡卡號：');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_EXPIRES', '信用卡到期日：');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_OWNER', '* 持卡人姓名不得少於 ' . CC_OWNER_MIN_LENGTH . ' 字\n');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_NUMBER', '* 信用卡號碼不得少於' . CC_NUMBER_MIN_LENGTH . ' 位數\n');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_ERROR_MESSAGE', '信用卡處理錯誤，請再試一次');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_DECLINED_MESSAGE', '您的信用卡無法使用，請換一張信用卡或聯絡您的發卡銀行');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_ERROR', '信用卡錯誤！');

?>