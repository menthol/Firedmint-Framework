<?php 
if (!defined('FM_SECURITY')) die();

function core_core_method_classStart($fm)
{
	fm::$core
		->loadConfig()
		->include(FM_PATH_SITE.FM_PATH_SITE_ALL.FM_FILE_FUNCTION)
		->include(FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_CLASS.'fm')
		->include(FM_PATH_SITE.FM_SITE_DIR.FM_FILE_FUNCTION)
		->include(FM_PATH_SITE.FM_SITE_DIR.FM_PATH_CLASS.'fm');
	
	if (!is_dir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR))
			mkdir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR);
	
	fm::$core
		->class('site')
		->class('route')
			->getController();
}

function core_core_method_loadConfig($fm)
{
	if (defined('FM_SITE_DIR'))
		return;
	
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
					
					$fm->error("Extension not found : ".$extension);
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
		$fm->message("Loading extension $extension ");
		$path = $values['path'];
		fm::$core
			->include(FM_PATH_CORE.FM_PATH_CLASS.$extension)
			->include(FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_CLASS.$extension)
			->include(FM_PATH_SITE.FM_SITE_DIR.FM_PATH_CLASS.$extension);
		foreach(fm::$core->extension as $data)
		{
			fm::$core->include($data['path'].FM_PATH_CLASS.$extension);
		}
		
		foreach(fm::$core->class as $class=>$data)
		{
			fm::$core->include($path.FM_PATH_CLASS.$class);
		}
			
		fm::$core
			->include($path.FM_PATH_CLASS.$extension)
			->include($path.FM_FILE_FUNCTION);
		
		if (file_exists($path.FM_FILE_CONFIG.FM_PHP_EXTENSION) && is_readable($path.FM_FILE_CONFIG.FM_PHP_EXTENSION))
		{
			$tmp_c = $c;
			$c = array();
			include $path.FM_FILE_CONFIG.FM_PHP_EXTENSION;
			$c = array_replace_recursive($c,$tmp_c);
		}
		
		fm::$core->extension[$extension] = array('object'=>clone fm::$stdObj,'type'=>$extension,'path'=>$path);
		
		fm::$core->extension[$extension]['object']
			->classBoot()
			->classConstruct()
			->classStart();
		$fm->message("Extension $extension Loaded");
		
	}
	
	$invers_ext = array_reverse(fm::$core->extension,true);
	
	foreach ($invers_ext as $extension=>$values)
	{
		fm::$core->extension[$extension]['object']
			->classBoot()
			->classConstruct()
			->classStart();
		$fm->message("Extension $extension Started");
	}
	
	$file = FM_PATH_CORE.FM_FILE_CONFIG.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_c = $c;
		$c = array();
		include $file;
		$c = array_replace_recursive($c,$tmp_c);
	}
	
	fm::$config = $c;
}
