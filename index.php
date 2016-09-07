<?php

//copyright：movie0312
//auther：movie0312
//date：2016.3.31
//function：对外接口入口

//加载基础类
include_once '/class/basic.class.php';
//加载标准类
include_once '/class/standard.class.php';

extension_loaded('mbstring') or standard::c_die('A001', 'php需要mbstring扩展支持');

//是否启用rsa
$rsa = standard::return_config('rsa');
//如果没有配置信息
$rsa === '' and standard::c_die('C007', '配置文件加密开关错误');

//加载rsa加密类
if ($rsa) {
	include_once '/class/rsa.class.php';
	$obj_rsa = new rsa();
}

//获取传入json
$data = standard::c_obtain('data');

if (!$data) {
	
	//如果没有传入任何数据
	$result = standard::return_result('0000', '');
	//加密
	$rsa and $result = $obj_rsa->encrypt($result);
	die($result);
	
}else{
	
	//解密
	$rsa and $data = $obj_rsa->decrypt($data);
	//将json转成数组
	$data = standard::json_to_arr($data);
	
}

//根据$arrdata进行相应操作

array_key_exists('b', $data) or standard::c_die('G001', '缺少传入参数data[b]');

array_key_exists('i', $data['b']) or standard::c_die('G002', '缺少传入参数data[b][i]');

(is_string($data['b']['i']) || $data['b']['i'] != '') or standard::c_die('G003', '传入值错误data[b][i]');

/*此处可根据['b']['i']做用户操作权限判断*/


array_key_exists('d', $data['b']) or standard::c_die('G004', '缺少数据参数data[b][d]');

(is_int($data['b']['d']) || $data['b']['d'] != '') or standard::c_die('G005', '传入值错误data[b][d]');

/*此处可根据['b']['d']做用户操作时限判断*/


if (array_key_exists('t', $data['b'])) {
	
	/*此处可根据['b']['t']做用户身份判断 并 赋值$_uid*/
	
}else{
	
	/*此处赋值用户标识session*/
	$_uid = $_session['uid'];
	
}

//session格式为字符串的数字
is_string($_uid) && ctype_digit($_uid) or standard::c_die('A002', '拒绝访问 错误身份');


array_key_exists('c', $data) or standard::c_die('G006', '缺少传入参数data[c]');

//加载配置类
include_once '/class/mysql.config.class.php';
//加载数据库类
include_once '/class/mysql.class.php';

$db = new pdo_mysql();

//执行操作
$info = $db->action($_uid, $data['c']);

$result = standard::return_result('0000', $info);

//加密
$rsa and $result = $obj_rsa->encrypt($result);

echo($result);
?>