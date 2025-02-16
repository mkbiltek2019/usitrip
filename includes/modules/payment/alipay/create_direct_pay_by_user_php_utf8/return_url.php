<?php
/* * 
 * 功能：支付宝页面跳转同步通知页面
 * 版本：3.2
 * 日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************页面功能说明*************************
 * 该页面可在本机电脑测试
 * 可放入HTML等美化页面的代码、商户业务逻辑程序代码
 * 该页面可以使用PHP开发工具调试，也可以使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyReturn
 
 * TRADE_FINISHED(表示交易已经成功结束，为普通即时到帐的交易状态成功标识);
 * TRADE_SUCCESS(表示交易已经成功结束，为高级即时到帐的交易状态成功标识);
 */

require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");

?>
<!DOCTYPE HTML>
<html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
//计算得出通知验证结果
$alipayNotify = new AlipayNotify($aliapy_config);
$verify_result = $alipayNotify->verifyReturn();
if($verify_result) {//验证成功
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//请在这里加上商户的业务逻辑程序代码
	
	//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
    //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
    $out_trade_no	= $_GET['out_trade_no'];	//获取订单号
    $trade_no		= $_GET['trade_no'];		//获取支付宝交易号
    $total_fee		= $_GET['total_fee'];		//获取总价格
	$notify_time    = $_GET['notify_time'];
	$buyer_email    = $_GET['buyer_email'];

	$notify_type    = $_GET['notify_type'].'（同步通知）';


    if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
		//判断该笔订单是否在商户网站中已经做过处理（可参考“集成教程”中“3.4返回数据处理”）
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//如果有做过处理，不执行商户的业务程序
			
			//记录成功结账信息，更新订单信息{
			$orders_id = (int)$out_trade_no;
			//返回页
			$return_url_page = HTTP_SERVER.'/account_history_info.php?order_id='.(int)$orders_id.'&need_send_payment_success_email=1&success_payment=alipay_direct_pay';
			
				$usa_value = tep_cny_to_usd($orders_id, $total_fee);
				$payment_method = iconv('utf-8','gb2312','支付宝');
				//$comment = print_r($_GET, true);
				$comment = "\n";
				$comment .= '人民币：'.$total_fee."\n";
				$comment .= '交易时间：'.$notify_time."\n";
				$comment .= '支付宝交易号：'.$trade_no."\n";
				$comment .= '订单号：'.$out_trade_no."\n";
				$comment .= '付款人手机或电子邮箱：'.$buyer_email."\n";
				$comment .= '通知类型：'.$notify_type."\n".__FILE__;
				$comment = iconv('utf-8','gb2312',$comment);
				$update_action = tep_payment_success_update($orders_id, $usa_value, $payment_method, $comment, 96, $out_trade_no);
				
				//结伴同游信息{
				if($update_action==true && isset($_GET['extra_common_param'])){
					$travelCompanionPayStr = $_GET['extra_common_param'];
					$travelCompanionPay = json_decode($travelCompanionPayStr,true);
					
					$orders_travel_companion_status = '1';
					if(number_format($usa_value,2,'.','') == number_format($travelCompanionPay['i_need_pay'],2,'.','')){
						$orders_travel_companion_status = '2';
					}
					
					//按份均分
					$averge_usa_value = $usa_value / (max(1, sizeof(explode(',',$travelCompanionPay['orders_travel_companion_ids']))));
					$averge_usa_value = number_format($averge_usa_value, 2,'.','');
					$sql_date_array = array(
											'last_modified' => date('Y-m-d H:i:s'),
											'orders_travel_companion_status' => $orders_travel_companion_status,	//2已付款
											'payment' => 'alipay_direct_pay',
											'payment_name' => $payment_method, 
											'payment_customers_id' => $travelCompanionPay['customer_id']
											);
					tep_db_perform('orders_travel_companion', $sql_date_array, 'update',' orders_id="'.(int)$orders_id.'" AND orders_travel_companion_id in('.$travelCompanionPay['orders_travel_companion_ids'].') ');
					tep_db_query('update orders_travel_companion set payment_description = CONCAT(`payment_description`,"\n '.tep_db_input($comment).'"), paid = paid+'.$averge_usa_value.' where orders_id="'.(int)$orders_id.'" AND orders_travel_companion_id in('.$travelCompanionPay['orders_travel_companion_ids'].') ');
					// 返回订单页面
					$return_url_page = HTTP_SERVER.'/orders_travel_companion_info.php?order_id='.(int)$orders_id;
					//print_r($travelCompanionPay);
				}
				//结伴同游信息}
			header('Location: '.$return_url_page);
			//记录成功结账信息，更新订单信息}
    }
    else {
      echo "trade_status=".$_GET['trade_status'];
    }
		
	echo "验证成功<br />";
	echo "trade_no=".$trade_no;
	
	//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
else {
    //验证失败
    //如要调试，请看alipay_notify.php页面的verifyReturn函数，比对sign和mysign的值是否相等，或者检查$responseTxt有没有返回true
    echo "验证失败";
}
?>
        <title>支付宝即时到帐接口</title>
	</head>
    <body>
    </body>
</html>