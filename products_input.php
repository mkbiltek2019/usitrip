?php
/**
 * 新旧站产品数据互导类
 * @author Howard
 * @package 此程序只在新旧站交接过程中使用，其它情况不用。
 * @copyright 周豪华   
 * @version 1.0
 */
ini_set('display_errors', '1');
error_reporting(E_ALL & ~E_NOTICE);
set_time_limit(0);

require('includes/application_top.php');

$num = 0;
/**
 * 取得旧网站产品数据资料类
 *
 */
class getOldProducts {
	/**
	 * 更新产品出发日期
	 *
	 * @param unknown_type $products_id
	 */
	public function update_date_info($products_id){

		die("出发日期功能已经被毙掉！请问许月方用excel导入");

		global $num;

		$url = 'http://208.109.123.18/WebTravel/Travel_showTdate.asp?id='.$products_id;
		//	products_start_day 星期几。1表示星期日，7表达星期六,0表示不明确星期几。范围0-7
		$weeks = array('1'=>'2','2'=>'3','3'=>'4','4'=>'5','5'=>'6','6'=>'7','7'=>'1');
		$html = file_get_contents($url);
		//$html = '2012-5-19,0;2012-9-12,0;';
		//$html = '5/19/2012,0;9/12/2012,0;';

		$html = preg_replace('/[[:space:]]+/','',$html);
		if(strtolower($html)!='error' && strpos($html,';')!==false){
			$array = explode(';',$html);
			//print_r($array);
			//echo '<hr>';
			$date_arary = array();
			tep_db_query('DELETE FROM `products_reg_irreg_date` WHERE products_id="'.(int)$products_id.'" ');
			$operate_start_date = $array[0];
			$operate_start_date = explode(',',$operate_start_date);
			$operate_start_date = date('m-d-Y',strtotime($operate_start_date[0]));

			$operate_end_date = $array[sizeof($array)-2];
			$operate_end_date = explode(',',$operate_end_date);
			$operate_end_date = date('m-d-Y',strtotime($operate_end_date[0]));

			//echo $operate_start_date."||".$operate_end_date;
			//exit;

			$loop=0;
			foreach((array)$array as $key => $value){
				if(tep_not_null($value)){
					$_tmp_array = explode(',',$value);
					$date_arary[] = array('date'=>date('Y-m-d',strtotime($_tmp_array[0])),'type'=>$_tmp_array[1]);
					$week = date('N',strtotime($_tmp_array[0]));
					$week = strtr($week, $weeks);
					$available_date = date('Y-m-d',strtotime($_tmp_array[0]));
					/*			tep_db_query('INSERT INTO `products_reg_irreg_date` (`operate_start_date` ,`operate_end_date` ,`available_date`, `products_id`, `products_start_day`) VALUES (
					"'.$operate_start_date.'", "'.$operate_end_date.'", "'.$available_date.'","'.(int)$products_id.'","'.$week.'");');
					*/
					if(!(int)$date_arary[$loop]['type']){
						//正常日期(正常日期分规则和不规则的日期)
						tep_db_query('INSERT INTO `products_reg_irreg_date` (`operate_start_date` ,`operate_end_date` , `products_id`, `products_start_day`) VALUES (
																					"'.$operate_start_date.'", "'.$operate_end_date.'", "'.(int)$products_id.'","'.$week.'");');

					}else{
						//假日日期
						tep_db_query('INSERT INTO `products_reg_irreg_date` (`operate_start_date` ,`operate_end_date` , `products_id`, `available_date`) VALUES (
																		"'.date('m-d-Y',strtotime($available_date)).'", "'.date('m-d-Y',strtotime($available_date)).'", "'.(int)$products_id.'","'.$available_date.'");');
					}
					$loop++;
				}
			}

			//重新筛选清理正常日期中无用数据{
			$where = 'WHERE operate_start_date="'.$operate_start_date.'" and operate_end_date="'.$operate_end_date.'" and products_id="'.(int)$products_id.'" and available_date="" ';
			$sql = tep_db_query('SELECT products_start_day FROM `products_reg_irreg_date` '.$where.' Group By products_start_day ORDER BY `products_start_day_id` ');
			$products_start_days = array();
			while($rows = tep_db_fetch_array($sql)){
				$products_start_days[] = $rows['products_start_day'];
			}
			if(tep_not_null($products_start_days)){
				tep_db_query('DELETE FROM `products_reg_irreg_date` '.$where);
				foreach($products_start_days as $key => $value){
					tep_db_query('INSERT INTO `products_reg_irreg_date` (`operate_start_date` ,`operate_end_date` , `products_id`, `products_start_day`) VALUES (
																					"'.$operate_start_date.'", "'.$operate_end_date.'", "'.(int)$products_id.'","'.$value.'");');
				}
			}
			//}

			if(tep_not_null($date_arary)){
				//print_r($date_arary);
			}
			$num++;
		}
	}
	/**
	 * 更新产品的所属目录
	 * 根据汉字的出发城市和结束城市的文字查找产品目录表的标题，如果有该包含出发地或结束地的标题则更新产品目录与产品ID到products_to_categories表，如果找不到就放到临时分类的ID为297的目录中
	 * 同时根据途经景点对应的目录更新产品的所在目录：如果目录中包含有途经景点的关键字就把产品自动对应到该目录（当产品不存在该目录时才更新）
	 * 特别注意：主题乐园 添加的行程应该是:迪士尼、海洋世界、环球影城的线路
	 * @param unknown_type $products_id
	 */
	public function update_categories($products_id){
		//根据出发城市和结束地选目录
		/*		$sql = tep_db_query('SELECT place1 , place2 FROM `products` where products_id="'.(int)$products_id.'" ');
		$row = tep_db_fetch_array($sql);
		if(tep_not_null($row['place1']) || tep_not_null($row['place2'])){
		$place1s = explode('/',$row['place1']);
		$place2 = explode('/',$row['place2']);
		$place = array_merge($place1s, $place2);
		$place = array_unique($place);
		foreach((array)$place as $val){
		$sql = tep_db_query('SELECT DISTINCT categories_id FROM `categories_description` where language_id="1" and categories_name like binary "%'.$val.'%" ');
		if(tep_db_num_rows($sql) && tep_not_null($val)){
		while ($rows = tep_db_fetch_array($sql)) {
		tep_db_query('INSERT INTO `products_to_categories` (`products_id` ,`categories_id` ,`products_sort_order`) VALUES ('.(int)$products_id.', '.(int)$rows['categories_id'].', '.(int)$products_id.');');
		}
		}else {
		tep_db_query('INSERT INTO `products_to_categories` (products_id, categories_id, products_sort_order) VALUES ("'.(int)$products_id.'", "297", "'.(int)$products_id.'");');
		}
		}
		}
		*/		//根据途经景点选目录
		$sql = tep_db_query('SELECT city_id FROM `products_destination` WHERE products_id="'.(int)$products_id.'" ');
		while ($rows = tep_db_fetch_array($sql)) {
			$city_name = tep_get_city_name($rows['city_id']);
			if(tep_not_null($city_name)){

				$f_sql = tep_db_query('SELECT key_words0, key_words1 FROM '.TABLE_FAULT_TOLERANT_KEYWORDS.' WHERE key_words0="'.$city_name.'" || key_words1="'.$city_name.'"');
				$faultArray = array();
				while($f_rows = tep_db_fetch_array($f_sql)){
					$faultArray[] = $f_rows['key_words0'];
					$faultArray[] = $f_rows['key_words1'];
				}
				$faultArray = array_unique($faultArray);
				$like1 = '';
				if(tep_not_null($faultArray)){
					foreach ($faultArray as $val){
						//$like1 .= ' || categories_name like BINARY "%'.$val.'%"  ';
						$like1 .= ' || categories_name like BINARY "'.$val.'%"  ';
					}
				}
				//$search_sql = tep_db_query('SELECT categories_id FROM `categories_description` WHERE (categories_name like BINARY "%'.$city_name.'%" '.$like1.' ) and language_id="1" ');
				$search_sql = tep_db_query('SELECT categories_id FROM `categories_description` WHERE (categories_name like BINARY "'.$city_name.'%" '.$like1.' ) and language_id="1" ');
				while ($rows = tep_db_fetch_array($search_sql)) {
					tep_db_query('INSERT INTO `products_to_categories` (`products_id` ,`categories_id` ,`products_sort_order`) VALUES ('.(int)$products_id.', '.(int)$rows['categories_id'].', '.(int)$products_id.');');
					if(in_array(trim($city_name),array('迪士尼','海洋世界','环球影城'))){	//目录 主题乐园 添加的行程应该是以下景点:迪士尼、海洋世界、环球影城的线路
						tep_db_query('INSERT INTO `products_to_categories` (`products_id` ,`categories_id` ,`products_sort_order`) VALUES ('.(int)$products_id.', "41", '.(int)$products_id.');');
					}
					tep_db_query('DELETE FROM `products_to_categories` WHERE `categories_id` = "297" AND `products_id` = "'.(int)$products_id.'" ');
				}
			}
		}
	}
	/**
	 * 更新产品的出发地和结束地
	 * 根据汉字的出发城市和结束城市的文字匹配系统内的city取得ID号记录到products表departure_city_id字段和departure_end_city_id字段中
	 * @param unknown_type $products_id
	 */
	public function update_products_city($products_id){
		$sql = tep_db_query('SELECT place1 , place2 FROM `products` where products_id="'.(int)$products_id.'" ');
		$row = tep_db_fetch_array($sql);
		if(tep_not_null($row['place1']) || tep_not_null($row['place2'])){
			$place1s = explode('/',$row['place1']);
			$place2 = explode('/',$row['place2']);
			tep_db_query('UPDATE `products` set `departure_city_id`=null, departure_end_city_id=null where products_id = "'.(int)$products_id.'" '); //清空旧出发城市和结束城市
			foreach((array)$place1s as $val){
				$sql = tep_db_query('SELECT DISTINCT city_id FROM `tour_city` where city ="'.trim($val).'" and departure_city_status="1" ');
				if(tep_db_num_rows($sql) && tep_not_null($val)){
					while ($rows = tep_db_fetch_array($sql)) {
						tep_db_query('UPDATE `products` set `departure_city_id`=CONCAT_WS(",",`departure_city_id`,"'.$rows['city_id'].'")   where products_id = "'.(int)$products_id.'" ');
					}
				}
			}
			foreach((array)$place2 as $val){
				$sql = tep_db_query('SELECT DISTINCT city_id FROM `tour_city` where city ="'.trim($val).'" and departure_city_status="1" ');
				if(tep_db_num_rows($sql) && tep_not_null($val)){
					while ($rows = tep_db_fetch_array($sql)) {
						tep_db_query('UPDATE `products` set `departure_end_city_id`=CONCAT_WS(",",`departure_end_city_id`,"'.$rows['city_id'].'")   where products_id = "'.(int)$products_id.'" ');
					}
				}
			}

		}

		//更新产品所属区域
		tep_db_query('update `products` p, tour_city tc set p.regions_id=tc.regions_id WHERE departure_city_id!="" and p.departure_city_id=tc.city_id and products_id="'.(int)$products_id.'" ');
	}
	/**
	 * 更新产品供应商
	 *
	 * @param unknown_type $products_id
	 */
	public function update_products_agency($products_id){
		$rpArray = array(
		"Orlando-DS"=> "奥兰多DS",
		"Hawaii--CTS"=> "夏威夷CTS",
		"x"=> "1000",
		"West-SEA"=> "海鸥假期",
		"East-LLT"=> "纵横假期美东部",
		"旧金山-LS"=> "丽山假期",
		"万象-喜悦假期"=> "喜悦假期（万象）",
		"Canada-SV"=> "加拿大美亚",
		"East-PAT"=> "天马假期美东部",
		"Europe-goeugo"=> "欧来欧去",
		"China-CTS"=> "中旅国际旅行社",
		"West-PCC"=> "黄金假期",
		"美亚假期"=> "美亚假期（黄石）",
		"纽约-steven"=> "纽约-Steven",
		"名人假期-PE"=> "名人假期",
		"彩虹假期"=> "夏威夷-彩虹假期",
		"阳光旅游-BOS"=> "见闻旅游-BOS",
		"拉斯维加斯-中美旅游"=> "中美假期-Tours & Show",
		"名之旅-MIT"=> "MIT-明之旅",
		"路嘉国际"=> "路嘉国际-游学",
		"一帆旅游"=> "纽约一帆旅游"
		);

		$sql = tep_db_query('SELECT agency_name FROM `products` where products_id="'.(int)$products_id.'" ');
		$row = tep_db_fetch_array($sql);
		if(tep_not_null($row['agency_name'])){
			if(array_key_exists(trim($row['agency_name']), $rpArray)){
				$sql = tep_db_query('SELECT agency_id FROM `tour_travel_agency` WHERE agency_name="'.$rpArray[trim($row['agency_name'])].'" || agency_name1="'.$rpArray[trim($row['agency_name'])].'" ');
				while ($row = tep_db_fetch_array($sql)){
					tep_db_query('update `products` set agency_id="'.(int)$row['agency_id'].'" where products_id="'.(int)$products_id.'" ');
				}
			}
		}
	}
	/**
	 * 更新产品url
	 *
	 * @param unknown_type $products_id
	 */
	public function update_products_url($products_id){
		$sql = tep_db_query('update `products` set products_urlname=products_id where (products_urlname="" || products_urlname IS NULL ) and products_id="'.(int)$products_id.'" ');
		$row = tep_db_fetch_array($sql);

	}
	/**
	 * 更新产品对应的目的地（途经景点）
	 *
	 * @param unknown_type $products_id
	 */
	public function update_products_destination($products_id){
		$intr = array(
		27=>"波士顿",
		28=>"纽约",
		29=>"华盛顿",
		30=>"罗德岛",
		31=>"费城",
		32=>"芝加哥",
		33=>"康宁玻璃艺术中心",
		34=>"尼亚加拉瀑布",
		35=>"田纳西",
		36=>"包伟湖",
		37=>"西峡谷玻璃桥",
		38=>"大提顿国家公园",
		39=>"大峡谷国家公园",
		40=>"迪斯尼乐园",
		41=>"疯马巨石",
		42=>"拱门",
		43=>"好莱坞环球影城",
		44=>"赫氏古堡",
		45=>"胡佛水坝",
		46=>"黄石公园",
		47=>"旧金山",
		48=>"拉斯维加斯",
		49=>"洛杉矶",
		50=>"圣地亚哥海洋世界",
		51=>"太浩湖",
		52=>"优胜美地国家公园",
		53=>"总统巨石",
		54=>"盐湖城",
		55=>"丹佛",
		56=>"十七里黄金海岸",
		57=>"布莱斯峡谷",
		58=>"西雅图",
		59=>"波利尼西亚文化中心",	 //玻里尼西亚文化中心
		60=>"火山岛",
		61=>"茂宜岛",
		62=>"欧胡岛",
		63=>"珍珠港",
		64=>"夏威夷",
		66=>"洛矶山",
		67=>"温哥华",
		68=>"多伦多",
		69=>"渥太华",
		70=>"魁北克市",
		71=>"班芙",
		72=>"哥伦比亚冰川",
		73=>"北京",
		74=>"上海",
		75=>"西安",
		77=>"香港",
		78=>"长江三峡",
		79=>"杭州",
		80=>"比利时",
		82=>"法国",
		83=>"德国",
		84=>"意大利",
		85=>"卢森堡",
		86=>"荷兰",
		87=>"英国",
		88=>"捷克",
		89=>"瑞士",
		90=>"匈牙利",
		91=>"奥地利",
		95=>"凤凰城",
		96=>"广州",
		101=>"哈佛",
		102=>"耶鲁",
		103=>"西点军校",
		104=>"自由女神",
		105=>"赫氏朱古力城",
		106=>"巴尔的摩",
		107=>"伍德佰里直销工厂",
		108=>"澳门",
		109=>"满地可",
		110=>"主题公园",
		111=>"奥兰多",
		112=>"巴黎",
		113=>"凡尔赛",
		114=>"汉斯",
		115=>"罗马",
		116=>"维也纳",
		117=>"布达佩斯",
		118=>"米兰",
		119=>"科隆",
		120=>"卢森堡",
		121=>"法兰克福",
		122=>"琉森",
		124=>"芝加哥",
		126=>"昆明",
		127=>"桂林",
		128=>"那帕酒乡",
		132=>"迈阿密",
		133=>"哥伦比亚大学",
		134=>"硅谷",
		135=>"斯坦福大学",
		136=>"加利福尼亚大学",
		137=>"澳大利亚",
		138=>"悉尼",
		139=>"堪培拉",
		140=>"布里斯本",
		141=>"东京",
		142=>"箱根",
		143=>"京都",
		144=>"大阪",
		145=>"牛津",
		146=>"莎士比亚故居",
		147=>"剑桥大学",
		148=>"维多利亚",
		149=>"史坦利公园",
		150=>"秀票",
		151=>"尼亚加拉古要塞");
		$_num = 0;
		$not_vals = array();
		$sql = tep_db_query('SELECT introduction FROM products where products_id="'.(int)$products_id.'" ');
		$row = tep_db_fetch_array($sql);
		if(tep_not_null($row['introduction'])){
			$introductions = explode(',',$row['introduction']);
			foreach($introductions as $val){
				$sql = tep_db_query('SELECT regions_id, city_id, city FROM `tour_city` where city="'.$intr[(int)$val].'" ');
				if(tep_db_num_rows($sql)){
					while ($rows = tep_db_fetch_array($sql)){
						$check_sql = tep_db_query('SELECT products_id FROM `products_destination` where products_id="'.(int)$products_id.'" and city_id="'.(int)$rows['city_id'].'" ');
						if(!tep_db_num_rows($check_sql)){
							tep_db_query('INSERT INTO `products_destination` (products_id, city_id) VALUES ("'.(int)$products_id.'", "'.(int)$rows['city_id'].'");');
							tep_db_query('update products set regions_id="'.(int)$rows['regions_id'].'" where products_id="'.(int)$products_id.'" and regions_id<"1" ');	//更新产品所属区域
						}
					}
				}

			}
		}
	}
	/**
	 * 更新产品的code
	 * 产品的CODE公式是出发城市代码+供应商ID+"-"+产品ID
	 * @param unknown_type $products_id
	 */
	public function update_products_code($products_id){
		$sql = tep_db_query('SELECT departure_city_id, agency_id FROM `products` where products_id="'.(int)$products_id.'" ');
		$row = tep_db_fetch_array($sql);
		$departure_city_id = (int)$row['departure_city_id'];
		$agency_id = (int)$row['agency_id'];
		if((int)$departure_city_id && (int)$agency_id){
			$sql = tep_db_query('SELECT c.countries_iso_code_2, tc.city_code FROM `tour_city` tc, `regions` r, `countries` c where tc.city_id ="'.(int)$departure_city_id.'" and tc.regions_id=r.regions_id and r.countries_id=c.countries_id and tc.departure_city_status="1" group by tc.city_id ');
			$row = tep_db_fetch_array($sql);
			if(tep_not_null($row['city_code']) && tep_not_null($row['countries_iso_code_2'])){
				//$products_model =$row['countries_iso_code_2'].$row['city_code'].$agency_id; 不要国家代码了
				$products_model =$row['city_code'].$agency_id;
				$products_model .= '-'.(int)$products_id;
				tep_db_query('update products set products_model="'.$products_model.'" where products_id="'.(int)$products_id.'" and (products_model="" || products_model IS NULL ) ');
			}
		}
	}
	/**
	 * 自动更新产品供应商的团代号
	 *
	 * @param unknown_type $products_id
	 */
	public function update_products_provider_tour_code($products_id){
		$sql = tep_db_query('SELECT provider_tour_code, provider_tour_code_old, agency_id FROM `products` WHERE products_id ="'.(int)$products_id.'" ');
		$row = tep_db_fetch_array($sql);
		if(!tep_not_null($row['provider_tour_code']) && tep_not_null($row['provider_tour_code_old']) && (int)$row['agency_id'] ){
			$agency_code = tep_get_agency_code((int)$row['agency_id']);
			if(tep_not_null($agency_code)){
				tep_db_query('update products set provider_tour_code="'.$agency_code.'-'.$row['provider_tour_code_old'].'" where products_id="'.(int)$products_id.'" ');
			}
		}
	}
	/**
	 * 列表纯线路的ID、型号、出发城市、结束城市、途经城市
	 *
	 * @param unknown_type $products_id
	 */
	/*public function get_products_info(){
	$data = array();
	$sql = tep_db_query('SELECT p.products_id, p.products_model, p.departure_city_id, p.departure_end_city_id FROM `products` p WHERE 1  ');
	$i=0;
	while($rows = tep_db_fetch_array($sql)){
	$data[$i] = $rows;
	//出发城市
	$_cf_sql = tep_db_query('select city where city_id in('.$rows['departure_city_id'].') ');
	$i++;
	}

	}*/
}

