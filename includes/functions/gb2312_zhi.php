<?php
//<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
//简繁体不能互转的文字
$sheng_pi_zhi_gb2312 = array();

$txet_gb2312 = iconv('utf-8','gb2312'.'//IGNORE','镕,頔,珪,玥,囧,犇,猋,骉,麤,毳,掱,垚,煊,烜,煐,烓,焺,炜,烓,燚,焜,瑛,璟,琀,玮,瑢,珄,瑱,琤,玽,玭,玚,琨,嫚,媖,婻,嬛,翀,婋,翙,翯,珝,翾,昫,昉,晞,昍,暔,暎,晢,旸,晹,甠,暒,眚,凊,湜,浛,汧,沄,湦,沕,陹,祎,竔,琞,屾,奡,劼,锳,骎,寗,郬,虓,鹍,靘,飏,誩,喆,臸,棽,顕,峣,昇,莜,燊');
$txet_gb2312_array = explode(',',$txet_gb2312);

//只有字符中不存在以下文字才执行替换，否则不可替换，绝对不可以。
$txet_can_not_replace_gb = iconv('utf-8','gb2312'.'//IGNORE','镕,蕸,珪,則,囧,犇,欣,蹻,麤,胥,掱,垚,盡,烜,煐,烓,焺,動,烓,燚,焜,踕,茂,斫,誺,瑢,哂,瑱,枴,哉,南,冑,踑,嫚,媖,婻,嬛,翀,婋,翙,麋,珝,嚮,昫,昉,晞,昍,暔,暎,晢,旸,晹,宰,暒,艜,凊,湜,浛,汧,沄,湦,沕,陹,睏,綑,柵,屾,奡,劼,噦,轒,寗,脯,虓,鹍,骻,飏,惀,喆,魔,棽,顕,峣,昇,溫,燊');
$txet_can_not_replace_gb_array = explode(',',$txet_can_not_replace_gb);


$txet_code_gb2312 = '&#38229;,&#38932;,&#29674;,&#29605;,&#22247;,&#29319;,&#29451;,&#39561;,&#40612;,&#27635;,&#25521;,&#22426;,&#29002;,&#28892;,&#29008;,&#28883;,&#28986;,&#28828;,&#28883;,&#29146;,&#28956;,&#29787;,&#29855;,&#29696;,&#29614;,&#29794;,&#29636;,&#29809;,&#29732;,&#29629;,&#29613;,&#29594;,&#29736;,&#23258;,&#23190;,&#23163;,&#23323;,&#32704;,&#23115;,&#32729;,&#32751;,&#29661;,&#32766;,&#26155;,&#26121;,&#26206;,&#26125;,&#26260;,&#26254;,&#26210;,&#26104;,&#26233;,&#29984;,&#26258;,&#30490;,&#20938;,&#28252;,&#27995;,&#27751;,&#27780;,&#28262;,&#27797;,&#38521;,&#31054;,&#31444;,&#29726;,&#23678;,&#22881;,&#21180;,&#38195;,&#39566;,&#23511;,&#37100;,&#34387;,&#40525;,&#38744;,&#39119;,&#35497;,&#21894;,&#33272;,&#26877;,&#38997;,&#23779;,&#26119;,&#33692;,&#29130;';
$txet_code_gb2312_array = explode(',',$txet_code_gb2312);

//取消替换
$txet_gb2312_array = array();
$txet_code_gb2312_array = array();

if(count($txet_gb2312_array) != count($txet_code_gb2312_array)){ echo 'string doce array error!'; exit; }


for($i = 0; $i <count($txet_gb2312_array); $i++){
	$sheng_pi_zhi_gb2312[$i] = array($txet_gb2312_array[$i],$txet_code_gb2312_array[$i]);
}

function special_string_replace_for_gb2312($str='',$action='0'){
	global $sheng_pi_zhi_gb2312,$txet_can_not_replace_gb_array;
	if($str==''){
		return $str;
	}
	$p = array();
	$repl = array();
	$replace_action = false;
	for($i=0; $i<count($sheng_pi_zhi_gb2312); $i++){
		$p = $sheng_pi_zhi_gb2312[$i][0];
		$repl = $sheng_pi_zhi_gb2312[$i][1];
		if(!preg_match('/'.$txet_can_not_replace_gb_array[$i].'/',$str)){
			$str = str_replace($p, $repl, $str);
		}
	}

	return $str;
}
?>