<?php 
if (!defined('FM_SECURITY')) die();

function __construct()
{
	$file = array();
	$file[] = FM_PATH_SITE.FM_SITE_DIR.FM_FILE_ROUTE;
	$file[] = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_FILE_ROUTE;
	foreach(fm::$extension as $data)
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
	
	route::$path = $r;
	
	
	$rr = array();
	$re = array();
	foreach (route::$path as $url=>$tmp_route)
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
				route::$path[$url] = array($controller,$action,$arguments,$vars,$url,'#^'.$regex.'$#');
			}
		}
		else
		{
			if (strpos($url,'%')===false)
			{
				$re['#^'.$url.'$#'] = array($controller,$action,array('extension'=>'[a-zA-Z0-9]*'),$vars,$url,'#^'.$url.'$#');
				$rr[$controller][$action][$url] = array('extension'=>'[a-zA-Z0-9]*');
				route::$path[$url] = array($controller,$action,array('extension'=>'[a-zA-Z0-9]*'),$vars,$url,'#^'.$url.'$#');
			}
		}
	}
	route::$inverse = $rr;
	route::$regex = $re;
}
