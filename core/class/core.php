<?php 
if (!defined('FM_SECURITY')) die();

function core_core_method_classStart($fm)
{
	fm::$core
		->loadConfig()
		->include(FM_PATH_SITE_ALL.FM_FILE_FUNCTION.FM_PHP_EXTENSION)
		->include(FM_PATH_SITE_ALL.FM_PATH_CLASS.'fm'.FM_PHP_EXTENSION)
		->include(FM_SITE_DIR.FM_FILE_FUNCTION.FM_PHP_EXTENSION)
		->include(FM_SITE_DIR.FM_PATH_CLASS.'fm'.FM_PHP_EXTENSION);
	fm::$core
		->class('site')
		->class('url');
}

/**
 * Load all declared config
 * 
 * @return Array configuration table
 */
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
					$o[FM_PATH_SITE."{$sub}{$u['host']}{$ext}{$port}{$dir}"] = FM_PATH_SITE."{$sub}{$u['host']}{$ext}{$port}{$dir}";
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
				$o[FM_PATH_SITE."{$port}{$dir}"] = FM_PATH_SITE."{$port}{$dir}";	
		}
	}while (array_pop($u['dir']));
	
	$o[substr(FM_PATH_SITE_DEFAULT,0,-1)] = substr(FM_PATH_SITE_DEFAULT,0,-1);
	
	$c = array();
	
	foreach ($o as $dir)
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
				$file = FM_SITE_DIR.FM_FILE_CONFIG.FM_PHP_EXTENSION;
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
					define('FM_SITE_DIR',"$dir/");
					$tmp_c = $c;
					$c = array();
					include $file;
					$c = array_replace_recursive($c,$tmp_c);
				}
			}
		}
	}
	
	$file = FM_PATH_SITE_ALL.FM_FILE_CONFIG.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_c = $c;
		$c = array();
		include $file;
		$c = array_replace_recursive($c,$tmp_c);
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
