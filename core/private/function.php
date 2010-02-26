<?php 
if (!defined('FM_SECURITY')) die();

function _boot()
{
	list($__config,$__extension) = _loadConfig();

	define('FM_BUILD_ID',sha1(FM_BUILD_KEY.var_export($__extension,true)));
	
	_class('kernel');
	list(kernel::$config,kernel::$extension) = _loadConfig();
	
	_class('log');
	_class('cache',true);
	_class('user',true);
	_class('auth',true);
	_class('acl',true);
	_class('controller');
	_class('route');
}

function _loadConfig()
{
	static $config = null;
	static $extension = null;
	
	if (!is_null($config))
	{
		return array($config,$extension);
	}
	
	$configCacheFile = FM_PATH_VAR.FM_PATH_BUILD.FM_BUILD_KEY.'.'.FM_FILE_CONFIG.FM_PHP_EXTENSION;
	
	if (file_exists($configCacheFile))
	{
		include $configCacheFile;
		
		if (!array_key_exists($config['clear']['key'],$_GET) || !in_array($_GET[$config['clear']['key']],array($config['clear']['config'],$config['clear']['all'])))
		{
			if (!defined('FM_SITE_DIR'))
				define('FM_SITE_DIR',$fm_site_dir);
			
			return array($config,$extension);
		}
	}

	if (defined('FM_SITE_DIR'))
		return array(array(),array());
	
	// rebuild config
	
	$o = array();
	$u = array();
	$tmp_host = explode('.',$_SERVER['SERVER_NAME']);
	
	if (count($tmp_host)==1)
	{
		$u['ext'] = null;
		$u['sub'] = null;
		$u['host'] = $tmp_host[0];
	}
	else
	{
		$u['ext'] = $tmp_host[(count($tmp_host)-1)];
		$u['sub'] = implode('.',array_slice($tmp_host,0,(count($tmp_host)-2)));
		$u['host'] = $tmp_host[(count($tmp_host)-2)];
	}
	
	$tmp_dir = explode('/', substr($_SERVER['SCRIPT_NAME'],1));
	
	array_pop($tmp_dir);
	$u['dir'] = $tmp_dir;
	$u['port'] = $_SERVER['SERVER_PORT'];
	
	do
	{
		$dir = (count($u['dir'])?'.':null).implode('.',$u['dir']);
		
		foreach (array('.'.$u['port'],'') as $port)
		{
			foreach (array($u['ext'],'') as $ext)
			{
				if (strlen($ext))
					$ext = ".$ext";
				
				foreach (array($u['sub'],'') as $sub)
				{
					if (strlen($sub))
						$sub = "$sub.";
					$o["{$sub}{$u['host']}{$ext}{$port}{$dir}"] = FM_PATH_SITE."{$sub}{$u['host']}{$ext}{$port}{$dir}";
				}
			}
		}
	
	}while (array_pop($u['dir']));
	
	$u['dir'] = $tmp_dir;
	do
	{
		$dir = implode('.',$u['dir']);
		foreach (array($_SERVER['SERVER_PORT'],'') as $port)
		{
			if (strlen($port) && $dir)
					$port = "$port.";
			
			if ($port || $dir)
				$o["{$port}{$dir}"] = FM_PATH_SITE."{$port}{$dir}";	
		}
	}while (array_pop($u['dir']));
	
	$o[substr(FM_PATH_DEFAULT,0,-1)] = substr(FM_PATH_SITE.FM_PATH_DEFAULT,0,-1);
	
	$c = array();
	
	foreach ($o as $key=>$dir)
	{
		if (!defined('FM_SITE_DIR'))
		{
			$file = $dir.FM_PHP_EXTENSION;
			if (file_exists($file))
			{
				$tmp_c = $c;
				$c = array();
				include $file;
				$c = array_replace_recursive($c,$tmp_c);
			}
			if (defined('FM_SITE_DIR'))
			{
				$file = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_PRIVATE.FM_FILE_CONFIG.FM_PHP_EXTENSION;
				if (file_exists($file))
				{				
					$tmp_c = $c;
					$c = array();
					include $file;
					$c = array_replace_recursive($c,$tmp_c);
				}
			}
			else
			{
				$file = "$dir/".FM_PATH_PRIVATE.FM_FILE_CONFIG.FM_PHP_EXTENSION;
				if (file_exists($file))
				{
					define('FM_SITE_DIR',"$key/");
					$tmp_c = $c;
					$c = array();
					include $file;
					$c = array_replace_recursive($c,$tmp_c);
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
	
		
	$file = FM_PATH_SITE.FM_PATH_ALL.FM_PATH_PRIVATE.FM_FILE_CONFIG.FM_PHP_EXTENSION;
	if (file_exists($file))
	{
		$tmp_c = $c;
		$c = array();
		include $file;
		$c = array_replace_recursive($c,$tmp_c);
	}

	
	// load extensions 
	$e = array('require'=>array(),'use'=>array());
	
	$file = FM_PATH_SITE.FM_SITE_DIR.FM_FILE_EXTENSION.FM_PHP_EXTENSION;
	if (file_exists($file))
	{
		$tmp_e = $e;
		$e = array();
		include $file;
		$e += array('require'=>array(),'use'=>array());
		$e['require'] = array_merge($tmp_e['require'],$e['require']);
		$e['use'] = array_merge($tmp_e['use'],$e['use']);
	}
	
	$file = FM_PATH_SITE.FM_PATH_ALL.FM_FILE_EXTENSION.FM_PHP_EXTENSION;
	if (file_exists($file))
	{
		$tmp_e = $e;
		$e = array();
		include $file;
		$e += array('require'=>array(),'use'=>array());
		$e['require'] = array_merge($tmp_e['require'],$e['require']);
		$e['use'] = array_merge($tmp_e['use'],$e['use']);
	}
	
	$file = FM_PATH_CORE.FM_SITE_DIR.FM_FILE_EXTENSION.FM_PHP_EXTENSION;
	if (file_exists($file))
	{
		$tmp_e = $e;
		$e = array();
		include $file;
		$e += array('require'=>array(),'use'=>array());
		$e['require'] = array_merge($tmp_e['require'],$e['require']);
		$e['use'] = array_merge($tmp_e['use'],$e['use']);
	}
	
	$ext_list = array();
	
	$e['use'] = array_map('strtolower',array_map('trim',$e['use']));
	$e['require'] = array_map('strtolower',array_map('trim',$e['require']));
	
	
	foreach ($e['use'] as $ext)
		$ext_list[$ext] = array('required_by'=>array(),'used_by'=>array('@fm@'));

	foreach ($e['require'] as $ext)
		$ext_list[$ext] = array('required_by'=>array('@fm@'),'used_by'=>array());
	
	$extension_list = array();
	
	while(count($ext_list))
	{
		foreach ($ext_list as $ext=>$values)
		{
			if (!array_key_exists($ext,$ext_list))
			{
				if (file_exists(FM_PATH_SITE.FM_PATH_ALL.FM_PATH_EXTENSION.$ext."/")||file_exists(FM_PATH_SITE.FM_SITE_DIR.FM_PATH_EXTENSION.$ext."/"))
				{
					$ext_list[$ext]['required_by'] = $ext_list[$ext]['required_by'];
					$ext_list[$ext]['used_by'] = $ext_list[$ext]['used_by'];
	
					if (file_exists(FM_PATH_SITE.FM_SITE_DIR.FM_PATH_EXTENSION."$ext/"))
						$ext_list[$ext]['path'] = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_EXTENSION."$ext/";
					else
						$ext_list[$ext]['path'] = FM_PATH_SITE.FM_PATH_ALL.FM_PATH_EXTENSION."$ext/";
					
					$e = array();
					if (file_exists($ext_list[$ext]['path'].FM_FILE_EXTENSION.FM_PHP_EXTENSION))
						include $ext_list[$ext]['path'].FM_FILE_EXTENSION.FM_PHP_EXTENSION;
					
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
					
					//$this->error("Extension not found : ".$ext);
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
		$path = $values['path'];
		if (file_exists($path.FM_FILE_CONFIG.FM_PHP_EXTENSION))
		{
			$tmp_c = $c;
			$c = array();
			include $path.FM_PATH_PRIVATE.FM_FILE_CONFIG.FM_PHP_EXTENSION;
			$c = array_replace_recursive($c,$tmp_c);
		}
	}
	
	$file = FM_PATH_CORE.FM_PATH_PRIVATE.FM_FILE_CONFIG.FM_PHP_EXTENSION;
	if (file_exists($file))
	{
		$tmp_c = $c;
		$c = array();
		include $file;
		$c = array_replace_recursive($c,$tmp_c);
	}
	
	_createDir($configCacheFile);
	file_put_contents($configCacheFile,FM_PHP_STARTFILE.'$config = '.var_export($c,true).';'.PHP_EOL.'$extension = '.var_export($extension,true).';'.PHP_EOL.'$fm_site_dir = \''.FM_SITE_DIR.'\';',LOCK_EX);
	
	$config = $c;
	
	return array($c,$extension);
}

function _class($class, $load = false)
{
	if (!class_exists($class,false))
	{
		list($config,$extension) = _loadConfig();
		
		$buildClassFile = FM_PATH_VAR.FM_PATH_BUILD.FM_SITE_DIR.FM_BUILD_ID.".$class".FM_PHP_EXTENSION;
		
		if ((array_key_exists($config['clear']['key'],$_GET)
		&& in_array($_GET[$config['clear']['key']],array($config['clear']['build'],$config['clear']['all'])))
		|| !file_exists($buildClassFile))
			_build($class);
			
		include_once $buildClassFile;
	}
	if ($load==true)
	{
		if (method_exists($class,'factory'))
			return call_user_func(array($class,'factory'));
		else
			return new $class();
	}
}

function _subClass($parrent,$class,$load = false)
{
	$parrent = strtolower($parrent);
	$class = strtolower($class);
	
	if (_find(FM_PATH_PRIVATE."$parrent/$class".FM_PHP_EXTENSION))
		include_once _find(FM_PATH_PRIVATE."$parrent/$class".FM_PHP_EXTENSION);
	
	if ($load==true)
	{
		if (method_exists($class,'factory'))
			return call_user_func(array($class,'factory'));
		else
			return new $class();
	}
}

function _find($file,$forced = false)
{
	static $finded  = array();
	
	if ($file[0]=='/')
		$file = substr($file,1);
	
	if (array_key_exists($file,$finded) && $forced==false)
		return $finded[$file];
	
	$paths = array();
	
	$paths[] = FM_PATH_SITE.FM_SITE_DIR.$file;
	$paths[] = FM_PATH_SITE.FM_PATH_ALL.$file;
	foreach (array_keys(kernel::$extension) as $ext)
		$paths[] = kernel::$extension[$ext]['path'].$file;
	
	$paths[] = FM_PATH_CORE.$file;
	
	foreach ($paths as $path)
	{
		if (file_exists($path))
		{
			$finded[$file] = $path;
			return $path;
		}
	}
	
	$finded[$file] = null;
	return null;
}

function _build($class)
{
	list($config,$extension) = _loadConfig();
	
	$build_file = FM_PATH_VAR.FM_PATH_BUILD.FM_SITE_DIR.FM_BUILD_ID.".$class".FM_PHP_EXTENSION; 

	$files = array();
	$paths = array();
	$paths[] = FM_PATH_CORE.FM_PATH_BUILDER."$class/";
	
	foreach (array_reverse(array_keys($extension)) as $ext)
		$paths[] = $extension[$ext]['path'].FM_PATH_BUILDER."$class/";
		
	
	$paths[] = FM_PATH_SITE.FM_PATH_ALL.FM_PATH_BUILDER."$class/";
	$paths[] = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_BUILDER."$class/";
	
	foreach ($paths as $dir)
	{
		if (is_dir($dir))
		{
			$dir_handle = @opendir($dir);
			while (($file = readdir($dir_handle)) !== false) 
			{
				if (preg_match('/^([a-z_][a-z0-9_]*)\.var\.php$/',$file,$matches))
				{
					$files['var'][$matches[1]] = $dir.$file;
				}
				elseif (preg_match('/^([a-z_][a-z0-9_]*)\.method\.php$/',$file,$matches))
				{
					$files['method'][$matches[1]] = $dir.$file;
				}
				
			}
			closedir($dir_handle);
		}
	}
	
	$out = FM_PHP_STARTFILE."class $class { ".PHP_EOL;
	
	if (array_key_exists('var',$files))
	{
		foreach ($files['var'] as $file)
		{
			$file_content =  file_get_contents($file);
			$matches = array();
			if (preg_match('/(static|var|public|protected|private)(.*;)/',$file_content,$matches))
				$out .= $matches[0].PHP_EOL;
		}
	}
	
	if (array_key_exists('method',$files))
	{
		foreach ($files['method'] as $file)
		{
			$file_content =  file_get_contents($file);
			$matches = array();
			if (preg_match('/((static|function|public|protected|private).*})[^}]*$/xs',$file_content,$matches))
				$out .= $matches[1].PHP_EOL;
		}
	}
	
	$out .= '}'.PHP_EOL;
	_createDir($build_file);
	file_put_contents($build_file,$out,LOCK_EX);
}

function _errorHandler($errno, $errstr, $errfile, $errline) {
	switch ($errno) {
		case E_ERROR:
		case E_USER_ERROR:
				$errors = "Fatal Error";
				log::error($errstr,array('no'=>$errno,'type'=>$error,'file'=>$errfile,'line'=> $errline));
				return false;
			break;
		case E_NOTICE:
		case E_USER_NOTICE:
				$errors = "Notice";
			break;
		case E_WARNING:
		case E_USER_WARNING:
				$errors = "Warning";
			break;

		default:
				$errors = "Unknown";
		break;
	}
	
	log::notice($errstr,array('no'=>$errno,'type'=>$errors,'file'=>$errfile,'line'=> $errline));
	return false;
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