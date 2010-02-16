<?php 
if (!defined('FM_SECURITY')) die();

function core_route_method_classBoot($fm)
{
	$file = array();
	$file[] = FM_PATH_SITE.FM_SITE_DIR.FM_FILE_ROUTE;
	$file[] = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_FILE_ROUTE;
	foreach(fm::$core->extension as $data)
	{
		$file[] = $data['path'].FM_FILE_ROUTE;
	}
	$file[] = FM_PATH_CORE.FM_FILE_ROUTE;

	$r = array();
	foreach ($file as $path)
	{
		$path .= FM_PHP_EXTENSION;
		if (file_exists($path))
		{
			$route = array();
			include $path;
			$r = $r + $route;
		}
	}
	$fm->route['path'] = $r;
	
	$rr = array();
	$re = array();
	foreach ($fm->route['path'] as $url=>$tmp_route)
	{
		list($controller,$action,$args,$vars) = $tmp_route + fm::$config['route']['default_route'] + array(null,null,array(),array());
		
		$args += array('extension'=>'[a-zA-Z0-9]*');
		
		$matches = array();
		preg_match_all('/%([0-9a-zA-Z-_]*)%/',$url,$matches);
		
		if (count($matches[0])>0)
		{
			$patterns = array();
			$replacements = array();
			$arguments = array();
			foreach ($matches[1] as $var)
			{
				$arguments[$var] = '.*';
				if (array_key_exists($var,$args))
					$arguments[$var] = $args[$var];
				
				$pattern = $arguments[$var];
				$patterns[]     = "/%{$var}%/";
				$replacements[] = "({$pattern})";
			}
			$regex = preg_replace($patterns, $replacements, $url);
			if (strpos($regex,'%')===false)
			{
				
				$arguments += array('extension'=>$args['extension']);
				
				$re['#^'.$regex.'$#'] = array($controller,$action,$arguments,$vars,$url,'#^'.$regex.'$#');
				$rr[$controller][$action][$url] = $arguments;
				$fm->route['path'][$url] = array($controller,$action,$arguments,$vars,$url,'#^'.$regex.'$#');
			}
		}
		else
		{
			if (strpos($url,'%')===false)
			{
				$re['#^'.$url.'$#'] = array($controller,$action,array('extension'=>'[a-zA-Z0-9]*'),$vars,$url,'#^'.$url.'$#');
				$rr[$controller][$action][$url] = array('extension'=>'[a-zA-Z0-9]*');
				$fm->route['path'][$url] = array($controller,$action,array('extension'=>'[a-zA-Z0-9]*'),$vars,$url,'#^'.$url.'$#');
			}
		}
		
		
		
	}
	$fm->route['inverse'] = $rr;
	$fm->route['regex'] = $re;
}

function core_route_method_getController($fm)
{	
	$url = (array_key_exists('PATH_INFO',$_SERVER)?$_SERVER['PATH_INFO']:'/');
	
	$matches = array();
	$extension = null;
	if (preg_match('/^(.*)\.([0-9a-zA-Z]*)$/',$url,$matches))
	{
		$url = $matches[1];
		$extension = $matches[2];
	}
	
	$path_keys = array_filter(array_keys($fm->route['regex']),create_function('$arg','return preg_match($arg,\''.$url.'\') && preg_match(\'#^(\'.fm::$core->class[\'route\'][\'object\']->route[\'regex\'][$arg][2][\'extension\'].\')$#\',\''.$extension.'\');'));
	
	if (count($path_keys)>0)
	{
		list($controller,$action,$args,$vars,$path,$regex) = $fm->route['regex'][current($path_keys)];
		
		$matches = array();
		
		preg_match($regex,$url,$matches);
		
		$arguments = array();
		
		if ($matches>1)
		{
			array_shift($matches);
			foreach ($args as $key=>$regex)
			{
				$arguments[$key] = array_shift($matches); 
			}
		}

		return fm::$core->class('controller',$controller,$action,array('extension'=>$extension) + $arguments + $vars + $_GET);
	}
	elseif (fm::$config['route']['magic_route']==true && count(explode('/',$url))>=3)
	{
		list($void,$controller,$action) = explode('/',$url);
		return fm::$core->class('controller',$controller,$action,array('extension'=>$extension) + $_GET);
	}
	else
	{
		return fm::$core->class('controller',fm::$config['route']['default_route'][0],fm::$config['route']['default_route'][1],array('extension'=>$extension) + $_GET);
	}
	
}