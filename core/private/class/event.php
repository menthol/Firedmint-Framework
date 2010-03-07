<?php
if (!defined('FM_SECURITY')) die();

class event
{
	public  static  $hook = array();
	
	function __construct()
	{
		if (_clear('event') || !is_array(event::$hook = cache::get('event','cached_hook')))
		{
			// compile event
			if (!is_array($hook = cache::getStatic('event','static_hook')))
				$hook = array();
						
			foreach (_getPaths('private/hook.php') as $file)
				include $file;
			
			cache::set('event','cached_hook',event::$hook = $hook,config::$config['event']['cache_lifetime']);
		}
	}
	
	static function trigger($type,$event,$states='before|after',&$arg1 = null, &$arg2 = null, &$arg3 = null, &$arg4 = null, &$arg5 = null)
	{
		if (isset(event::$hook[$type][$event]['before']) && preg_match('/^('.$states.')$/','before'))
			foreach (event::$hook[$type][$event]['before'] as $callback)
				if (is_callable($callback) && is_string($__ = call_user_func($callback,&$arg1,&$arg2,&$arg3,&$arg4,&$arg5)))
					return $__;
		
		if (isset(event::$hook[$type][$event]['after']) && preg_match('/^('.$states.')$/','after'))
			foreach (event::$hook[$type][$event]['after'] as $callback)
				if (is_callable($callback) && is_string($__ = call_user_func($callback,&$arg1,&$arg2,&$arg3,&$arg4,&$arg5)))
					return $__;
	}
	
	static function hook($type,$event,$callback,$part = 'main')
	{
		if (preg_match('/^('.$part.')$/','before'))
			event::$hook[$type][$event]['before'][] = $callback;
			
		if (preg_match('/^('.$part.')$/','after'))
			event::$hook[$type][$event]['after'][] = $callback;
	}
}