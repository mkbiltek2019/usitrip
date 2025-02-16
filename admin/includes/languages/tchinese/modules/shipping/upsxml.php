<?php
/*
  $Id: upsxml.php,v 1.5 2003/06/27 01:03:03 torinwalker Exp $

  Written by Torin Walker
  torinwalker@rogers.com

  Copyright(c) 2003 by Torin Walker, All rights reserved.

  Released under the GNU General Public License
*/

	define('MODULE_SHIPPING_UPSXML_RATES_TEXT_TITLE', 'United Parcel Service (XML)');
	define('MODULE_SHIPPING_UPSXML_RATES_TEXT_DESCRIPTION', 'United Parcel Service (XML)');
	define('MODULE_SHIPPING_UPSXML_RATES_TEXT_UNKNOWN_ERROR', 'An unknown error occured with the ups shipping calculations.');
	define('MODULE_SHIPPING_UPSXML_RATES_TEXT_IF_YOU_PREFER', 'If you prefer to use ups as your shipping method, please contact');
	define('MODULE_SHIPPING_UPSXML_RATES_TEXT_COMM_ERROR', 'A communication error occured while attempting to contact the UPS gateway');
	define('MODULE_SHIPPING_UPSXML_RATES_TEXT_COMM_UNKNOWN_ERROR', 'An unknown error occured while attempting to contact the UPS gateway');
	define('MODULE_SHIPPING_UPSXML_RATES_TEXT_COMM_VERSION_ERROR', 'This module supports only xpci version 1.0001 of the UPS Rates Interface. Please contact the webmaster for additional assistance.');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_US_ORIGIN_01', 'UPS Next Day Air&reg;');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_US_ORIGIN_02', 'UPS 2nd Day Air&reg;');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_US_ORIGIN_03', 'UPS Ground');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_US_ORIGIN_07', 'UPS Worldwide Express<sup>SM</sup>');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_US_ORIGIN_08', 'UPS Worldwide Expedited<sup>SM</sup>');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_US_ORIGIN_11', 'UPS Standard');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_US_ORIGIN_12', 'UPS 3 Day Select&reg;');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_US_ORIGIN_13', 'UPS Next Day Air Saver&reg;');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_US_ORIGIN_14', 'UPS Next Day Air&reg; Early A.M.&reg;');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_US_ORIGIN_54', 'UPS Worldwide Express Plus<sup>SM</sup>');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_US_ORIGIN_59', 'UPS 2nd Day Air A.M.<sup>SM</sup>');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_US_ORIGIN_65', 'UPS Express Saver&reg;');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_CANADA_ORIGIN_01', 'UPS Express');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_CANADA_ORIGIN_02', 'UPS Expedited');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_CANADA_ORIGIN_07', 'UPS Worldwide Express<sup>SM</sup>');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_CANADA_ORIGIN_08', 'UPS Worldwide Expedited<sup>SM</sup>');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_CANADA_ORIGIN_11', 'UPS Standard');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_CANADA_ORIGIN_12', 'UPS 3 Day Select&reg;');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_CANADA_ORIGIN_13', 'UPS Express Saver&reg;');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_CANADA_ORIGIN_14', 'UPS Express Early A.M.&reg;');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_CANADA_ORIGIN_54', 'UPS Worldwide Express Plus<sup>SM</sup>');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_EU_ORIGIN_07', 'UPS Express');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_EU_ORIGIN_08', 'UPS Expedited');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_EU_ORIGIN_11', 'UPS Standard');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_EU_ORIGIN_54', 'UPS Worldwide Express Plus<sup>SM</sup>');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_EU_ORIGIN_65', 'UPS Express NA1');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_EU_ORIGIN_69', 'UPS Express Saver&reg;');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_PR_ORIGIN_01', 'UPS Next Day Air&reg;');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_PR_ORIGIN_02', 'UPS 2nd Day Air&reg;');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_PR_ORIGIN_03', 'UPS Ground');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_PR_ORIGIN_07', 'UPS Worldwide Express<sup>SM</sup>');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_PR_ORIGIN_08', 'UPS Worldwide Expedited<sup>SM</sup>');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_PR_ORIGIN_14', 'UPS Next Day Air&reg; Early A.M.&reg;');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_PR_ORIGIN_54', 'UPS Worldwide Express Plus<sup>SM</sup>');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_MEXICO_ORIGIN_07', 'UPS Express');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_MEXICO_ORIGIN_08', 'UPS Expedited');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_MEXICO_ORIGIN_54', 'UPS Express Plus');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_OTHER_ORIGIN_07', 'UPS Worldwide Express<sup>SM</sup>');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_OTHER_ORIGIN_08', 'UPS Worldwide Expedited<sup>SM</sup>');
	define('MODULE_SHIPPING_UPSXML_SERVICE_CODE_OTHER_ORIGIN_54', 'UPS Worldwide Express Plus<sup>SM</sup>');
?>
