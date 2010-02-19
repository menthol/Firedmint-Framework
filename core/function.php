<?php 
if (!defined('FM_SECURITY')) die();

function boot()
{
	// Load config
	if (defined('FM_SITE_DIR'))
		return $this;
	
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
	
	if ($_SERVER['SCRIPT_NAME'][0]=='/')
		$tmp_dir = explode('/', substr($_SERVER['SCRIPT_NAME'],1));
	else
		$tmp_dir = explode('/', $_SERVER['SCRIPT_NAME']);
	
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
	
	$o[substr(FM_PATH_SITE_DEFAULT,0,-1)] = substr(FM_PATH_SITE.FM_PATH_SITE_DEFAULT,0,-1);
	
	$c = array();
	
	foreach ($o as $key=>$dir)
	{
		if (!defined('FM_SITE_DIR'))
		{
			$file = $dir.FM_PHP_EXTENSION;
			if (file_exists($file) && is_readable($file))
			{
				$tmp_c = $c;
				$c = array();
				include $file;
				$c = array_replace_recursive($c,$tmp_c);
			}
			if (defined('FM_SITE_DIR'))
			{
				$file = FM_PATH_SITE.FM_SITE_DIR.FM_FILE_CONFIG.FM_PHP_EXTENSION;
				if (file_exists($file) && is_readable($file))
				{				
					$tmp_c = $c;
					$c = array();
					include $file;
					$c = array_replace_recursive($c,$tmp_c);
				}
			}
			else
			{
				$file = "$dir/".FM_FILE_CONFIG.FM_PHP_EXTENSION;
				if (file_exists($file) && is_readable($file))
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
	
	$file = FM_PATH_SITE.FM_SITE_DIR.FM_FILE_FUNCTION.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		include $file;
	}
	
	$file = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_FILE_FUNCTION.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		include $file;
	}
		
	$file = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_FILE_CONFIG.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_c = $c;
		$c = array();
		include $file;
		$c = array_replace_recursive($c,$tmp_c);
	}

	
	// load extensions 
	$e = array('require'=>array(),'use'=>array());
	
	$file = FM_PATH_SITE.FM_SITE_DIR.FM_FILE_EXTENSION.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_e = $e;
		$e = array();
		include $file;
		$e += array('require'=>array(),'use'=>array());
		$e['require'] = array_merge($tmp_e['require'],$e['require']);
		$e['use'] = array_merge($tmp_e['use'],$e['use']);
	}
	
	$file = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_FILE_EXTENSION.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_e = $e;
		$e = array();
		include $file;
		$e += array('require'=>array(),'use'=>array());
		$e['require'] = array_merge($tmp_e['require'],$e['require']);
		$e['use'] = array_merge($tmp_e['use'],$e['use']);
	}
	
	$file = FM_PATH_CORE.FM_SITE_DIR.FM_FILE_EXTENSION.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
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
		foreach ($ext_list as $extension=>$values)
		{
			if (!array_key_exists($extension,$extension_list))
			{
				if (file_exists(FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_EXTENSION.$extension."/")||file_exists(FM_PATH_SITE.FM_SITE_DIR.FM_PATH_EXTENSION.$extension."/"))
				{
					$extension_list[$extension]['required_by'] = $ext_list[$extension]['required_by'];
					$extension_list[$extension]['used_by'] = $ext_list[$extension]['used_by'];
	
					if (file_exists(FM_PATH_SITE.FM_SITE_DIR.FM_PATH_EXTENSION."$extension/"))
						$extension_list[$extension]['path'] = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_EXTENSION."$extension/";
					else
						$extension_list[$extension]['path'] = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_EXTENSION."$extension/";
					
					$e = array();
					if (file_exists($extension_list[$extension]['path'].FM_FILE_EXTENSION.FM_PHP_EXTENSION))
						include $extension_list[$extension]['path'].FM_FILE_EXTENSION.FM_PHP_EXTENSION;
					
					$e += array('require'=>array(),'use'=>array());
					$e['use'] = array_map('strtolower',array_map('trim',$e['use']));
					$e['require'] = array_map('strtolower',array_map('trim',$e['require']));
				
					$extension_list[$extension]['require'] = $e['require'];
					$extension_list[$extension]['use'] = $e['use'];
					
					foreach ($extension_list[$extension]['require'] as $require)
					{
						if (array_key_exists($require,$extension_list))
						{
							$extension_list[$require]['required_by'][] = $extension;
							$extension_list[$extension]['require'] = array_unique(array_merge($extension_list[$extension]['require'],$extension_list[$require]['require']));
						}
						else
						{
							if (!array_key_exists($require,$ext_list))
								$ext_list[$require] = array('required_by'=>array(),'used_by'=>array());
							
							$ext_list[$require]['required_by'][] = $extension;
						}
					}
					foreach ($extension_list[$extension]['use'] as $use)
					{
						if (array_key_exists($use,$extension_list))
							$extension_list[$use]['used_by'][] = $extension;
						else
						{
							if (!array_key_exists($use,$ext_list))
								$ext_list[$use] = array('required_by'=>array(),'used_by'=>array());
							
							$ext_list[$use]['used_by'][] = $extension;
						}
					}
				}
				else
				{
					$extension_list[$extension] = array(
						'required_by' => $ext_list[$extension]['required_by'],
						'used_by'     => $ext_list[$extension]['used_by'],
						'path'        => null,
						'require'     => array(),
						'use'         => array(),
					);
					
					//$this->error("Extension not found : ".$extension);
				}
			}
			unset($ext_list[$extension]);
		}
	}
	
	$ext_list = array();
	
	do
	{
		$ko = false;
		foreach ($extension_list as $extension=>$values)
		{
			if (is_null($values['path']))
			{
				foreach ($values['required_by'] as $required_by)
				{
					if ($required_by!='@fm@' && !is_null($extension_list[$required_by]['path']))
					{
						$extension_list[$required_by]['path'] = null;
						$ko = true;
					}
				}
			}
		}
	}while ($ko);
	
	$main_extension_list = array();
	
	foreach ($extension_list as $extension=>$values)
	{	
		if (!is_null($values['path']))
		{
			foreach ($values['required_by'] as $required_by)
			{
				if ($required_by=='@fm@')
				{
					$main_extension_list[$extension] = $values;
				}
			}
		}
	}
	
	$loadable_extension = array();
	
	while(count($main_extension_list)>0)
	{
		foreach ($main_extension_list as $extension=>$values)
		{
			foreach ($values['require'] as $require)
			{
				if (!array_key_exists($require,$loadable_extension) && !array_key_exists($require,$main_extension_list))
				{
					$main_extension_list[$require] = $extension_list[$requirezs];
				}
			}
			
			foreach ($values['use'] as $use)
			{
				if (!array_key_exists($use,$loadable_extension) && !array_key_exists($use,$main_extension_list) && !is_null($extension_list[$use]['path']))
				{
					$main_extension_list[$use] = $extension_list[$use];
				}
			}
			
			$loadable_extension[$extension] = $values;
			
			unset($main_extension_list[$extension]);
			
		}
	}
	
	foreach ($loadable_extension as $extension=>$values)
	{
		$path = $values['path'];
		if (file_exists($path.FM_FILE_CONFIG.FM_PHP_EXTENSION) && is_readable($path.FM_FILE_CONFIG.FM_PHP_EXTENSION))
		{
			$tmp_c = $c;
			$c = array();
			include $path.FM_FILE_CONFIG.FM_PHP_EXTENSION;
			$c = array_replace_recursive($c,$tmp_c);
		}
		
		if (file_exists($path.FM_FILE_FUNCTION.FM_PHP_EXTENSION) && is_readable($path.FM_FILE_FUNCTION.FM_PHP_EXTENSION))
		{
			include $path.FM_FILE_FUNCTION.FM_PHP_EXTENSION;
		}
	}
	
	$file = FM_PATH_CORE.FM_FILE_CONFIG.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_c = $c;
		$c = array();
		include $file;
		$c = array_replace_recursive($c,$tmp_c);
	}
	
	$compil_file = FM_PATH_VAR_PRIVATE.FM_SITE_DIR.'compil/'.sha1(serialize($loadable_extension)).FM_PHP_EXTENSION; 
	
	if (!is_dir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR))
		mkdir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR);
	if (!is_dir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR.'compil'))
		mkdir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR.'compil/');
	
	if (array_key_exists('rebuild',$_GET)
	 && array_key_exists('build',$c)
	 && array_key_exists('key',$c['build'])
	 && $_GET['rebuild']==$c['build']['key'])
	{
		unlink($compil_file);
	}
		
	if (!file_exists($compil_file))
	{
		$files = array();
		$paths = array();
		$paths[] = FM_PATH_CORE.FM_PATH_METHOD;
		
		foreach (array_reverse(array_keys($loadable_extension)) as $extension)
		{
			$paths[] = $loadable_extension[$extension]['path'].FM_PATH_METHOD;
			$files[$extension] = array();
		}
		
		$paths[] = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_METHOD;
		$paths[] = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_METHOD;
		
		
		
		
		foreach ($paths as $path_method)
		{
			if (is_dir($path_method))
			{
				$dir_handle = @opendir($path_method);
				while ($file = readdir($dir_handle)) 
				{
					if (preg_match('/^([a-z][a-z0-9]*)$/',$file) && is_dir($path_method.$file))
					{
						$dir_handle_class = @opendir($path_method.$file);
						while ($file_method = readdir($dir_handle_class)) 
						{
							$matches = array();
							if (preg_match('/^([a-z_][a-z0-9_]*)\.var\.php$/',$file_method,$matches))
							{
								$files[$file]['var'][$matches[1]] = $path_method."$file/$file_method";
							}
							elseif (preg_match('/^([a-z_][a-z0-9_]*)\.php$/',$file_method,$matches))
							{
								$files[$file]['method'][$matches[1]] = $path_method."$file/$file_method";
							}
						}
						closedir($dir_handle_class);
					}
				}
				closedir($dir_handle);
			}
		}
		
		$out = "<?php if (!defined('FM_SECURITY')) die();".PHP_EOL."class fm { ".PHP_EOL;
		
		if (array_key_exists('var',$files['fm']))
		{
			foreach ($files['fm']['var'] as $file)
			{
				$file_content =  file_get_contents($file);
				$matches = array();
				if (preg_match('/(static|var|public|protected|private)(.*;)/',$file_content,$matches))
					$out .= $matches[0].PHP_EOL;
			}
		}
		
		if (array_key_exists('method',$files['fm']))
		{
			foreach ($files['fm']['method'] as $file)
			{
				$file_content =  file_get_contents($file);
				$matches = array();
				if (preg_match('/((static|function|public|protected|private).*})[^}]*$/xs',$file_content,$matches))
					$out .= $matches[1].PHP_EOL;
			}
		}
		
		$out .= '}'.PHP_EOL;
		
		unset($files['fm']);
		
		foreach ($files as $extension=>$values)
		{
			$out .= "class $extension extends fm {".PHP_EOL;
		
			if (array_key_exists('var',$values))
			{
				foreach ($values['var'] as $file)
				{
					$file_content =  file_get_contents($file);
					$matches = array();
					if (preg_match('/(static|var|public|protected|private)(.*;)/',$file_content,$matches))
						$out .= $matches[0].PHP_EOL;
				}
			}
			
			if (array_key_exists('method',$values))
			{
				foreach ($values['method'] as $file)
				{
					$file_content =  file_get_contents($file);
					$matches = array();
					if (preg_match('/((static|function|public|protected|private).*})[^}]*$/xs',$file_content,$matches))
						$out .= $matches[1].PHP_EOL;
				}
			}
			
			$out .= '}'.PHP_EOL;
						
		}
		@file_put_contents($compil_file,$out);
	}
	
	if (!file_exists($compil_file))
	{
		header('HTTP/1.1 500 Internal Server Error');
		print '<html><head><title>500 Application Error</title></head><body><h1>Application Error</h1><p>The Firedmint application could not be launched.</p></body></html>';	
	}
	
	include $compil_file ;
	
	set_error_handler("fm_ErrorHandler");
	
	fm::$config          = $c;
	
	foreach ($loadable_extension as $extension=>$data)
	{
		fm::$extension[$extension] = array('path'=>$data['path'],'object'=>call_user_func(array($extension,'factory')));
	}
	
	$controller = route::factory()->getController();
	
	$view = $controller->startController();
	
	if (is_a($view,'view'))
	{
		$view->get('document');
	}
}

function fm_ErrorHandler($errno, $errstr, $errfile, $errline) {
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

