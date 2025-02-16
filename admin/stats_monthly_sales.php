<?php
/*
  $Id: stats_monthly_sales.php,v 2.1.1 2004/08/12 23:38:59 ccwjr Exp $

  contributed by Fritz Clapp <osc@sonnybargercycles.com>

This report displays a summary of monthly totals:
	gross income (order totals)
	subtotals of all orders in the selected period
	nontaxed sales subtotals
	taxed sales subtotals
	tax collected
	shipping/handling charges
	low order fees (if present)
	gift vouchers (or other addl order total component, if present)
The data comes from the orders and orders_total tables, therefore this report
works only for osCommerce snapshots since 2002/04/08.

Data is reported as of order purchase date.

If an order status is chosen, the report summarizes orders with that status.

Version 2.0 introduces the capability to "drill down" on any month
to report the daily summary for that month.

Report rows are initially shown in newest to oldest, top to bottom,
but this order may be inverted by clicking the "Invert" control button.

Version 2.1 adds a popup display that lists the various types (and their
subtotals) comprising the tax values in the report rows.

**NOTE:
This Version 2.1.1 has columns that summarize nontaxed and taxed order subtotals.
The assumptions made for this feature may not be appropriate for all stores.
The taxed column summarizes subtotals for orders in which tax was charged.
The nontaxed column is the subtotal for the row less the taxed column value.

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2004 osCommerce
  Released under the GNU General Public License
*/

  require('includes/application_top.php');
// 备注添加删除
if($_GET['ajax']=="true"){
	include DIR_FS_CLASSES . 'Remark.class.php';
	$remark = new Remark('stats_monthly_sales');
	$remark->checkAction($_GET['action'], $login_id);//添加删除动作，统一在方法里面处理了
}
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();
//
// entry for help popup window
if (isset($_GET['help'])){
  echo TEXT_HELP;
  exit;
};
//
// entry for bouncing csv string back as file
if (isset($_POST['csv'])) {
if ($HTTP_POST_VARS['saveas']) {  // rebound posted csv as save file
		$savename= $HTTP_POST_VARS['saveas'] . ".csv";
		}
		else $savename='unknown.csv';
$csv_string = '';
if ($HTTP_POST_VARS['csv']) $csv_string=$HTTP_POST_VARS['csv'];
  if (strlen($csv_string)>0){
  header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
  header("Last-Modified: " . gmdate('D,d M Y H:i:s') . ' GMT');
  header("Cache-Control: no-cache, must-revalidate");
  header("Pragma: no-cache");
  header("Content-Type: Application/octet-stream");
  header("Content-Disposition: attachment; filename=$savename");
  echo $csv_string;
  }
  else echo "CSV string empty";
exit;
};
//
// entry for popup display of tax detail
// show=ot_tax
if (isset($HTTP_GET_VARS['show'])) {
	$ot_type = tep_db_prepare_input($HTTP_GET_VARS['show']);
	$sel_month = tep_db_prepare_input($HTTP_GET_VARS['month']);
	$sel_year = tep_db_prepare_input($HTTP_GET_VARS['year']);
	$sel_day = 0;
	if (isset($HTTP_GET_VARS['day'])) $sel_day = tep_db_prepare_input($HTTP_GET_VARS['day']);
	$status = '';
	if ($HTTP_GET_VARS['status']) $status = tep_db_prepare_input($HTTP_GET_VARS['status']);
	// construct query for selected detail
	$detail_query_raw = "select sum(round(ot.value,2)) amount, ot.title description from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ";
	if ($status<>'') $detail_query_raw .= "o.orders_status ='" . $status . "' and ";
	$detail_query_raw .= "ot.class = '" . $ot_type . "' and month(o.date_purchased)= '" . $sel_month . "' and year(o.date_purchased)= '" . $sel_year . "'";
	if ($sel_day<>0) $detail_query_raw .= " and dayofmonth(o.date_purchased) = '" . $sel_day . "'";
	$detail_query_raw .= " group by ot.title";
	$detail_query = tep_db_query($detail_query_raw);
	echo "<!doctype html public \"-//W3C//DTD HTML 4.01 Transitional//EN\"><html " . HTML_PARAMS . "><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . CHARSET . "\" />" . "<title>" . TEXT_DETAIL . "</title><link rel=\"stylesheet\" type=\"text/css\" href=\"includes/stylesheet.css\"></head><body><br><table width=\"80%\" align=center><caption align=center>";
	echo $sel_year . "/" . $sel_month;
	if ($sel_day<>0) echo "/" . $sel_day;
	if ($status<>'') echo "<br>" . HEADING_TITLE_STATUS . ":" . "&nbsp;" . $status;
	echo "</caption>";

	while ($detail_line = tep_db_fetch_array($detail_query)) {
	echo "<tr class=dataTableRow><td align=left width='75%'>" . $detail_line['description'] . "</td><td align=right>" . number_format($detail_line['amount'],2) . "</td></tr>";}
	echo "</table></body>";
exit;
};
//
// main entry for report display
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="<?php if(!$print) {
	echo 'includes/stylesheet.css';}
	else echo 'includes/printer.css'; ?>">
	<script type="text/javascript" src="includes/jquery-1.3.2/jquery-1.3.2.min.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<?php
