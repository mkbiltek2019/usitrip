<?php

/* *
 * 配置文件
 * 版本：3.2
 * 日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
	
 * 提示：如何获取安全校验码和合作身份者id
 * 1.用您的签约支付宝账号登录支付宝网站(www.alipay.com)
 * 2.点击“商家服务”(https://b.alipay.com/order/myorder.htm)
 * 3.点击“查询合作者身份(pid)”、“查询安全校验码(key)”
	
 * 安全校验码查看时，输入支付密码后，页面呈灰色的现象，怎么办？
 * 解决方法：
 * 1、检查浏览器配置，不让浏览器做弹框屏蔽设置
 * 2、更换浏览器或电脑，重新登录查询。
 */
// Howard added { 
// set timezone
ini_set('date.timezone','UTC-07:00');
// set the level of error reporting
ini_set('display_errors', '1');
error_reporting(E_ALL & ~E_NOTICE);

header("Content-type: text/html; charset=utf-8");
define('INCLUDES_DIR',preg_replace('@/includes/.*@','',dirname(__FILE__))."/includes/");
if(file_exists(INCLUDES_DIR."configure_local.php")){
	require_once(INCLUDES_DIR."configure_local.php");
}else{
	require_once(INCLUDES_DIR."configure.php");
}

require_once(INCLUDES_DIR."functions/database.php");
tep_db_connect() or die('Unable to connect to database server!');
//取得支付宝定义在数据库中的常量
$key_in = " 'MODULE_PAYMENT_ALIPAY_DIRECT_PAY_STATUS', 'MODULE_PAYMENT_ALIPAY_DIRECT_PAY_API_DIR','MODULE_PAYMENT_ALIPAY_DIRECT_PAY_API_WEB_DIR', 'MODULE_PAYMENT_ALIPAY_DIRECT_PAY_SORT_ORDER', 'MODULE_PAYMENT_ALIPAY_DIRECT_PAY_ID', 'MODULE_PAYMENT_ALIPAY_DIRECT_PAY_KEY','MODULE_PAYMENT_ALIPAY_DIRECT_PAY_EMAIL','MODULE_PAYMENT_ALIPAY_DIRECT_PAY_NOTIFY_URL','MODULE_PAYMENT_ALIPAY_DIRECT_PAY_RETURN_URL','MODULE_PAYMENT_ALIPAY_DIRECT_PAY_SHOW_URL','MODULE_PAYMENT_ALIPAY_DIRECT_PAY_MAIN_NAME' ";

$sql = tep_db_query("select configuration_key, configuration_value from configuration where configuration_key in (".$key_in.") ");
while($rows = tep_db_fetch_array($sql)){
	define($rows['configuration_key'], $rows['configuration_value']);
}
//导入一些必要的函数库
require_once(INCLUDES_DIR."functions/general.php");
require_once(INCLUDES_DIR."functions/webmakers_added_functions.php");

// Howard added }

//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者id，以2088开头的16位纯数字
$aliapy_config['partner']      = MODULE_PAYMENT_ALIPAY_DIRECT_PAY_ID;

//安全检验码，以数字和字母组成的32位字符
$aliapy_config['key']          = MODULE_PAYMENT_ALIPAY_DIRECT_PAY_KEY;

//签约支付宝账号或卖家支付宝帐户
$aliapy_config['seller_email'] = MODULE_PAYMENT_ALIPAY_DIRECT_PAY_EMAIL;

//页面跳转同步通知页面路径，要用 http://格式的完整路径，不允许加?id=123这类自定义参数
//return_url的域名不能写成http://localhost/create_direct_pay_by_user_php_utf8/return_url.php ，否则会导致return_url执行无效
$aliapy_config['return_url']   = MODULE_PAYMENT_ALIPAY_DIRECT_PAY_RETURN_URL;

//服务器异步通知页面路径，要用 http://格式的完整路径，不允许加?id=123这类自定义参数
$aliapy_config['notify_url']   = MODULE_PAYMENT_ALIPAY_DIRECT_PAY_NOTIFY_URL;

//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

//签名方式 不需修改
$aliapy_config['sign_type']    = 'MD5';

//字符编码格式 目前支持 gbk 或 utf-8
$aliapy_config['input_charset']= 'utf-8';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$aliapy_config['transport']    = 'http';

?>