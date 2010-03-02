<?php 
if (!defined('FM_SECURITY')) die();

function _boot()
{	
	_loadConfig();
	
	define('FM_REQUEST_ID',sha1(FM_START_TIME._ip()));
	
	// 2nd includes 
	$__paths = _getPaths();
	array_pop($__paths);
	
	foreach ($__paths as $__path)
		if (file_exists($__file = $__path.'private/compatibility'.FM_PHP_EXTENSION))
			include $__file;
	
	foreach ($__paths as $__path)
		if (file_exists($__file = $__path.'private/function'.FM_PHP_EXTENSION))
			include $__file;
	
	_class('config',false);
	_class('extension',false);
	_class('log');
	
	list(config::$config,extension::$extension) = _loadConfig();
	
	// 3rd includes 
	foreach (_getPaths() as $__path)
		if (file_exists($__file = $__path.'private/boot'.FM_PHP_EXTENSION))
			include $__file;
	
	_class('cache');
	_class('l10n');
	_class('acl');
	_class('user');
	_class('header',false);
	_class('auth');
	_class('route');
	_class('template',false);
	_class('view',false);
	
	$httpGet = $_GET;
	if (array_key_exists(config::$config['clear']['key'],$httpGet))
		unset($httpGet[config::$config['clear']['key']]);
	
	$route = route::getView(array_key_exists('PATH_INFO',$_SERVER)?$_SERVER['PATH_INFO']:'/',$httpGet,config::$config['route']['magic_route']);
	$route = acl::routeControl(auth::getUser(),$route);
	
	return view::start($route);
}

function _shutdown()
{
	// shutdown includes 
	foreach (_getPaths() as $__path)
		if (file_exists($__file = $__path.'private/shutdown'.FM_PHP_EXTENSION))
			include $__file;
}