// set printer-friendly toggle
(tep_db_prepare_input($HTTP_GET_VARS['print']=='yes')) ? $print=true : $print=false;
// set inversion toggle
(tep_db_prepare_input($HTTP_GET_VARS['invert']=='yes')) ? $invert=true : $invert=false;
?>
<!-- header //-->
<?php if(!$print) require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<?php
//echo $login_id;
include DIR_FS_CLASSES . 'Remark.class.php';
$listrs = new Remark('stats_monthly_sales');
$list = $listrs->showRemark();
?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>

<?php  // suppress left column for printer-friendly version
	if(!$print) {?>
	<td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
	<!-- left_navigation //-->
	<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
	<!-- left_navigation_eof //-->
        </table></td>
<?php	};	?>

<!-- body_text //-->
    <td width="100%" valign="top">
	<table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td>
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
<?php if ($print) {
	echo "<tr><td class=\"pageHeading\">" . STORE_NAME ."</td></tr>";
	};
?>
		  <tr>
            <td class="pageHeading">
			<?php echo HEADING_TITLE; ?></td>
<?php
// detect whether this is monthly detail request
$sel_month = 0;
	if ($HTTP_GET_VARS['month']&& $HTTP_GET_VARS['year']) {
	$sel_month = tep_db_prepare_input($HTTP_GET_VARS['month']);
	$sel_year = tep_db_prepare_input($HTTP_GET_VARS['year']);
	};
// get list of orders_status names for dropdown selection
  $orders_statuses = array();
  $orders_status_array = array();
  $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . $languages_id . "'");
  while ($orders_status = tep_db_fetch_array($orders_status_query)) {
    $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                 'text' => $orders_status['orders_status_name']);
    $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
	  };
// name of status selection
$orders_status_text = TEXT_ALL_ORDERS;
if ($HTTP_GET_VARS['status']) {
  $status = tep_db_prepare_input($HTTP_GET_VARS['status']);
  $orders_status_query = tep_db_query("select orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . $languages_id . "' and orders_status_id =" . $status);
  while ($orders_status = tep_db_fetch_array($orders_status_query)) {
	  $orders_status_text = $orders_status['orders_status_name'];}
				};
if (!$print) { ?>
			<td align="right">
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
			  <tr><td class="smallText" align="right">
				<?php echo tep_draw_form('status', FILENAME_STATS_MONTHLY_SALES, '', 'get');
				// get list of orders_status names for dropdown selection
				  $orders_statuses = array();
				  $orders_status_array = array();
				  $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . $languages_id . "'");
				  while ($orders_status = tep_db_fetch_array($orders_status_query)) {
				    $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
			         'text' => $orders_status['orders_status_name']);
					$orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
				  };
                echo HEADING_TITLE_STATUS . ': ' . tep_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), '', 'onChange="this.form.submit();"'); ?>
				<input type="hidden" name="selected_box" value="reports">
				<?php
					if ($sel_month<>0)
					echo "<input type='hidden' name='month' value='" . $sel_month . "'><input type='hidden' name='year' value='" . $sel_year . "'>";
					if ($invert) echo "<input type='hidden' name='invert' value='yes'>";
				?>
				</td>
              </form></tr>
             </table>
			 </td>
<?php		}; ?>

