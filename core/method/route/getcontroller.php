<?php 
if (!defined('FM_SECURITY')) die();

function getController()
{	
	$url = (array_key_exists('PATH_INFO',$_SERVER)?$_SERVER['PATH_INFO']:'/');
	
	$matches = array();
	$extension = null;
	if (preg_match('/^(.*)\.([0-9a-zA-Z]+)$/',$url,$matches))
	{
		$url = $matches[1];
		$extension = $matches[2];
	}
	
	$path_keys = array_filter(array_keys(route::$regex),create_function('$arg','return preg_match($arg,\''.$url.'\') && preg_match(\'#^(\'.route::$regex[$arg][2][\'extension\'].\')$#\',\''.$extension.'\');'));
	
	if (count($path_keys)>0)
	{
		list($controller,$action,$args,$vars,$path,$regex) = route::$regex[current($path_keys)];
		
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
		
		$arguments = array('extension'=>$extension) + $arguments + $vars + $_GET;
		
	}
	elseif (fm::$config['route']['magic_route']==true && count(explode('/',$url))>=3)
	{
		list($void,$controller,$action) = explode('/',$url);
		$arguments = array('extension'=>$extension) + $_GET;
	}
	else
	{
		$controller = fm::$config['route']['default_route'][0];
		$action = fm::$config['route']['default_route'][1];
		$arguments = array('extension'=>$extension) + $_GET;
	}
	
	fm::find(FM_PATH_CONTROLLER.$controller)->load();
	
	if (class_exists("controller_$controller"))
	{
		return call_user_func(array("controller_$controller",'factory'),$controller,$action,$arguments); 
	}
	else
	{
		return controller::factory($controller,$action,$arguments);
	}
}