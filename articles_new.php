<?php
/*
  $Id: articles_new.php, v1.0 2003/12/04 12:00:00 ra Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  require(DIR_FS_LANGUAGES . $language . '/' . FILENAME_ARTICLES_NEW);

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_ARTICLES_NEW));

  $content = CONTENT_ARTICLES_NEW;

  require(DIR_FS_TEMPLATES . TEMPLATE_NAME . '/' . TEMPLATENAME_MAIN_PAGE);  
  
  require(DIR_FS_INCLUDES . 'application_bottom.php'); ?>