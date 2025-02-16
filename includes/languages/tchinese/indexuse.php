<?php
/*
  $Id: index.php,v 1.1 2003/06/11 17:38:00 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/




//FOR TABEL HEADIGND
define('TABLE_HEADING_NEW_PRODUCTS', '特色景點');
define('TABLE_HEADING_UPCOMING_PRODUCTS', '即將推出的商品');
define('TABLE_HEADING_UPCOMING_SERVICES_ARTICLES', '即將推出的服務、工作坊');
define('TABLE_HEADING_DEFAULT_SPECIALS', '%s 特別推薦的特價商品');


define('TEXT_VIEW', '查看更多關於');
define('TEXT_TOURS', '點擊查看全部');

define('TABLE_HEADING_DATE_EXPECTED', '預計日期');

if ( ($category_depth == 'products') || (isset($HTTP_GET_VARS['manufacturers_id'])) ) {
//  define('HEADING_TITLE', '來瞧瞧我們店�埵酗偵�');
  define('HEADING_TITLE', '');
  define('TABLE_HEADING_IMAGE', '圖片');
  define('TABLE_HEADING_MODEL', '種類');
  define('TABLE_HEADING_PRODUCTS', '商品名稱');
  define('TABLE_HEADING_MANUFACTURER', '製造廠商');
  define('TABLE_HEADING_QUANTITY', '數量');
  define('TABLE_HEADING_PRICE', '價格');
  define('TABLE_HEADING_WEIGHT', '重量');
  define('TABLE_HEADING_BUY_NOW', '下手了');
  define('TEXT_NO_PRODUCTS', '無此類相關產品');
  define('TEXT_NO_PRODUCTS2', '本製造場商目前沒有任何商品.');
  define('TEXT_NUMBER_OF_PRODUCTS', '商品數量: ');
  define('TEXT_SHOW', '<b>顯示:</b>');
  define('TEXT_BUY', '增加對購物籃子');
  define('TEXT_NOW', '\' 現在');
  define('TEXT_ALL_CATEGORIES', '景點目錄');
  define('TEXT_ALL_MANUFACTURERS', '所有製造商');
} elseif ($category_depth == 'top') {
  define('HEADING_TITLE', '新到景點？');
} elseif ($category_depth == 'nested') {
  define('HEADING_TITLE', '景點目錄');
}

define('TEXT_DURATION_DAY','天');
define('TEXT_DURATION_HOUR','小時');
define('TEXT_DURATION_DAYS','天');
define('TEXT_DURATION_HOURS','小時');

define('HEADING_TEXT_NEW_ARRIVE','<strong>新品推薦</strong>');
define('HEADING_TEXT_ON_SALES','<strong>近期促銷</strong>');
define('HEADING_TEXT_BEST_SALLERS','<strong>精品路線</strong>');
define('TEXT_SHOW_HIDE_MAP','顯示 / 隱藏 地圖');
define('TEXT_DIRATION_MINUTES','分鐘');
?>
