<?php 
if (!defined('FM_SECURITY')) die();

static function build($arguments = array(),$decorators = array(),$argument_separator = '&')
{
	$arguments += array('extension'=>null,'l10n'=>fm::$config['l10n']['local']);
	$full_args = $arguments + $decorators;
	$uri = null;
	if (array_key_exists($arguments['controller'],route::$inverse) && array_key_exists($arguments['action'],route::$inverse[$arguments['controller']]))
	{
		$routes = route::$inverse[$arguments['controller']][$arguments['action']];
		
		foreach ($routes as $key=>$values)
		{
			$args = route::$path[$key][2];
			$vars = route::$path[$key][3];
			$uri = route::$path[$key][4];
			
			
			foreach ($args as $var_name=>$var_regex)
			{
				if (!array_key_exists($var_name,$full_args) || !preg_match("/^($var_regex)$/",$full_args[$var_name]))
				{
					continue 2;
				}
			}
			
			foreach ($vars as $var_name=>$var_value)
			{
				if (!array_key_exists($var_name,$full_args) || $full_args[$var_name]!=$var_value)
				{
					continue 2;
				}
			}
			
			foreach ($vars as $var_name=>$var_value)
			{
				if (array_key_exists($var_name,$arguments))
					unset($arguments[$var_name]);
			}
			
			$extension = $arguments['extension'];
			unset($arguments['extension']);
			unset($args['extension']);
			unset($arguments['controller']);
			unset($arguments['action']);
			
			foreach ($args as  $var_name=>$var_regex)
			{
				$uri = preg_replace("/%$var_name%/",$full_args[$var_name],$uri);
				if (array_key_exists($var_name,$arguments))
					unset($arguments[$var_name]);
			}
			
			if (strlen($uri)>1)
			{
				if (strlen($extension)>0 && $extension!=fm::$config['route']['default_extension'])
				{
					$uri .= ".$extension"; 
				} elseif (fm::$config['route']['show_extension']==true)
				{
					$uri .= '.'.fm::$config['route']['default_extension'];
				}
			}
			elseif ($extension!=fm::$config['route']['default_extension'])
			{
				$arguments['extension'] = $extension;
			}
			
			if (array_key_exists('extension',$arguments) && strlen($arguments['extension'])==0)
				unset($arguments['extension']);
			
			if (array_key_exists('l10n',$arguments) && $arguments['l10n']==fm::$config['l10n']['local'])
				unset($arguments['l10n']);
				
			if (count($arguments)>0)
			{
				$uri .='?'.http_build_query($arguments,'__',$argument_separator);
			}
			
			$return = fm::factory();
			$return->value = $uri;
			return $return;
		}
	}
	
	if (fm::$config['route']['magic_route']==true)
	{
		$uri = "/{$arguments['controller']}/{$arguments['controller']}";
		unset($arguments['controller']);
		unset($arguments['action']);
	}
	else
	{
		$uri = "/";
	}
	
	if (count($arguments)>0)
	{
		$uri .='?'.http_build_query($arguments,'__',$argument_separator);
	}
	
	$return = fm::factory();
	$return->value = $uri;
	return $return;
}
