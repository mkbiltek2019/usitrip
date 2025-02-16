<?php
/*
  $Id: html_output.php,v 1.2 2004/03/05 00:36:42 ccwjr Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

////
// The HTML href link wrapper function
  function tep_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true) {
    global $request_type, $session_started, $SID, $spider_flag;

//以下是通过404.php处理的部分
//information.php	公告新闻等页面
	//if($page=='information.php' && preg_match('/^info_id=(\d+)$/',$parameters) ){
	if($page=='information.php'){
		$information_s = 'information';
		if(preg_match('/^info_id=(\d+)$/',$parameters)) $information_s = 'informations';
		$link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
		$link .= $information_s.'/'.preg_replace('/^info_id=(\d+)$/','$1',$parameters);
		
		return $link;
	}
	
	
//以下处理是为伪静态服务
	$page = preg_replace('/\..*$/','.html',$page);

	$page_e_p = array();
	$page_e_p[0] = 'product_details';
	$page_e_p[1] = 'entries_details';
	$page_e_p[2] = 'designer_index';
	
	$page_e_p[3] = 'product_list';
	$page_e_p[4] = 'entries_list';
	
	$page_e_p[5] = 'tags_results';
	
	for($i=0; $i<count($page_e_p); $i++){
		if(preg_replace('/\..*$/','',$page) == $page_e_p[$i]){
			
			//产品和设计详细页面
			if(preg_match('/entries_id=\d+/',$parameters)){
				$int = preg_replace('/.*entries_id=/','',$parameters);
				$int = preg_replace('/(\d+).*/','$1',$int);
				
				$page = $page_e_p[$i].'-e'.(int)$int.'.html';
				$parameters = preg_replace('/entries_id=\d+/','',$parameters);
			}
			//设计师公共首页
			if(preg_match('/customers_id=\d+/',$parameters)){
				$int = preg_replace('/.*customers_id=/','',$parameters);
				$int = preg_replace('/(\d+).*/','$1',$int);
				
				$page = $page_e_p[$i].'-c'.(int)$int.'.html';
				$parameters = preg_replace('/customers_id=\d+/','',$parameters);
			}
			
			//Tags标签部分
			if(preg_match('/tags_id=\d+/',$parameters)){
				$int = preg_replace('/.*tags_id=/','',$parameters);
				$int = preg_replace('/(\d+).*/','$1',$int);
				
				$page = $page_e_p[$i].'-t'.(int)$int.'.html';
				$parameters = preg_replace('/tags_id=\d+/','',$parameters);
			}

			$page = preg_replace('/_/','-',$page);
			
			break;
			
		}
	}
	
	
//以下处理是为伪静态服务 end


    if (!tep_not_null($page)) {
     // die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine the page link!<br><br>');
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000">'.TEP_HREF_LINK_ERROR1);
    }

    if ($connection == 'NONSSL') {
      $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
    } elseif ($connection == 'SSL') {
      if (ENABLE_SSL == true) {
        $link = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
      } else {
        $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
      }
    } else {
      //die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine connection method on a link!<br><br>Known methods: NONSSL SSL</b><br><br>');
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000">'.TEP_HREF_LINK_ERROR2);
    }

    if (tep_not_null($parameters)) {
      while ( (substr($parameters, -5) == '&amp;') ) $parameters = substr($parameters, 0, strlen($parameters)-5);
      $link .= $page . '?' . tep_output_string($parameters);
      $separator = '&amp;';
    } else {
      $link .= $page;
      $separator = '?';
    }

    // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    // there is a minor logic problem with the original osCommerce code
    // the SESSION_FORCE_COOKIE_USE must not be honored if changing from nonssl to ssl
    
    /*
    if ( ($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False') ) {
      if (tep_not_null($SID)) {
        $_sid = $SID;
      } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
        if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN) {
          $_sid = tep_session_name() . '=' . tep_session_id();
        }
      }
    }
    */
    // if session is not started or requested not to add session, skip it
    if ( ($add_session_id == true) && ($session_started == true) ){

      // if cookies are not set and not forced, then add the session info incase the set cookie fails 
      if ( ! isset($_COOKIE[tep_session_name()]) && (SESSION_FORCE_COOKIE_USE == 'False')) {
        $_sid = tep_session_name() . '=' . tep_session_id();
        
      // if we are chaning modes and cookie domains differ, we need to add the session info
      } elseif ( HTTP_COOKIE_DOMAIN . HTTP_COOKIE_PATH != HTTPS_COOKIE_DOMAIN . HTTPS_COOKIE_PATH
                 &&
                 (
                   ( $request_type == 'NONSSL' && $connection == 'SSL' && ENABLE_SSL == true )
                   ||
                   ( $request_type == 'SSL' && $connection == 'NONSSL' )
                 )
               ) {
        $_sid = tep_session_name() . '=' . tep_session_id();
      }	  
    
    }
    
    if (isset($_sid) && !$spider_flag ) {
      $link .= $separator . tep_output_string($_sid);
    }
    return $link;
  }

