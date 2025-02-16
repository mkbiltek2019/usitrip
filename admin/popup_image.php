<?php
/*
  $Id: popup_image.php,v 1.1.1.1 2004/03/04 23:38:52 ccwjr Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

if ($HTTP_GET_VARS['image']) {

     $big_image = tep_image(DIR_WS_CATALOG_IMAGES . $HTTP_GET_VARS['image'], $HTTP_GET_VARS['image']);

} else {

  reset($HTTP_GET_VARS);
  while (list($key, ) = each($HTTP_GET_VARS)) {
    switch ($key) {
      case 'banner':
        $banners_id = tep_db_prepare_input($HTTP_GET_VARS['banner']);

        $banner_query = tep_db_query("select banners_title, banners_image, banners_html_text from " . TABLE_BANNERS . " where banners_id = '" . (int)$banners_id . "'");
        $banner = tep_db_fetch_array($banner_query);

        $page_title = $banner['banners_title'];

        if ($banner['banners_html_text']) {
          $image_source = $banner['banners_html_text'];
        } elseif ($banner['banners_image']) {
          $image_source = tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $banner['banners_image'], $page_title);
        }
        break;
		case 'tphoto':
        $traveler_photo_id = tep_db_prepare_input($HTTP_GET_VARS['tphoto']);

        $photo_query = tep_db_query("select image_title, image_name from " . TABLE_TRAVELER_PHOTOS . " where traveler_photo_id = '" . (int)$traveler_photo_id . "'");
        $photo = tep_db_fetch_array($photo_query);

        $page_title = $photo['image_title'];

        /*if ($banner['banners_html_text']) {
          $image_source = $banner['banners_html_text'];
        } elseif ($banner['banners_image']) {
          $image_source = tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $banner['banners_image'], $page_title);
        }*/
		
		if(file_exists(DIR_FS_DOCUMENT_ROOT . '/images/reviews/detail_' . $photo['image_name']))
		{
			$image_source = tep_image(HTTP_CATALOG_SERVER.DIR_WS_CATALOG_IMAGES . 'reviews/detail_' . $photo['image_name'],''); 
		}
		else if(file_exists(DIR_FS_DOCUMENT_ROOT . '/images/reviews/thumb_' . $photo['image_name']))
		{
			$image_source = tep_image(HTTP_CATALOG_SERVER.DIR_WS_CATALOG_IMAGES . 'reviews/thumb_' . $photo['image_name']); 
		}
		else if(file_exists(DIR_FS_DOCUMENT_ROOT . '/images/reviews/' . $photo['image_name']))
		{
			$image_source = tep_image(HTTP_CATALOG_SERVER.DIR_WS_CATALOG_IMAGES . 'reviews/' . $photo['image_name'],''); 
		}
        break;
    }
  }
 }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<title><?php echo $page_title . $HTTP_GET_VARS['image']; ?></title>
<script language="javascript"><!--
var i=0;

function resize() {
  if (navigator.appName == 'Netscape') i = 30;
  window.resizeTo(document.images[0].width + 30, document.images[0].height + 100 - i);
}
//--></script>
</head>

<body onLoad="resize();">

<?php
   if ($HTTP_GET_VARS['image']) {
         echo $big_image;
         } else {
         echo $image_source;
   }
 ?>

</body>

</html>
