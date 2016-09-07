<?php

//copyright：movie0312
//auther：movie0312
//date：2016.3.24
//class：加密类 rsa
	
class rsa extends standard {
	
	public function __construct () {
		extension_loaded('openssl') or parent::c_die('E001', 'php需要openssl扩展支持');
	}
	
	/*
	密送 公钥加密私钥解密
	签名 私钥加密公钥解密
	*/
	//加密 返回加密后的字符串
	//$str:待加密字符串
	//$dir:路径
	//$config:配置文件
	public function encrypt ($str, $dir = '', $config = 'default') {
		
		$cipher = self::get_cipher($config);
		
		if ($cipher) {
			
			//密送 私钥加密
			$key = self::private_key($dir, $config);
			
			//进行加密
			if (openssl_private_encrypt($str, $newstr, $key)) {
				return base64_encode($newstr);
			}else{
				parent::c_die('E006', '加密出错');
			}
			
		} else {

			//签名 公钥加密
			$key = self::public_key($dir, $config);
			
			//进行加密
			if (openssl_public_encrypt($str, $newstr, $key)) {
				return base64_encode($newstr);
			}else{
				parent::c_die('E006', '加密出错');
			}
			
		}
		
	}
	
	//解密 返回解密后的字符串
	//$str:待解密字符串
	//$dir:路径
	//$config:配置文件
	public function decrypt ($str, $dir = '', $config = 'default') {
		
		$cipher = self::get_cipher($config);
		
		if ($cipher) {
			
			//密送 私钥解密
			$key = self::private_key($dir, $config);
			
			//进行解密
			if (openssl_private_decrypt(base64_decode($str), $newstr, $key)) {
				return $newstr;
			}else{
				parent::c_die('E007', '解密出错');
			}
			
		} else {

			//签名 公钥解密
			$key = self::public_key($dir, $config);
			
			//进行解密
			if (openssl_public_decrypt(base64_decode($str), $newstr, $key)) {
				return $newstr;
			}else{
				parent::c_die('E007', '解密出错');
			}
			
		}
		
	}
	
	//获取密送方式 返回密送配置
	//$config:配置文件
	private function get_cipher ($config) {
		
		$cipher = parent::return_config('cipher', $config);
		//如果没有配置信息
		$cipher === '' and parent::c_die('C006', '配置文件密送方式错误');
		return $cipher;
		
	}
	
	//获取私钥 返回Resource类型的私钥
	//$dir:路径
	//$config:配置文件
	private function private_key ($dir, $config) {
		
		//获取私钥文件的路径
		$private_key_path = parent::return_config('private_key_path', $config);
		//如果没有配置信息
		$private_key_path === '' and parent::c_die('C002', '配置文件私钥文件的路径错误');
		
		//获取私钥文件名
		$private_key_name = parent::return_config('private_key_name', $config);
		//如果没有配置信息
		$private_key_name === '' and parent::c_die('C004', '配置文件私钥文件名错误');
		
		//私钥文件
		$cipher = self::get_cipher($config);
		if (!$cipher) {
			$dir or parent::c_die('E008', '私钥路径错误');
			$private_key_path = $private_key_path.$dir.'/';
		}
		$private_key_file = $private_key_path.$private_key_name;
		
		file_exists($private_key_file) or parent::c_die('E002', '私钥文件不存在');
		
		//生成Resource类型的私钥
		$private_key = openssl_pkey_get_private(file_get_contents($private_key_file));
		
		$private_key or parent::c_die('E003', '私钥不可用');
		
		return $private_key;

	}
	
	//获取公钥 返回Resource类型的公钥
	//$dir:路径
	//$config:配置文件
	private function public_key ($dir, $config) {
		
		//获取公钥文件的路径
		$public_key_path = parent::return_config('public_key_path', $config);
		//如果没有配置信息
		$public_key_path === '' and parent::c_die('C003', '配置文件公钥文件的路径错误');
		
		//获取公钥文件名
		$public_key_name = self::return_config('public_key_name', $config);
		//如果没有配置信息
		$public_key_name === '' and parent::c_die('C005', '配置文件公钥文件名错误');
		
		//公钥文件
		$cipher = self::get_cipher($config);
		if (!$cipher) {
			$dir or parent::c_die('E009', '公钥路径错误');
			$public_key_path = $public_key_path.$dir.'/';
		}
		$public_key_file = $public_key_path.$public_key_name;
		
		file_exists($public_key_file) or parent::c_die('E004', '公钥文件不存在');
		
		//生成Resource类型的公钥
		$public_key = openssl_pkey_get_public(file_get_contents($public_key_file));
		
		$public_key or parent::c_die('E005', '公钥不可用');
		
		return $public_key;

	}
	
}

?>