<?php if ($print) { ?>
			<td>
			</td>
		<tr><td>
				<table>
				<tr><td class="smallText"><?php echo HEADING_TITLE_REPORTED . ": "; ?></td>
				<td width="8"></td>
				<td class="smallText" align="left"><?php echo date(ltrim(TEXT_REPORT_DATE_FORMAT)); ?></td>
				</tr>
				<tr><td class="smallText" align="left">
				<?php echo HEADING_TITLE_STATUS . ": ";  ?></td>
				<td width="8"></td>
				<td class="smallText" align="left">
				<?php echo $orders_status_text;?>
				</td>
				</tr>
				<table>
			</td><td></td>
		</tr>
<?php 	};	 ?>
        </table></td>
      </tr>
<?php if(!$print) { ?>
<!--
row for buttons to print, save, and help
-->
			<tr>
				<td  align="right">
				<table align=right cellspacing="10"><tr>
				<td align="left" class="smallText">
				<?php  // back button if monthly detail
				if ($sel_month<>0)	 {
				echo "<a href='" . $_SERVER['PHP_SELF'] . "?&selected_box=reports";
				if (isset($HTTP_GET_VARS['status'])) echo "&status=" . $status;
				if (isset($HTTP_GET_VARS['invert'])) echo "&invert=yes";
				echo "' title='" . TEXT_BUTTON_REPORT_BACK_DESC . "'>" . TEXT_BUTTON_REPORT_BACK . "</a>";
				};
				?>
				</td>
				<td class="smallText">
				<a href="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . '&print=yes';?>" target="print" title="<?php echo TEXT_BUTTON_REPORT_PRINT_DESC?>"><?php echo TEXT_BUTTON_REPORT_PRINT; ?></a>
				</td>
				<td class="smallText"><a href='<?php echo $_SERVER['PHP_SELF'] . "?" . ereg_replace('&invert=yes','',$_SERVER['QUERY_STRING']); 
				if (!$invert) echo "&invert=yes";
				?>' <?php 
				echo " title= '" . TEXT_BUTTON_REPORT_INVERT_DESC . "'";
				?>><?php echo TEXT_BUTTON_REPORT_INVERT; ?></a>
				</td>
				<td class="smallText"><a href="#" onClick="window.open('<?php echo $_SERVER['PHP_SELF'] . "?&help=yes";	?>','help','config=\'height=400,width=600,scrollbars=1, resizable=1\'')" title="<?php echo TEXT_BUTTON_REPORT_HELP_DESC ?>"><?php echo TEXT_BUTTON_REPORT_HELP; ?></a>
				</td>
				</tr></table>
				</td>
			</tr>
<?php	};
//
// determine if loworder fee is enabled in configuration, include/omit the column
$loworder_query_raw = "select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key =" . "'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE'";
$loworder = false;
$loworder_query = tep_db_query($loworder_query_raw);
if (tep_db_num_rows($loworder_query)>0) {
	$low_setting=tep_db_fetch_array($loworder_query);
	if ($low_setting['configuration_value']=='true') $loworder=true;
};
//
// if there are extended class values in orders_table
// create extra column so totals are comprehensively correct
$class_val_subtotal = "'ot_subtotal'";
$class_val_tax = "'ot_tax'";
$class_val_shiphndl = "'ot_shipping'";
$class_val_loworder = "'ot_loworderfee'";
$class_val_total = "'ot_total'";
	$extra_class_query_raw = "select value from " . TABLE_ORDERS_TOTAL . " where class <> " . $class_val_subtotal . " and class <>" . $class_val_tax . " and class <>" . $class_val_shiphndl . " and class <>" . $class_val_loworder . " and class <>" . $class_val_total;
	$extra_class = false;
	$extra_class_query = tep_db_query($extra_class_query_raw);
	if (tep_db_num_rows($extra_class_query)>0) $extra_class = true;
// active row, user wants to display days of month
$sel_month = 0;
	if ($HTTP_GET_VARS['month']&& $HTTP_GET_VARS['year']) {
	$sel_month = tep_db_prepare_input($HTTP_GET_VARS['month']);
	$sel_year = tep_db_prepare_input($HTTP_GET_VARS['year']);
	};
// start accumulator for the report content mirrored in CSV
$csv_accum = '';
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td valign="top">
			<table border="0" width='100%' cellspacing="0" cellpadding="2">
