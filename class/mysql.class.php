<?php

//copyright：movie0312
//auther：movie0312
//date：2016.3.27
//class：数据库类 mysql

//加载配置类
include_once '/class/mysql.config.class.php';
	
class pdo_mysql extends mysql_config {
	
	public function __construct () {
		extension_loaded('pdo_mysql') or parent::c_die('D001', 'php需要pdo_mysql扩展支持');
	}
	
	//执行传入内容 返回数组
	//$contents:具体执行内容 数组
	//$config:配置文件
	public function action ($uid, $contents, $config = 'default') {
	
		$result = array();
		
		//一维遍历
		foreach ($contents as $key => $value) {
			
			//判断是否有操作类型
			array_key_exists('a', $value) or parent::c_die('G007', '缺少传入参数data[c]['.$key.'][a]');
			
			//判断操作类型
			(is_int($value['a']) || in_array($value['a'], array(0, 1, 2, 3))) or parent::c_die('G008', '传入值错误data[c]['.$key.'][a]');
			
			//判断是否有操作的表
			array_key_exists('t', $value) or parent::c_die('G009', '缺少传入参数data[c]['.$key.'][t]');
			
			//判断操作的表
			(is_int($value['t']) || array_key_exists($value['t'], parent::$_tables)) or parent::c_die('G010', '传入值错误data[c]['.$key.'][t]');
			
			//判断表是否有操作权限
			switch ($value['a']) {
				
				case 0:
				
					//判断是否允许增加
					if (array_key_exists('add', parent::$_tables[$value['t']])) {
						is_bool(parent::$_tables[$value['t']]['add']) or parent::c_die('C010', 'mysql基本设置表允许增加开关设置错误');
						parent::$_tables[$value['t']]['add'] === false and parent::c_die('G011', '传入参数不允许增加操作data[c]['.$key.'][t]');
					}
					
					//判断是否有字段值
					array_key_exists('v', $value) or parent::c_die('G015', '缺少传入参数data[c]['.$key.'][v]');
					
					break;
					
				case 1:
				
					//判断是否允许删除
					if (array_key_exists('del', parent::$_tables[$value['t']])) {
						is_bool(parent::$_tables[$value['t']]['del']) or parent::c_die('C011', 'mysql基本设置表允许删除开关设置错误');
						parent::$_tables[$value['t']]['del'] === false and parent::c_die('G012', '传入参数不允许删除操作data[c]['.$key.'][t]');
					}
					
					break;
					
				case 2:
				
					//判断是否允许修改
					if (array_key_exists('edit', parent::$_tables[$value['t']])) {
						is_bool(parent::$_tables[$value['t']]['edit']) or parent::c_die('C012', 'mysql基本设置表允许修改开关设置错误');
						parent::$_tables[$value['t']]['edit'] === false and parent::c_die('G013', '传入参数不允许修改操作data[c]['.$key.'][t]');
					}
					
					//判断是否有字段值
					array_key_exists('v', $value) or parent::c_die('G015', '缺少传入参数data[c]['.$key.'][v]');
					
					break;
					
				case 3:
				
					//判断是否允许查询
					if ($value['a'] == 3 && array_key_exists('select', parent::$_tables[$value['t']])) {
						is_bool(parent::$_tables[$value['t']]['select']) or parent::c_die('C013', 'mysql基本设置表允许查询开关设置错误');
						parent::$_tables[$value['t']]['select'] === false and parent::c_die('G014', '传入参数不允许查询操作data[c]['.$key.'][t]');
					}
					
					//判断是否有显示的字段
					array_key_exists('s', $value) or parent::c_die('G026', '缺少传入参数data[c]['.$key.'][s]');
					
					break;
					
			}
			
			//判断字段
			array_key_exists('v', $value) and self::judge_fields($key, $value['t'], $value['v']);
			
			//判断条件
			array_key_exists('w', $value) and self::judge_where($key, $value['t'], $value['w']);
			
			//判断显示的字段
			array_key_exists('s', $value) and self::judge_select($key, $value['t'], $value['s']);
			
			//判断显示的起始和数量
			array_key_exists('l', $value) and self::judge_limit($key, $value['t'], $value['l']);
			
			//判断排序
			array_key_exists('o', $value) and self::judge_order($key, $value['t'], $value['o']);
			
			//执行操作
			switch ($value['a']) {
				case 0:
					$result[$key] = self::add($uid, $value, $config);
					break;
				case 1:
					$result[$key] = self::del($uid, $value, $config);
					break;
				case 2:
					$result[$key] = self::update($uid, $value, $config);
					break;
				case 3:
					$result[$key] = self::select($uid, $value, $config);
					break;
			}
			
		}
		
		return $result;
	
	}
	
