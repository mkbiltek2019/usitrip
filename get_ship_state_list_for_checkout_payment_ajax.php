<?php
require('includes/application_top.php');
header("Content-type: text/html; charset=utf-8");


//根据国家取得省份列表
if((int)$_GET['country_id']){
	$sql = tep_db_query('SELECT zone_id, zone_name FROM `zones` WHERE  zone_country_id ="'.(int)$_GET['country_id'].'" ORDER BY  zone_code ');
	$rows = tep_db_fetch_array($sql);;
	if((int)$rows['zone_id']){
        $zones_array = array();
        $zones_array[] = array('id' => 's', 'text' => iconv(CHARSET,'utf-8'.'//IGNORE',PULL_DOWN_DEFAULT) );
		do{
			$zones_array[] = array('id' =>  iconv(CHARSET,'utf-8'.'//IGNORE',db_to_html($rows['zone_name'])), 'text' =>  iconv(CHARSET,'utf-8'.'//IGNORE',db_to_html($rows['zone_name'])));
		}while($rows = tep_db_fetch_array($sql));
		echo tep_draw_pull_down_menu('ship_state', $zones_array,$ship_state,'id="ship_state" class="required validate-length-ship_state" title="'.iconv(CHARSET,'utf-8'.'//IGNORE',ENTRY_STATE_ERROR).'" onchange="return get_ship_city(this.value,checkout_payment,\'ship_city\');" ');
	}else{
		echo tep_draw_input_field('ship_state','','id="ship_state" class="required validate-length-ship_state" title="'.iconv(CHARSET,'utf-8'.'//IGNORE',ENTRY_STATE_ERROR).'"');
	}
}

//根据省份名称取得城市列表
if($_POST['ship_state']){
	$_POST['ship_state'] = iconv('utf-8',CHARSET.'//IGNORE',urldecode($_POST['ship_state']));
	$_POST['ship_state'] = iconv(CHARSET,'gb2312'.'//IGNORE',$_POST['ship_state']);
	
	$sql_city = tep_db_query('SELECT zc.city_id, zc.city_name FROM `zones` z , `zones_city` zc WHERE  z.zone_id=zc.zone_id AND z.zone_name="'.tep_db_prepare_input($_POST['ship_state']).'" ORDER BY  zc.city_id ');

	$city_array = array();
	while($city_rows = tep_db_fetch_array($sql_city)){
		$city_array[] = array('id' => iconv(CHARSET,'utf-8'.'//IGNORE',db_to_html($city_rows['city_name'])), 'text' => iconv(CHARSET,'utf-8'.'//IGNORE',db_to_html($city_rows['city_name'])));
	}
	if((int)count($city_array)){
		echo tep_draw_pull_down_menu('ship_city', $city_array,'','id="ship_city" class="required validate-length-ship_city" title="'.iconv(CHARSET,'utf-8'.'//IGNORE',ENTRY_CITY_ERROR).'"');
	}else{
		unset($ship_city);
		echo tep_draw_input_field('ship_city','','id="ship_city" class="required validate-length-ship_city" title="'.iconv(CHARSET,'utf-8'.'//IGNORE',ENTRY_CITY_ERROR).'"');
	}
}

?>