function _loadConfig()
{
	static $config = null;
	static $extension = null;
	
	if (is_array($config))
		return array($config,$extension);
	
	$build_key = sha1($_SERVER['SERVER_NAME'].$_SERVER['SERVER_PORT'].$_SERVER['SCRIPT_NAME']);
	$configCacheFile = FM_PATH_VAR."build/$build_key.config".FM_PHP_EXTENSION;
	
	if (file_exists($configCacheFile))
	{
		include $configCacheFile;
		
		if (is_array($config) && array_key_exists('clear',$config) && !_clear('config',$config['clear']))
		{
			if (!defined('FM_SITE_DIR'))
				define('FM_SITE_DIR',$fm_site_dir);
			
			return array($config,$extension);
		}
	}

	if (defined('FM_SITE_DIR'))
		return array(array(),array());
	
	
	// rebuild config
	$uriPart = array();
	$hostPart = explode('.',$_SERVER['SERVER_NAME']);
	
	if (count($hostPart)==1)
	{
		$uriPart['ext']  = null;
		$uriPart['sub']  = null;
		$uriPart['host'] = $hostPart[0];
	}
	else
	{
		$uriPart['ext']  = $hostPart[(count($hostPart)-1)];
		$uriPart['sub']  = implode('.',array_slice($hostPart,0,(count($hostPart)-2)));
		$uriPart['host'] = $hostPart[(count($hostPart)-2)];
	}
	
	$dirPart = explode('/', substr($_SERVER['SCRIPT_NAME'],1));
	
	array_pop($dirPart);
	$uriPart['dir'] = $dirPart;
	$uriPart['port'] = $_SERVER['SERVER_PORT'];
	
	$__dirs = array();
	while ($dir = implode('.',$uriPart['dir']))
	{
		array_pop($uriPart['dir']);
		
		$__dirs[] = array($uriPart['sub'],$uriPart['host'],$uriPart['ext'],$uriPart['port'],$dir);
		$__dirs[] = array(                $uriPart['host'],$uriPart['ext'],$uriPart['port'],$dir);
		$__dirs[] = array($uriPart['sub'],$uriPart['host'],                $uriPart['port'],$dir);
		$__dirs[] = array(                $uriPart['host'],                $uriPart['port'],$dir);
		$__dirs[] = array($uriPart['sub'],$uriPart['host'],$uriPart['ext'],                 $dir);
		$__dirs[] = array(                $uriPart['host'],$uriPart['ext'],                 $dir);
		$__dirs[] = array($uriPart['sub'],$uriPart['host'],                                 $dir);
		$__dirs[] = array(                $uriPart['host'],                                 $dir);
	};
	
	$__dirs[] = array($uriPart['sub'],$uriPart['host'],$uriPart['ext'],$uriPart['port']);
	$__dirs[] = array(                $uriPart['host'],$uriPart['ext'],$uriPart['port']);
	$__dirs[] = array($uriPart['sub'],$uriPart['host'],                $uriPart['port']);
	$__dirs[] = array(                $uriPart['host'],                $uriPart['port']);
	$__dirs[] = array($uriPart['sub'],$uriPart['host'],$uriPart['ext']                 );
	$__dirs[] = array(                $uriPart['host'],$uriPart['ext']                 );
	$__dirs[] = array($uriPart['sub'],$uriPart['host']                                 );
	$__dirs[] = array(                $uriPart['host']                                 );
	
	$uriPart['dir'] = $dirPart;
	while ($dir = implode('.',$uriPart['dir']))
	{
		array_pop($uriPart['dir']);
		
		$__dirs[] = array($uriPart['port'],$dir);
		$__dirs[] = array(                 $dir);
	};
	
	$__dirs[] = array($uriPart['port']);
	$__dirs[] = array('default');
	
	$__dirs =  array_map('array_filter',$__dirs);
	
	foreach ($__dirs as $key=>$value)
	{
		unset($__dirs[$key]);
		$__dirs[implode('.',$value)] = FM_PATH_SITE.implode('.',$value);
	}
		
	$__config = array();
	foreach ($__dirs as $key=>$dir)
	{
		if (!defined('FM_SITE_DIR'))
		{
			if (file_exists($file = $dir.FM_PHP_EXTENSION))
			{
				$c = array();
				include $file;
				$__config = array_replace_recursive($c,$__config);
				
				if (defined('FM_SITE_DIR'))
				{
					if (file_exists($file = FM_PATH_SITE.FM_SITE_DIR.'private/config'.FM_PHP_EXTENSION))
					{
						$c = array();
						include $file;
						$__config = array_replace_recursive($c,$__config);
					}
					break;
				}
				
			}
			else
			{
				if (is_dir($dir))
				{
					define('FM_SITE_DIR',"$key/");
					if (file_exists($file = "$dir/".'private/config'.FM_PHP_EXTENSION))
					{
						$c = array();
						include $file;
						$__config = array_replace_recursive($c,$__config);
					}
				}
			}
		}
	}
		
	if (!defined('FM_SITE_DIR'))
	{
		header('HTTP/1.1 500 Internal Server Error');
		print '<html><head><title>500 Application Error</title></head><body><h1>Application Error</h1><p>The Firedmint application could not be launched.</p></body></html>';
		die();
	}
	
	if (file_exists($file = FM_PATH_SITE.'default/config'.FM_PHP_EXTENSION))
	{
		$c = array();
		include $file;
		$__config = array_replace_recursive($c,$__config);
	}
	
	// load extensions 
	$__extension = array('require'=>array(),'use'=>array());
	
	$__dirs = array();
	$__dirs[] = FM_PATH_SITE.FM_SITE_DIR;
	$__dirs[] = FM_PATH_SITE.'all/';
	
	foreach ($__dirs as $dir)
		if (file_exists($file = $dir.'extension'.FM_PHP_EXTENSION))
		{
			$e = array();
			include $file;
			$e += array('require'=>array(),'use'=>array());
			$__extension['require'] = array_merge($__extension['require'],$e['require']);
			$__extension['use'] = array_merge($__extension['use'],$e['use']);
		}
		
	
	$ext_list = array();
	
	$__extension['use'] = array_map('strtolower',array_map('trim',$__extension['use']));
	$__extension['require'] = array_map('strtolower',array_map('trim',$__extension['require']));
	
	
	foreach ($__extension['use'] as $ext)
		$ext_list[$ext] = array('required_by'=>array(),'used_by'=>array('@'));

	foreach ($__extension['require'] as $ext)
		$ext_list[$ext] = array('required_by'=>array('@'),'used_by'=>array());
	
	$extension_list = array();
	
	while(count($ext_list))
	{
		foreach ($ext_list as $ext=>$values)
		{
			if (!array_key_exists($ext,$ext_list))
			{
				if (is_dir(FM_PATH_SITE."all/extension/$ext/") || is_dir(FM_PATH_SITE.FM_SITE_DIR."extension/$ext/"))
				{
					$ext_list[$ext]['required_by'] = $ext_list[$ext]['required_by'];
					$ext_list[$ext]['used_by'] = $ext_list[$ext]['used_by'];
	
					if (file_exists(FM_PATH_SITE.FM_SITE_DIR.'extension/'."$ext/"))
						$ext_list[$ext]['path'] = FM_PATH_SITE.FM_SITE_DIR."extension/$ext/";
					else
						$ext_list[$ext]['path'] = FM_PATH_SITE."all/extension/$ext/";
					
					$e = array();
					if (file_exists($ext_list[$ext]['path'].'extension'.FM_PHP_EXTENSION))
						include $ext_list[$ext]['path'].'extension'.FM_PHP_EXTENSION;
					
					$e += array('require'=>array(),'use'=>array());
					$e['use'] = array_map('strtolower',array_map('trim',$e['use']));
					$e['require'] = array_map('strtolower',array_map('trim',$e['require']));
				
					$ext_list[$ext]['require'] = $e['require'];
					$ext_list[$ext]['use'] = $e['use'];
					
					foreach ($ext_list[$ext]['require'] as $require)
					{
						if (array_key_exists($require,$ext_list))
						{
							$ext_list[$require]['required_by'][] = $ext;
							$ext_list[$ext]['require'] = array_unique(array_merge($ext_list[$ext]['require'],$ext_list[$require]['require']));
						}
						else
						{
							if (!array_key_exists($require,$ext_list))
								$ext_list[$require] = array('required_by'=>array(),'used_by'=>array());
							
							$ext_list[$require]['required_by'][] = $ext;
						}
					}
					foreach ($ext_list[$ext]['use'] as $use)
					{
						if (array_key_exists($use,$ext_list))
							$ext_list[$use]['used_by'][] = $ext;
						else
						{
							if (!array_key_exists($use,$ext_list))
								$ext_list[$use] = array('required_by'=>array(),'used_by'=>array());
							
							$ext_list[$use]['used_by'][] = $ext;
						}
					}
				}
				else
				{
					$ext_list[$ext] = array(
						'required_by' => $ext_list[$ext]['required_by'],
						'used_by'     => $ext_list[$ext]['used_by'],
						'path'        => null,
						'require'     => array(),
						'use'         => array(),
					);
				}
			}
			unset($ext_list[$ext]);
		}
	}
	
	$ext_list = array();
	
	do
	{
		$ko = false;
		foreach ($ext_list as $ext=>$values)
		{
			if (is_null($values['path']))
			{
				foreach ($values['required_by'] as $required_by)
				{
					if ($required_by!='@fm@' && !is_null($ext_list[$required_by]['path']))
					{
						$ext_list[$required_by]['path'] = null;
						$ko = true;
					}
				}
			}
		}
	}while ($ko);
	
	$main_extension_list = array();
	
	foreach ($ext_list as $ext=>$values)
	{	
		if (!is_null($values['path']))
		{
			foreach ($values['required_by'] as $required_by)
			{
				if ($required_by=='@fm@')
				{
					$main_extension_list[$ext] = $values;
				}
			}
		}
	}
	
	$extension = array();
	while(count($main_extension_list)>0)
	{
		foreach ($main_extension_list as $ext=>$values)
		{
			foreach ($values['require'] as $require)
			{
				if (!array_key_exists($require,$extension) && !array_key_exists($require,$main_extension_list))
				{
					$main_extension_list[$require] = $ext_list[$require];
				}
			}
			
			foreach ($values['use'] as $use)
			{
				if (!array_key_exists($use,$extension) && !array_key_exists($use,$main_extension_list) && !is_null($ext_list[$use]['path']))
				{
					$main_extension_list[$use] = $ext_list[$use];
				}
			}
			
			$extension[$ext] = $values;
			
			unset($main_extension_list[$ext]);
			
		}
	}
	
	foreach ($extension as $ext=>$values)
	{
		if (file_exists($file = $values['path'].FM_FILE_CONFIG.FM_PHP_EXTENSION))
		{
			$c = array();
			include $file;
			$__config = array_replace_recursive($c,$__config);
		}
	}
	
	if (file_exists($file = FM_PATH_CORE.'private/config'.FM_PHP_EXTENSION))
	{
		$c = array();
		include $file;
		$__config = array_replace_recursive($c,$__config);
	}
	
	_createDir($configCacheFile);
	file_put_contents($configCacheFile,FM_PHP_STARTFILE.'$config = '.var_export($__config,true).';'.PHP_EOL.'$extension = '.var_export($extension,true).';'.PHP_EOL.'$fm_site_dir = \''.FM_SITE_DIR.'\';',LOCK_EX);
	
	$config = $__config;
	
	return array($config,$extension);
}