/**
 * 结伴同游类
 *
 */
class travelCompanion{
	/**
	 * 自动更新结伴同旅游的目录ID，只能更新一次（aBen导数据给我的时候更新，其它时刻不要更新）
	 *
	 * @param unknown_type $products_id
	 */
	public static function update_categories_id(){
		//老站的目录ID
		//ID	category
		$old_cates = array(
		1=>99999,	//"美国",
		2=>25,	//"美东",
		3=>24,	//"美西",
		4=>33,	//"夏威夷",
		5=>34,	//"佛罗里达",
		6=>54,	//"加拿大",
		7=>157,	//"欧洲+亚洲",
		17=>54,	//"加拿大",
		18=>193,	//"亚洲",
		21=>157,	//"欧洲",
		28=>186,	//"大洋洲",
		29=>29,	//"洛杉矶",
		30=>32, //"拉斯维加斯",
		32=>35,	//"黄石公园",
		33=>30	//"旧金山"
		);
		$check_sql = tep_db_query('SELECT categories_id FROM `travel_companion` WHERE categories_id="99999" Limit 1 ;');
		$check_row = tep_db_fetch_array($check_sql);
		if((int)$check_row['categories_id']){
			die("已经运行了一次不能再重复运行！");
		}else{
			$sql = tep_db_query('SELECT categories_id,t_companion_id,products_id FROM `travel_companion` ');
			while($rows = tep_db_fetch_array($sql)){
				if((int)$rows['categories_id']){
					tep_db_query('update travel_companion SET categories_id="'.$old_cates[$rows['categories_id']].'" WHERE categories_id="'.$rows['categories_id'].'"');
					self::update_min_categories($rows['products_id'],$rows['t_companion_id']);
				}
			}

		}
	}
	/**
	 * 根据产品ID更新所属目的地
	 * @param string $products_id 产品的ID
	 * @param int $t_companion_id 结伴同游贴ID
	 *修改字段类型
	 *ALTER TABLE `travel_companion` CHANGE `categories_id` `categories_id` VARCHAR( 100 ) NOT NULL DEFAULT '0' 
	 */
	private function update_min_categories($products_id,$t_companion_id) {
		if (tep_not_null($products_id)) {
			$min_categories = tep_db_query("select categories_id from products_to_categories where products_id = '" . $products_id . "'");
			$arr = array();
			while($row = tep_db_fetch_array($min_categories)) {
				$arr[] = $row['categories_id'];
			}
			if (tep_not_null($arr)){
				$temp = join(',',$arr);
				tep_db_query("update travel_companion SET categories_id ='" . $temp . "' where t_companion_id='" . (int)$t_companion_id . "'");
			}
		}
	}