<tr class="dataTableHeadingRow">
<td class="dataTableHeadingContent" width='45' align='left' valign="bottom"><?php
if ($sel_month == 0) mirror_out(TABLE_HEADING_MONTH); else mirror_out(TABLE_HEADING_MONTH); ?>
</td>
<td class="dataTableHeadingContent" width='35' align='left' valign="bottom"><?php
if ($sel_month == 0) mirror_out(TABLE_HEADING_YEAR); else mirror_out(TABLE_HEADING_DAY); ?></td>
<td class="dataTableHeadingContent" width='70' align='right' valign="bottom"><?php mirror_out(TABLE_HEADING_INCOME); ?></td>
<?php if($access_full_edit == 'true') { ?>	
<td class="dataTableHeadingContent" width='70' align='right' valign="bottom"><?php mirror_out(TABLE_HEADING_TOTAL_COST); ?></td>
<?php } ?>
<td class="dataTableHeadingContent" width='70' align='right' valign="bottom"><?php mirror_out(TABLE_HEADING_SALES); ?></td>
<td class="dataTableHeadingContent" width='70' align='right' valign="bottom"><?php mirror_out(TABLE_HEADING_NONTAXED); ?></td>
<td class="dataTableHeadingContent" width='70' align='right' valign="bottom"><?php mirror_out(TABLE_HEADING_TAXED); ?></td>
<td class="dataTableHeadingContent" width='70' align='right' valign="bottom"><?php mirror_out(TABLE_HEADING_TAX_COLL); ?></td>
<td class="dataTableHeadingContent" width='70' align='right' valign="bottom"><?php mirror_out(TABLE_HEADING_SHIPHNDL); ?></td>
<?php
if ($loworder) { ?>
<td class="dataTableHeadingContent" width='70' align='right' valign="bottom"><?php mirror_out(TABLE_HEADING_LOWORDER); ?></td>
<?php }; ?>
<?php
if ($extra_class) { ?>
<td class="dataTableHeadingContent" width='70' align='right' valign="bottom"><?php mirror_out(TABLE_HEADING_OTHER); ?></td>
<?php }; ?>
</tr>
<?php
// clear footer totals
	$footer_gross = 0;
	$footer_cost = 0;
	$footer_sales = 0;
	$footer_sales_nontaxed = 0;
	$footer_sales_taxed = 0;
	$footer_tax_coll = 0;
	$footer_shiphndl = 0;
	$footer_loworder = 0;
	$footer_other = 0;
// new line for CSV
$csv_accum .= "\n";
// order totals, the driving force
$status = '';
$sales_query_raw = "select sum(round(ot.value,2)) gross_sales, sum(round(o.order_cost,2)) gross_sales_cost, monthname(o.date_purchased) row_month, year(o.date_purchased) row_year, month(o.date_purchased) i_month, dayofmonth(o.date_purchased) row_day  from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ";
if ($HTTP_GET_VARS['status']) {
  $status = tep_db_prepare_input($HTTP_GET_VARS['status']);
  $sales_query_raw .= "o.orders_status =" . $status . " and ";
	};
$sales_query_raw .= "ot.class = " . $class_val_total;
if ($sel_month<>0) $sales_query_raw .= " and month(o.date_purchased) = " . $sel_month;
$sales_query_raw .= " group by year(o.date_purchased), month(o.date_purchased)";
if ($sel_month<>0) $sales_query_raw .= ", dayofmonth(o.date_purchased)";
$sales_query_raw .=  " order by o.date_purchased ";
if ($invert) $sales_query_raw .= "asc"; else $sales_query_raw .= "desc";
$sales_query = tep_db_query($sales_query_raw);
$num_rows = tep_db_num_rows($sales_query);
if ($num_rows==0) echo '<tr><td class="smalltext">' . TEXT_NOTHING_FOUND . '</td></tr>';
$rows=0;
//
// loop here for each row reported
while ($sales = tep_db_fetch_array($sales_query)) {
	$rows++;
	if ($rows>1 && $sales['row_year']<>$last_row_year) {  // emit annual footer
?>
<tr class="dataTableHeadingRow">
<td class="dataTableHeadingContent" align="left">
<?php
	if ($sales['row_year']==date("Y")) mirror_out(TABLE_FOOTER_YTD);
	else
		if ($sel_month==0) mirror_out(TABLE_FOOTER_YEAR);
		else
			mirror_out(strtoupper(substr($sales['row_month'],0,3)));
?>
</td>
<td class="dataTableHeadingContent" align="left">
<?php mirror_out($last_row_year); ?></td>
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_gross,2)); ?>
</td>
<?php if($access_full_edit == 'true') { ?>	
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_cost,2)); ?>
</td>
<?php } ?> 