function _getPaths($file = '.', $forced = false)
{
	static $paths = array();
	
	if ($file[0]=='/')
		$file = substr($file,1);
	
	if (array_key_exists($file,$paths) && !$forced)
		return $paths[$file];
	
	if (!array_key_exists('.',$paths) || $forced)
	{
		list($config,$extension) = _loadConfig();
				
		$paths['.'] = array();
		$paths['.'][] = FM_PATH_SITE.FM_SITE_DIR;
		$paths['.'][] = FM_PATH_SITE.'all/';
	
		foreach($extension as $data)
			$paths['.'][] = $data['path'];
		
		$paths['.'][] = FM_PATH_CORE;
	}
	
	if ($file=='.')
		return $paths['.'];
	
	$paths[$file] = array();
	foreach ($paths['.'] as $path)
		if (file_exists($path.$file))
			$paths[$file][] = $path.$file;
	
	return $paths[$file];
}

function _find($file,$forced = false)
{
	$paths = _getPaths($file,$forced);
	if (count($paths)>0)
		return $paths[0];
}

function _class($class, $load = true)
{
	$class = strtolower($class);
	if (!class_exists($class,false) && _find("private/class/$class".FM_PHP_EXTENSION))
			include_once _find("private/class/$class".FM_PHP_EXTENSION);
	
	if ($load==true)
	{
		if (method_exists($class,'factory'))
			return call_user_func(array($class,'factory'));
		else
			return new $class();
	}
}