	//插入数据 返回插入数据id
	//$uid:用户身份标识
	//$data:具体插入的数据 数组
	//$config:配置文件
	//$debug:是否测试 当为true时不执行仅显示sql语句 true or false
	private function add ($uid, $data, $config, $debug = false) {
		
		//获取表名
		$table = self::code_to_name($data['t']);
		
		//获取用户身份标识字段
		$field_uid = self::get_uid($data['t']);
		
		//设置sql语句 param语句 sql_fields语句 sql_values语句
		$sql = '';
		$bindparam = '';
		$sql_fields = '';
		$sql_values = '';
		
		foreach ($data['v'] as $key => $value) {
			
			//设置字段名
			$sql_fields .= '`'.self::code_to_name($data['t'], $key).'`, ';
			
			if ($debug) {
				
				//设置字段值
				$sql_values .= '\''.$value.'\', ';
				
			}else{
				
				//设置字段值
				$sql_values .= ':'.self::code_to_name($data['t'], $key).', ';
				
				//设置绑定
				$bindparam .= '$result->bindparam(\':'.self::code_to_name($data['t'], $key).'\',$data[\'v\'][\''.$key.'\']);';
				
			}
			
		}
		
		//增加用户身份标识
		if ($field_uid) {
			
			//设置字段名
			$sql_fields .= '`'.$field_uid.'`, ';
			
			if ($debug) {
				//设置字段值
				$sql_values .= '\''.$uid.'\', ';
			}else{
				//设置字段值
				$sql_values .= ':'.$field_uid.', ';
				//设置绑定
				$bindparam .= '$result->bindparam(\':'.$field_uid.'\',$uid);';
			}
			
		}
		
		//设置sql语句
		$sql_fields = '('.substr($sql_fields, 0, strlen($sql_fields)-2).')';
		$sql_values = '('.substr($sql_values, 0, strlen($sql_values)-2).')';
		$sql = 'insert into `'.$table.'` '.$sql_fields.' values '.$sql_values;
		
		if ($debug) {
		
			//显示sql语句
			echo $sql;
			echo '<br />';
		
		}else{
			
			//连接数据库
			$db = self::open_conn($config);
			
			try {
				
				//预处理sql
				$result = $db->prepare($sql);
				
				//绑定参数
				eval($bindparam);
				
				//执行sql
				$result->execute();
				
				//返回插入数据id
				$return = $db->lastinsertid();
				
				//关闭连接
				$db = null;
				
			} catch (pdoexception $e) {
				
				parent::c_die('D003', iconv("GB2312", "UTF-8", $e->getmessage()));
				
			}
			
			return $return;
			
		}
		
	}
	
	//删除数据 返回影响行数
	//$uid:用户身份标识
	//$data:具体删除的条件 数组
	//$config:配置文件
	//$debug:是否测试 当为true时不执行仅显示sql语句 true or false
	private function del ($uid, $data, $config, $debug = false) {
		
		//获取表名
		$table = self::code_to_name($data['t']);
		
		//获取用户身份标识字段
		$field_uid = self::get_uid($data['t']);
		
		//设置sql语句 param语句 where语句
		$sql = '';
		$bindparam = '';
		$where = array();
		$where_uid = '';
		
		//条件字符串处理
		if (array_key_exists('w', $data)) {
			$where = self::where_to_sql($data['t'], $data['w'], $debug);
		}else{
			$where['sql_where'] = '';
			$where['bindparam'] = '';
		}
		
		//增加用户身份标识
		if ($field_uid) {
			
			if ($debug) {
				
				//设置where_uid语句
				$where_uid = '`'.$field_uid.'` = \''.$uid.'\'';
				
			}else{
				
				//设置where_uid语句
				$where_uid = '`'.$field_uid.'`=:'.$field_uid;
				
				//设置绑定
				$bindparam .= '$result->bindparam(\':'.$field_uid.'\',$uid);';
			}
			
		}
		
		//设置sql语句
		$sql = 'delete from `'.$table.'`';
		
		if ($where['sql_where'] || $where_uid) {
			
			$sql .= ' where ';
			
			if ($where['sql_where']) {
				
				if ($where_uid) {
					$sql .= '('.$where['sql_where'].') and ('.$where_uid.')';
				}else{
					$sql .= $where['sql_where'];
				}
				
			}else{
				
				$sql .= $where_uid;
				
			}
			
		}
		
		//设置绑定
		$bindparam .= $where['bindparam'];
		
		if ($debug) {
		
			echo $sql;
			echo '<br />';
		
		}else{
			
			//连接数据库
			$db = self::open_conn($config);
			
			try {
				
				//预处理sql
				$result = $db->prepare($sql);
				
				//绑定参数
				eval($bindparam);
				
				//执行sql
				$result->execute();
				
				//返回影响行数
				$return = $result->rowcount();
				
				//关闭连接
				$db = null;
				
			} catch (pdoexception $e) {
				
				parent::c_die('D003', iconv("GB2312", "UTF-8", $e->getmessage()));
				
			}
			
			return $return;
			
		}
		
	}
	
	//修改数据 返回影响行数
	//$uid:用户身份标识
	//$data:具体修改的内容 数组
	//$config:配置文件
	//$debug:是否测试 当为true时不执行仅显示sql语句 true or false
	private function update ($uid, $data, $config, $debug = false) {
		
		//获取表名
		$table = self::code_to_name($data['t']);
		
		//获取用户身份标识字段
		$field_uid = self::get_uid($data['t']);
		
		//设置sql语句 param语句 setvalue语句 where语句
		$sql = '';
		$bindparam = '';
		$setvalue = '';
		$where = array();
		$where_uid = '';
		
		//设置修改内容
		foreach ($data['v'] as $key => $value) {
			
			if ($debug) {
				
				$setvalue .= '`'.self::code_to_name($data['t'], $key).'`=\''.$value.'\', ';
				
			}else{
				
				$setvalue .= '`'.self::code_to_name($data['t'], $key).'`=:v'.self::code_to_name($data['t'], $key).', ';
				
				//设置绑定
				$bindparam .= '$result->bindparam(\':v'.self::code_to_name($data['t'], $key).'\',$data[\'v\'][\''.$key.'\']);';
				
			}
			
		}
		
		$setvalue = substr($setvalue, 0, strlen($setvalue)-2);
		
		//条件字符串处理
		if (array_key_exists('w', $data)) {
			$where = self::where_to_sql($data['t'], $data['w'], $debug);
		}else{
			$where['sql_where'] = '';
			$where['bindparam'] = '';
		}
		
		//增加用户身份标识
		if ($field_uid) {
			
			if ($debug) {
				
				//设置where_uid语句
				$where_uid = '`'.$field_uid.'` = \''.$uid.'\'';
				
			}else{
				
				//设置where_uid语句
				$where_uid = '`'.$field_uid.'`=:'.$field_uid;
				
				//设置绑定
				$bindparam .= '$result->bindparam(\':'.$field_uid.'\',$uid);';
			}
			
		}
		
		//设置sql语句
		$sql = 'update `'.$table.'` set '.$setvalue;
		
		if ($where['sql_where'] || $where_uid) {
			
			$sql .= ' where ';
			
			if ($where['sql_where']) {
				
				if ($where_uid) {
					$sql .= '('.$where['sql_where'].') and ('.$where_uid.')';
				}else{
					$sql .= $where['sql_where'];
				}
				
			}else{
				
				$sql .= $where_uid;
				
			}
			
		}
		
		//设置绑定
		$bindparam .= $where['bindparam'];
		
		if ($debug) {
		
			echo $sql;
			echo '<br />';
		
		}else{
			
			//连接数据库
			$db = self::open_conn($config);
			
			try {
				
				//预处理sql
				$result = $db->prepare($sql);
				
				//绑定参数
				eval($bindparam);
				
				//执行sql
				$result->execute();
				
				//返回影响行数
				$return = $result->rowcount();
				
				//关闭连接
				$db = null;
				
			} catch (pdoexception $e) {
				
				parent::c_die('D003', iconv("GB2312", "UTF-8", $e->getmessage()));
				
			}
			
			return $return;
			
		}
		
	}
	
