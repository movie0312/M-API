<?php

//copyright：movie0312
//auther：movie0312
//date：2016.3.24
//class：标准类 常用函数

abstract class standard extends basic {

	//显示错误信息 或 显示错误代码
	//$code:错误代码
	//$str:错误说明
	//$config:配置文件
	public static function c_die ($code, $info, $config = 'default') {
		
		//获取调试模式
		$display_errors = self::return_config('display_errors', $config);
		//如果没有配置信息
		if ($display_errors === '') {
			$result['state'] = 0;
			$result['code'] = 'C000';
			die(self::arr_to_json($result));
		}
		
		//是否启用错误信息
		if (!$display_errors) {
			
			$result['state'] = 0;
			$result['code'] = $code;
			die(self::arr_to_json($result));
			
		} else {

			$result['state'] = 0;
			$result['code'] = $code;
			$result['info'] = $info;
			die(self::arr_to_json($result));
			
		}
		
	}
	
	//返回信息和代码 返回json
	//$code:代码
	//$info:信息
	public static function return_result ($code, $info) {
		
		$result['state'] = 1;
		$result['code'] = $code;
		$result['info'] = $info;
		return self::arr_to_json($result);
		
	}
	
	//获取数据 返回string
	//$param:获取变量
	//$config:配置文件
	public static function c_obtain ($param, $config = 'default') {
		
		//获取接收方式
		$get = self::return_config('get', $config);
		//如果没有配置信息
		$get === '' and self::c_die('C001', '配置文件获取数据模式错误');
		
		if ($get) {
			$val = isset($_GET[$param]) ? trim($_GET[$param]) : '';
		}else{
			$val = isset($_POST[$param]) ? trim($_POST[$param]) : '';
		}
		
		return $val;
		
	}
	
	//判断配置文件是否存在 返回配置
	//$i:一维键
	//$j:二维键
	public static function return_config ($j = '', $i = 'default') {
		
		$i = trim($i);
		$j = trim($j);
		
		if ($j === '') {
			$val = isset(parent::$_config[$i]) ? parent::$_config[$i] : '';
		}else{
			$val = isset(parent::$_config[$i][$j]) ? parent::$_config[$i][$j] : '';
		}
		
		return $val;
		
	}
	
	//字符串json处理 返回json
	//$arr:数组
	public static function arr_to_json ($arr) {
		return json_encode($arr, JSON_UNESCAPED_UNICODE);
	}
	
	//json数组处理 返回数组
	//$json:json
	public static function json_to_arr ($json) {
		return json_decode($json, true);
	}
	
	//大精度数字的计算
	//$m:第一个数字
	//$n:第二个数字
	//$x:计算方式 add加,sub减,mul乖,div除,pow乘方,sqrt开方,mod求余,powmod求模,comp比较
	//加减乖除:参数1 加上/减去/乘以/除以 参数2
	//乘方:参数1 的 参数2 次方
	//开方:求 参数1 的算术平方根 参数2不起作用 但不能省略
	//求余:参数1 除以 参数2 得到的余数
	//求模:参数1 除以 参数2 得到的模
	//比较:参数1 和 参数2 比较
	public static function calc($m, $n, $x) {
		
		//错误信息
		$errors=array('被除数不能为零','负数没有平方根');
		
		//进行计算
		switch($x) {
			
			//加
			case 'add':
			
				$t=bcadd($m, $n);
				break;
				
			//减
			case 'sub':
			
				$t=bcsub($m, $n);
				break;
				
			//乘
			case 'mul':
			
				$t=bcmul($m, $n);
				break;
				
			//除
			case 'div':
			
				if ( $n!=0 ) {
					$t=bcdiv($m, $n);
				}else{
					return $errors[0];
				}
				break;
					
			//乘方
			case 'pow':
			
				$t=bcpow($m, $n);
				break;
				
			//开方
			case 'sqrt':
			
				if ($m >= 0) {
					$t=bcsqrt($m);
				}else{
					return $errors[1];
				}
				break;
				
			//求余
			case 'mod':
			
				if ($n != 0) {
					$t=bcmod($m, $n);
				}else{
					return $errors[0];
				}
				break;
				
			//求模
			case 'powmod':
			
				if ($n != 0) {
					$t=bcpowmod($m, $n);
				}else{
					return $errors[0];
				}
				break;
				
			//比较
			//比较两个高精度数字 返回-1(左小), 0(相等), 1(左大)
			case 'comp':
			
				$t=bccomp($m, $n);
				break;
				
		}
		
		$t=preg_replace("/\..*0+$/",'',$t);
		return $t;
		
	}

}

?>