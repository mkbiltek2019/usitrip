<?php
/*
  $Id: links_submit_success.php,v 1.1.1.1 2004/03/04 23:38:00 ccwjr Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require(DIR_FS_LANGUAGES . $language . '/' . FILENAME_LINKS_SUBMIT_SUCCESS);

  // links breadcrumb
  $breadcrumb->add(NAVBAR_TITLE_1, FILENAME_LINKS);
  $breadcrumb->add(NAVBAR_TITLE_2);

  if (sizeof($navigation->snapshot) > 0) {
    $origin_href = tep_href_link($navigation->snapshot['page'], tep_array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
    $navigation->clear_snapshot();
  } else {
    $origin_href = tep_href_link(FILENAME_DEFAULT);
  }


  $content = CONTENT_LINKS_SUBMIT_SUCCESS;

  require(DIR_FS_TEMPLATES . TEMPLATE_NAME . '/' . TEMPLATENAME_MAIN_PAGE);

  require(DIR_FS_INCLUDES . 'application_bottom.php');
?>
