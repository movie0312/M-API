<?php

//copyright：movie0312
//auther：movie0312
//date：2016.3.28
//class：mysql配置类

abstract class mysql_config extends standard {
	
	public static $_tables = array(
	
	  /*
	  '0' => array(
		'0' => 'table_name'
		//允许增加 默认为true 可不设置 true or false
		//'add' => false,
		//允许删除 默认为true 可不设置 true or false
		//'del' => false,
		//允许修改 默认为true 可不设置 true or false
		//'edit' => false,
		//允许查询 默认为true 可不设置 true or false
		//'select' => false
	  )
	  */
	  
	);
	
	public static $_fields = array(
	  
	  /*
	  '0' => array(
		  '0' => 'field_name',
		  '1' => 'char(15)',
		  '2' => 'NO',
		  '3' => 'UNI'
		  //身份标识 当为身份标识时接口内部使用
		  //每个表仅有一个字段需要设置该属性
		  //禁止任何外部调用 edit和select设置失效
		  //默认为false 可不设置 true or false
		  //'isuid' => false,
		  //允许修改 默认为true 可不设置 true or false
		  //'edit' => false,
		  //允许查询 默认为true 可不设置 true or false
		  //'select' => false,
		  //正则过滤 默认为空 可不设置
		  //'pattern' => '/^[a-z\d_]{5,20}$/i'
		)
	  */
	  
	);
}
	
?>