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
			if (!is_array($__path = cache::$static->get('phproute','static_routes')))
				$__path = array();
			
			foreach (_getPaths(FM_PATH_PRIVATE.FM_FILE_ROUTE.FM_PHP_EXTENSION) as $file)
			{
				$route = array();
				include $file;
				$__path += $route;
			}
	
			$__inverse = array();
			$__regex = array();
			foreach ($__path as $url=>$route)
			{
				$__view = array_shift($route);
				$__status = 200; # HTTP/1.1 200 OK
				$__arguments = array();
				$__extensions = '[a-z0-9]*';
				
				foreach ($route as $value)
					if (is_array($value))
						$__arguments = $value;
					elseif (!is_string($value) && is_numeric($value) && in_array("$value",header::$statusCodes))
						$__status = $value;
					elseif (is_string($value))
						$__extensions = $value;
				
				preg_match_all('/%([0-9a-z-_]+)%/i',$url,$matches);
				
				if (count($matches[1])>0)
				{
					$patterns = array();
					$replacements = array();
					$arguments = array();
					foreach ($matches[1] as $var)
					{
						if (!(array_key_exists($var,$__arguments) && $arguments[$var]=$__arguments[$var]))	
							$arguments[$var] = '.+';
						
						unset($__arguments[$var]);
						
						$patterns[]     = "/%{$var}%/";
						$replacements[] = "({$arguments[$var]})";
					}
					$regex = preg_replace($patterns, $replacements, $url);
					if (strpos($regex,'%')===false)
					{
						$__regex['#^'.$regex.'$#'] = array($__view,$__status,$__extensions,$arguments,$__arguments,$url,'#^'.$regex.'$#');
						$__inverse[$__view][$url] = array($__view,$__status,$__extensions,$arguments,$__arguments,$url,'#^'.$regex.'$#');
						$__path[$url] = array($__view,$__status,$__extensions,$arguments,$__arguments,$url,'#^'.$regex.'$#');
					}
				}
				else
				{
					if (strpos($url,'%')===false)
					{
						$__regex['#^'.$url.'$#'] = array($__view,$__status,$__extensions,array(),$__arguments,$url,'#^'.$url.'$#');
						$__inverse[$__view][$url] = array($__view,$__status,$__extensions,array(),$__arguments,$url,'#^'.$url.'$#');
						$__path[$url] = array($__view,$__status,$__extensions,array(),$__arguments,$url,'#^'.$url.'$#');
					}
				}
			}
			
			phpRoute::$path    = $__path;
			phpRoute::$inverse = $__inverse;
			phpRoute::$regex   = $__regex;
			
			cache::$value->set('phproute','cached_routes',array(phpRoute::$path,phpRoute::$inverse,phpRoute::$regex),kernel::$config['route']['cache_lifetime']);
		}
	}
	
	function getView($url,$httpGet,$magicRoute)
	{	
		$routeKey = $url.var_export($httpGet,true).$magicRoute;
		if (!_clear('route') && is_array($route = cache::$value->get('phproute',$routeKey)))
			return $route;
		else
		{
			$matches = array();
			$extension = null;

			if (array_key_exists('extension',$path_parts = pathinfo($url)))
			{
				$url = substr($url,0,-1-strlen($path_parts['extension']));
				$extension = $path_parts['extension'];
			}
			
			$path_keys = array();
			foreach (array_keys(phpRoute::$regex) as $regex)
				if (preg_match($regex,$url) && preg_match('#^('.phpRoute::$regex[$regex][2].')$#',$extension))
					$path_keys[] = $regex; 
			
			$arguments = array();
			$status = 200;
			
			if (count($path_keys)>0)
			{
				list($view,$status,$extensions,$urlArguments,$routeArguments,$route,$regex) = phpRoute::$regex[$path_keys[0]];
				preg_match($regex,$url,$matches);
				if (count($matches)>1)
				{
					array_shift($matches);
					foreach ($urlArguments as $key=>$regex)
						$arguments[$key] = array_shift($matches); 
				}
				$arguments += $routeArguments + $httpGet;
				$params = array('url'=>$url,'view'=>$view,'params'=>array());
			}
			elseif ($magicRoute==true && $url!='/')
			{
				$args = explode('/',$url);
				array_shift($args);
				$view = array_shift($args);
				$arguments = array('params'=>$args) + $httpGet;
				$params = array('url'=>$url,'view'=>$view,'params'=>$args);
			}
			else
			{
				$view = kernel::$config['route']['404_route'];
				$status = 404;
				$args = explode('/',$url);
				array_shift($args);
				$_view = array_shift($args);
				$arguments = $httpGet;
				$params = array('url'=>$url,'view'=>$_view,'params'=>$args);
			}
			
			$route = array($view,$status,$extension,$arguments,$params);
			cache::$value->set('phproute',$routeKey,$route,kernel::$config['route']['cache_lifetime']);
			
			return $route;
		}
	}
}