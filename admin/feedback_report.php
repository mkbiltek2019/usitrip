<?php
/*
  $Id: admin_members.php,v 1.2 2004/03/12 18:33:12 ccwjr Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/
  header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );
  header( "Cache-Control: no-cache, must-revalidate" );
  header( "Pragma: no-cache" );

require('includes/application_top.php');
 $sql = tep_db_query('select count(*) as total from feedback ');
 $rows_total = tep_db_result($sql,"0","total");
if($rows_total=='0'){
	die('暂无客户查询反馈记录！');
}
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=big5">
<title>调查结果！</title>
</head>
<body>
<table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#B1C3D9">
  <tr>
    <td valign="top" bgcolor="#FFFFFF">
	
	<?php
	$age_sql = tep_db_query('select tours_age from feedback ');
	$age_18 = 0;
	$age_18_30 = 0;
	$age_31_45 = 0;
	$age_45_60 = 0;
	$age_60 = 0;
	while($age_rows = tep_db_fetch_array($age_sql)){
		if($age_rows['tours_age']=='18'){$age_18++;}
		if($age_rows['tours_age']=='18-30'){$age_18_30++;}
		if($age_rows['tours_age']=='31-45'){$age_31_45++;}
		if($age_rows['tours_age']=='45-60'){$age_45_60++;}
		if($age_rows['tours_age']=='60'){$age_60++;}
	}
	
	$max_val_age = max($age_18,$age_18_30,$age_31_45,$age_45_60,$age_60);
	
	$age_18_bfb = ($age_18/$rows_total);
	$age_18_h = intval(200 * $age_18_bfb);
	$age_img_18_name = 'report_b.gif';
	if($age_18==$max_val_age){
		$age_img_18_name = 'report_r.gif';
	}
	
	$age_18_30_bfb = ($age_18_30/$rows_total);
	$age_18_30_h = intval(200 * $age_18_30_bfb);
	$age_img_18_30_name = 'report_b.gif';
	if($age_18_30==$max_val_age){
		$age_img_18_30_name = 'report_r.gif';
	}
	
	$age_31_45_bfb = ($age_31_45/$rows_total);
	$age_31_45_h = intval(200 * $age_31_45_bfb);
	$age_img_31_45_name = 'report_b.gif';
	if($age_31_45==$max_val_age){
		$age_img_31_45_name = 'report_r.gif';
	}
	
	$age_45_60_bfb = ($age_45_60/$rows_total);
	$age_45_60_h = intval(200 * $age_45_60_bfb);
	$age_img_45_60_name = 'report_b.gif';
	if($age_45_60==$max_val_age){
		$age_img_45_60_name = 'report_r.gif';
	}
	
	$age_60_bfb = ($age_60/$rows_total);
	$age_60_h = intval(200 * $age_60_bfb);
	$age_img_60_name = 'report_b.gif';
	if($age_60==$max_val_age){
		$age_img_60_name = 'report_r.gif';
	}
	?>
	<table border="0" cellspacing="5" cellpadding="0">
      <tr>
        <td height="25" colspan="5"><strong>1、您的年龄</strong></td>
        </tr>
      <tr>
        <td height="200" align="left" valign="bottom">
		<?php echo $age_18 ?><br>
          <img src="images/icons/<?php echo $age_img_18_name ?>" width="20" height="<?php echo $age_18_h?>"></td>
        <td align="left" valign="bottom">
		<?php echo $age_18_30 ?><br>
		<img src="images/icons/<?php echo $age_img_18_30_name ?>" width="20" height="<?php echo $age_18_30_h?>"></td>
        <td align="left" valign="bottom">
		<?php echo $age_31_45 ?><br>
		<img src="images/icons/<?php echo $age_img_31_45_name ?>" width="20" height="<?php echo $age_31_45_h?>"></td>
        <td align="left" valign="bottom">
		<?php echo $age_45_60 ?><br>
		<img src="images/icons/<?php echo $age_img_45_60_name ?>" width="20" height="<?php echo $age_45_60_h?>"></td>
        <td align="left" valign="bottom">
		<?php echo $age_60 ?><br>
		<img src="images/icons/<?php echo $age_img_60_name ?>" width="20" height="<?php echo $age_60_h?>"></td>
      </tr>
      <tr>
        <td align="left">18岁以下</td>
        <td align="left">18-30</td>
        <td align="left">31-45</td>
        <td align="left">45-60</td>
        <td align="left">60以上</td>
      </tr>
    </table></td>
    <td valign="top" bgcolor="#FFFFFF">
	
	<?php
	$gender_sql = tep_db_query('select tours_gender from feedback ');
	$gender_f = 0;
	$gender_m = 0;
	while($gender_rows = tep_db_fetch_array($gender_sql)){
		if($gender_rows['tours_gender']=='f'){$gender_f++;}
		if($gender_rows['tours_gender']=='m'){$gender_m++;}
	}
	
	$max_val_gender = max($gender_f,$gender_m);
	
	$gender_f_bfb = ($gender_f/$rows_total);
	$gender_f_h = intval(200 * $gender_f_bfb);
	$gender_img_f_name = 'report_b.gif';
	if($gender_f==$max_val_gender){
		$gender_img_f_name = 'report_r.gif';
	}
	
	$gender_m_bfb = ($gender_m/$rows_total);
	$gender_m_h = intval(200 * $gender_m_bfb);
	$gender_img_m_name = 'report_b.gif';
	if($gender_m==$max_val_gender){
		$gender_img_m_name = 'report_r.gif';
	}
	
	?>
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
      <tr>
        <td height="25" colspan="2"><strong>2、您的性别</strong></td>
        </tr>
      <tr>
        <td height="200" align="left" valign="bottom">
		<?php echo $gender_m ?><br>
          <img src="images/icons/<?php echo $gender_img_m_name ?>" width="20" height="<?php echo $gender_m_h?>"></td>
        <td align="left" valign="bottom">
		<?php echo $gender_f ?><br>
		<img src="images/icons/<?php echo $gender_img_f_name ?>" width="20" height="<?php echo $gender_f_h?>"></td>
        </tr>
      <tr>
        <td align="left">男</td>
        <td align="left">女</td>
      </tr>
    </table></td>
    <td valign="top" bgcolor="#FFFFFF">
	<?php
	$job_sql = tep_db_query('select tours_job from feedback ');
	
	$job_array = array(0=>'自由职业',1=>'互联网',2=>'IT',3=>'管理',4=>'学生',5=>'机械',6=>'化工',7=>'能源',8=>'医疗',9=>'教育出版',10=>'其他');
	foreach($job_array as $key => $val){
		$job_[$key] = 0;	//var
	}
	
	while($job_rows = tep_db_fetch_array($job_sql)){
		foreach($job_array as $key => $val ){
			if($job_rows['tours_job']==$key){$job_[$key]++;}
		}
	}
	
	$max_val_job = max($job_[0],$job_[1],$job_[2],$job_[3],$job_[4],$job_[5],$job_[6],$job_[7],$job_[8],$job_[9],$job_[10]);
	
	foreach($job_array as $key => $val ){
		$job_bfb_[$key] = ($job_[$key]/$rows_total);
		$job_h_[$key] = intval(200 * $job_bfb_[$key]);
		$job_img_name_[$key] = 'report_b.gif';
		if($job_[$key]==$max_val_job){
			$job_img_name_[$key] = 'report_r.gif';
			
		}
	}
	
	?>	
	
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
      <tr>
        <td height="25" colspan="2"><strong>3、您的职业</strong></td>
        </tr>
      <tr>
    
	<?php foreach($job_array as $key => $val ){?>   
		<td height="200" align="left" valign="bottom">
		<?php echo $job_[$key] ?><br>
          <img src="images/icons/<?php echo $job_img_name_[$key] ?>" width="20" height="<?php echo $job_h_[$key]?>"></td>
	<?php }?>
        </tr>
      <tr>
	
	<?php foreach($job_array as $key => $val ){?>   
	    <td align="left"><?php echo $val?></td>
	<?php }?>
      </tr>
    </table>	</td>
  </tr>
  <tr>
    <td valign="top" bgcolor="#FFFFFF">
	<?php
	$like_sql = tep_db_query('select tours_like from feedback ');
	
	$like_array = array(0=>'自由行',1=>'自驾游',2=>'跟团',3=>'直升机游',4=>'飞机游',5=>'游轮',6=>'城市游',7=>'自然景观',8=>'其他');
	foreach($like_array as $key => $val){
		$like_[$key] = 0;	//var
	}
	
	while($like_rows = tep_db_fetch_array($like_sql)){
		foreach($like_array as $key => $val ){
			if($like_rows['tours_like']==$key){$like_[$key]++;}
		}
	}
	
	$max_val_like = max($like_[0],$like_[1],$like_[2],$like_[3],$like_[4],$like_[5],$like_[6],$like_[7],$like_[8]);
	
	foreach($like_array as $key => $val ){
		$like_bfb_[$key] = ($like_[$key]/$rows_total);
		$like_h_[$key] = intval(200 * $like_bfb_[$key]);
		$like_img_name_[$key] = 'report_b.gif';
		if($like_[$key]==$max_val_like){
			$like_img_name_[$key] = 'report_r.gif';
			
		}
	}
	
	?>	
	
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
      <tr>
        <td height="25" colspan="2"><strong>4、您的旅游爱好</strong></td>
        </tr>
      <tr>
    
	<?php foreach($like_array as $key => $val ){?>   
		<td height="200" align="left" valign="bottom">
		<?php echo $like_[$key] ?><br>
          <img src="images/icons/<?php echo $like_img_name_[$key] ?>" width="20" height="<?php echo $like_h_[$key]?>"></td>
	<?php }?>
        </tr>
      <tr>
	
	<?php foreach($like_array as $key => $val ){?>   
	    <td align="left"><?php echo $val?></td>
	<?php }?>
      </tr>
    </table>	</td>
    <td valign="top" bgcolor="#FFFFFF">
	<?php
	$from_sql = tep_db_query('select tours_from from feedback ');
	
	$from_array = array(0=>'google',1=>'baidu',2=>'yahoo',3=>'bbs',4=>'friend',5=>'other');
	foreach($from_array as $key => $val){
		$from_[$key] = 0;	//var
	}
	
	while($from_rows = tep_db_fetch_array($from_sql)){
		foreach($from_array as $key => $val ){
			if($from_rows['tours_from']==$val){$from_[$key]++;}
		}
	}
	
	$max_val_from = max($from_[0],$from_[1],$from_[2],$from_[3],$from_[4],$from_[5]);
	
	foreach($from_array as $key => $val ){
		$from_bfb_[$key] = ($from_[$key]/$rows_total);
		$from_h_[$key] = intval(200 * $from_bfb_[$key]);
		$from_img_name_[$key] = 'report_b.gif';
		if($from_[$key]==$max_val_from){
			$from_img_name_[$key] = 'report_r.gif';
			
		}
	}
	
	?>	
	
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
      <tr>
        <td height="25" colspan="2"><strong>5、您是通过何种方式知道本网站的</strong></td>
        </tr>
      <tr>
    
	<?php foreach($from_array as $key => $val ){?>   
		<td height="200" align="left" valign="bottom">
		<?php echo $from_[$key] ?><br>
          <img src="images/icons/<?php echo $from_img_name_[$key] ?>" width="20" height="<?php echo $from_h_[$key]?>"></td>
	<?php }?>
        </tr>
      <tr>
	
	<?php foreach($from_array as $key => $val ){?>   
	    <td align="left"><?php echo $val?></td>
	<?php }?>
      </tr>
    </table>	</td>
    <td valign="top" bgcolor="#FFFFFF">
	<?php
	$ServicesEval_sql = tep_db_query('select ServicesEval from feedback ');
	
	$ServicesEval_array = array(0=>'优秀',1=>'良好',2=>'中等',3=>'及格',4=>'差',5=>'很差');
	foreach($ServicesEval_array as $key => $val){
		$ServicesEval_[$key] = 0;	//var
	}
	
	while($ServicesEval_rows = tep_db_fetch_array($ServicesEval_sql)){
		foreach($ServicesEval_array as $key => $val ){
			if($ServicesEval_rows['ServicesEval']==$key){$ServicesEval_[$key]++;}
		}
	}
	
	$max_val_ServicesEval = max($ServicesEval_[0],$ServicesEval_[1],$ServicesEval_[2],$ServicesEval_[3],$ServicesEval_[4],$ServicesEval_[5]);
	
	foreach($ServicesEval_array as $key => $val ){
		$ServicesEval_bfb_[$key] = ($ServicesEval_[$key]/$rows_total);
		$ServicesEval_h_[$key] = intval(200 * $ServicesEval_bfb_[$key]);
		$ServicesEval_img_name_[$key] = 'report_b.gif';
		if($ServicesEval_[$key]==$max_val_ServicesEval){
			$ServicesEval_img_name_[$key] = 'report_r.gif';
			
		}
	}
	
	?>	
	
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
      <tr>
        <td height="25" colspan="2"><strong>6、您对本站服务的评价</strong></td>
        </tr>
      <tr>
    
	<?php foreach($ServicesEval_array as $key => $val ){?>   
		<td height="200" align="left" valign="bottom">
		<?php echo $ServicesEval_[$key] ?><br>
          <img src="images/icons/<?php echo $ServicesEval_img_name_[$key] ?>" width="20" height="<?php echo $ServicesEval_h_[$key]?>"></td>
	<?php }?>
        </tr>
      <tr>
	
	<?php foreach($ServicesEval_array as $key => $val ){?>   
	    <td align="left"><?php echo $val?></td>
	<?php }?>
      </tr>
    </table>	</td>
  </tr>
  <tr>
    <td valign="top" bgcolor="#FFFFFF">
	<?php
	$number_sql = tep_db_query('select tours_number from feedback ');
	
	$number_array = array(0=>'0',1=>'0-1',2=>'1',3=>'2',4=>'3',5=>'4',6=>'5');
	foreach($number_array as $key => $val){
		$number_[$key] = 0;	//var
	}
	
	while($number_rows = tep_db_fetch_array($number_sql)){
		foreach($number_array as $key => $val ){
			if($number_rows['tours_number']==$val){$number_[$key]++;}
		}
	}
	
	$max_val_number = max($number_[0],$number_[1],$number_[2],$number_[3],$number_[4],$number_[5],$number_[6]);
	
	foreach($number_array as $key => $val ){
		$number_bfb_[$key] = ($number_[$key]/$rows_total);
		$number_h_[$key] = intval(200 * $number_bfb_[$key]);
		$number_img_name_[$key] = 'report_b.gif';
		if($number_[$key]==$max_val_number){
			$number_img_name_[$key] = 'report_r.gif';
			
		}
	}
	
	?>	
	
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
      <tr>
        <td height="25" colspan="2"><strong>7、您最近几年平均每年出行几次？</strong></td>
        </tr>
      <tr>
    
	<?php foreach($number_array as $key => $val ){?>   
		<td height="200" align="left" valign="bottom">
		<?php echo $number_[$key] ?><br>
          <img src="images/icons/<?php echo $number_img_name_[$key] ?>" width="20" height="<?php echo $number_h_[$key]?>"></td>
	<?php }?>
        </tr>
      <tr>
	
	<?php foreach($number_array as $key => $val ){?>   
	    <td align="left"><?php echo $val?></td>
	<?php }?>
      </tr>
    </table>	</td>
    <td valign="top" bgcolor="#FFFFFF">
	<?php
	$add_server_sql = tep_db_query('select tours_add_server from feedback ');
	
	$add_server_array = array(0=>'机票',1=>'签证',2=>'租车',3=>'其他');
	foreach($add_server_array as $key => $val){
		$add_server_[$key] = 0;	//var
	}
	
	while($add_server_rows = tep_db_fetch_array($add_server_sql)){
		foreach($add_server_array as $key => $val ){
			if($add_server_rows['tours_add_server']==$key){$add_server_[$key]++;}
		}
	}
	
	$max_val_add_server = max($add_server_[0],$add_server_[1],$add_server_[2],$add_server_[3]);
	
	foreach($add_server_array as $key => $val ){
		$add_server_bfb_[$key] = ($add_server_[$key]/$rows_total);
		$add_server_h_[$key] = intval(200 * $add_server_bfb_[$key]);
		$add_server_img_name_[$key] = 'report_b.gif';
		if($add_server_[$key]==$max_val_add_server){
			$add_server_img_name_[$key] = 'report_r.gif';
			
		}
	}
	
	?>	
	
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
      <tr>
        <td height="25" colspan="2"><strong>8、您觉得我们需要增加哪些方面的业务/服务？</strong></td>
        </tr>
      <tr>
    
	<?php foreach($add_server_array as $key => $val ){?>   
		<td height="200" align="left" valign="bottom">
		<?php echo $add_server_[$key] ?><br>
          <img src="images/icons/<?php echo $add_server_img_name_[$key] ?>" width="20" height="<?php echo $add_server_h_[$key]?>"></td>
	<?php }?>
        </tr>
      <tr>
	
	<?php foreach($add_server_array as $key => $val ){?>   
	    <td align="left"><?php echo $val?></td>
	<?php }?>
      </tr>
    </table>	</td>
    <td valign="top" bgcolor="#FFFFFF">
	<?php
	$i_rec_sql = tep_db_query('select tours_i_rec from feedback ');
	
	$i_rec_array = array(0=>'没有',1=>'准备推荐',2=>'推荐了1位',3=>'推荐了2位',4=>'推荐了3位及以上');
	foreach($i_rec_array as $key => $val){
		$i_rec_[$key] = 0;	//var
	}
	
	while($i_rec_rows = tep_db_fetch_array($i_rec_sql)){
		foreach($i_rec_array as $key => $val ){
			if($i_rec_rows['tours_i_rec']==$key){$i_rec_[$key]++;}
		}
	}
	
	$max_val_i_rec = max($i_rec_[0],$i_rec_[1],$i_rec_[2],$i_rec_[3],$i_rec_[4]);
	
	foreach($i_rec_array as $key => $val ){
		$i_rec_bfb_[$key] = ($i_rec_[$key]/$rows_total);
		$i_rec_h_[$key] = intval(200 * $i_rec_bfb_[$key]);
		$i_rec_img_name_[$key] = 'report_b.gif';
		if($i_rec_[$key]==$max_val_i_rec){
			$i_rec_img_name_[$key] = 'report_r.gif';
			
		}
	}
	
	?>	
	
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
      <tr>
        <td height="25" colspan="2"><strong>9、您推荐本站产品给其他朋友没？</strong></td>
        </tr>
      <tr>
    
	<?php foreach($i_rec_array as $key => $val ){?>   
		<td height="200" align="left" valign="bottom">
		<?php echo $i_rec_[$key] ?><br>
          <img src="images/icons/<?php echo $i_rec_img_name_[$key] ?>" width="20" height="<?php echo $i_rec_h_[$key]?>"></td>
	<?php }?>
        </tr>
      <tr>
	
	<?php foreach($i_rec_array as $key => $val ){?>   
	    <td align="left"><?php echo $val?></td>
	<?php }?>
      </tr>
    </table>	</td>
  </tr>
  <tr>
    <td valign="top" bgcolor="#FFFFFF">
	<?php
	$consumer_sql = tep_db_query('select tours_consumer from feedback ');
	
	$consumer_array = array(0=>'$100以下',1=>'$100-$500',2=>'$500-$1000',3=>'$1000以上');
	foreach($consumer_array as $key => $val){
		$consumer_[$key] = 0;	//var
	}
	
	while($consumer_rows = tep_db_fetch_array($consumer_sql)){
		foreach($consumer_array as $key => $val ){
			if($consumer_rows['tours_consumer']==$key){$consumer_[$key]++;}
		}
	}
	
	$max_val_consumer = max($consumer_[0],$consumer_[1],$consumer_[2],$consumer_[3]);
	
	foreach($consumer_array as $key => $val ){
		$consumer_bfb_[$key] = ($consumer_[$key]/$rows_total);
		$consumer_h_[$key] = intval(200 * $consumer_bfb_[$key]);
		$consumer_img_name_[$key] = 'report_b.gif';
		if($consumer_[$key]==$max_val_consumer){
			$consumer_img_name_[$key] = 'report_r.gif';
			
		}
	}
	
	?>	
	
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
      <tr>
        <td height="25" colspan="2"><strong>10、您一般花费多少在一次旅游行程上呢？</strong></td>
        </tr>
      <tr>
    
	<?php foreach($consumer_array as $key => $val ){?>   
		<td height="200" align="left" valign="bottom">
		<?php echo $consumer_[$key] ?><br>
          <img src="images/icons/<?php echo $consumer_img_name_[$key] ?>" width="20" height="<?php echo $consumer_h_[$key]?>"></td>
	<?php }?>
	
        </tr>
      <tr>
	
	<?php foreach($consumer_array as $key => $val ){?>   
	    <td align="left"><?php echo $val?></td>
	<?php }?>

      </tr>
    </table>
	
	</td>
    <td valign="top" bgcolor="#FFFFFF">
	<?php
	$focus_sql = tep_db_query('select tours_focus from feedback ');
	
	$focus_array = array(0=>'酒店',1=>'行程',2=>'价格',3=>'服务',4=>'机票');
	foreach($focus_array as $key => $val){
		$focus_[$key] = 0;	//var
	}
	
	while($focus_rows = tep_db_fetch_array($focus_sql)){
		foreach($focus_array as $key => $val ){
			if($focus_rows['tours_focus']==$key){$focus_[$key]++;}
		}
	}
	
	$max_val_focus = max($focus_[0],$focus_[1],$focus_[2],$focus_[3],$focus_[4]);
	
	foreach($focus_array as $key => $val ){
		$focus_bfb_[$key] = ($focus_[$key]/$rows_total);
		$focus_h_[$key] = intval(200 * $focus_bfb_[$key]);
		$focus_img_name_[$key] = 'report_b.gif';
		if($focus_[$key]==$max_val_focus){
			$focus_img_name_[$key] = 'report_r.gif';
			
		}
	}
	
	?>	
	
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
      <tr>
        <td height="25" colspan="2"><strong>11、您在旅游过程中，更在乎下面中的哪一样呢？</strong></td>
        </tr>
      <tr>
    
	<?php foreach($focus_array as $key => $val ){?>   
		<td height="200" align="left" valign="bottom">
		<?php echo $focus_[$key] ?><br>
          <img src="images/icons/<?php echo $focus_img_name_[$key] ?>" width="20" height="<?php echo $focus_h_[$key]?>"></td>
	<?php }?>
	
        </tr>
      <tr>
	
	<?php foreach($focus_array as $key => $val ){?>   
	    <td align="left"><?php echo $val?></td>
	<?php }?>

      </tr>
    </table>
	
	</td>
    <td valign="top" bgcolor="#FFFFFF">
	<?php
	$tours_add_prod_sql = tep_db_query('select tours_add_prod from feedback_add_prod ');
	
	$tours_add_prod_array = array(0=>'签证常识',1=>'旅游常识',2=>'旅游信息',3=>'同行好友',4=>'更多图片',5=>'更多视频',6=>'开放评论',7=>'自由行',8=>'其他');
	foreach($tours_add_prod_array as $key => $val){
		$tours_add_prod_[$key] = 0;	//var
	}
	
	while($tours_add_prod_rows = tep_db_fetch_array($tours_add_prod_sql)){
		foreach($tours_add_prod_array as $key => $val ){
			if(($tours_add_prod_rows['tours_add_prod']-1)==$key){$tours_add_prod_[$key]++;}
		}
	}
	
	$max_val_tours_add_prod = max($tours_add_prod_[0],$tours_add_prod_[1],$tours_add_prod_[2],$tours_add_prod_[3],$tours_add_prod_[4],$tours_add_prod_[5],$tours_add_prod_[6],$tours_add_prod_[7],$tours_add_prod_[8]);
	
	foreach($tours_add_prod_array as $key => $val ){
		$tours_add_prod_bfb_[$key] = ($tours_add_prod_[$key]/$rows_total);
		$tours_add_prod_h_[$key] = intval(200 * $tours_add_prod_bfb_[$key]);
		$tours_add_prod_img_name_[$key] = 'report_b.gif';
		if($tours_add_prod_[$key]==$max_val_tours_add_prod){
			$tours_add_prod_img_name_[$key] = 'report_r.gif';
			
		}
	}
	
	?>	
	
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
      <tr>
        <td height="25" colspan="2"><strong>12、你希望本站除了专业的旅游产品外，还可以添加哪些方面内容呢？</strong></td>
        </tr>
      <tr>
    
	<?php foreach($tours_add_prod_array as $key => $val ){?>   
		<td height="200" align="left" valign="bottom">
		<?php echo $tours_add_prod_[$key] ?><br>
          <img src="images/icons/<?php echo $tours_add_prod_img_name_[$key] ?>" width="20" height="<?php echo $tours_add_prod_h_[$key]?>"></td>
	<?php }?>
	
        </tr>
      <tr>
	
	<?php foreach($tours_add_prod_array as $key => $val ){?>   
	    <td align="left"><?php echo $val?></td>
	<?php }?>

      </tr>
    </table>
	
	</td>
  </tr>
  <tr>
    <td valign="top" bgcolor="#FFFFFF">
	<?php
	$site_improve_sql = tep_db_query('select tours_site_improve from feedback ');
	
	$site_improve_array = array(0=>'页面布局',1=>'导航设计',2=>'搜索引擎',3=>'客服质量',4=>'其他');
	foreach($site_improve_array as $key => $val){
		$site_improve_[$key] = 0;	//var
	}
	
	while($site_improve_rows = tep_db_fetch_array($site_improve_sql)){
		foreach($site_improve_array as $key => $val ){
			if($site_improve_rows['tours_site_improve']==$key){$site_improve_[$key]++;}
		}
	}
	
	$max_val_site_improve = max($site_improve_[0],$site_improve_[1],$site_improve_[2],$site_improve_[3],$site_improve_[4]);
	
	foreach($site_improve_array as $key => $val ){
		$site_improve_bfb_[$key] = ($site_improve_[$key]/$rows_total);
		$site_improve_h_[$key] = intval(200 * $site_improve_bfb_[$key]);
		$site_improve_img_name_[$key] = 'report_b.gif';
		if($site_improve_[$key]==$max_val_site_improve){
			$site_improve_img_name_[$key] = 'report_r.gif';
			
		}
	}
	
	?>	
	
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
      <tr>
        <td height="25" colspan="2"><strong>13、您觉得本站需要在哪些方面进行改进？</strong></td>
        </tr>
      <tr>
    
	<?php foreach($site_improve_array as $key => $val ){?>   
		<td height="200" align="left" valign="bottom">
		<?php echo $site_improve_[$key] ?><br>
          <img src="images/icons/<?php echo $site_improve_img_name_[$key] ?>" width="20" height="<?php echo $site_improve_h_[$key]?>"></td>
	<?php }?>
	
        </tr>
      <tr>
	
	<?php foreach($site_improve_array as $key => $val ){?>   
	    <td align="left"><?php echo $val?></td>
	<?php }?>

      </tr>
    </table>
	
	</td>
    <td valign="middle" bgcolor="#FFFFFF">
<?php
$sql = tep_db_query('select count(*) as total from customers ');
$rows_cus_total = tep_db_result($sql,"0","total");
$rep_l = (intval(($rows_total/$rows_cus_total) * 10000) / 10000 ) * 100;
?>

		<p><strong><?php echo "到目前为止，共收到老客户调查反馈问卷 ".$rows_total." 份！";?></strong></p>
		<p><strong><?php echo "共发出客户邮件 ".$rows_cus_total." 份！";?></strong></p>
		<p><strong><?php echo "反馈率为 ".$rep_l."%。";?></strong></p>
	</td>
    <td valign="top" bgcolor="#FFFFFF">&nbsp;</td>
  </tr>
</table>
</body>
</html>