	/* *
	* 静态方法 更新当前结伴贴的所属子栏目
	* /
	/ *public static function update_min(){
	$sql = tep_db_query('SELECT categories_id,t_companion_id,products_id FROM `travel_companion` ');
	while($rows = tep_db_fetch_array($sql)){
	self::update_min_categories($rows['products_id'],$rows['t_companion_id']);
	}
	}*/

}
//用户咨询


/**
 * 老客户积分赠送
 * 1. 在旧版网站上注册但未消费的老客户，原来其手中的coupon将直接转换成200积分。
 * 2. 曾在旧版网站上消费过的老用户，走四方旅游网将根据其过往的消费记录金额的累加，直接给予1:1.5的对应积分（1美元=>1.5积分）。
 * @author howard
 */
function old_customers_handsel_points(){
	$added_points = 0;
	if($_GET['action']=='old_customers_handsel_points'){
		$db = new mysqli(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, 'old_usitrip');
		$new_customers_sql = tep_db_query('SELECT customers_id, customers_email_address FROM `customers` WHERE customers_id < 60000 ORDER BY customers_id ASC ');
		while($new_rows = tep_db_fetch_array($new_customers_sql)){
			$check_sql = tep_db_query('SELECT customer_id as id FROM '.TABLE_CUSTOMERS_POINTS_PENDING.' WHERE customer_id="'.$new_rows['customers_id'].'" and points_comment="TEXT_OLD_SITE_WELCOME_POINTS_COMMENT" ');
			$check_row = tep_db_fetch_array($check_sql);
			if(!(int)$check_row['id']){
				$result = $db->query('SELECT `ID`,`mail`,`total_comsume`,`consume_count` FROM `user` WHERE `ID`="'.$new_rows['customers_id'].'" ORDER BY `ID` ');
				if((int)$result->num_rows){
					while ($results = $result->fetch_object()){
						if($new_rows['customers_id'] == $results->ID && strtolower(trim($new_rows['customers_email_address'])) == strtolower(trim($results->mail))){
							if((int)$results->total_comsume > 0){	//有消费过的积分 = 消费金额*1.5
								$add_points = max(200, (round(($results->total_comsume * 1.5), 0)));	//消费积分不足200的都直接给200
							}else{	//无消费过的用户给200积分
								$add_points = 200;
							}
							$added_points += $add_points;

							$sql_data_array = array('customer_id' => (int)$new_rows['customers_id'],
							'points_pending' => $add_points,
							'date_added' => 'now()',
							'points_comment' => 'TEXT_OLD_SITE_WELCOME_POINTS_COMMENT',
							'points_type' => 'RG',
							'points_status' => 2);

							tep_db_perform(TABLE_CUSTOMERS_POINTS_PENDING, $sql_data_array);
							tep_auto_fix_customers_points((int)$new_rows['customers_id']);	//自动校正用户积分

						}
					}
				}
			}

		}
		echo '积分赠送：'.$added_points;
		exit;
	}
}
old_customers_handsel_points();

