<?php

//copyright：movie0312
//auther：movie0312
//date：2016.3.24
//class：基础类	基本配置信息
	
abstract class basic {
	
	//基本设置
	public static $_config = array(
	
		'default' => array(
		
			//是否启用调试 true时为调试模式 true or false
			'display_errors' => true,
			
			//是否启用rsa加密 false时为不加密 true or false
			'rsa' => false,
			
			//是否启用密送（该选项当ras为true时有效） false时为签名 true or false
			'cipher' => true,
			
			//私钥文件的路径（该选项当ras为true时有效） 当cipher为false时路径后自动增加子文件夹 需传入文件夹名称
			'private_key_path' => 'pem/',
			
			//公钥文件的路径（该选项当ras为true时有效） 当cipher为false时路径后自动增加子文件夹 需传入文件夹名称
			'public_key_path' => 'pem/',
			
			//私钥文件名（该选项当ras为true时有效）
			'private_key_name' => 'rsa_private_key.pem',
			
			//公钥文件名（该选项当ras为true时有效）
			'public_key_name' => 'rsa_public_key.pem',
			
			//是否启用jsond false时为json（待续）
			//'jsond' => true,
			
			//是否启用get false时为post true or false
			//当rsa为true时需为false
			'get' => false,
			
			//数据库类型
			'db_type'=>'mysql',
			
			//服务器地址
			'db_host'=>'',
			
			//端口
			'db_port'=>3306,
			
			//数据库名
			'db_name'=>'',
			
			//用户名
			'db_user'=>'',
			
			//密码
			'db_pwd'=>''
			
		)
	
	);
	
}
	
?>