////
// The HTML image wrapper function
  function tep_image($src, $alt = '', $width = '', $height = '', $parameters = '') {
    if ( (empty($src) || ($src == DIR_WS_IMAGES)) && (IMAGE_REQUIRED == 'false') ) {
      return false;
    }

// alt is added to the img tag even if it is null to prevent browsers from outputting
// the image filename as default
    $image = '<img src="' . tep_output_string($src) . '" border="0" alt="' . tep_output_string($alt) . '"';

    if (tep_not_null($alt)) {
      $image .= ' title=" ' . tep_output_string($alt) . ' "';
    }

    if ( (CONFIG_CALCULATE_IMAGE_SIZE == 'true') && (empty($width) || empty($height)) ) {
      if ($image_size = @getimagesize($src)) {
        if (empty($width) && tep_not_null($height)) {
          $ratio = $height / $image_size[1];
          $width = $image_size[0] * $ratio;
        } elseif (tep_not_null($width) && empty($height)) {
          $ratio = $width / $image_size[0];
          $height = $image_size[1] * $ratio;
        } elseif (empty($width) && empty($height)) {
          $width = $image_size[0];
          $height = $image_size[1];
        }
      } elseif (IMAGE_REQUIRED == 'false') {
        return false;
      }
    }

    if (tep_not_null($width) && tep_not_null($height)) {
      $image .= ' width="' . tep_output_string($width) . '" height="' . tep_output_string($height) . '"';
    }

    if (tep_not_null($parameters)) $image .= ' ' . $parameters;

    $image .= '>';

    return $image;
  }


// The Javascript Image wrapper build a image tag for a dummy picture,
// then uses javascript to load the actual picure.  This approach prevents spiders from stealing images.
  function tep_javascript_image($src, $name, $alt = '', $width = '', $height = '', $parameters = '', $popup = 'false') {
    global $product_info;
    $image = '';
    if ( empty($name) || ((empty($src) || ($src == DIR_WS_IMAGES)) && (IMAGE_REQUIRED == 'false')) ) {
      return false;
    }
    // Do we need to add the pop up link code?
    if ( $popup ) {
      $image .= '<div align="center"><a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_POPUP_IMAGE, 'pID=' . $product_info['products_id'] . '&image=0') . '\')">' . "\n";
    }
    // alt is added to the img tag even if it is null to prevent browsers from outputting
    // the image filename as default
    $image .= '<img name="' . tep_output_string($name) . '" src="' . DIR_WS_IMAGES . 'pixel_trans.gif" border="0" alt="' . tep_output_string($alt) . '"';

    if (tep_not_null($alt)) {
      $image .= ' title=" ' . tep_output_string($alt) . ' "';
    }

    if ( (CONFIG_CALCULATE_IMAGE_SIZE == 'true') && (empty($width) || empty($height)) ) {
      if ($image_size = @getimagesize($src)) {
        if (empty($width) && tep_not_null($height)) {
          $ratio = $height / $image_size[1];
          $width = $image_size[0] * $ratio;
        } elseif (tep_not_null($width) && empty($height)) {
          $ratio = $width / $image_size[0];
          $height = $image_size[1] * $ratio;
        } elseif (empty($width) && empty($height)) {
          $width = $image_size[0];
          $height = $image_size[1];
        }
      } elseif (IMAGE_REQUIRED == 'false') {
        return false;
      }
    }

    if (tep_not_null($width) && tep_not_null($height)) {
      $image .= ' width="' . tep_output_string($width) . '" height="' . tep_output_string($height) . '"';
    }

    if (tep_not_null($parameters)) $image .= ' ' . $parameters;

    $image .= '>' . "\n";
    
    if ( $popup ) {
      $image .= '<br>' . tep_template_image_button('image_enlarge.gif', TEXT_CLICK_TO_ENLARGE) . '</a></div>' . "\n";
    }
    
    // Now for the Javascript loading code
    $image .= '<script type="text/javascript"><!-- ' . "\n";
    $image .= "document['" . tep_output_string($name) . "'].src = '" . tep_output_string($src) . "'" . "\n";
    $image .= ' //--></script>' ."\n";

    return $image;
  }


  