/**
 * 老客户积分修正版20121030
 * 要求：在旧版网站上最后一次消费过的老用户，走四方旅游网将根据最后一次的消费记录金额，直接给予1:1.5的对应积分(1美元=>1.5积分)， 在新站上的消费无最低消费额要求，可按新站兑换原则全部兑换。
 */
function old_customers_handsel_points_update(){
	if(!isset($_GET['action']) || $_GET['action']!='old_customers_handsel_points_update'){ return false; }
	$sql = tep_db_query('SELECT customers_id, old_site_total_comsume_last FROM `customers` WHERE customers_id < 60000 ');
	while ($rows = tep_db_fetch_array($sql)){
		$p = max(200, (round(($rows['old_site_total_comsume_last']*1.5),0)));
		tep_db_query('update customers_points_pending set points_pending="'.$p.'" where customer_id="'.$rows['customers_id'].'" AND points_comment="TEXT_OLD_SITE_WELCOME_POINTS_COMMENT" ');
		tep_auto_fix_customers_points((int)$rows['customers_id']);	//自动校正用户积分
	}
}
old_customers_handsel_points_update();

/**
 * 更新所有产品对应的目的地ID
 */
function update_products_destination_a(){
	$error = false;
	$sql = tep_db_query('SELECT * FROM aaaaaaaa WHERE 1 ');
	while ($rows=tep_db_fetch_array($sql)) {
		if(tep_not_null($rows['jdname'])){
			tep_db_query('DELETE FROM `products_destination` WHERE `products_id` = "'.(int)$rows['pid'].'" ');
			$array = explode(',', $rows['jdname']);
			foreach ((array)$array as $cname){
				$cname = trim($cname);
				if($cname!=""){
					$tc_sql = tep_db_query('SELECT city_id FROM `tour_city` WHERE city ="'.$cname.'" ');
					$rows_count = tep_db_num_rows($tc_sql);
					if($rows_count == 1){
						$row = tep_db_fetch_array($tc_sql);
						tep_db_query('INSERT INTO `products_destination` set `products_id`="'.(int)$rows['pid'].'" , `city_id`="'.(int)$row['city_id'].'" ');
					}else{
						$error[$cname] = $rows_count;
					}
				}
			}
		}
	}
	if($error!==false){
		echo '以下数据异常：'.'<br>'; 
		echo '<pre>';
		print_r($error);
		echo '</pre>';
	}
	echo '更新途经景点完毕！';
}