<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_sales,2)); ?>
</td>
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_sales_nontaxed,2)); ?>
</td>
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_sales_taxed,2)); ?>
</td>
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_tax_coll,2)); ?>
</td>
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_shiphndl,2)); ?>
</td>
<?php if ($loworder) { ?>
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_loworder,2)); ?>
</td>
<?php }; ?>
<?php if ($extra_class) { ?>
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_other,2)); ?>
</td>
<?php };
// clear footer totals
$footer_gross = 0;
$footer_cost = 0;
$footer_sales = 0;
$footer_sales_nontaxed = 0;
$footer_sales_taxed = 0;
$footer_tax_coll = 0;
$footer_shiphndl = 0;
$footer_loworder = 0;
$footer_other = 0;
// new line for CSV
$csv_accum .= "\n";
?>
</tr>
<?php };
//
// determine net sales for row
$net_sales_query_raw = "select sum(round(ot.value,2)) net_sales from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ";
if ($status<>'') $net_sales_query_raw .= "o.orders_status ='" . $status . "' and ";
$net_sales_query_raw .= "ot.class = " . $class_val_subtotal . " and month(o.date_purchased)= '" . $sales['i_month'] . "' and year(o.date_purchased)= '" . $sales['row_year'] . "'";
if ($sel_month<>0) $net_sales_query_raw .= " and dayofmonth(o.date_purchased) = '" . $sales['row_day'] . "'";
$net_sales_query = tep_db_query($net_sales_query_raw);
$net_sales_this_row = 0;
if (tep_db_num_rows($net_sales_query)>0)
	$net_sales_this_row = tep_db_fetch_array($net_sales_query);
//
// determine tax collected for row
$tax_coll_query_raw = "select sum(round(ot.value,2)) tax_coll from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ";
if ($status<>'') $tax_coll_query_raw .= "o.orders_status ='" . $status . "' and ";
$tax_coll_query_raw .= "ot.class = " . $class_val_tax . " and month(o.date_purchased)= '" . $sales['i_month'] . "' and year(o.date_purchased)= '" . $sales['row_year'] . "'";
if ($sel_month<>0) $tax_coll_query_raw .= " and dayofmonth(o.date_purchased) = '" . $sales['row_day'] . "'";
$tax_coll_query = tep_db_query($tax_coll_query_raw);
$tax_this_row = 0;
if (tep_db_num_rows($tax_coll_query)>0)
	$tax_this_row = tep_db_fetch_array($tax_coll_query);
//
// shipping and handling charges for row
$shiphndl_query_raw = "select sum(round(ot.value,2)) shiphndl from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ";
if ($status<>'') $shiphndl_query_raw .= "o.orders_status ='" . $status . "' and ";
$shiphndl_query_raw .= "ot.class = " . $class_val_shiphndl . " and month(o.date_purchased)= '" . $sales['i_month'] . "' and year(o.date_purchased)= '" . $sales['row_year'] . "'";
if ($sel_month<>0) $shiphndl_query_raw .= " and dayofmonth(o.date_purchased) = '" . $sales['row_day'] . "'";
$shiphndl_query = tep_db_query($shiphndl_query_raw);
$shiphndl_this_row = 0;
if (tep_db_num_rows($shiphndl_query)>0)
	$shiphndl_this_row = tep_db_fetch_array($shiphndl_query);
//
// low order fees for row
$loworder_this_row = 0;
if ($loworder) {
	$loworder_query_raw = "select sum(round(ot.value,2)) loworder from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ";
	if ($status<>'') $loworder_query_raw .= "o.orders_status ='" . $status . "' and ";
	$loworder_query_raw .= "ot.class = " . $class_val_loworder . " and month(o.date_purchased)= '" . $sales['i_month'] . "' and year(o.date_purchased)= '" . $sales['row_year'] . "'";
	if ($sel_month<>0) $loworder_query_raw .= " and dayofmonth(o.date_purchased) = '" . $sales['row_day'] . "'";
	$loworder_query = tep_db_query($loworder_query_raw);
	if (tep_db_num_rows($loworder_query)>0)
	$loworder_this_row = tep_db_fetch_array($loworder_query);
};
//
// additional column if extra class value in orders_total table
$other_this_row = 0;
if ($extra_class) {
	$other_query_raw = "select sum(round(ot.value,2)) other from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ";
	if ($status<>'') $other_query_raw .= "o.orders_status ='" . $status . "' and ";
	$other_query_raw .= "ot.class <> " . $class_val_subtotal . " and class <> " . $class_val_tax . " and class <> " . $class_val_shiphndl . " and class <> " . $class_val_loworder . " and class <> " . $class_val_total . " and month(o.date_purchased)= '" . $sales['i_month'] . "' and year(o.date_purchased)= '" . $sales['row_year'] . "'";
	if ($sel_month<>0) $other_query_raw .= " and dayofmonth(o.date_purchased) = '" . $sales['row_day'] . "'";
	$other_query = tep_db_query($other_query_raw);
	if (tep_db_num_rows($other_query)>0)
	$other_this_row = tep_db_fetch_array($other_query);
	};