////
// The HTML form submit button wrapper function
// Outputs a button in the selected language
  function tep_image_submit($image, $alt = '', $parameters = '') {
    global $language;

    $image_submit = '<input type="image" src="' . tep_output_string(DIR_WS_TEMPLATE_IMAGES . 'buttons/' . $language . '/' .  $image) . '" alt="' . tep_output_string($alt) . '"';

// EOM
    if (tep_not_null($alt)) $image_submit .= ' title=" ' . tep_output_string($alt) . ' "';

    if (tep_not_null($parameters)) $image_submit .= ' ' . $parameters;

    $image_submit .= '>';
    return $image_submit;

  }
////
// Output a function button in the selected language
  function tep_image_button($image, $alt = '', $parameters = '') {
    global $language;
      $image_button = tep_image(DIR_WS_TEMPLATE_IMAGES . 'buttons/' . $language . '/' .  $image, $alt, '', '', $parameters);

 return $image_button;
// EOM
  }

////
// The HTML form submit button wrapper function
// Outputs a button in the selected language
  function tep_image_nontemplate_submit($image, $alt = '', $parameters = '') {
    global $language;
// BOM Mod: force all buttons to come from the tempalte folders
  $image_submit = '<input type="image" src="' . tep_output_string($image) . '" alt="' . tep_output_string($alt) . '"';
//    $image_submit = '<input type="image" src="' . tep_output_string(DIR_WS_TEMPLATES . TEMPLATE_NAME . '/images/buttons/' . $language . '/' .  $image) . '" alt="' . tep_output_string($alt) . '"';
// EOM
    if (tep_not_null($alt)) $image_submit .= ' title=" ' . tep_output_string($alt) . ' "';

    if (tep_not_null($parameters)) $image_submit .= ' ' . $parameters;

    $image_submit .= '>';

    return $image_submit;
  }

////
// Output a function button in the selected language
  function tep_image_nontemplate_button($image, $alt = '', $parameters = '') {
    global $language;
// BOM Mod: force all buttons to come from the tempalte folders
    return tep_image($image, $alt, '', '', $parameters);
//     return tep_image(DIR_WS_TEMPLATES . TEMPLATE_NAME . '/images/buttons/' . $language . '/' .  $image, $alt, '', '', $parameters);
// EOM
  }

////
// Output a separator either through whitespace, or with an image
  function tep_draw_separator($image = 'pixel_black.gif', $width = '100%', $height = '1') {
    return tep_image(DIR_WS_IMAGES . $image, '', $width, $height);
  }

////
// Output a form
  function tep_draw_form($name, $action, $method = 'post', $parameters = '') {
    $form = '<form name="' . tep_output_string($name) . '" action="' . tep_output_string($action) . '" method="' . tep_output_string($method) . '"';

    if (tep_not_null($parameters)) $form .= ' ' . $parameters;

    $form .= '>';

    return $form;
  }

