<?php
/*
  $Id: stats_sales_report2.php,v 1.00 2003/03/08 19:02:22 Exp $

  Charly Wilhelm  charly@yoshi.ch
  
  Released under the GNU General Public License

  Copyright (c) 2003 osCommerce
  
  possible views (srView):
  1 yearly
  2 monthly
  3 weekly
  4 daily
  
  possible options (srDetail):
  0 no detail
  1 show details (products)
  2 show details only (products)
  
  export
  0 normal view
  1 html view without left and right
  2 csv
  
  sort
  0 no sorting
  1 product description asc
  2 product description desc
  3 #product asc, product descr asc
  4 #product desc, product descr desc
  5 revenue asc, product descr asc
  6 revenue desc, product descr desc
  
*/

  require('includes/application_top.php');
  // 备注添加删除
  if($_GET['ajax']=="true"){
  	include DIR_FS_CLASSES . 'Remark.class.php';
  	$remark = new Remark('stats_sales_report2');
  	$remark->checkAction($_GET['action'], $login_id);//添加删除动作，统一在方法里面处理了
  }
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  // default detail no detail
  $srDefaultDetail = 0;
  // default view (daily)
  $srDefaultView = 2;
  // default export
  $srDefaultExp = 0;
  // default sort
  $srDefaultSort = 4;
  
  // report views (1: yearly 2: monthly 3: weekly 4: daily)
  if ( ($HTTP_GET_VARS['report']) && (tep_not_null($HTTP_GET_VARS['report'])) ) 
{    $srView = $HTTP_GET_VARS['report'];
  }
  if ($srView < 1 || $srView > 4) {
    $srView = $srDefaultView;
  }

  // detail
  if ( ($HTTP_GET_VARS['detail']) && (tep_not_null($HTTP_GET_VARS['detail'])) ) 
{    $srDetail = $HTTP_GET_VARS['detail'];
  }
  if ($srDetail < 0 || $srDetail > 2) {
    $srDetail = $srDefaultDetail;
  }
  
  // report views (1: yearly 2: monthly 3: weekly 4: daily)
  if ( ($HTTP_GET_VARS['export']) && (tep_not_null($HTTP_GET_VARS['export'])) ) 
{    $srExp = $HTTP_GET_VARS['export'];
  }
  if ($srExp < 0 || $srExp > 2) {
    $srExp = $srDefaultExp;
  }
  
  // item_level
  if ( ($HTTP_GET_VARS['max']) && (tep_not_null($HTTP_GET_VARS['max'])) ) {
    $srMax = $HTTP_GET_VARS['max'];
  }
  if (!is_numeric($srMax)) {
    $srMax = 0;
  }
      
  // order status
  if ( ($HTTP_GET_VARS['status']) && (tep_not_null($HTTP_GET_VARS['status'])) ) 
{    $srStatus = $HTTP_GET_VARS['status'];
  }
  if (!is_numeric($srStatus)) {
    $srStatus = 0;
  }
  
  // sort
  if ( ($HTTP_GET_VARS['sort']) && (tep_not_null($HTTP_GET_VARS['sort'])) ) {
    $srSort = $HTTP_GET_VARS['sort'];
  }
  if ($srSort < 1 || $srSort > 6) {
    $srSort = $srDefaultSort;
  }
    
  // check start and end Date
  $startDate = "";
  $startDateG = 0;
  if ( ($HTTP_GET_VARS['startD']) && (tep_not_null($HTTP_GET_VARS['startD'])) ) 
{    $sDay = $HTTP_GET_VARS['startD'];
    $startDateG = 1;
  } else {
    $sDay = 1;
  }
  if ( ($HTTP_GET_VARS['startM']) && (tep_not_null($HTTP_GET_VARS['startM'])) ) 
{    $sMon = $HTTP_GET_VARS['startM'];
    $startDateG = 1;
  } else {
    $sMon = 1;
  }
  if ( ($HTTP_GET_VARS['startY']) && (tep_not_null($HTTP_GET_VARS['startY'])) ) 
{    $sYear = $HTTP_GET_VARS['startY'];
    $startDateG = 1;
  } else {
    $sYear = date("Y");
  }
  if ($startDateG) {
    $startDate = mktime(0, 0, 0, $sMon, $sDay, $sYear);
  } else {
    $startDate = mktime(0, 0, 0, date("m"), 1, date("Y"));
  }
    
  $endDate = "";
  $endDateG = 0;
  if ( ($HTTP_GET_VARS['endD']) && (tep_not_null($HTTP_GET_VARS['endD'])) ) {
    $eDay = $HTTP_GET_VARS['endD'];
    $endDateG = 1;
  } else {
    $eDay = 1;
  }
  if ( ($HTTP_GET_VARS['endM']) && (tep_not_null($HTTP_GET_VARS['endM'])) ) {
    $eMon = $HTTP_GET_VARS['endM'];
    $endDateG = 1;
  } else {
    $eMon = 1;
  }
  if ( ($HTTP_GET_VARS['endY']) && (tep_not_null($HTTP_GET_VARS['endY'])) ) {
    $eYear = $HTTP_GET_VARS['endY'];
    $endDateG = 1;
  } else {
    $eYear = date("Y");
  }
  if ($endDateG) {
    $endDate = mktime(0, 0, 0, $eMon, $eDay + 1, $eYear);
  } else {
    $endDate = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
  }
  
  $srPayStatus = 'all';
  if(isset($_GET['orders_products_payment_status'])){ $srPayStatus = $_GET['orders_products_payment_status']; }  
  
  require(DIR_WS_CLASSES . 'sales_report2.php');
  $sr = new sales_report($srView, $startDate, $endDate, $srSort, $srStatus, $srFilter, $srPayStatus);  $startDate = $sr->startDate;
  $endDate = $sr->endDate;  
  
  if ($srExp < 2) {
    // not for csv export
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo 
CHARSET; ?>" />  <title><?php echo TITLE; ?></title>
  <link rel="stylesheet" type="text/css" href="includes/stylesheet.css" />
  <script type="text/javascript" src="includes/jquery-1.3.2/jquery-1.3.2.min.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF"><!-- header //-->
<?php
    if ($srExp < 1) {
      require(DIR_WS_INCLUDES . 'header.php');
    }
?>
<!-- header_eof //-->

<!-- body //-->
<?php
//echo $login_id;
include DIR_FS_CLASSES . 'Remark.class.php';
$listrs = new Remark('stats_sales_report2');
$list = $listrs->showRemark();
?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<?php
    if ($srExp < 1) {
?>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top">
      <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1"
cellpadding="1" class="columnLeft">      <!-- left_navigation //-->
<?php
      require(DIR_WS_INCLUDES . 'column_left.php');
?>
      <!-- left_navigation_eof //-->
      </table>
    </td>
<!-- body_text //-->
<?php
    } // end sr_exp
?>
    <td width="100%" valign="top">
      <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td colspan=2>
            <table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
              </tr>
            </table>
          </td>
        </tr>
<?php
    if ($srExp < 1) {
?>
        <tr>
          <td colspan="2">
            <form action="" method="get">
              <table border="0" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                  <td align="left" rowspan="2" class="menuBoxHeading">
                    <input type="radio" name="report" value="1" <?php if ($srView == 1) echo "checked"; ?>><?php echo REPORT_TYPE_YEARLY; ?><br>
                    <input type="radio" name="report" value="2" <?php if ($srView == 2) echo "checked"; ?>><?php echo REPORT_TYPE_MONTHLY; ?><br>
                    <input type="radio" name="report" value="3" <?php if ($srView == 3) echo "checked"; ?>><?php echo REPORT_TYPE_WEEKLY; ?><br>
                    <input type="radio" name="report" value="4" <?php if ($srView == 4) echo "checked"; ?>><?php echo REPORT_TYPE_DAILY; ?><br>
                  </td>
                  <td class="menuBoxHeading" nowrap="nowrap">
<?php echo REPORT_START_DATE; ?><br>
                    <select name="startD" size="1">
<?php
      if ($startDate) {
        $j = date("j", $startDate);
      } else {
        $j = 1;
      }
      for ($i = 1; $i < 32; $i++) {
?>
                        <option<?php if ($j == $i) echo " selected"; ?>><?php echo $i; ?></option>
<?php
      }
?>
                    </select>
                    <select name="startM" size="1">
<?php
      if ($startDate) {
        $m = date("n", $startDate);
      } else {
        $m = 1;
      }
      for ($i = 1; $i < 13; $i++) {
?>
                      <option<?php if ($m == $i) echo " selected"; ?> value="<?php echo $i; ?>"><?php echo strftime("%B", mktime(0, 0, 0, $i, 1)); ?></option>
<?php
      }
?>
                    </select>
                    <select name="startY" size="1">
<?php
      if ($startDate) {
        $y = date("Y") - date("Y", $startDate);
      } else {
        $y = 0;
      }
      for ($i = 10; $i >= 0; $i--) {
?>
                      <option<?php if ($y == $i) echo " selected"; ?>><?php echo date("Y") - $i; ?></option>
<?php
    }
?>
                    </select>
                  </td>
                  <td rowspan="2" align="left" class="menuBoxHeading">
                    <?php echo REPORT_DETAIL; ?><br>
                    <select name="detail" size="1">
                      <option value="0"<?php if ($srDetail == 0) echo "selected"; ?>><?php echo DET_HEAD_ONLY; ?></option>
                      <option value="1"<?php if ($srDetail == 1) echo " selected"; ?>><?php echo DET_DETAIL; ?></option>
                      <option value="2"<?php if ($srDetail == 2) echo " selected"; ?>><?php echo DET_DETAIL_ONLY; ?></option>
                    </select><br>
<?php echo REPORT_MAX; ?><br>
                    <select name="max" size="1">
                      <option value="0"><?php echo REPORT_ALL; ?></option>
                      <option<?php if ($srMax == 1) echo " selected"; ?>>1</option>
                      <option<?php if ($srMax == 3) echo " selected"; ?>>3</option>
                      <option<?php if ($srMax == 5) echo " selected"; ?>>5</option>
                      <option<?php if ($srMax == 10) echo " selected"; ?>>10</option>
                      <option<?php if ($srMax == 25) echo " selected"; ?>>25</option>
                      <option<?php if ($srMax == 50) echo " selected"; ?>>50</option>
                    </select>
                  </td>
                  <td rowspan="2" align="left" class="menuBoxHeading">
                    <?php echo REPORT_STATUS_FILTER; ?><br>
                    <select name="status" size="1" style="width:200px;">
                      <option value="0"><?php echo REPORT_ALL; ?></option>
<?php
                        foreach ($sr->status as $value) {
?>
                      <option value="<?php echo $value["orders_status_id"]?>"<?php if ($srStatus == $value["orders_status_id"]) echo " selected"; ?>><?php echo $value["orders_status_name"] ; ?></option>
<?php
                         }
?>
                    </select><br>
					
					付款状态：<br>
					<select name="orders_products_payment_status" size="1" style="width:200px;">
                      <option value="all"><?php echo REPORT_ALL; ?></option>
					  <option value="0" <?php if ($orders_products_payment_status === "0" ) echo " selected"; ?>>未付款</option>
					  <option value="1" <?php if ($orders_products_payment_status === "1" ) echo " selected"; ?>>已付款</option>
					  <option value="2" <?php if ($orders_products_payment_status === "2" ) echo " selected"; ?>>已部分付款</option>
					  </select>
					
                  </td>
                  <td rowspan="2" align="left" class="menuBoxHeading">
                    <?php echo REPORT_EXP; ?><br>
                    <select name="export" size="1">
                      <option value="0" selected><?php echo EXP_NORMAL; ?></option>
                      <option value="1"><?php echo EXP_HTML; ?></option>
                      <option value="2"><?php echo EXP_CSV; ?></option>
                    </select><br>
                    <?php echo REPORT_SORT; ?><br>
                    <select name="sort" size="1">
                      <option value="0"<?php if ($srSort == 0) echo " selected"; ?>><?php echo SORT_VAL0; ?></option>
                      <option value="1"<?php if ($srSort == 1) echo " selected"; ?>><?php echo SORT_VAL1; ?></option>
                      <option value="2"<?php if ($srSort == 2) echo " selected"; ?>><?php echo SORT_VAL2; ?></option>
                      <option value="3"<?php if ($srSort == 3) echo " selected"; ?>><?php echo SORT_VAL3; ?></option>
                      <option value="4"<?php if ($srSort == 4) echo " selected"; ?>><?php echo SORT_VAL4; ?></option>
                      <option value="5"<?php if ($srSort == 5) echo " selected"; ?>><?php echo SORT_VAL5; ?></option>
                      <option value="6"<?php if ($srSort == 6) echo " selected"; ?>><?php echo SORT_VAL6; ?></option>
                    </select><br>
                  </td>
                </tr>
                <tr>
                  <td class="menuBoxHeading">
<?php echo REPORT_END_DATE; ?><br>
                    <select name="endD" size="1">
<?php
    if ($endDate) {
      $j = date("j", $endDate - 60* 60 * 24);
    } else {
      $j = date("j");
    }
    for ($i = 1; $i < 32; $i++) {
?>
                      <option<?php if ($j == $i) echo " selected"; ?>><?php echo $i; ?></option>
<?php
    }
?>
                    </select>
                    <select name="endM" size="1">
<?php
    if ($endDate) {
      $m = date("n", $endDate - 60* 60 * 24);
    } else {
      $m = date("n");
    }
    for ($i = 1; $i < 13; $i++) {
?>
                      <option<?php if ($m == $i) echo " selected"; ?> value="<?php echo $i; ?>"><?php echo strftime("%B", mktime(0, 0, 0, $i, 1)); ?></option>
<?php
    }
?>
                    </select>
                    <select name="endY" size="1">
<?php
    if ($endDate) {
      $y = date("Y") - date("Y", $endDate - 60* 60 * 24);
    } else {
      $y = 0;
    }
    for ($i = 10; $i >= 0; $i--) {
?>
                      <option<?php if ($y == $i) echo " selected"; ?>><?php echo
date("Y") - $i; ?></option><?php
    }
?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td colspan="5" class="menuBoxHeading">Group by Country:
				    <input type="radio" name="group_country" value="1" <?php if ($group_country == 1) echo "checked"; ?>> Yes &nbsp;
                    <input type="radio" name="group_country" value="2" <?php if ($group_country == 2 || $group_country == '') echo "checked"; ?>> No
                    </td>
                <tr>
                  <td colspan="5" class="menuBoxHeading" align="right">
                    <input type="submit" value="<?php echo REPORT_SEND; ?>">
                  </td>
              </table>
            </form>
          </td>
        </tr>
<?php
  } // end of ($srExp < 1)
?>
        <tr>
          <td width=100% valign=top>
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td valign="top">
                  <table border="0" width="100%" cellspacing="0" cellpadding="2">
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_DATE; ?></td>
                      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDERS;?></td>
                      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ITEMS; ?></td>
                      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_REVENUE;?></td>
					  <?php if($login_groups_id == '1') { ?>	
					  <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_COST;?></td>
					  <td class="dataTableHeadingContent" align=right><?php echo TABLE_HEADING_GROSS_PROFIT; ?></td>
					  <td class="dataTableHeadingContent" align=right><?php echo TABLE_HEADING_GROSS_PROFIT_PERCENTAGE; ?></td>
					  <?php } ?>
                      <td class="dataTableHeadingContent" align="right"><?php echo  TABLE_HEADING_SHIPPING;?></td>
                    </tr>
<?php
} // end of if $srExp < 2 csv export
$sum = 0;
while ($sr->actDate < $sr->endDate) {
  $info = $sr->getNext();
  $last = sizeof($info) - 1;
  if ($srExp < 2) {
?>
                    <tr class="dataTableRow" onMouseOver="this.className='dataTableRowOver';this.style.cursor='hand'" onMouseOut="this.className='dataTableRow'">
<?php
    switch ($srView) {
      case '3':
?>
                      <td class="dataTableContent" align="right"><?php echo tep_date_long(date("Y-m-d\ H:i:s", $sr->showDate)) . " - " . tep_date_short(date("Y-m-d\ H:i:s", $sr->showDateEnd)); ?></td>
<?php
        break;
      case '4':
?>
                      <td class="dataTableContent" align="right"><?php echo tep_date_long(date("Y-m-d\ H:i:s", $sr->showDate)); ?></td>
<?php
        break;
      default;
?>
                      <td class="dataTableContent" align="right"><?php echo tep_date_short(date("Y-m-d\ H:i:s", $sr->showDate)) . " - " . tep_date_short(date("Y-m-d\ H:i:s", $sr->showDateEnd)); ?></td>
<?php
    }
?>
                      <td class="dataTableContent" align="right"><?php echo $info[0]['order']; ?></td>
                      <td class="dataTableContent" align="right"><?php echo $info[$last - 1]['totitem']; ?></td>
                      <td class="dataTableContent" align="right"><?php echo $currencies->format($info[$last - 1]['totsum']);?></td>
					   <?php if($login_groups_id == '1') { ?>	
					  <td class="dataTableContent" align="right"><?php echo $currencies->format($info[$last - 1]['totsum_cost']);?></td>
					  <?php
						$gross_price = $info[$last - 1]['totsum'];
						$gross_cost = $info[$last - 1]['totsum_cost'];
						$gross_profit = $currencies->format(($gross_price - $gross_cost));
						if($gross_price != 0){	
							$margin = tep_round((((($gross_price)-($gross_cost))/($gross_price))*100), 0);
						}else{
							$margin = 0;
						}
						?>
						<td class="dataTableContent" align="right"><?php echo $gross_profit;?></td>
						<td class="dataTableContent" align="right"><?php echo $margin . '%';?></td>
					  <?php } ?>
                      <td class="dataTableContent" align="right"><?php echo $currencies->format($info[0]['shipping']);?></td>
                    </tr>
<?php
  } else {
    // csv export
    echo date(DATE_FORMAT, $sr->showDate) . SR_SEPARATOR1 . date(DATE_FORMAT, $sr->showDateEnd) . SR_SEPARATOR1;
    echo $info[0]['order'] . SR_SEPARATOR1;
    echo $info[$last - 1]['totitem'] . SR_SEPARATOR1;
    echo $currencies->format($info[$last - 1]['totsum']) . SR_SEPARATOR1;
    echo $currencies->format($info[0]['shipping']) . SR_NEWLINE;
  }
  if ($srDetail || ($srDetail=='' && $_GET['group_country'] == '1')) {
    for ($i = 0; $i < $last; $i++) {
      if ($srMax == 0 or $i < $srMax) {
        if ($srExp < 2) {
?>

					<?php 
					if(isset($_GET['group_country']) && $_GET['group_country'] == '1' && $_GET['detail'] > 0){
					if($lastcounty != $info[$i]['customers_country']) {
					 $lastcounty = $info[$i]['customers_country']; 
					$external_row_query1 = $_SESSION['externalquery'] . ' and o.customers_country ="'.$lastcounty.'"';
					$external_row1 = tep_db_query($external_row_query1);
					if($external_recored1 = tep_db_fetch_array($external_row1)){
					$displaycount1 = $external_recored1['order_ext_cnt'];
					}
					?>
					<tr class="dataTableRow" >
					<td  class="dataTableContent" ><b><?php echo $lastcounty; ?></b></td>
					<td  class="dataTableContent" align="right" ><b><?php echo $displaycount1 ; ?></b></td>
					<td  class="dataTableContent" colspan="5"></td>
					</tr>
					<?php					
					}
					} 
					?>
					
                    <tr class="dataTableRow" onMouseOver="this.className='dataTableRowOver';this.style.cursor='hand'" onMouseOut="this.className='dataTableRow'">
                    <td class="dataTableContent"><b>
					<?php 
					if(isset($_GET['group_country']) && $_GET['group_country'] == '1' && $_GET['detail'] == 0){
					if($lastcounty != $info[$i]['customers_country']) {
					echo $lastcounty = $info[$i]['customers_country'];
					}
					} 
					?>
					</b>
					</td>
					
                    
					<?php
				
					
					if($srDetail!='')
					{
					?>
					<td class="dataTableContent" align="left">
					<a href="<?php echo tep_catalog_href_link("product_info.php?products_id=" . $info[$i]['pid']) ?>" target="_blank"><?php echo $info[$i]['pname']; ?></a>
					
									<?php
									  if (is_array($info[$i]['attr'])) {
										$attr_info = $info[$i]['attr'];
										foreach ($attr_info as $attr) {
										  echo '<div style="font-style:italic;">&nbsp;' . $attr['quant'] . 'x ' ;
										  //  $attr['options'] . ': '
										  $flag = 0;
										  foreach ($attr['options_values'] as $value) {
											if ($flag > 0) {
											  echo "," . $value;
											} else {
											  echo $value;
											  $flag = 1;
											}
										  }
										  $price = 0;
										  foreach ($attr['price'] as $value) {
											$price += $value;
										  }
										  if ($price != 0) {
											echo ' (';
											if ($price > 0) {
											  echo "+";
											}
											echo $currencies->format($price). ')';
										  }
										  echo '</div>';
										}
									  }
									  ?>
									  </td>
									  <?php
				}// end of if($srDetail=='' && $_GET['group_country'] == '1')
				else
				{
				
				$external_row_query = $_SESSION['externalquery'] . ' and o.customers_country ="'.$lastcounty.'"';
				$external_row = tep_db_query($external_row_query);
				if($external_recored = tep_db_fetch_array($external_row)){
				$displaycount = $external_recored['order_ext_cnt'];

				}		
				
				echo '<td class="dataTableContent" align="right">'.$displaycount.'</td>';
				}
									?>                    
					
					

                    <td class="dataTableContent" align="right"><?php echo $info[$i]['pquant']; ?></td>
<?php
if($srDetail=='')
{
	echo '<td class="dataTableContent" align="right">'.$currencies->format($info[$i]['psum']).'</td>';
	if($login_groups_id == '1') { 
	echo '<td class="dataTableContent" align="right">'.$currencies->format($info[$i]['psum_cost']).'</td>';
	}
	echo '<td class="dataTableContent" align="right">'.$currencies->format($info[$i]['shipping']).'</td>';
}
	
          if ($srDetail == 2) {?>
                    <td class="dataTableContent" align="right"><?php echo $currencies->format($info[$i]['psum']); ?></td>
					<?php if($login_groups_id == '1') { ?>	
					<td class="dataTableContent" align="right"><?php echo $currencies->format($info[$i]['psum_cost']); ?></td>
					<?php } ?>
<?php
          } else { ?>
                    <td class="dataTableContent">&nbsp;</td>
					<?php if($login_groups_id == '1') { ?>	
					 <td class="dataTableContent">&nbsp;</td>
					<?php } ?>
					
<?php
          }
?>

                    <td class="dataTableContent">&nbsp;</td>
                  </tr>
<?php
        } else {
        // csv export
          if (is_array($info[$i]['attr'])) {
            $attr_info = $info[$i]['attr'];
            foreach ($attr_info as $attr) {
              echo $info[$i]['pname'] . "(";
              $flag = 0;
              foreach ($attr['options_values'] as $value) {
                if ($flag > 0) {
                  echo ", " . $value;
                } else {
                  echo $value;
                  $flag = 1;
                }
              }
              $price = 0;
              foreach ($attr['price'] as $value) {
                $price += $value;
              }
              if ($price != 0) {
                echo ' (';
                if ($price > 0) {
                  echo "+";
                } else {
                  echo " ";
                }
                echo $currencies->format($price). ')';
              }
              echo ")" . SR_SEPARATOR2;
              if ($srDetail == 2) {
                echo $attr['quant'] . SR_SEPARATOR2;
                echo $currencies->format( $attr['quant'] * ($info[$i]['price'] + $price)) . SR_NEWLINE;
              } else {
                echo $attr['quant'] . SR_NEWLINE;
              }
              $info[$i]['pquant'] = $info[$i]['pquant'] - $attr['quant'];
            }
          }
          if ($info[$i]['pquant'] > 0) {
            echo $info[$i]['pname'] . SR_SEPARATOR2;
            if ($srDetail == 2) {
              echo $info[$i]['pquant'] . SR_SEPARATOR2;
              echo $currencies->format($info[$i]['pquant'] * $info[$i]['price']) . SR_NEWLINE;
            } else {
              echo $info[$i]['pquant'] . SR_NEWLINE;
            }
          }
        }
      }
    }
  }
}
if ($srExp < 2) {
?>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php
  if ($srExp < 1) {
    require(DIR_WS_INCLUDES . 'footer.php');
  }
?>
<!-- footer_eof //-->
</body>
</html>
<?php
  require(DIR_WS_INCLUDES . 'application_bottom.php');
} // end if $srExp < 2
?>
