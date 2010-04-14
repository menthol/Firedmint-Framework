<?php
if (!defined('FM_SECURITY')) die();

class phpRoute
{
	static $path    = array();
	static $inverse = array();
	static $regex   = array();
	
	function __construct()
	{
		$this->__update();
	}
	
	function __update()
	{
		if (!_clear('route') && is_array($routes = cache::get('phproute','cached_routes')))
		{
			list(phpRoute::$path,phpRoute::$inverse,phpRoute::$regex) = $routes;
		}
		else
		{			
			if (!is_array($__path = cache::get('phproute','static_routes')))
				$__path = array();
			
			foreach (_getPaths('private/route.php') as $file)
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
				
				foreach ($route as $key=>$value)
					if (is_string($key))
						$__arguments = array($key=>$value);
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
						if (!(isset($arguments[$var]) && $arguments[$var]=$__arguments[$var]))	
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
			
			cache::set('phproute','cached_routes',array(phpRoute::$path,phpRoute::$inverse,phpRoute::$regex),config::$config['route']['cache_lifetime']);
		}
	}
	
	function getUrl($view,$arguments = array(),$decorators = array())
	{
		if (isset(phpRoute::$inverse[$view]))
		{
			$routes = array();
			foreach (phpRoute::$inverse[$view] as $url=>$routeData)
			{
				$ok = true;
				foreach ($routeData[4] as $key=>$value)
				{
					if (!(isset($arguments[$key]) && $arguments[$key]==$value) && !(isset($decorators[$key]) && $decorators[$key]==$value))
						$ok = false;
				}
				foreach ($routeData[3] as $key=>$value)
				{
					if (!(isset($arguments[$key]) && preg_match('/^('.$value.')$/',$arguments[$key])) && !(isset($decorators[$key]) && preg_match('/^'.$value.'$/',$decorators[$key])))
						$ok = false;
				}
				
				if ($ok)
					$routes[$url] = $routeData + array('count'=>0);
			}
			
			$route = null;
			
			if (count($routes)>0)
			{
				$maxCount = 0;
				foreach ($routes as $url=>$routeData)
				{
					foreach (array_keys($arguments) as $argument)
					{
						if (isset($routeData[3][$argument]) || isset($routeData[4][$argument]))
							++$routes[$url]['count'];
					}
					
					if ($routes[$url]['count']>$maxCount)
							$maxCount = $routes[$url]['count'];
				}
				foreach ($routes as $url=>$routeData)
				{
					if ($routeData['count']==$maxCount)
					{
						// gen url
						$fullArgs = $arguments + $decorators;
						
						$regex = array();
						$replacement = array();
						
						foreach ($routeData[3] as $argument=>$pattern)
						{
							$regex[] = "#%$argument%#";
							$replacement[] =  $fullArgs[$argument];
							unset($fullArgs[$argument]);
						}
						
						foreach (array_keys($routeData[4]) as $argument)
							unset($fullArgs[$argument]);
						
						foreach (array_keys($decorators) as $argument)
							if (!isset($arguments[$argument]))
								unset($fullArgs[$argument]);
						
						if (isset($fullArgs['l10n']) && $fullArgs['l10n']==config::$config['l10n']['default'])
							unset($fullArgs['l10n']);
						
						return preg_replace($regex,$replacement,$url).(count($fullArgs)>0?'?'.http_build_query($fullArgs,'__','&'):null);
					}
				}
			}
		}
		
		if (config::$config['route']['magic_route']==true && preg_match('/^('.config::$config['route']['magic_view'].')$/',$view))
		{
			$urlArgs = array();
			$queryArgs = array();
			foreach ($arguments as $key=>$value)
				if (is_numeric($key))
					$urlArgs[$key] = $value;
				else
					$queryArgs[$key] = $value;
			
			return "/$view".(count($urlArgs)>0?'/'.implode('/',$urlArgs):null).(count($queryArgs)>0?'?'.http_build_query($queryArgs,'__','&'):null);
		}
		
		if (!empty($view))
		{
			$arguments['view'] = $view;
		}
		
		return "/".(count($arguments)>0?'?'.http_build_query($arguments,'__','&'):null);
	}
	
	function getView($url,$httpGet)
	{	
		$routeKey = $url.var_export($httpGet,true);
		if (!_clear('route') && is_array($route = cache::get('phproute',$routeKey)))
			return $route;
		else
		{
			$matches = array();
			$extension = null;
			$path_parts = pathinfo($url);
			
			if (isset($path_parts['extension']))
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
			
			if (count($path_keys)>0 && ($url!='/' || !isset($httpGet['view']) || empty($httpGet['view'])))
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
			elseif (config::$config['route']['magic_route']==true && $url!='/' && is_array($args = explode('/',$url)) && preg_match('/^('.config::$config['route']['magic_view'].')$/',$args[1]))
			{
				array_shift($args);
				$view = array_shift($args);
				$arguments = array('params'=>$args) + $httpGet;
				$params = array('url'=>$url,'view'=>$view,'params'=>$args);
			}
			elseif(isset($httpGet['view']))
			{
				$view = $httpGet['view'];
				$arguments = $httpGet;
				$params = array('url'=>$url,'view'=>$view,'params'=>array());
			}
			else
			{
				$view = config::$config['route']['404_route'];
				$status = 404;
				$args = explode('/',$url);
				array_shift($args);
				$_view = array_shift($args);
				$arguments = $httpGet;
				$params = array('url'=>$url,'view'=>$_view,'params'=>$args);
			}
			
			$route = array($view,$status,$extension,$arguments,$params);
			cache::set('phproute',$routeKey,$route,config::$config['route']['cache_lifetime']);
			
			return $route;
		}
	}
}