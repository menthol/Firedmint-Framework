<?php 
if (!defined('FM_SECURITY')) die();

function _boot()
{	
	_loadConfig();
	
	define('FM_REQUEST_ID',sha1(FM_START_TIME._ip()));
	
	// 2nd includes 
	$__paths = _getPaths('private/compatibility.php');
	array_pop($__paths);
	foreach ($__paths as $__file)
		include $__file;
	
	$__paths = _getPaths('private/function.php');
	array_pop($__paths);
	foreach ($__paths as $__file)
		include $__file;
		
	_class('config',false);
	_class('extension',false);
	list(config::$config,extension::$extension) = _loadConfig();
	
	_getPaths('.',true);
	
	_class('log');
	_class('cache');
	_class('model');
	_class('modelMagic');
	_class('event');
	_class('html');
	_class('l10n');
	_class('header',false);
	_class('validator',false);
	_class('user');
	_class('auth');
	_class('acl');
	_class('route');
	_class('template',false);
	_class('view',false);
	
	// 3rd includes 
	foreach (_getPaths('private/boot.php') as $__file)
		include $__file;
	
	_class('form');
	
	$httpGet = $_GET;
	
	if (isset($httpGet[config::$config['clear']['key']]))
		unset($httpGet[config::$config['clear']['key']]);
	
	$route = route::getView(isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'/',$httpGet,config::$config['route']['magic_route']);
	$route = acl::routeControl(auth::getUser(),$route);
	
	route::$pageRoute = $route;
	
	return view::start($route);
}

function _shutdown()
{
	// shutdown includes 
	foreach (_getPaths('private/shutdown.php') as $__file)
		include $__file;
}

