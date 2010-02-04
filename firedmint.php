<?php 
// security check
if (defined('FM_SECURITY'))
{
	header('HTTP/1.1 500 Internal Server Error');
	print '<html><head><title>500 Application Error</title></head><body><h1>Application Error</h1><p>The Firedmint application could not be launched.</p></body></html>';
	die();
}

@define('FM_SECURITY',true);

@define('FM_START_TIME',microtime(true));

// don't show errors
error_reporting(E_ALL);
ini_set('display_errors',1);

// define default primary paths
define('FM_PATH_VAR',            'var/');
define('FM_PATH_VAR_PRIVATE',    FM_PATH_VAR.'private/');
define('FM_PATH_VAR_PUBLIC',     FM_PATH_VAR.'public/');
define('FM_PATH_VAR_LOG',        FM_PATH_VAR.'log/');
define('FM_PATH_CORE',           'core/');
define('FM_PATH_SITE',           'site/');
define('FM_PATH_SITE_ALL',       FM_PATH_SITE.'all/');
define('FM_PATH_SITE_DEFAULT',   FM_PATH_SITE.'default/');

// define default secondary paths (directories)
define('FM_PATH_AUTH',           'auth/');
define('FM_PATH_CLASS',          'class/');
define('FM_PATH_CONTROLLER',     'controller/');
define('FM_PATH_DB',             'db/');
define('FM_PATH_ELEMENT',        'element/');
define('FM_PATH_EXTENSION',      'extension/');
define('FM_PATH_FORM',           'form/');
define('FM_PATH_LOG',            'log/');
define('FM_PATH_MODEL',          'model/');
define('FM_PATH_PUBLIC',         'public/');
define('FM_PATH_TASK',           'task/');
define('FM_PATH_TEMPLATE',       'template/');
define('FM_PATH_URL',            'url/');
define('FM_PATH_VIEW',           'view/');

// define default secondary paths (files)
define('FM_FILE_CONFIG',         'config');
define('FM_FILE_FUNCTION',       'function');
define('FM_FILE_L10N',           'l10n');
define('FM_FILE_METHOD',         'method');

// others constants
define('FM_PHP_EXTENSION','.php');

print dirname(__FILE__);

print_r(get_defined_constants());

// boot function declaration
function fm_getConfig()
{
	static $c = array();
	
	if (defined('FM_SITE_DIR'))
	{
		return $c;
	}
	
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
	{
		$tmp_dir = explode('/', substr($_SERVER['SCRIPT_NAME'],1));
	}
	else
	{
		$tmp_dir = explode('/', $_SERVER['SCRIPT_NAME']);
	}
	
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
					$o[FM_PATH_SITE."{$sub}{$u['host']}{$ext}{$port}{$dir}/"] = FM_PATH_SITE."{$sub}{$u['host']}{$ext}{$port}{$dir}/";
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
				$o[FM_PATH_SITE."{$port}{$dir}/"] = FM_PATH_SITE."{$port}{$dir}/";	
		}
	}while (array_pop($u['dir']));
	
	$o[FM_PATH_SITE_DEFAULT] = FM_PATH_SITE_DEFAULT;
	
	$c = array();
	
	foreach ($o as $dir)
	{
		$file = $dir.FM_FILE_CONFIG.FM_PHP_EXTENSION;
		if (file_exists($file) && is_readable($file) && !defined('FM_SITE_DIR'))
		{
			define('FM_SITE_REAL_DIR',$dir);
			include $file;
			
			if (defined('FM_SITE_DIR'))
			{
				$file = FM_SITE_DIR.FM_FILE_CONFIG.FM_PHP_EXTENSION;
				if (file_exists($file) && is_readable($file))
				{
					$tmp_c = $c;
					$c = array();
					include $file;
					
					$c = array_merge($c,$tmp_c);
				}
			}
			else
			{
				define('FM_SITE_DIR',FM_SITE_REAL_DIR);
			}
		}
	}
	
	$file = FM_PATH_SITE_ALL.FM_FILE_CONFIG.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_c = $c;
		$c = array();
		include $file;
		
		$c = rawurlencode($c,$tmp_c);
	}		
	
	
	return $c;
	
}