	//查询数据 返回数组
	//$uid:用户身份标识
	//$data:具体查询的条件 数组
	//$config:配置文件
	//$debug:是否测试 当为true时不执行仅显示sql语句 true or false
	private function select ($uid, $data, $config, $debug = false) {
		
		//获取表名
		$table = self::code_to_name($data['t']);
		
		//获取用户身份标识字段
		$field_uid = self::get_uid($data['t']);
		
		//设置sql语句 param语句 fields语句 where语句 order语句 limit语句
		$sql = '';
		$bindparam = '';
		$fields = '';
		$where = array();
		$where_uid = '';
		$order = '';
		$limit = '';
		
		//设置显示的字段
		$arrstr = explode(",",$data['s']);
		
		foreach ($arrstr as $value) {
			$fields .= '`'.self::code_to_name($data['t'], $value).'`, ';
		}
		
		$fields = substr($fields, 0, strlen($fields)-2);
		
		//条件字符串处理
		if (array_key_exists('w', $data)) {
			$where = self::where_to_sql($data['t'], $data['w'], $debug);
		}else{
			$where['sql_where'] = '';
			$where['bindparam'] = '';
		}
		
		//增加用户身份标识
		if ($field_uid) {
			
			if ($debug) {
				
				//设置where_uid语句
				$where_uid = '`'.$field_uid.'` = \''.$uid.'\'';
				
			}else{
				
				//设置where_uid语句
				$where_uid = '`'.$field_uid.'`=:'.$field_uid;
				
				//设置绑定
				$bindparam .= '$result->bindparam(\':'.$field_uid.'\',$uid);';
			}
			
		}
		
		//设置排序方式
		if (array_key_exists('o', $data)) {
			
			$order .= 'order by ';
			
			foreach ($data['o'] as $key => $value) {
				
				if ($value === 'desc') {
					$order .= '`'.self::code_to_name($data['t'], $key).'` '.$value.', ';
				}else{
					$order .= '`'.self::code_to_name($data['t'], $key).'`, ';
				}
				
			}
			
			$order = substr($order, 0, strlen($order)-2);
			
		}
		
		//设置显示的起始和数量
		if (array_key_exists('l', $data)) {
			$arrstr = explode(",",$data['l']);
			$limit = 'limit '.$arrstr[0].', '.$arrstr[1];
		}
		
		//设置sql语句
		$sql = 'select ';
		
		if ($fields) {
			$sql .= $fields;
		}else{
			$sql .= '*';
		}
		
		$sql .= ' from `'.$table.'`';
		
		if ($where['sql_where'] || $where_uid) {
			
			$sql .= ' where ';
			
			if ($where['sql_where']) {
				
				if ($where_uid) {
					$sql .= '('.$where['sql_where'].') and ('.$where_uid.')';
				}else{
					$sql .= $where['sql_where'];
				}
				
			}else{
				
				$sql .= $where_uid;
				
			}
			
		}
		
		$order and $sql .= ' '.$order;
		
		$limit and $sql .= ' '.$limit;
		
		//设置绑定
		$bindparam .= $where['bindparam'];
		
		if ($debug) {
		
			echo $sql;
			echo '<br />';
		
		}else{
			
			//连接数据库
			$db = self::open_conn($config);
			
			try {
				
				//预处理sql
				$result = $db->prepare($sql);
				
				//绑定参数
				eval($bindparam);
				
				//执行sql
				$result->execute();
				
				//返回查询结果
				$return = $result->fetchall();
				
				//关闭连接
				$db = null;
				
			} catch (pdoexception $e) {
				
				parent::c_die('D003', iconv("GB2312", "UTF-8", $e->getmessage()));
				
			}
			
			return $return;
			
		}
		
	}
	