//
// sum of order subtotals taxed
	$taxed_query_raw = "select sum(round(t0.value,2)) taxed_sales, monthname(o.date_purchased) row_month, month(o.date_purchased) i_month from " . TABLE_ORDERS_TOTAL . " t0, " .  TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " t1 on (o.orders_id = t1.orders_id) where t0.orders_id=t1.orders_id ";
	if ($status<>'') $taxed_query_raw .= " and o.orders_status ='" . $status . "'";
	$taxed_query_raw .= " and t0.class = " . $class_val_subtotal . " and t1.class = " . $class_val_tax  . " and t1.value>0 and month(o.date_purchased)= '" . $sales['i_month'] . "' and year(o.date_purchased)= '" . $sales['row_year'] . "'";
	if ($sel_month<>0) {
		$taxed_query_raw .= " and dayofmonth(o.date_purchased) = '" . $sales['row_day'] . "' group by o.date_purchased";
	} else {
		$taxed_query_raw .= " group by month(o.date_purchased)";
	};
	$taxed_query = tep_db_query($taxed_query_raw);
	$taxed_this_row = tep_db_fetch_array($taxed_query);
//
// sum of order subtotals not taxed
	$nontaxed_this_row_value = $net_sales_this_row['net_sales'] - $taxed_this_row['taxed_sales'];
//
// accumulate row results in footer
	$footer_gross += $sales['gross_sales'];
	$footer_cost += $sales['gross_sales_cost'];
	$footer_sales += $net_sales_this_row['net_sales'];
	$footer_sales_nontaxed += $nontaxed_this_row_value;
	$footer_sales_taxed += $taxed_this_row['taxed_sales'];
	$footer_tax_coll += $tax_this_row['tax_coll'];
	$footer_shiphndl += $shiphndl_this_row['shiphndl'];
	$footer_loworder += $loworder_this_row['loworder'];
	if ($extra_class) $footer_other += $other_this_row['other'];
?>
<tr class="dataTableRow">
<td class="dataTableContent" align="left">
<?php  // live link to report monthly detail
if ($sel_month == 0	&& !$print) {
	echo "<a href='" . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] . "&month=" . $sales['i_month'] . "&year=" . $sales['row_year'] . "' title='" . TEXT_BUTTON_REPORT_GET_DETAIL . "'>";
	}
mirror_out(substr($sales['row_month'],0,3));
if ($sel_month == 0 && !$print) echo '</a>';
?>
</td>
<td class="dataTableContent" align="left">
<?php
if ($sel_month==0) mirror_out($sales['row_year']);
else mirror_out($sales['row_day']);
$last_row_year = $sales['row_year']; // save this row's year to check for annual footer
?>
</td>
<td class="dataTableContent" width='70' align="right"><?php mirror_out(number_format($sales['gross_sales'],2)); ?></td>
<?php if($access_full_edit == 'true') { ?>	
<td class="dataTableContent" width='70' align="right"><?php mirror_out(number_format($sales['gross_sales_cost'],2)); ?></td>
<?php } ?>
<td class="dataTableContent" width='70' align="right"><?php mirror_out(number_format($net_sales_this_row['net_sales'],2)); ?></td>
<td class="dataTableContent" width='70' align="right"><?php mirror_out(number_format($nontaxed_this_row_value,2)); ?></td>
<td class="dataTableContent" width='70' align="right"><?php mirror_out(number_format($taxed_this_row['taxed_sales'],2)); ?></td>
<td class="dataTableContent" width='70' align="right">
<?php
	// make this a link to the detail popup if nonzero
	if (!$print && ($tax_this_row['tax_coll']>0)) {
		echo "<a href=\"#\" onClick=\"window.open('" . $_SERVER['PHP_SELF'] . "?&show=ot_tax&year=" . $sales['row_year'] . "&month=" . $sales['i_month'];
		if ($sel_month<>0) echo "&day=" . $sales['row_day'];
		if ($status<>'') echo "&status=" . $status;
		echo "','detail',config='height=200,width=400,scrollbars=1, resizable=1')\" title=\"Show detail\">";
	};
	mirror_out(number_format($tax_this_row['tax_coll'],2));
	if (!$print && $tax_this_row['tax_coll']>0) echo "</a>";