/**
 * 产品更新动作
 *
 */
class productsUpdateAction{
	/**
	 * 更新产品的GP值，即毛利率
	 */
	public static function updateGP($pid){
		$pid = (int)$pid;
		$sql = tep_db_query('SELECT * FROM `products` where products_id="'.$pid.'" ');
		$row = tep_db_fetch_array($sql);
		$gp = 0;
		$array = array('products_single','products_single_pu','products_double','products_triple','products_quadr');
		foreach($array as $val){
			if((int)$row[$val] > 0 && (int)$row[$val.'_cost']>0){
				$gp = self::gpFormula($row[$val], $row[$val.'_cost']);
				break;
			}
		}
		$products_margin = ($gp*100);
		if(is_numeric($products_margin)){
			tep_db_query("UPDATE products set products_margin={$products_margin} WHERE  products_id={$pid}");
		}
		//echo (int)$pid.','.$row['products_model'].','.$products_margin."\n";
		return 1;
	}
	/**
	 * 毛利率计算公式
	 * @param float $Retail	卖价，不能小于或等于0
	 * @param float $cost 成本价
	 * @return float number 返回的值是小于1的小数
	 */
	private static function gpFormula($Retail, $cost){
		$n=0;
		if($Retail>0){
			$n = ($Retail-$cost)/$Retail;
			$n = round($n,4);
		}
		return $n;
	}
}