	//显示外部调用库结构 返回数组
	//$table:显示的表名 数组 默认显示全部
	//$config:配置文件
	public function show_struct($table = '', $config = 'default') {
		
		//初始化数组
		$result = array();
		$i = 0;

		//获取表名
		$tables = self::get_tables($config);
		
		//遍历表名查询字段
		foreach ($tables as $key => $value) {
			
			if($table == '' || in_array($value['0'],$table)) {
				
				//获取字段
				$fields = self::get_fields($value['0'], $config);
				
				//写入数组
				$result['tables'][$i] = $value['0'];
				$result['fields'][$i] = $fields;
				
				$i++;
				
			}
			
		}
		
		$len = count($result['tables']);
		
		//显示数组
		echo '数据库结构<br /><br />public static $_tables = array(<br />';
		
		foreach ($result['tables'] as $key => $value) {
			if ($key == $len - 1) {
				echo "&nbsp;&nbsp;'".$key."' => array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'0' => '".$value."'<br />&nbsp;&nbsp;)<br />";
			}else{
				echo "&nbsp;&nbsp;'".$key."' => array(<br />&nbsp;&nbsp;&nbsp;&nbsp;'0' => '".$value."'<br />&nbsp;&nbsp;),<br />";
			}
		}
		
		echo ');<br /><br />public static $_fields = array(<br />';
		
		//一维遍历
		foreach ($result['fields'] as $key => $value) {
			
			echo "&nbsp;&nbsp;'".$key."' => array(<br />";
			
			//二维遍历
			foreach ($value as $key1 => $value1) {
				
				$len1 = count($value);
				
				echo "&nbsp;&nbsp;&nbsp;&nbsp;'".$key1."' => array(<br />";
				
				//三维遍历
				foreach ($value1 as $key2 => $value2) {
					
					//显示全部
					if ($key2 == 5) {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'".$key2."' => '".$value2."'<br />";
					}else{
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'".$key2."' => '".$value2."',<br />";
					}
					
				}
				
				if ($key1 == $len1 - 1) {
					echo "&nbsp;&nbsp;&nbsp;&nbsp;)<br />";
				}else{
					echo "&nbsp;&nbsp;&nbsp;&nbsp;),<br />";
				}
				
			}
			
			if ($key == $len - 1) {
				echo "&nbsp;&nbsp;)<br />";
			}else{
				echo "&nbsp;&nbsp;),<br />";
			}
			
		}
		
		echo ');';
		
	}
	