?></td>
<td class="dataTableContent" width='70' align="right"><?php mirror_out(number_format($shiphndl_this_row['shiphndl'],2)); ?></td>
<?php if ($loworder) { ?>
<td class="dataTableContent" width='70' align="right"><?php mirror_out(number_format($loworder_this_row['loworder'],2)); ?></td>
<?php }; ?>
<?php
if ($extra_class) { ?>
<td class="dataTableContent" width='70' align="right"><?php mirror_out(number_format($other_this_row['other'],2)); ?></td>
<?php }; ?>
</tr>
<?php
// new line for CSV
$csv_accum .= "\n";
//
//
// output footer below ending row
if ($rows==$num_rows){
?>
<tr class="dataTableHeadingRow">
<td class="dataTableHeadingContent" align="left">
<?php
	if ($sel_month<>0)
	mirror_out(strtoupper(substr($sales['row_month'],0,3)));
	else
	{if ($sales['row_year']==date("Y")) mirror_out(TABLE_FOOTER_YTD);
	 else mirror_out(TABLE_FOOTER_YEAR);};
?>
</td>
<td class="dataTableHeadingContent" align="left">
<?php mirror_out($sales['row_year']); ?></td>
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_gross,2)); ?>
</td>
<?php if($access_full_edit == 'true') { ?>	
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_cost,2)); ?>
</td>
<?php } ?>	
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_sales,2)); ?>
</td>
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_sales_nontaxed,2)); ?>
</td>
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_sales_taxed,2)); ?>
</td>
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_tax_coll,2)); ?>
</td>
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_shiphndl,2)); ?>
</td>
<?php if ($loworder) { ?>
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_loworder,2)); ?>
</td>
<?php }; ?>
<?php if ($extra_class) { ?>
<td class="dataTableHeadingContent" width='70' align="right">
<?php mirror_out(number_format($footer_other,2)); ?>
</td>
<?php };
// clear footer totals
$footer_gross = 0;
$footer_cost = 0;
$footer_sales = 0;
$footer_sales_nontaxed = 0;
$footer_sales_taxed = 0;
$footer_tax_coll = 0;
$footer_shiphndl = 0;
$footer_loworder = 0;
$footer_other = 0;
// new line for CSV
$csv_accum .= "\n";
?>
</tr>
<?php };
  };
// done with report body
//
// button for Save CSV
if ($num_rows>0 && !$print) {
?>
<tr>
				<td class="smallText" colspan="4"><form action="<?php echo $_SERVER['PHP_SELF']; ?>" method=post><input type='hidden' name='csv' value='<?php echo $csv_accum; ?>'><input type='hidden' name='saveas' value='sales_report_<?php
					//suggested file name for csv, include year and month if detail
					//include status if selected, end with date and time of report
				if ($sel_month<10) $sel_month_2 = "0" . $sel_month;
				else $sel_month_2 = $sel_month;
				if ($sel_month<>0) echo $sel_year . $sel_month_2 . "_";
				if (strpos($orders_status_text,' ')) echo substr($orders_status_text, 0, strpos($orders_status_text,' ')) . "_" . date("YmdHi"); else echo $orders_status_text . "_" . date("YmdHi");
				?>'><input type="submit" value="<?php echo TEXT_BUTTON_REPORT_SAVE ;?>"></form>
				</td>
</tr>
<?php }; // end button for Save CSV ?>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php  // suppress footer for printer-friendly version
	if(!$print) require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php');

function mirror_out ($field) {
	global $csv_accum;
	echo $field;
	$field = strip_tags($field);
	$field = ereg_replace (",","",$field);
	if ($csv_accum=='') $csv_accum=$field;
	else
	{if (strrpos($csv_accum,chr(10)) == (strlen($csv_accum)-1)) $csv_accum .= $field;
		else $csv_accum .= "," . $field; };
	return;
};

?>
