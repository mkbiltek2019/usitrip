<?php
header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );
header( "Cache-Control: no-cache, must-revalidate" );
header( "Pragma: no-cache" );

  require('includes/application_top.php');
  // 备注添加删除
  if($_GET['ajax']=="true"){
  	include DIR_FS_CLASSES . 'Remark.class.php';
  	$remark = new Remark('double_room_preferences');
  	$remark->checkAction($_GET['action'], $login_id);//添加删除动作，统一在方法里面处理了
  }
  switch($_GET['action']){
  	case 'setstate': 
	 	if((int)$_GET['products_double_room_preferences_id']){
			tep_db_query('update products_double_room_preferences set status="'.(int)$_GET['status'].'" where products_double_room_preferences_id="'.(int)$_GET['products_double_room_preferences_id'].'"');
		}
	 break;
	case 'DelConfirmed':
		if((int)$_GET['products_double_room_preferences_id']){
			tep_db_query('DELETE FROM `products_double_room_preferences` WHERE `products_double_room_preferences_id` = "'.(int)$_GET['products_double_room_preferences_id'].'" ');
			tep_db_query('OPTIMIZE TABLE `products_double_room_preferences` ');
		}
	 break;
	case 'edit_confirmation':	//ajax 编辑内容
		if($_POST['ajax_send']=='true'){
			require(DIR_WS_INCLUDES . 'ajax_encoding_control.php');
			
			$id = (int)$_POST['products_double_room_preferences_id'];
			if($_POST['min_start_date_'.$id]!=""){
				$products_departure_date_begin = date2DATE($_POST['min_start_date_'.$id]).' 00:00:00';
			}
			if($_POST['max_start_date_'.$id]!=""){
				$products_departure_date_end = date2DATE($_POST['max_start_date_'.$id]).' 23:59:59';
			}
			$excluding_dates = str_replace(' ','',$_POST['excluding_dates_'.$id]);
			$products_id = (int)$_POST['products_id_input_'.$id];
			$value = number_format($_POST['value_input_'.$id],2);
			
			tep_db_query('update `products_double_room_preferences` set value="'.$value.'", products_id="'.$products_id.'", products_departure_date_begin="'.$products_departure_date_begin.'", products_departure_date_end="'.$products_departure_date_end.'", excluding_dates="'.$excluding_dates.'" WHERE products_double_room_preferences_id="'.$id.'" ');
			
			echo '[VALUE]'.$value.'[VALUE]';
			echo '[PROD]'.$products_id.'[PROD]';
			echo '[DATEBEGIN]'.chardate($products_departure_date_begin,"D","1").'[DATEBEGIN]';
			echo '[DATEEND]'.chardate($products_departure_date_end,"D","1").'[DATEEND]';
			echo '[EXCLUDING]'.str_replace(',','<br>',$excluding_dates).'[EXCLUDING]';
			
			exit;
		}
	 break;
		 
  }
 
 //增加记录
 if(tep_not_null($_GET['Insert'])){
 	if(tep_not_null($_GET['search_products_id']) && tep_not_null($_GET['search_value'])){
		$value = number_format($_GET['search_value'],2);
		$products_departure_date_begin = $_GET['search_start_date'];
		$products_departure_date_end = $_GET['search_end_date'];
		$excluding_dates = str_replace(' ','',$_GET['search_excluding_dates']);
		$products_ids = explode(',',(string)$_GET['search_products_id']);
		for($i=0, $n=count($products_ids); $i<$n; $i++){
			$sql_date_array = array('value' => $value,
									'products_departure_date_begin' => $products_departure_date_begin." 00:00:00",
									'products_departure_date_end' => $products_departure_date_end." 23:59:59",
									'excluding_dates' => $excluding_dates,
									'products_id' => $products_ids[$i],
									'status' => '1'
									);
			tep_db_perform('products_double_room_preferences', $sql_date_array);
		}
		
		tep_redirect(tep_href_link('double_room_preferences.php'),'SSL');
	}
 }
  
  //条件
  $where =' where products_double_room_preferences_id > 0 ';
  if($_GET['search']=='1' && tep_not_null($_GET['Send'])){
  	if(tep_not_null($_GET['search_start_date'])){
		$where .=' AND products_departure_date_begin >= "'.$_GET['search_start_date'].' 00:00:00" ';
	}
  	if(tep_not_null($_GET['search_end_date'])){
		$where .=' AND products_departure_date_begin <= "'.$_GET['search_end_date'].' 23:59:59" ';
	}
	if(tep_not_null($_GET['search_products_id'])){
		$where .=' AND products_id Like "'.$_GET['search_products_id'].'%" ';
	}
	if(tep_not_null($_GET['search_value'])){
		$where .=' AND value Like "'.$_GET['search_value'].'%" ';
	}
	if(tep_not_null($_GET['search_excluding_dates'])){
		$where .=' AND excluding_dates Like "%'.$_GET['search_excluding_dates'].'%" ';
	}
	
  }
  //排序
  $order_by = ' order by products_double_room_preferences_id desc ';

	$sql_str = 'select * from `products_double_room_preferences` '.$where.$order_by;
	$companion_query_numrows = 0;
	$companion_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_ADMIN, $sql_str, $companion_query_numrows);
	
	$companion_query = tep_db_query($sql_str);

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">

