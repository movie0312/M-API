<?php

//copyright：movie0312
//auther：movie0312
//date：2016.3.31
//function：快速设置数据库结构前可通过该方法显示基本信息

//加载基础类
include_once '/class/basic.class.php';
//加载标准类
include_once '/class/standard.class.php';
//加载数据库类
include_once '/class/mysql.class.php';

$db = new pdo_mysql();

//指定显示的表
//提示：此处请自行确认表名是否正确，表名不正确会报错
//$db->show_struct(array('muc_members', 'muc_members_info', 'muc_members_log'));

//显示全部表信息
$db->show_struct();

?>