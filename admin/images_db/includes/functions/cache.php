<?php
/*
  $Id: cache.php,v 1.1.1.1 2004/03/04 23:40:48 ccwjr Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

////
//! Write out serialized data.
//  write_cache uses serialize() to store $var in $filename.
//  $var      -  The variable to be written out.
//  $filename -  The name of the file to write to.
  function write_cache(&$var, $filename) {
    $filename = DIR_FS_CATALOG . DIR_FS_CACHE . $filename;
    $success = false;

// try to open the file
    if ($fp = @fopen($filename, 'w')) {
// obtain a file lock to stop corruptions occuring
      flock($fp, 2); // LOCK_EX
// write serialized data
      fputs($fp, serialize($var));
// release the file lock
      flock($fp, 3); // LOCK_UN
      fclose($fp);
      $success = true;
    }

    return $success;
  }

////
//! Read in seralized data.
//  read_cache reads the serialized data in $filename and
//  fills $var using unserialize().
//  $var      -  The variable to be filled.
//  $filename -  The name of the file to read.
  function read_cache(&$var, $filename, $auto_expire = false){
    $filename = DIR_FS_CATALOG . DIR_FS_CACHE . $filename;
    $success = false;

    if (($auto_expire == true) && file_exists($filename)) {
      $now = time();
      $filetime = filemtime($filename);
      $difference = $now - $filetime;

      if ($difference >= $auto_expire) {
        return false;
      }
    }

// try to open file
    if ($fp = @fopen($filename, 'r')) {
// read in serialized data
      $szdata = fread($fp, filesize($filename));
      fclose($fp);
// unserialze the data
      $var = unserialize($szdata);

      $success = true;
    }

    return $success;
  }

////
//! Get data from the cache or the database.
//  get_db_cache checks the cache for cached SQL data in $filename
//  or retreives it from the database is the cache is not present.
//  $SQL      -  The SQL query to exectue if needed.
//  $filename -  The name of the cache file.
//  $var      -  The variable to be filled.
//  $refresh  -  Optional.  If true, do not read from the cache.
  function get_db_cache($sql, &$var, $filename, $refresh = false){
    $var = array();

// check for the refresh flag and try to the data
    if (($refresh == true)|| !read_cache($var, $filename)) {
// Didn' get cache so go to the database.
//      $conn = mysql_connect("localhost", "apachecon", "apachecon");
      $res = tep_db_query($sql);
//      if ($err = mysql_error()) trigger_error($err, E_USER_ERROR);
// loop through the results and add them to an array
      while ($rec = tep_db_fetch_array($res)) {
        $var[] = $rec;
      }
// write the data to the file
      write_cache($var, $filename);
    }
  }

////
// Cache the categories box
  function tep_cache_coolmenu($auto_expire = false, $refresh = false) {
    global $cPath, $language, $languages_id, $tree, $cPath_array, $categories_string, $foo, $id, $aa;

    if (($refresh == true) || !read_cache($cache_output, 'tep_cache_coolmenu-'. TEMPLATE_NAME . '.'  . $language . '.cache' . $cPath, $auto_expire)) {

//get default template name
  $customer_template_query = tep_db_query("select  customers_selected_template as template_selected from " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'");
  $cptemplate1 = tep_db_fetch_array($customer_template_query);

   if (tep_not_null($cptemplate1['template_selected'])) {
  define(TEMPLATE_NAME, $cptemplate['template_selected']);
    }else if  (tep_not_null(DEFAULT_TEMPLATE)){
  define(TEMPLATE_NAME, DEFAULT_TEMPLATE);  
    } else {
    define(TEMPLATE_NAME, 'default');
    }
 //set all possible catagories X.php names    
     ob_start();
       include(DIR_WS_TEMPLATES . TEMPLATE_NAME . '/boxes/coolmenu.php');
      $cache_output = ob_get_contents();
      ob_end_clean();
      write_cache($cache_output, 'coolmenu-'. TEMPLATE_NAME . '.'  . $language . '.cache' . $cPath);
    }

    return $cache_output;
  }

////
// Cache the categories box
  function tep_cache_categories_box($auto_expire = false, $refresh = false) {
    global $cPath, $language, $languages_id, $tree, $cPath_array, $categories_string, $foo, $id, $aa;

    if (($refresh == true) || !read_cache($cache_output, 'categories_box-'. TEMPLATE_NAME . '.'  . $language . '.cache' . $cPath, $auto_expire)) {

//get default template name
  $customer_template_query = tep_db_query("select  customers_selected_template as template_selected from " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'");
  $cptemplate1 = tep_db_fetch_array($customer_template_query);

   if (tep_not_null($cptemplate1['template_selected'])) {
  define(TEMPLATE_NAME, $cptemplate['template_selected']);
    }else if  (tep_not_null(DEFAULT_TEMPLATE)){
  define(TEMPLATE_NAME, DEFAULT_TEMPLATE);  
    } else {
    define(TEMPLATE_NAME, 'default');
    }
 //set all possible catagories X.php names    
     ob_start();
       include(DIR_WS_TEMPLATES . TEMPLATE_NAME . '/boxes/categories.php');
      $cache_output = ob_get_contents();
      ob_end_clean();
      write_cache($cache_output, 'categories_box-'. TEMPLATE_NAME . '.'  . $language . '.cache' . $cPath);
    }

    return $cache_output;
  }

////
// Cache the categories box1
function tep_cache_categories_box1($auto_expire = false, $refresh = false) {
    global $cPath, $language, $languages_id, $tree, $cPath_array, $categories_string1, $foo, $id, $aa;

    if (($refresh == true) || !read_cache($cache_output, 'categories1_box-' . TEMPLATE_NAME . '.' . $language . '.cache' . $cPath, $auto_expire)) {

//get default template name
  $customer_template_query = tep_db_query("select  customers_selected_template as template_selected from " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'");
  $cptemplate1 = tep_db_fetch_array($customer_template_query);

   if (tep_not_null($cptemplate1['template_selected'])) {
  define(TEMPLATE_NAME, $cptemplate['template_selected']);
    }else if  (tep_not_null(DEFAULT_TEMPLATE)){
  define(TEMPLATE_NAME, DEFAULT_TEMPLATE);  
    } else {
    define(TEMPLATE_NAME, 'default');
    }
 //set all possible catagories X.php names    
     ob_start();
       include(DIR_WS_TEMPLATES . TEMPLATE_NAME . '/boxes/categories1.php');
      $cache_output = ob_get_contents();
      ob_end_clean();
      write_cache($cache_output, 'categories1_box-' . TEMPLATE_NAME . '.' . $language . '.cache' . $cPath);
    }

    return $cache_output;
  }
////
// Cache the categories box
  function tep_cache_categories_box2($auto_expire = false, $refresh = false) {
    global $cPath, $language, $languages_id, $tree, $cPath_array, $categories_string, $foo, $id, $aa;

    if (($refresh == true) || !read_cache($cache_output, 'categories2_box-'. TEMPLATE_NAME . '.'  . $language . '.cache' . $cPath, $auto_expire)) {

//get default template name
  $customer_template_query = tep_db_query("select  customers_selected_template as template_selected from " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'");
  $cptemplate1 = tep_db_fetch_array($customer_template_query);

   if (tep_not_null($cptemplate1['template_selected'])) {
  define(TEMPLATE_NAME, $cptemplate['template_selected']);
    }else if  (tep_not_null(DEFAULT_TEMPLATE)){
  define(TEMPLATE_NAME, DEFAULT_TEMPLATE);  
    } else {
    define(TEMPLATE_NAME, 'default');
    }
 //set all possible catagories X.php names    
     ob_start();
       include(DIR_WS_TEMPLATES . TEMPLATE_NAME . '/boxes/categories2.php');
      $cache_output = ob_get_contents();
      ob_end_clean();
      write_cache($cache_output, 'categories2_box-'. TEMPLATE_NAME . '.'  . $language . '.cache' . $cPath);
    }

    return $cache_output;
  }
////
// Cache the categories box
  function tep_cache_categories_box3($auto_expire = false, $refresh = false) {
    global $cPath, $language, $languages_id, $tree, $cPath_array, $categories_string, $foo, $id, $aa;

    if (($refresh == true) || !read_cache($cache_output, 'categories3_box-'. TEMPLATE_NAME . '.'  . $language . '.cache' . $cPath, $auto_expire)) {

//get default template name
  $customer_template_query = tep_db_query("select  customers_selected_template as template_selected from " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'");
  $cptemplate1 = tep_db_fetch_array($customer_template_query);

   if (tep_not_null($cptemplate1['template_selected'])) {
  define(TEMPLATE_NAME, $cptemplate['template_selected']);
    }else if  (tep_not_null(DEFAULT_TEMPLATE)){
  define(TEMPLATE_NAME, DEFAULT_TEMPLATE);  
    } else {
    define(TEMPLATE_NAME, 'default');
    }
 //set all possible catagories X.php names    
     ob_start();
       include(DIR_WS_TEMPLATES . TEMPLATE_NAME . '/boxes/categories3.php');
      $cache_output = ob_get_contents();
      ob_end_clean();
      write_cache($cache_output, 'categories3_box-'. TEMPLATE_NAME . '.'  . $language . '.cache' . $cPath);
    }

    return $cache_output;
  }

////
// Cache the categories box4
function tep_cache_categories_box4($auto_expire = false, $refresh = false) {
    global $cPath, $language, $languages_id, $tree, $cPath_array, $categories_string4, $foo, $id, $aa;

    if (($refresh == true) || !read_cache($cache_output, 'categories4_box-'. TEMPLATE_NAME . '.'  . $language . '.cache' . $cPath, $auto_expire)) {

//get default template name
  $customer_template_query = tep_db_query("select  customers_selected_template as template_selected from " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'");
  $cptemplate1 = tep_db_fetch_array($customer_template_query);

   if (tep_not_null($cptemplate1['template_selected'])) {
  define(TEMPLATE_NAME, $cptemplate['template_selected']);
    }else if  (tep_not_null(DEFAULT_TEMPLATE)){
  define(TEMPLATE_NAME, DEFAULT_TEMPLATE);  
    } else {
    define(TEMPLATE_NAME, 'default');
    }
 //set all possible catagories X.php names    
     ob_start();
       include(DIR_WS_TEMPLATES . TEMPLATE_NAME . '/boxes/categories4.php');
      $cache_output = ob_get_contents();
      ob_end_clean();
      write_cache($cache_output, 'categories4_box-'. TEMPLATE_NAME . '.'  . $language . '.cache' . $cPath);
    }

    return $cache_output;
  }

////
// Cache the categories box
  function tep_cache_categories_box5($auto_expire = false, $refresh = false) {
    global $cPath, $language, $languages_id, $tree, $cPath_array, $categories_string, $foo, $id, $aa;

    if (($refresh == true) || !read_cache($cache_output, 'categories5_box-' . TEMPLATE_NAME . '.' . $language . '.cache' . $cPath, $auto_expire)) {

//get default template name
  $customer_template_query = tep_db_query("select  customers_selected_template as template_selected from " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'");
  $cptemplate1 = tep_db_fetch_array($customer_template_query);

   if (tep_not_null($cptemplate1['template_selected'])) {
  define(TEMPLATE_NAME, $cptemplate['template_selected']);
    }else if  (tep_not_null(DEFAULT_TEMPLATE)){
  define(TEMPLATE_NAME, DEFAULT_TEMPLATE);  
    } else {
    define(TEMPLATE_NAME, 'default');
    }
 //set all possible catagories X.php names    
     ob_start();
       include(DIR_WS_TEMPLATES . TEMPLATE_NAME . '/boxes/categories5.php');
      $cache_output = ob_get_contents();
      ob_end_clean();
      write_cache($cache_output, 'categories5_box-'. TEMPLATE_NAME . '.'  . $language . '.cache' . $cPath);
    }

    return $cache_output;
  }

////
//! Cache the manufacturers box
// Cache the manufacturers box
  function tep_cache_manufacturers_box($auto_expire = false, $refresh = false) {
    global $HTTP_GET_VARS, $language;
  $customer_template_query = tep_db_query("select  customers_selected_template as template_selected from " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'");
  $cptemplate1 = tep_db_fetch_array($customer_template_query);

   if (tep_not_null($cptemplate1['template_selected'])) {
  define(TEMPLATE_NAME, $cptemplate['template_selected']);
    }else if  (tep_not_null(DEFAULT_TEMPLATE)){
  define(TEMPLATE_NAME, DEFAULT_TEMPLATE);  
    } else {
    define(TEMPLATE_NAME, 'default');
    }

    $manufacturers_id = '';
    if (isset($HTTP_GET_VARS['manufactuers_id']) && tep_not_null($HTTP_GET_VARS['manufacturers_id'])) {
      $manufacturers_id = $HTTP_GET_VARS['manufacturers_id'];
    }

    if (($refresh == true) || !read_cache($cache_output, 'manufacturers_box-'. TEMPLATE_NAME . '.'  . $language . '.cache' . $manufacturers_id, $auto_expire)) {
     ob_start();
      include(DIR_WS_TEMPLATES . TEMPLATE_NAME . '/boxes/manufacturers.php');
      $cache_output = ob_get_contents();
      ob_end_clean();
      write_cache($cache_output, 'manufacturers_box-'. TEMPLATE_NAME . '.'  . $language . '.cache' . $manufacturers_id);
    }

    return $cache_output;
  }

////
//! Cache the also purchased module
// Cache the also purchased module
  function tep_cache_also_purchased($auto_expire = false, $refresh = false) {
    global $HTTP_GET_VARS, $language, $languages_id;

    if (($refresh == true) || !read_cache($cache_output, 'also_purchased-'. TEMPLATE_NAME . '.'  . $language . '.cache' . $HTTP_GET_VARS['products_id'], $auto_expire)) {
      ob_start();
      include(DIR_WS_MODULES . FILENAME_ALSO_PURCHASED_PRODUCTS);
      $cache_output = ob_get_contents();
      ob_end_clean();
      write_cache($cache_output, 'also_purchased-'. TEMPLATE_NAME . '.'  . $language . '.cache' . $HTTP_GET_VARS['products_id']);
    }

    return $cache_output;
  }
?>