<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>

<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/big5_gb-min.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script type="text/javascript">
//创建ajax对象
var ajax = false;
if(window.XMLHttpRequest) {
	 ajax = new XMLHttpRequest();
}
else if (window.ActiveXObject) {
	try {
			ajax = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
	try {
			ajax = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (e) {}
	}
}
if (!ajax) {
	window.alert("<?php echo db_to_html('不能创建XMLHttpRequest对象实例.')?>");
}
</script>
<script type="text/javascript" src="includes/javascript/calendar.js"></script>
<script language="javascript"><!--

//var Date_Reg_start = new ctlSpiffyCalendarBox("Date_Reg_start", "form_search", "search_start_date","btnDate1","<?php echo ($search_start_date); ?>",scBTNMODE_CUSTOMBLUE);
//var Date_Reg_end = new ctlSpiffyCalendarBox("Date_Reg_end", "form_search", "search_end_date","btnDate2","<?php echo ($search_end_date); ?>",scBTNMODE_CUSTOMBLUE);

<?php
$p=array('/&amp;/','/&quot;/');
$r=array('&','"');
?>

function DelTravel(t_id){
	if(t_id<1){ alert('no id'); return false;}
	if(window.confirm("真的要删除它吗？\t")==true){
		parent.location = "<?php echo preg_replace($p,$r,tep_href_link('double_room_preferences.php','action=DelConfirmed'))?>&products_double_room_preferences_id="+t_id;
		return false;
	}
}

var edit_action = false;
function EditShow(id){
	var products_id = document.getElementById('products_id_'+id);
	var value = document.getElementById('value_'+id);
	var min_start_date = document.getElementById('min_start_date_'+id);
	var max_start_date = document.getElementById('max_start_date_'+id);
	var excluding_dates = document.getElementById('excluding_dates_'+id);
	var edit_button = document.getElementById('edit_button_'+id);
	var edit_confirmation = document.getElementById('edit_confirmation_'+id);

	if(edit_action==false){
		edit_action = true;
		edit_button.style.display = "none";
		edit_confirmation.style.display = "";
		
		products_id.innerHTML = '<input size="10" name="products_id_input_'+ id +'" type="text" id="products_id_input_'+ id +'" value="'+parseInt(products_id.innerHTML)+'" />';
		value.innerHTML = '<input size="6" name="value_input_'+ id +'" type="text" id="value_input_'+ id +'" value="'+value.innerHTML+'" />';
		min_start_date.innerHTML = '<input size="11" maxlength="10" name="min_start_date_'+ id +'" type="text" id="min_start_date_'+ id +'" value="'+min_start_date.innerHTML.replace(/\D/gi,'-').substr(0,min_start_date.innerHTML.length-1)+'" />';
		max_start_date.innerHTML = '<input size="11" maxlength="10" name="max_start_date_'+ id +'" type="text" id="max_start_date_'+ id +'" value="'+max_start_date.innerHTML.replace(/\D/gi,'-').substr(0,max_start_date.innerHTML.length-1)+'" />';
		excluding_dates.innerHTML = '<input size="46" name="excluding_dates_'+ id +'" type="text" id="excluding_dates_'+ id +'" value="'+excluding_dates.innerHTML.replace(/\<br\>/gi,', ')+'" />';
	}
}

function EditSubmit(id){
	var form_obj = document.getElementById('form_content');
	var load_img = document.getElementById('load_img_'+id);
	load_img.style.display = "";

	var products_id = document.getElementById('products_id_'+id);
	var value = document.getElementById('value_'+id);
	var min_start_date = document.getElementById('min_start_date_'+id);
	var max_start_date = document.getElementById('max_start_date_'+id);
	var excluding_dates = document.getElementById('excluding_dates_'+id);
	var edit_button = document.getElementById('edit_button_'+id);
	var edit_confirmation = document.getElementById('edit_confirmation_'+id);

	
	var url = "<?php echo preg_replace($p,$r,tep_href_link('double_room_preferences.php','action=edit_confirmation')) ?>";
	var aparams=new Array();
	for(i=0; i<form_obj.elements.length; i++){
		if( ((form_obj.elements[i].type=='checkbox' || form_obj.elements[i].type=='radio') && form_obj.elements[i].checked==true) || (form_obj.elements[i].type!='checkbox' && form_obj.elements[i].type!='radio' )){	//处理复选框和单选的值
			var sparam=encodeURIComponent(form_obj.elements[i].name);
			sparam+="=";
			sparam+=encodeURIComponent(form_obj.elements[i].value);
			aparams.push(sparam);

		}else if(form_obj.elements[i].type!='checkbox' && form_obj.elements[i].type!='radio' ){

			var sparam=encodeURIComponent(form_obj.elements[i].name);  //取得表单元素名
			sparam+="=";     //名与值之间用"="号连接
			sparam+=encodeURIComponent(form_obj.elements[i].value);   //获得表单元素值
			aparams.push(sparam);   //push是把新元素添加到数组中去
		
		}
	}
	sparam+= '&ajax_send=true&products_double_room_preferences_id='+id;
	aparams.push(sparam);
	
	var post_str=aparams.join("&");		//使用&将各个元素连接

	ajax.open("post", url, true);
	//定义传输的文件HTTP头信息
	ajax.setRequestHeader("Content-Type","application/x-www-form-urlencoded"); 
	ajax.send(post_str); 

	var comp_show = document.getElementById('orders_travel_comp_show');
	var comp_edit = document.getElementById('orders_travel_comp_edit');
	var send_mail_travel = document.getElementById('send_mail_travel');
	ajax.onreadystatechange = function() { 
		if (ajax.readyState == 4 && ajax.status == 200) { 
			load_img.style.display = "none";
			
			var rows = ajax.responseText;
			products_id.innerHTML = rows.replace(/.*\[PROD\](.*)\[PROD\].*/gi,'$1');
			value.innerHTML = rows.replace(/.*\[VALUE\](.*)\[VALUE\].*/gi,'$1');
			min_start_date.innerHTML = rows.replace(/.*\[DATEBEGIN\](.*)\[DATEBEGIN\].*/gi,'$1');
			max_start_date.innerHTML = rows.replace(/.*\[DATEEND\](.*)\[DATEEND\].*/gi,'$1');
			excluding_dates.innerHTML = rows.replace(/.*\[EXCLUDING\](.*)\[EXCLUDING\].*/gi,'$1');
			edit_button.style.display = "";
			edit_confirmation.style.display = "none";
			edit_action = false;
		}
	}
	
}
//--></script>

<div id="spiffycalendar" class="text"></div>
<script type="text/javascript" src="includes/jquery-1.3.2/jquery-1.3.2.min.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">





<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<?php
//echo $login_id;
include DIR_FS_CLASSES . 'Remark.class.php';
$listrs = new Remark('double_room_preferences');
$list = $listrs->showRemark();
?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
        </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading">双人折扣团管理</td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td>
          <!--search form start-->
		  <fieldset>
		  <legend align="left"> Search Module </legend>
		  <?php echo tep_draw_form('form_search', 'double_room_preferences.php', tep_get_all_get_params(array('page','y','x', 'action')), 'get'); ?>
		  
		  <table width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td><table border="0" cellspacing="0" cellpadding="0" style="margin:10px;">
                  
				  <tr>
                    <td height="30" align="right" valign="middle" nowrap class="main">团号</td>
                    <td align="left" valign="middle" class="main">&nbsp;<?php echo tep_draw_input_field('search_products_id')?></td>
                    <td>&nbsp;</td>
                    <td align="right" nowrap class="main">&nbsp;</td>
                    <td align="right" nowrap class="main">出团日期</td>
                    <td class="main" align="left">
                      <table border="0" cellspacing="0" cellpadding="0">
                        <tr>
                          <td nowrap class="main">&nbsp;
						  <?php echo tep_draw_input_field('search_start_date', tep_get_date_disp($search_start_date), ' class="textTime" style="ime-mode: disabled;" onclick="GeCalendar.SetUnlimited(1); GeCalendar.SetDate(this);"');?>
						  <!--script language="javascript">Date_Reg_start.writeControl(); Date_Reg_start.dateFormat="yyyy-MM-dd";</script--></td>
                          <td class="main">&nbsp;至&nbsp;</td>
                          <td nowrap class="main">
						  <?php echo tep_draw_input_field('search_end_date', tep_get_date_disp($search_end_date), ' class="textTime" style="ime-mode: disabled;" onclick="GeCalendar.SetUnlimited(1); GeCalendar.SetDate(this);"');?>
						  <!--script language="javascript">Date_Reg_end.writeControl(); Date_Reg_end.dateFormat="yyyy-MM-dd";</script--></td>
                        </tr>
                      </table></td>
                    <td class="main" align="left">&nbsp;</td>
				    <td class="main" align="left">优惠价值</td>
				    <td class="main" align="left"><?php echo tep_draw_input_field('search_value')?></td>
				    <td class="main" align="left">&nbsp;</td>
				    <td align="left" nowrap class="main">排除日期</td>
				    <td align="left" nowrap class="main"><?php echo tep_draw_input_field('search_excluding_dates')?></td>
				  </tr>
                  <tr>
                    <td class="main" align="right">&nbsp;</td>
                    <td class="main" align="left">&nbsp;<input name="Send" type="submit" value="搜索" style="width:100px; height:30px; margin-top:10px;"></td>
                    <td>&nbsp;</td>
                    <td class="main" align="right">&nbsp;</td>
                    <td align="right" class="main"><input name="search" type="hidden" id="search" value="1">					</td>
                    <td align="left" class="main"><input name="Insert" type="submit" id="Insert" style="width:100px; height:30px; margin-top:10px;" value="增加记录"></td>
                    <td align="right" class="main">&nbsp;</td>
                    <td align="right" class="main">&nbsp;</td>
                    <td align="right" class="main">&nbsp;</td>
                    <td align="right" class="main">&nbsp;</td>
                    <td align="right" class="main">&nbsp;</td>
                    <td align="right" class="main">&nbsp;</td>
                  </tr>
                </table></td>
			  </tr>
			</table>

		  <?php echo '</form>';?>
		  </fieldset>
		  <!--search form end-->
		  </td>
      </tr>
      <tr>
        <td>
		<fieldset>
		  <legend align="left"> Stats Results </legend>

		<table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td valign="top">
			<form action="" method="post" name="form_content" id="form_content">
			<table border="0" width="100%" cellspacing="1" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" nowrap="nowrap">团号</td>
				<td class="dataTableHeadingContent" nowrap="nowrap">每人优惠多少钱</td>

				<td class="dataTableHeadingContent" nowrap="nowrap">出团日期</td>

				<td class="dataTableHeadingContent" nowrap="nowrap">以下出团日期不优惠</td>
				<td class="dataTableHeadingContent" nowrap="nowrap">状态</td>
				<td class="dataTableHeadingContent" nowrap="nowrap">操作</td>
              </tr>
			<?php
			while($rows = tep_db_fetch_array($companion_query)){
			    $rows_num++;
			
				if (strlen($rows) < 2) {
				  $rows_num = '0' . $rows_num;
				}
				
				$bg_color = "#F0F0F0";
				if((int)$rows_num %2 ==0 && (int)$rows_num){
					$bg_color = "#ECFFEC";
				}

			?>
              
			  <tr class="dataTableRow" style="cursor:auto; background-color:<?= $bg_color;?>">
                <td class="dataTableContent" id="products_id_<?php echo $rows['products_double_room_preferences_id']?>" style="font-weight: bold;"><?php echo tep_db_output($rows['products_id'])?></td>
                <td class="dataTableContent" id="value_<?php echo $rows['products_double_room_preferences_id']?>"><?php echo nl2br(tep_db_output($rows['value']))?></td>
				
				<td class="dataTableContent">
				<?php
				$min_start_date = "";
				$max_start_date = "";
				if($rows['products_departure_date_begin']>"2000-01-01 01:01:01"){
					$min_start_date = chardate($rows['products_departure_date_begin'],"D","1");
				}
				if($rows['products_departure_date_end']>"2000-01-01 01:01:01"){
					$max_start_date = chardate($rows['products_departure_date_end'],"D","1");
				}
				echo '<span id="min_start_date_'.(int)$rows['products_double_room_preferences_id'].'">'.trim($min_start_date).'</span> 至 <span id="max_start_date_'.(int)$rows['products_double_room_preferences_id'].'">'.trim($max_start_date).'</span>';
				?>			
				</td>
				<td class="dataTableContent" id="excluding_dates_<?php echo $rows['products_double_room_preferences_id']?>"><?php echo str_replace(',','<br>',$rows['excluding_dates'])?></td>
				<td class="dataTableContent">
				<?php
					  if ($rows['status'] == '1') {
						echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link('double_room_preferences.php', 'action=setstate&status=0&products_double_room_preferences_id=' . $rows['products_double_room_preferences_id'].(isset($HTTP_GET_VARS['page']) ? '&page=' . $HTTP_GET_VARS['page'] . '' : '').(isset($HTTP_GET_VARS['sortorder']) ? '&sortorder=' . $HTTP_GET_VARS['sortorder'] . '' : '')) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
					  } else {
						echo '<a href="' . tep_href_link('double_room_preferences.php', 'action=setstate&status=1&products_double_room_preferences_id=' . $rows['products_double_room_preferences_id'] .(isset($HTTP_GET_VARS['page']) ? '&page=' . $HTTP_GET_VARS['page'] . '' : ''). (isset($HTTP_GET_VARS['sortorder']) ? '&sortorder=' . $HTTP_GET_VARS['sortorder'] . '' : '')) .'">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
					  }
				?>				</td>
				<td nowrap class="dataTableContent">[<a id="edit_button_<?php echo $rows['products_double_room_preferences_id']?>" href="JavaScript:void(0);" onClick="EditShow(<?php echo $rows['products_double_room_preferences_id']?>);">编辑</a><a id="edit_confirmation_<?php echo $rows['products_double_room_preferences_id']?>" style="display:none" href="JavaScript:void(0);" onClick="EditSubmit(<?php echo $rows['products_double_room_preferences_id']?>);">确定</a>]<img id="load_img_<?php echo $rows['products_double_room_preferences_id']?>" src="images/loading.gif" style="display:none" />
				
				[<a href="JavaScript:void(0);" onClick="DelTravel(<?php echo $rows['products_double_room_preferences_id']?>); return false;">删除</a>]</td>
              </tr>
			  
			<?php
			}
			?>  

            </table>
			</form>
			</td>
          </tr>
		  <tr>

			<td colspan="<?= $colspan?>"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText" valign="top"><?php echo $companion_split->display_count($companion_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_ADMIN, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></td>
                <td class="smallText" align="right"><?php echo $companion_split->display_links($companion_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_ADMIN, MAX_DISPLAY_PAGE_LINKS, $_GET['page'],tep_get_all_get_params(array('page','y','x', 'action'))); ?>&nbsp;</td>
              </tr>
            </table></td>
          </tr>
		</table>
		
		</fieldset>
		</td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