function _loadConfig()
{
	static $config = null;
	static $extension = null;
	
	if (is_array($config))
		return array($config,$extension);
	
	$build_key = sha1($_SERVER['SERVER_NAME'].$_SERVER['SERVER_PORT'].$_SERVER['SCRIPT_NAME']);
	$configCacheFile = FM_PATH_VAR."build/$build_key.config.php";
	
	if (file_exists($configCacheFile))
	{
		include $configCacheFile;
		
		if (is_array($config) && isset($config['clear']) && !_clear('config',$config['clear']))
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
			if (file_exists($file = $dir.'.php'))
			{
				$c = array();
				include $file;
				$__config = array_replace_recursive($c,$__config);
				
				if (defined('FM_SITE_DIR'))
				{
					if (file_exists($file = FM_PATH_SITE.FM_SITE_DIR.'private/config.php'))
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
					if (file_exists($file = "$dir/".'private/config.php'))
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
	
	if (file_exists($file = FM_PATH_SITE.'default/config.php'))
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
		if (file_exists($file = $dir.'extension.php'))
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
			if (!isset($ext_list[$ext]))
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
					if (file_exists($ext_list[$ext]['path'].'extension.php'))
						include $ext_list[$ext]['path'].'extension.php';
					
					$e += array('require'=>array(),'use'=>array());
					$e['use'] = array_map('strtolower',array_map('trim',$e['use']));
					$e['require'] = array_map('strtolower',array_map('trim',$e['require']));
				
					$ext_list[$ext]['require'] = $e['require'];
					$ext_list[$ext]['use'] = $e['use'];
					
					foreach ($ext_list[$ext]['require'] as $require)
					{
						if (isset($ext_list[$require]))
						{
							$ext_list[$require]['required_by'][] = $ext;
							$ext_list[$ext]['require'] = array_unique(array_merge($ext_list[$ext]['require'],$ext_list[$require]['require']));
						}
						else
						{
							if (!isset($ext_list[$require]))
								$ext_list[$require] = array('required_by'=>array(),'used_by'=>array());
							
							$ext_list[$require]['required_by'][] = $ext;
						}
					}
					foreach ($ext_list[$ext]['use'] as $use)
					{
						if (isset($ext_list[$use]))
							$ext_list[$use]['used_by'][] = $ext;
						else
						{
							if (!isset($ext_list[$use]))
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
				if (!isset($extension[$require]) && !isset($main_extension_list[$require]))
				{
					$main_extension_list[$require] = $ext_list[$require];
				}
			}
			
			foreach ($values['use'] as $use)
			{
				if (!isset($extension[$use]) && !isset($main_extension_list[$use]) && !is_null($ext_list[$use]['path']))
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
		if (file_exists($file = $values['path'].'private/config.php'))
		{
			$c = array();
			include $file;
			$__config = array_replace_recursive($c,$__config);
		}
	}
	
	if (file_exists($file = FM_PATH_CORE.'private/config.php'))
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
	
	if ($forced)
		$paths = array();
			
	if ($file[0]=='/')
		$file = substr($file,1);
	
	if (isset($paths[$file]))
		return $paths[$file];
		

	
	if (!isset($paths['.']))
	{
		list($config,$extension) = _loadConfig();
				
		$paths['.'] = array();
		$paths['.'][] = FM_PATH_SITE.FM_SITE_DIR;
		$paths['.'][] = FM_PATH_SITE.'all/';
	
		foreach($extension as $data)
			$paths['.'][] = $data['path'];
		
		if (class_exists('config'))
			if (!empty(config::$config['view']['template']))
				if (is_dir($path = FM_PATH_SITE.FM_SITE_DIR.'template/'.config::$config['view']['template'].'/'))
					$paths['.'][] = $path;
				else
					$paths['.'][] = FM_PATH_SITE.'all/template/'.config::$config['view']['template'].'/';
		
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
	if (!class_exists($class,false) && _find("private/class/$class.php"))
		include_once _find("private/class/$class.php");
	
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
	
	if (_find("private/class/$parrent/$class.php"))
		include_once _find("private/class/$parrent/$class.php");
	
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
	$path = str_replace('\\', '/',dirname($path));

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

	if(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']))
		$ip = $_SERVER['HTTP_CLIENT_IP'];

	if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
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
	if (!is_array($config) || !isset($config['key']))
		if (class_exists('config'))
			$config = config::$config['clear'];
		else
			return false;
	
	if (!isset($_GET[$config['key']]))
		return false;
	
	if (!isset($config[$name]))
		$config[$name] = $name;
	
	return preg_match("/^({$_GET[$config['key']]})$/",$name) || preg_match("/^({$_GET[$config['key']]})$/",'all');
}

function _t($lang,$key,$args = array())
{
	if (!class_exists('l10n') || !($value = l10n::get($lang,$key,$args)))
		$value = $key;
	
	return $value;
}

function _l($key,$args = array())
{
	if (class_exists('l10n'))
		return _t(l10n::$lang,$key,$args);
	
	return $key;
}

function _path($path = null)
{
	if ($path[0]=='/')
		$path = substr($path,1);
	
	$base_uri = (str_replace('\\', '/',dirname($_SERVER['SCRIPT_NAME']))=='/'?null:dirname($_SERVER['SCRIPT_NAME'])).'/';
	
	return $base_uri.$path;	
}

function _url($view,$arguments = array(), $decorator = array())
{
	$route = route::getUrl($view,$arguments,$decorator,config::$config['route']['magic_route']);
	if ($route=='/')
		return _path();
	
	return _path(config::$config['route']['url_base'].$route);
}

function _pageView()
{
	return route::$pageRoute[0];
}

function _pageStatus()
{
	return route::$pageRoute[1];
}

function _pageExtension()
{
	return route::$pageRoute[2];
}

function _pageData()
{
	return route::$pageRoute[3];
}

function _pageEnvironment()
{
	return route::$pageRoute[4];
}

function _attribute($data)
{
	return addslashes($data);
}

function _redirect($url,$code = 302)
{
	header::set('Status',$code,true);
	header::set('Location',$url,true);
}

function _thisPage()
{
	return _url(_pageView(),_pageData());
}

function _model($table)
{
	$return = _class('modelMagic');
	$return->setModel($table);
	
	return $return;
}

// view functions

function t($lang,$key,$args = array(),$addslashes = false)
{
	if ($args===true)
	{
		$args = array();
		$addslashes = true;
	}
	if ($addslashes)
		echo '<?php echo _attribute(l10n::get('.var_export($lang,true).','.var_export($key,true).','.var_export($args,true).')); ?>';
	else
		echo '<?php echo l10n::get('.var_export($lang,true).','.var_export($key,true).','.var_export($args,true).'); ?>';
}

function l($key,$args = array(),$addslashes = false)
{
	if ($args===true)
	{
		$args = array();
		$addslashes = true;
	}
	if ($addslashes)
		echo '<?php echo _attribute(l10n::get($view->l10n,'.var_export($key,true).','.var_export($args,true).')); ?>';
	else
		echo '<?php echo l10n::get($view->l10n,'.var_export($key,true).','.var_export($args,true).'); ?>';
}

function show($value,$addslashes = false)
{
	if ($addslashes)
		echo '<?php echo _attribute('.var_export($value,true).'); ?>';
	else
		echo '<?php echo '.var_export($value,true).'; ?>';
}

function path($path = null,$addslashes = false)
{
	if ($path===true)
	{
		$path = null;
		$addslashes = true;
	}
	
	show(_path($path),$addslashes);
}

function url($view,$arguments = array(),$decorators = array(),$addslashes = false)
{
	if ($arguments == true)
	{
		$arguments = array();
		$decorators = array();
		$addslashes = true;
	} elseif ($decorators == true)
	{
		$decorators = array();
		$addslashes = true;
	}
	if ($addslashes)
		echo '<?php echo _attribute(_url('.var_export($view,true).','.var_export($arguments,true).','.var_export($decorators,true).')); ?>';
	else
		echo '<?php echo _url('.var_export($view,true).','.var_export($arguments,true).','.var_export($decorators,true).'); ?>';
}

function find($file,$addslashes = false)
{
	show(_path(_find($file)));
}

function part($__part,$__arguments = null)
{
	echo '<?php echo $view->select('.var_export($__part,true).','.var_export($__arguments,true).'); ?>';
}

function form($form)
{
	echo '<?php form::get($view,'.var_export($form,true).'); ?>';
}