//===============================================================================================================================================================================================

$action = strtolower((string)$_GET['action']);
/* if ($action == 'test') {
travelCompanion::update_min();
exit();
} */
if ($action == 'update_categories_id') {
	//更新结伴同游所属目录
	travelCompanion::update_categories_id();
}elseif(isset($_GET['action'])) {

	$p_sql = tep_db_query('SELECT products_id FROM `products` ORDER BY `products_id` ASC');
	while(true == ($p_rows = tep_db_fetch_array($p_sql))){
		switch ($action) {
			case 'update_date_info':
				getOldProducts::update_date_info((int)$p_rows['products_id']);
				break;
			case 'update_categories':
				getOldProducts::update_categories((int)$p_rows['products_id']);
				break;
			case 'update_products_agency':
				getOldProducts::update_products_agency((int)$p_rows['products_id']);
				break;
			case 'update_products_city':
				getOldProducts::update_products_city((int)$p_rows['products_id']);
				break;
			case 'update_products_destination':
				//getOldProducts::update_products_destination((int)$p_rows['products_id']);
				break;
			case 'update_products_code':
				getOldProducts::update_products_code((int)$p_rows['products_id']);
				break;
			case 'update_products_provider_tour_code':
				getOldProducts::update_products_provider_tour_code((int)$p_rows['products_id']);
				break;
			case 'updategp':
				productsUpdateAction::updateGP((int)$p_rows['products_id']);
				break;
		}
	}
}
//getOldProducts::update_products_code('1692');
//getOldProducts::update_products_code('1698');
//getOldProducts::update_products_code('1708');
//getOldProducts::update_products_code('1710');
//getOldProducts::update_products_code('1648');

if($_GET['action']=='update_products_destination_a'){
	update_products_destination_a();
}

echo 'done';
exit;
?>