////
// Output a form input field
  function tep_draw_input_field($name, $value = '', $parameters = '', $type = 'text', $reinsert_value = true) {
    $field = '<input type="' . tep_output_string($type) . '" id="'.tep_output_string($name).'" name="' . tep_output_string($name) . '"';

    if ( (isset($GLOBALS[$name])) && ($reinsert_value == true) ) {
      $field .= ' value="' . tep_output_string(stripslashes($GLOBALS[$name])) . '"';
    } elseif (tep_not_null($value)) {
      $field .= ' value="' . tep_output_string($value) . '"';
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    return $field;
  }

////
// Output a form password field
  function tep_draw_password_field($name, $value = '', $parameters = 'maxlength="40"') {
    return tep_draw_input_field($name, $value, $parameters, 'password', false);
  }

////
// Output a selection field - alias function for tep_draw_checkbox_field() and tep_draw_radio_field()
  function tep_draw_selection_field($name, $type, $value = '', $checked = false, $parameters = '') {
    $selection = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if (tep_not_null($value)) $selection .= ' value="' . tep_output_string($value) . '"';

    //if ( ($checked == true) || ( isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) && ( ($GLOBALS[$name] == 'on') || (isset($value) && (stripslashes($GLOBALS[$name]) == $value)) ) ) ) {
    if ( ($checked == true) || ( stripslashes($GLOBALS[$name]) == $value && $value !="" ) ) {
      $selection .= ' CHECKED';
    }

    if (tep_not_null($parameters)) $selection .= ' ' . $parameters;

    $selection .= '>';

    return $selection;
  }

////
// Output a form checkbox field
  function tep_draw_checkbox_field($name, $value = '', $checked = false, $parameters = '') {
    return tep_draw_selection_field($name, 'checkbox', $value, $checked, $parameters);
  }

////
// Output a form radio field
  function tep_draw_radio_field($name, $value = '', $checked = false, $parameters = '') {
    return tep_draw_selection_field($name, 'radio', $value, $checked, $parameters);
  }

////
// Output a form textarea field
  function tep_draw_textarea_field($name, $wrap, $width, $height, $text = '', $parameters = '', $reinsert_value = true) {
    $field = '<textarea id="'.tep_output_string($name).'" name="' . tep_output_string($name) . '" wrap="' . tep_output_string($wrap) . '" cols="' . tep_output_string($width) . '" rows="' . tep_output_string($height) . '"';

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if ( (isset($GLOBALS[$name])) && ($reinsert_value == true) ) {
      $field .= tep_output_string_protected(stripslashes($GLOBALS[$name]));
    } elseif (tep_not_null($text)) {
      $field .= tep_output_string_protected($text);
    }

    $field .= '</textarea>';

    return $field;
  }

////
// Output a form hidden field
  function tep_draw_hidden_field($name, $value = '', $parameters = '') {
    $field = '<input type="hidden" name="' . tep_output_string($name) . '"';

    if (tep_not_null($value)) {
      $field .= ' value="' . tep_output_string($value) . '"';
    } elseif (isset($GLOBALS[$name])) {
      $field .= ' value="' . tep_output_string(stripslashes($GLOBALS[$name])) . '"';
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    return $field;
  }

////
// Hide form elements
  function tep_hide_session_id() {
    global $session_started, $SID;

    if (($session_started == true) && tep_not_null($SID)) {
      return tep_draw_hidden_field(tep_session_name(), tep_session_id());
    }
  }

////
// Output a form pull down menu
  function tep_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
    $field = '<select name="' . tep_output_string($name) . '" id="' . tep_output_string($name) . '"';

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if (empty($default) && isset($GLOBALS[$name])) $default = stripslashes($GLOBALS[$name]);

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . tep_output_string($values[$i]['id']) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' SELECTED';
      }

      $field .= '>' . tep_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }

////
// Creates a pull-down list of countries
  function tep_get_country_list($name, $selected = '', $parameters = '') {
    $countries_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
    $countries = tep_get_countries();

    for ($i=0, $n=sizeof($countries); $i<$n; $i++) {
      $countries_array[] = array('id' => $countries[$i]['countries_id'], 'text' => $countries[$i]['countries_name']);
    }

    return tep_draw_pull_down_menu($name, $countries_array, $selected, $parameters);
  }
?>
