<?php
/*
_____________________________________________________________________

dbrepair.php Version 1.0.2 23/03/2002

osCommerce Database Maintenance Add-on
Copyright (c) 2002 James C. Logan

osCommerce, Open Source E-Commerce Solutions
Copyright (c) 2002 osCommerce
http://www.oscommerce.com

IMPORTANT NOTE:

This script is not part of the official osCommerce distribution
but an add-on contributed to the osCommerce community. Please
read the README and  INSTALL documents that are provided 
with this file for further information and installation notes.

LICENSE:

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
_____________________________________________________________________

*/

require('includes/application_top.php');
@set_time_limit(600);

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="3" cellpadding="3">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top">
      <form action="<?php echo FILENAME_DBMTCE_REPAIR; ?>" method="post">
      <input type=hidden name="action" value="doRepair">
      <table border="0" align="center" width="100%" cellspacing="0" cellpadding="2">
        <tr><td class="pageHeading"><?php echo TEXT_HEADING_DBREPAIR; ?></td></tr>
        <tr><td class="main"><br><?php echo TEXT_DISPLAY_DBREPAIR; ?><br>&nbsp;</td></tr>
        <tr>
          <td align="center">
            <select name="tables[]" size="10" MULTIPLE>
              <option value="all" selected><?php echo TEXT_DISPLAY_DBREPAIR_ALLTABLES; ?>
<?php
  $tables_query = tep_db_query('SHOW TABLES');
  while ($tables = tep_db_fetch_array($tables_query)) {
				  list (,$value) = each ($tables);
				  echo "              <option value=\"$value\">$value\n";
				}
?>
            </select>
          </td>
        </tr>
        <tr>
          <td align="center">&nbsp;<br><input type=submit value="<?php echo TEXT_DISPLAY_DBREPAIR_SUBMIT ?>"></td>
        </tr>
      </table>
      </form>
      <br>
<?php
  if ($HTTP_POST_VARS['action']=="doRepair" && $HTTP_POST_VARS['tables'][0]) {
    if ($HTTP_POST_VARS['tables'][0]=="all") {
    		$tables_query = tep_db_query('SHOW TABLES');
    		while ($tables = tep_db_fetch_array($tables_query)) {
        list (,$value) = each ($tables);
        $toRepair .= $value . ",";
    		}
    }
    else {
      while (list(, $value) = each ($HTTP_POST_VARS['tables'])) {
        $toRepair .= $value . ","; 
      }
    }
    $toRepair = SUBSTR($toRepair,0,-1);
    echo "      <table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">
        <tr><td class=\"pageHeading\">" . TEXT_HEADING_DBREPAIR_RESULTS . "<br>&nbsp;</td></tr>";
    $tables_query = tep_db_query('REPAIR TABLE ' . $toRepair);
    while ($tables = tep_db_fetch_array($tables_query)) {
      while (list($key, $value) = each ($tables)) {
        if ($tables['Table']==$value) {
          echo "        <tr><td colspan=\"2\" class=\"infoBoxHeading\" align=\"center\"><b>$value</b></td></tr>\n";
        }
        else {
          echo "        <tr><td align=\"right\" class=\"infoBoxContent\" width=\"50%\">$key</td><td class=\"infoBoxContent\" width=\"50%\"> : $value</td></tr>\n";
        }
      }
      echo "        <tr><td colspan=\"2\" class=\"main\">&nbsp;</td></tr>\n";
    }
    echo "      </table>\n";
  }
?>
    </td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