	//开启数据库连接 返回数据库连接对象
	//$config:配置文件
	private function open_conn ($config) {
		
		//获取参数
		$db_config = self::get_db_config($config);
		$db_config['db_type'] != 'mysql' and parent::c_die('C009', '配置文件数据库类型错误');
		
		//连接
		try {
			
			$db = new pdo('mysql:host='.$db_config['db_host'].';port='.$db_config['db_host'].';dbname='.$db_config['db_name'], $db_config['db_user'], $db_config['db_pwd']);
			
		} catch (pdoexception $e) {
			
			parent::c_die('D002', iconv("GB2312", "UTF-8", $e->getmessage()));
			
		}
		
		//默认数组显示模式
		//对应结果集中的每一行作为一个由列号索引的数组返回，从第0列开始
		$db->setattribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_NUM);
		//由mysql进行变量处理
		$db->setattribute(PDO::ATTR_EMULATE_PREPARES,false);
		//如果发生错误，则抛出一个 PDOException 异常
		$db->setattribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		
		return $db;
		
	}
	
	//获取数据库配置 返回数组
	//$config:配置文件
	private function get_db_config ($config) {
		
		$db_config['db_type'] = parent::return_config('db_type', $config);
		$db_config['db_host'] = parent::return_config('db_host', $config);
		$db_config['db_port'] = parent::return_config('db_port', $config);
		$db_config['db_name'] = parent::return_config('db_name', $config);
		$db_config['db_user'] = parent::return_config('db_user', $config);
		$db_config['db_pwd'] = parent::return_config('db_pwd', $config);
		
		//如果缺少配置信息
		($db_config['db_type'] === '' || $db_config['db_host'] === '' || $db_config['db_port'] === '' || $db_config['db_name'] === '' || $db_config['db_user'] === '' || $db_config['db_pwd'] === '') and parent::c_die('C008', '配置文件数据库参数错误');
		
		return $db_config;
		
	}
	
	//获取数据库所有表名 返回数组
	//$config:配置文件
	private function get_tables($config) {
		
		//连接数据库
		$db = self::open_conn($config);
		
		try {
			
			//查询结果
			$data = $db->query('show tables from '.parent::return_config('db_name', $config));
			
			//关闭连接
			$db = null;
			
		} catch (pdoexception $e) {
			
			parent::c_die('D002', iconv("GB2312", "UTF-8", $e->getmessage()));
			
		}
		
		return $data->fetchAll();
		
	}
	
	//获取表中的字段 返回数组
	//$table:表名
	//$config:配置文件
	private function get_fields($table, $config) {
		
		//连接数据库
		$db = self::open_conn($config);
		
		try {
			
			//查询结果
			$data = $db->query('describe '.$table);
			
			//关闭连接
			$db = null;
			
		} catch (pdoexception $e) {
			
			parent::c_die('D002', iconv("GB2312", "UTF-8", $e->getmessage()));
			
		}
		
		return $data->fetchAll();
		
	}
	
	//获取字段有效性 返回数组
	//$fields:字段 数组
	private function get_fields_valid ($fields) {
		
		//字段类型
		$type = $fields['1'];
		//(出现的位置
		$i = strpos($type, '(');
		//)出现的位置
		$j = strpos($type, ')');
		
		//具体字段类型
		$type1 = substr($type, 0, $i);
		
		switch ($type1) {
			
			case 'char':
			case 'varchar':
			
				//获取字符串长度
				$type2 = substr($type, $i+1, $j-$i-1);
				
				//类型
				$result['type'] = 'string';
				
				//长度
				$result['length_max'] = (int)$type2;
				
				break;
				
			case 'tinyint':
			
				//类型
				$result['type'] = 'int';
				
				//获取是否无符号数
				$type2 = substr($type, $j+2);
				
				//最大值和最小值
				if ($type2 == 'unsigned') {
					$result['min'] = 0;
					$result['max'] = 255;
				}else{
					$result['min'] = -128;
					$result['max'] = 127;
				}
				
				break;
				
			case 'smallint':
			
				//类型
				$result['type'] = 'int';
				
				//获取是否无符号数
				$type2 = substr($type, $j+2);
				
				//最大值和最小值
				if ($type2 == 'unsigned') {
					$result['min'] = 0;
					$result['max'] = 65535;
				}else{
					$result['min'] = -32768;
					$result['max'] = 32767;
				}
				
				break;
				
			case 'mediumint':
			
				//类型
				$result['type'] = 'int';
				
				//获取是否无符号数
				$type2 = substr($type, $j+2);
				
				//最大值和最小值
				if ($type2 == 'unsigned') {
					$result['min'] = 0;
					$result['max'] = 16777215;
				}else{
					$result['min'] = -8388608;
					$result['max'] = 8388607;
				}
				
				break;
				
			case 'int':
			
				//类型
				$result['type'] = 'int';
				
				//获取是否无符号数
				$type2 = substr($type, $j+2);
				
				//最大值和最小值
				if ($type2 == 'unsigned') {
					$result['min'] = 0;
					$result['max'] = 4294967295;
				}else{
					$result['min'] = -2147483648;
					$result['max'] = 2147483647;
				}
				
				break;
				
			case 'bigint':
			
				//类型
				$result['type'] = 'int_str';
				
				//获取是否无符号数
				$type2 = substr($type, $j+2);
				
				//最大值和最小值
				if ($type2 == 'unsigned') {
					$result['min'] = '0';
					$result['max'] = '18446744073709551615';
				}else{
					$result['min'] = '-9223372036854775808';
					$result['max'] = '9223372036854775807';
				}
				
				break;
				
			case 'decimal':
			
				//类型
				$result['type'] = 'decimal_str';
				
				//获取精度和小数位
				$type2 = substr($type, $i+1, $j-$i-1);
				$arr = explode(',', $type2);
				
				//整数位数
				$result['i'] = (int)$arr[0] - (int)$arr[1];
				
				//小数部分
				$result['d'] = (int)$arr[1];
				
				//设置最大值
				$result['max'] = '';
				
				for ($x = 0; $x < $result['i']; $x++) {
					$result['max'] .= '9';
				}
				
				$result['max'] .= '.';
				for ($x = 0; $x < $result['d']; $x++) {
					$result['max'] .= '9';
				}
				
				//获取是否无符号数
				$type2 = substr($type, $j+2);
				
				//最大值和最小值
				if ($type2 == 'unsigned') {
					$result['min'] = '0';
				}else{
					$result['min'] = '-'.$result['max'];
				}
				
				break;
				
				//(5,2)
				//0.00 - 999.99
				//-999.99 - 999.99
				
			default:
			
				$result['type'] = false;
				break;
				
		}
		
		//允许为空
		if ($fields['2'] == 'YES') {
			$result['isnull'] = true;
		}else{
			$result['isnull'] = false;
		}
		
		//正则
		if (array_key_exists('pattern', $fields)) {
			$result['pattern'] = $fields['pattern'];
		}else{
			$result['pattern'] = false;
		}
		
		return $result;
		
	}
	
	//获取用户身份标识字段
	//$table_code:表代号
	private function get_uid($table_code) {
		
		$result = '';
		
		foreach (parent::$_fields[$table_code] as $key => $value) {
			
			if(array_key_exists('isuid', $value)) {
				
				if ($value['isuid'] === true) {
					$result = $value['0'];
				}
				
				return $result;
				
			}
			
		}
		
		return false;
		
	}
	
	//判断字段
	//$key:执行组号
	//$table_code:表代号
	//$fields:字段数组
	private function judge_fields ($key, $table_code, $fields) {
		
		//二维遍历
		foreach ($fields as $key1 => $value1) {
			
			//判断字段
			array_key_exists($key1, parent::$_fields[$table_code]) or parent::c_die('G017', '传入参数错误data[c]['.$key.'][v]['.$key1.']');
			
			//判断是否为身份标识
			if (array_key_exists('isuid', parent::$_fields[$table_code][$key1])) {
				is_bool(parent::$_fields[$table_code][$key1]['isuid']) or parent::c_die('C016', 'mysql基本设置字段身份标识开关设置错误');
				parent::$_fields[$table_code][$key1]['isuid'] === true and parent::c_die('G022', '传入参数不允许任何操作data[c]['.$key.'][v]['.$key1.']');
			}
			
			//判断是否允许修改
			if (array_key_exists('edit', parent::$_fields[$table_code][$key1])) {
				is_bool(parent::$_fields[$table_code][$key1]['edit']) or parent::c_die('C014', 'mysql基本设置字段允许修改开关设置错误');
				parent::$_fields[$table_code][$key1]['edit'] === false and parent::c_die('G016', '传入参数不允许更改操作data[c]['.$key.'][v]['.$key1.']');
			}
			
			//判断值
			
			//获取有效性
			$valid = self::get_fields_valid(parent::$_fields[$table_code][$key1]);
			
			//判断有效性
			self::judge_fields_valid('data[c]['.$key.'][v]['.$key1.']', $value1, $valid);
		
		}
		
	}
	
	//判断字段有效性
	//$key:字段键
	//$value:字段值
	//$valid:有效性 数组
	private function judge_fields_valid ($key, $value, $valid) {
		
		//判断是否为空
		!$valid['isnull'] && is_null($value) and parent::c_die('G018', '传入值不允许为空'.$key);
		
		//判断格式
		if ($valid['pattern']) {
			preg_match_all($valid['pattern'], $value) or parent::c_die('G021', '传入值格式错误'.$key);
		}
		
		switch ($valid['type']) {
				
			case 'string':
				
				//判断类型
				is_string($value) or parent::c_die('G019', '传入值类型错误'.$key);
				
				//判断范围
				$len = mb_strlen($value,'utf8');
				$len > $valid['length_max'] and parent::c_die('G020', '传入值超出范围'.$key);
				
				break;
				
			case 'int':
				
				//判断类型
				is_int($value) or parent::c_die('G019', '传入值类型错误'.$key);
				
				//判断范围
				$value < $valid['min'] || $value > $valid['max'] and parent::c_die('G020', '传入值超出范围'.$key);
				
				break;
				
			case 'int_str':
				
				//判断类型
				is_string($value) && ctype_digit($value) or parent::c_die('G019', '传入值类型错误'.$key);
				
				//判断范围
				parent::calc($value, $valid['min'], 'comp') == -1 || parent::calc($value, $valid['max'], 'comp') == 1 and parent::c_die('G020', '传入值超出范围'.$key);
				
				break;
				
			case 'decimal_str':
			
				//判断类型
				$pattern = '/^-?\d{1,'.$valid['i'].'}+[\.]?\d{0,'.$valid['d'].'}$/';
				preg_match_all($pattern, $value) or parent::c_die('G019', '传入值类型错误'.$key);
				
				//判断范围
				parent::calc($value, $valid['min'], 'comp') == -1 || parent::calc($value, $valid['max'], 'comp') == 1 and parent::c_die('G020', '传入值超出范围'.$key);
				
				break;
				
		}
		
	}
	
	//判断条件
	//$key:执行组号
	//$table_code:表代号
	//$where:条件数组
	private function judge_where ($key, $table_code, $where) {
		
		//二维遍历
		foreach ($where as $key1 => $value1) {
			
			if ($key1 === 'logic') {continue;}
			
			//判断字段
			array_key_exists($key1, parent::$_fields[$table_code]) or parent::c_die('G024', '传入参数错误data[c]['.$key.'][w]['.$key1.']');
			
			//判断是否为身份标识
			if (array_key_exists('isuid', parent::$_fields[$table_code][$key1])) {
				is_bool(parent::$_fields[$table_code][$key1]['isuid']) or parent::c_die('C016', 'mysql基本设置字段身份标识开关设置错误');
				parent::$_fields[$table_code][$key1]['isuid'] === true and parent::c_die('G023', '传入参数不允许任何操作data[c]['.$key.'][w]['.$key1.']');
			}
			
			//三维遍历
			foreach ($value1 as $key2 => $value2) {
				in_array($key2, array('logic', 'eq', 'neq', 'gt', 'egt', 'lt', 'elt', 'like', 'notlike', 'like', 'between', 'notbetween', 'in', 'notin')) or parent::c_die('G025', '传入参数错误data[c]['.$key.'][w]['.$key1.']['.$key2.']');
			}
		
		}
		
	}
	
	//判断显示的字段
	//$key:执行组号
	//$table_code:表代号
	//$select:显示的字段
	private function judge_select ($key, $table_code, $select) {
		
		$arrstr = explode(",",$select);
		
		//二维遍历
		foreach ($arrstr as $key1 => $value1) {
			
			//判断字段
			array_key_exists($value1, parent::$_fields[$table_code]) or parent::c_die('G027', '传入值错误data[c]['.$key.'][s]');
			
			//判断是否为身份标识
			if (array_key_exists('isuid', parent::$_fields[$table_code][$value1])) {
				is_bool(parent::$_fields[$table_code][$value1]['isuid']) or parent::c_die('C016', 'mysql基本设置字段身份标识开关设置错误');
				parent::$_fields[$table_code][$value1]['isuid'] === true and parent::c_die('G028', '传入值不允许任何操作data[c]['.$key.'][s]');
			}
			
			//判断是否允许查询
			if (array_key_exists('select', parent::$_fields[$table_code][$value1])) {
				is_bool(parent::$_fields[$table_code][$value1]['select']) or parent::c_die('C015', 'mysql基本设置字段允许查询开关设置错误');
				parent::$_fields[$table_code][$value1]['select'] === false and parent::c_die('G029', '传入值不允许查询操作data[c]['.$key.'][s]');
			}
			
		}
		
	}
	
	//判断显示的起始和数量
	//$key:执行组号
	//$table_code:表代号
	//$limit:显示的字段
	private function judge_limit ($key, $table_code, $limit) {
		
		$arrstr = explode(",",$limit);
		
		//判断参数数量
		count($arrstr) > 2 and parent::c_die('G033', '该值仅允许输入两个参数data[c]['.$key.'][l]');
		
		//二维遍历
		foreach ($arrstr as $key1 => $value1) {
			
			//判断类型
			is_string($value1) && ctype_digit($value1) or parent::c_die('G034', '传入值错误data[c]['.$key.'][l]['.$key1.']');
			
		}
		
	}
	
	//判断排序
	//$key:执行组号
	//$table_code:表代号
	//$order:排序数组
	private function judge_order ($key, $table_code, $order) {
		
		//二维遍历
		foreach ($order as $key1 => $value1) {
			
			//判断字段
			array_key_exists($key1, parent::$_fields[$table_code]) or parent::c_die('G030', '传入参数错误data[c]['.$key.'][o]['.$key1.']');
			
			//判断是否为身份标识
			if (array_key_exists('isuid', parent::$_fields[$table_code][$key1])) {
				is_bool(parent::$_fields[$table_code][$key1]['isuid']) or parent::c_die('C016', 'mysql基本设置字段身份标识开关设置错误');
				parent::$_fields[$table_code][$key1]['isuid'] === true and parent::c_die('G031', '传入参数不允许任何操作data[c]['.$key.'][o]['.$key1.']');
			}
			
			//判断值
			$value1 === '' || $value1 === 'desc' or parent::c_die('G032', '传入值错误data[c]['.$key.'][o]['.$key1.']');
			
		}
		
	}
	
	//（表/字段）代号转换为名称 返回str
	//$code:代号
	//$table_code:表代号
	//$field_code:字段代号
	private function code_to_name ($table_code, $field_code = '') {
		if ($field_code === '') {
			return parent::$_tables[$table_code]['0'];
		}else{
			return parent::$_fields[$table_code][$field_code]['0'];
		}
	}
	
	//sql语句条件处理
	//返回数组 $result['sql_where']（sql语句） $result['bindparam']（param语句）
	//$table_code:表代号
	//$data:条件数组
	//$debug:是否测试 当为true时不执行仅显示sql语句 true or false
	private function where_to_sql ($table_code, $data, $debug) {
		
		$result = array();
		$result['sql_where'] = '';
		$result['bindparam'] = '';
		$old_str = '(';
		
		//获取字段与字段的条件关系
		if (array_key_exists('logic', $data)) {
			
			if ($data['logic'] == 'and') {
				$sql_logic = 'and';
			}else{
				$sql_logic = 'or';
			}
			
		}else{
			
			$sql_logic = 'and';
			
		}
		
		//一维遍历
		foreach ($data as $key => $value) {
			
			if ($key === 'logic') {continue;}
			
			$debug or $i = 0;
			
			//获取条件字段名
			$field = self::code_to_name($table_code, $key);
			
			$result['sql_where'] .= '(';
			
			//获取字段的条件关系
			if (array_key_exists('logic', $value)) {
				
				if ($value['logic'] == 'and') {
					$field_logic = 'and';
				}else{
					$field_logic = 'or';
				}
				
			}else{
				
				$field_logic = 'and';
				
			}
			
			//二维遍历
			foreach ($value as $key1 => $value1) {
				
				if ($key1 === 'logic') {continue;}
				
				//设置条件
				switch ($key1) {
					
					//等于
					case 'eq':
					
						if ($debug) {
							
							$result['sql_where'] .= '`'.$field.'`=\''.$value1.'\' '.$field_logic.' ';
							
						}else{
							
							//设置sql_where
							$result['sql_where'] .= '`'.$field.'`=:'.$field.$i.' '.$field_logic.' ';
							
							//设置绑定
							$result['bindparam'] .= '$result->bindparam(\':'.$field.$i.'\',$data[\'w\'][\''.$key.'\'][\''.$key1.'\']);';
							
						}
						
						break;
						
					//不等于
					case 'neq':
						
						if ($debug) {
							
							$result['sql_where'] .= '`'.$field.'`<>\''.$value1.'\' '.$field_logic.' ';
							
						}else{
							
							//设置sql_where
							$result['sql_where'] .= '`'.$field.'`<>:'.$field.$i.' '.$field_logic.' ';
							
							//设置绑定
							$result['bindparam'] .= '$result->bindparam(\':'.$field.$i.'\',$data[\'w\'][\''.$key.'\'][\''.$key1.'\']);';
							
						}
						
						break;
						
					//大于
					case 'gt':
					
						if ($debug) {
							
							$result['sql_where'] .= '`'.$field.'`>\''.$value1.'\' '.$field_logic.' ';
							
						}else{
							
							//设置sql_where
							$result['sql_where'] .= '`'.$field.'`>:'.$field.$i.' '.$field_logic.' ';
							
							//设置绑定
							$result['bindparam'] .= '$result->bindparam(\':'.$field.$i.'\',$data[\'w\'][\''.$key.'\'][\''.$key1.'\']);';
							
						}
						
						break;
						
					//大于等于
					case 'egt':
					
						if ($debug) {
							
							$result['sql_where'] .= '`'.$field.'`>=\''.$value1.'\' '.$field_logic.' ';
							
						}else{
							
							//设置sql_where
							$result['sql_where'] .= '`'.$field.'`>=:'.$field.$i.' '.$field_logic.' ';
							
							//设置绑定
							$result['bindparam'] .= '$result->bindparam(\':'.$field.$i.'\',$data[\'w\'][\''.$key.'\'][\''.$key1.'\']);';
							
						}
						
						break;
						
					//小于
					case 'lt':
						
						if ($debug) {
							
							$result['sql_where'] .= '`'.$field.'`<\''.$value1.'\' '.$field_logic.' ';
							
						}else{
							
							//设置sql_where
							$result['sql_where'] .= '`'.$field.'`<:'.$field.$i.' '.$field_logic.' ';
							
							//设置绑定
							$result['bindparam'] .= '$result->bindparam(\':'.$field.$i.'\',$data[\'w\'][\''.$key.'\'][\''.$key1.'\']);';
							
						}
						
						break;
						
					//小于等于
					case 'elt':
						
						if ($debug) {
							
							$result['sql_where'] .= '`'.$field.'`<=\''.$value1.'\' '.$field_logic.' ';
							
						}else{
							
							//设置sql_where
							$result['sql_where'] .= '`'.$field.'`<=:'.$field.$i.' '.$field_logic.' ';
							
							//设置绑定
							$result['bindparam'] .= '$result->bindparam(\':'.$field.$i.'\',$data[\'w\'][\''.$key.'\'][\''.$key1.'\']);';
							
						}
						
						break;
						
					//模糊查询
					case 'like':
						
						if (is_array($value1)) {
							
							$result['sql_where'] .= '(';
							
							if ($debug) {
								
								//四维遍历
								foreach ($value1[0] as $value2) {
									$result['sql_where'] .= '`'.$field.'` like \''.$value2.'\' '.$value1[1].' ';
								}
								
							}else{
								
								//四维遍历
								foreach ($value1[0] as $key2 => $value2) {
									
									//设置sql_where
									$result['sql_where'] .= '`'.$field.'` like :'.$field.$i.' '.$value1[1].' ';
									
									//设置绑定
									$result['bindparam'] .= '$result->bindparam(\':'.$field.$i.'\',$data[\'w\'][\''.$key.'\'][\''.$key1.'\'][\'0\'][\''.$key2.'\']);';
									
									$i++;
									
								}
								
							}
							
							if ($value1[1] == 'and') {
								$result['sql_where'] = substr($result['sql_where'], 0, strlen($result['sql_where'])-5).') '.$field_logic.' ';
							}else{
								$result['sql_where'] = substr($result['sql_where'], 0, strlen($result['sql_where'])-4).') '.$field_logic.' ';
							}
							
						}else{
							
							if ($debug) {
								
								$result['sql_where'] .= '`'.$field.'` like \''.$value1.'\' '.$field_logic.' ';
								
							}else{
								
								//设置sql_where
								$result['sql_where'] .= '`'.$field.'` like :'.$field.$i.' '.$field_logic.' ';
								
								//设置绑定
								$result['bindparam'] .= '$result->bindparam(\':'.$field.$i.'\',$data[\'w\'][\''.$key.'\'][\''.$key1.'\']);';
								
							}
							
						}
						
						break;
						
					//模糊查询（反）
					case 'notlike':
						
						if ($debug) {
						
							$result['sql_where'] .= '`'.$field.'` not like \''.$value1.'\' '.$field_logic.' ';
							
						}else{
							
							//设置sql_where
							$result['sql_where'] .= '`'.$field.'` not like :'.$field.$i.' '.$field_logic.' ';
							
							//设置绑定
							$result['bindparam'] .= '$result->bindparam(\':'.$field.$i.'\',$data[\'w\'][\''.$key.'\'][\''.$key1.'\']);';
							
						}
						
						break;
						
					//区间查询
					case 'between':
						
						if ($debug) {
						
							$result['sql_where'] .= '`'.$field.'` between \''.$value1[0].'\' and \''.$value1[1].'\' '.$field_logic.' ';
							
						}else{
							
							$i++;
							
							//设置sql_where
							$result['sql_where'] .= '`'.$field.'` between :'.$field.($i-1).' and :'.$field.$i.' '.$field_logic.' ';
							
							//设置绑定
							$result['bindparam'] .= '$result->bindparam(\':'.$field.($i-1).'\',$data[\'w\'][\''.$key.'\'][\''.$key1.'\'][\'0\']);$result->bindparam(\':'.$field.$i.'\',$data[\'w\'][\''.$key.'\'][\''.$key1.'\'][\'1\']);';
							
						}
						
						break;
						
					//区间查询（反）
					case 'notbetween':
						
						if ($debug) {
						
							$result['sql_where'] .= '`'.$field.'` not between \''.$value1[0].'\' and \''.$value1[1].'\' '.$field_logic.' ';
							
						}else{
							
							$i++;
							
							//设置sql_where
							$result['sql_where'] .= '`'.$field.'` not between :'.$field.($i-1).' and :'.$field.$i.' '.$field_logic.' ';
							
							//设置绑定
							$result['bindparam'] .= '$result->bindparam(\':'.$field.($i-1).'\',$data[\'w\'][\''.$key.'\'][\''.$key1.'\'][\'0\']);$result->bindparam(\':'.$field.$i.'\',$data[\'w\'][\''.$key.'\'][\''.$key1.'\'][\'1\']);';
							
						}
						
						break;
						
					//IN查询
					case 'in':
						
						$result['sql_where'] .= '`'.$field.'` in (';
						
						//三维遍历
						foreach ($value1 as $key2 => $value2) {
							
							if ($debug) {
							
								$result['sql_where'] .= '\''.$value2.'\',';
								
							}else{
								
								//设置sql_where
								$result['sql_where'] .= ':'.$field.$i.',';
								
								//设置绑定
								$result['bindparam'] .= '$result->bindparam(\':'.$field.$i.'\',$data[\'w\'][\''.$key.'\'][\''.$key1.'\'][\''.$key2.'\']);';
								
								$i++;
								
							}
							
						}
						
						$result['sql_where'] = substr($result['sql_where'], 0, strlen($result['sql_where'])-1).') '.$field_logic.' ';
						
						break;
						
					//IN查询（反）
					case 'notin':
						
						$result['sql_where'] .= '`'.$field.'` not in (';
						
						//三维遍历
						foreach ($value1 as $key2 => $value2) {
							
							if ($debug) {
							
								$result['sql_where'] .= '\''.$value2.'\',';
								
							}else{
								
								//设置sql_where
								$result['sql_where'] .= ':'.$field.$i.',';
								
								//设置绑定
								$result['bindparam'] .= '$result->bindparam(\':'.$field.$i.'\',$data[\'w\'][\''.$key.'\'][\''.$key1.'\'][\''.$key2.'\']);';
								
								$i++;
								
							}
							
						}
						
						$result['sql_where'] = substr($result['sql_where'], 0, strlen($result['sql_where'])-1).') '.$field_logic.' ';
						
						break;
					
				}
				
				$debug or $i++;
				
			}
			
			//判断该次循环是否有条件设置
			if ($old_str != $result['sql_where']) {
				
				//字符串处理
				if ($field_logic == 'and') {
					$result['sql_where'] = substr($result['sql_where'], 0, strlen($result['sql_where'])-5).') '.$sql_logic.' ';
				}else{
					$result['sql_where'] = substr($result['sql_where'], 0, strlen($result['sql_where'])-4).') '.$sql_logic.' ';
				}
				
			}else{
				
				$result['sql_where'] = substr($result['sql_where'], 0, strlen($result['sql_where'])-1);
				
			}

			
			//保存当前状态 下一次循环用来判断是否有条件设置
			$old_str = $result['sql_where'].'(';
			
		}
		
		if ($result['sql_where']) {
			
			if ($sql_logic == 'and') {
				$result['sql_where'] = substr($result['sql_where'], 0, strlen($result['sql_where'])-5);
			}else{
				$result['sql_where'] = substr($result['sql_where'], 0, strlen($result['sql_where'])-4);
			}
			
		}
		
		return $result;
		
	}
	
}

?>