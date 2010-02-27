<?php
if (!defined('FM_SECURITY')) die();

class phpRoute
{
	static $path    = array();
	static $inverse = array();
	static $regex   = array();
	
	function __construct()
	{
		$this->update();
	}
	
	function update()
	{
		if (!_clear('route') && is_array($routes = cache::$value->get('phproute','cached_routes')))
		{
			list(phpRoute::$path,phpRoute::$inverse,phpRoute::$regex) = $routes;
		}
		else
		{
			// compile routes 
			list($config,$extension) = _loadConfig();
			
			$file = array();
			$file[] = FM_PATH_SITE.FM_PATH_PRIVATE.FM_SITE_DIR.FM_FILE_ROUTE;
			$file[] = FM_PATH_SITE.FM_PATH_PRIVATE.FM_PATH_ALL.FM_FILE_ROUTE;
	
			foreach($extension as $data)
				$file[] = $data['path'].FM_PATH_PRIVATE.FM_FILE_ROUTE;
			
			$file[] = FM_PATH_CORE.FM_PATH_PRIVATE.FM_FILE_ROUTE;
			
			if (!is_array($r = cache::$static->get('phproute','static_routes')))
				$r = array();
			
			foreach ($file as $path)
			{
				$path .= FM_PHP_EXTENSION;
				if (file_exists($path))
				{
					$route = array();
					include $path;
					$r += $route;
				}
			}
			
			phpRoute::$path = $r;
	
			$rr = array();
			$re = array();
			foreach (phpRoute::$path as $url=>$tmp_route)
			{
				list($view,$args,$vars) = $tmp_route + array(null,array(),array());
				
				$args += array('extension'=>'[a-z0-9]*');
				
				$matches = array();
				preg_match_all('/%([0-9a-z-_]+)%/i',$url,$matches);
				
				if (count($matches[0])>0)
				{
					$patterns = array();
					$replacements = array();
					$arguments = array();
					foreach ($matches[1] as $var)
					{
						$arguments[$var] = '.+';
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
						
						$re['#^'.$regex.'$#'] = array($view,$arguments,$vars,$url,'#^'.$regex.'$#');
						$rr[$view][$url] = $arguments;
						phpRoute::$path[$url] = array($view,$arguments,$vars,$url,'#^'.$regex.'$#');
					}
				}
				else
				{
					if (strpos($url,'%')===false)
					{
						$re['#^'.$url.'$#'] = array($view,array('extension'=>'[a-z0-9]*'),$vars,$url,'#^'.$url.'$#');
						$rr[$view][$url] = array('extension'=>'[a-z0-9]*');
						phpRoute::$path[$url] = array($view,array('extension'=>'[a-z0-9]*'),$vars,$url,'#^'.$url.'$#');
					}
				}
			}
			phpRoute::$inverse = $rr;
			phpRoute::$regex = $re;
			cache::$value->set('phproute','cached_routes',array(phpRoute::$path,phpRoute::$inverse,phpRoute::$regex),kernel::$config['route']['cache_lifetime']);
		}
	}
	
	function getView($url,$getArgs,$magicRoute)
	{	
		$routeKey = $url.var_export($getArgs,true).$magicRoute;
		if (!_clear('route') && is_array($route = cache::$value->get('phproute',$routeKey)))
		{
			return $route;
		}
		else
		{
			$matches = array();
			$extension = null;
			if (preg_match('/^(.*)\.([0-9a-z]+)$/i',$url,$matches))
			{
				$url = $matches[1];
				$extension = strtolower($matches[2]);
			}
	
			$path_keys = array_filter(array_keys(phpRoute::$regex),create_function('$arg','return preg_match($arg,\''.$url.'\') && preg_match(\'#^(\'.phpRoute::$regex[$arg][1][\'extension\'].\')$#\',\''.$extension.'\');'));
			
			if (count($path_keys)>0)
			{
				list($view,$args,$vars,$path,$regex) = phpRoute::$regex[current($path_keys)];
				
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
				
				$arguments = array('extension'=>$extension) + $arguments + $vars + $getArgs;
				
			}
			elseif ($magicRoute==true && count(explode('/',$url))>=3)
			{
				$args = explode('/',$url);
				array_shift($args);
				$view = array_shift($args);
				$arguments = array('extension'=>$extension) + $getArgs;
			}
			else
			{
				$view = kernel::$config['route']['404_route'];
				$arguments = array('extension'=>$extension) + $getArgs;
			}
			
			$route = array($view,$arguments);
			cache::$value->set('phproute',$routeKey,$route,kernel::$config['route']['cache_lifetime']);
			return $route;
		}
	}
}