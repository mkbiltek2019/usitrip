<?php
/*
  $Id: create_account_success.php,v 1.1.1.1 2004/03/04 23:37:58 ccwjr Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

define('NO_SET_SNAPSHOT','1');
require('includes/application_top.php');
/*
  require(DIR_FS_LANGUAGES . $language . '/' . FILENAME_CREATE_ACCOUNT_SUCCESS);

  $breadcrumb->add(NAVBAR_TITLE_1);
  $breadcrumb->add(NAVBAR_TITLE_2);

  if (sizeof($navigation->snapshot) > 0) {
    $origin_href = tep_href_link($navigation->snapshot['page'], tep_array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
    $navigation->clear_snapshot();
	 tep_redirect($origin_href);
	 die();
  } else {
    //$origin_href = tep_href_link(FILENAME_ACCOUNT);
	$origin_href = tep_href_link(FILENAME_DEFAULT);
  }
*/
  $content = CONTENT_CREATE_ACCOUNT_SUCCESS;

  require(DIR_FS_TEMPLATES . TEMPLATE_NAME . '/' . TEMPLATENAME_MAIN_PAGE);


require(DIR_FS_INCLUDES . 'application_bottom.php');
 
 
?>