function _subClass($parrent,$class,$load = true)
{
	$parrent = strtolower($parrent);
	$class = strtolower($class);
	
	if (_find("private/class/$parrent/$class".FM_PHP_EXTENSION))
		include_once _find("private/class/$parrent/$class".FM_PHP_EXTENSION);
	
	if ($load==true)
	{
		if (method_exists($class,'factory'))
			return call_user_func(array($class,'factory'));
		else
			return new $class();
	}
}

function _createDir($path)
{
	$path = dirname($path);
	
	if (is_dir($path))
		return true;
		
	return mkdir($path,0777,true);
}

function _deleteDir($dir) 
{
	if(!$dir_handle = @opendir($dir)) 
		return; 

	while (($file = readdir($dir_handle)) !== false) 
	{ 
		if($file == '.' || $file == '..') 
			continue; 

		if (is_dir("$dir/$file"))
			_deleteDir("$dir/$file");
		elseif(is_file("$dir/$file"))
			unlink("$dir/$file");  
	}

	closedir($dir_handle);  
    
	return @rmdir($dir);; 
} 

function _ip()
{
	static $ip = false;
	
	if ($ip!=false)
		return $ip;

	if(array_key_exists('HTTP_CLIENT_IP',$_SERVER) && !empty($_SERVER['HTTP_CLIENT_IP']))
		$ip = $_SERVER['HTTP_CLIENT_IP'];

	if(array_key_exists('HTTP_X_FORWARDED_FOR',$_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		$ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
		if($ip)
		{
			array_unshift($ips, $ip);
			$ip = false;
		}

		for($i = 0; $i < count($ips); $i++)
		{
			if(!preg_match("/^(10|172\.16|192\.168)\./i", $ips[$i]))
			{
				if(ip2long($ips[$i]) != false)
				{
					$ip = $ips[$i];
					break;
				}
			}
		}
	}
	return ($ip?$ip:$_SERVER['REMOTE_ADDR']);
}

function _clear($name,$config = null)
{
	if (!is_array($config) || !array_key_exists('key',$config))
		if (class_exists('config'))
			$config = config::$config['clear'];
		else
			return false;
	
	if (!array_key_exists($config['key'],$_GET))
		return false;
	
	if (!array_key_exists($name,$config))
		$config[$name] = $name;
	
	return preg_match("/^({$_GET[$config['key']]})$/",$name) || preg_match("/^({$_GET[$config['key']]})$/",'all');
}

function _t($lang,$key,$args = array(),$return = false)
{
	if (!class_exists('l10n') || !($value = l10n::get($lang,$key,$args)))
		$value = $key;
	
	if ($return)
		return $value;
	
	return _print($value);
}

function _l($key,$args = array(),$return = false)
{
	if (class_exists('l10n'))
		return _t(l10n::$lang,$key,$args,$return);
	
	if ($return)
		return $key;
	
	return _print($key);
}

function _print($value,$return = false)
{
	if ($return)
		return _echo($value,true);
		
	return print _echo($value,true);
}

function _echo($value,$return = false)
{
	if ($return)
		return '<?php print '.var_export($value).'; ?>';
		
	echo '<?php print '.var_export($value).'; ?>';
}
