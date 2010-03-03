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
						
			foreach (_getPaths('private/hook'.FM_PHP_EXTENSION) as $file)
				include $file;
			
			cache::set('event','cached_hook',event::$hook = $hook,config::$config['event']['cache_lifetime']);
		}
	}
	
	static function trigger($type,$event,$states='before|main|after',&$arg1 = null, &$arg2 = null, &$arg3 = null, &$arg4 = null, &$arg5 = null)
	{
		if (array_key_exists($type,event::$hook) && array_key_exists($event,event::$hook[$type]))
			foreach (array('before','main','after') as $state)
				if (array_key_exists($state,event::$hook[$type][$event]) && preg_match('/^('.$states.')$/',$state))
					foreach (event::$hook[$type][$event][$state] as $callback)
						if (is_string($__ = call_user_func($callback,&$arg1,&$arg2,&$arg3,&$arg4,&$arg5)))
							return $__;
	}
	
	static function hook($type,$event,$callback,$part = 'main')
	{
		if (preg_match('/^('.$part.')$/','before'))
			event::$hook[$type][$event]['before'][] = $callback;
		
		if (preg_match('/^('.$part.')$/','main'))
			event::$hook[$type][$event]['main'][] = $callback;
		
		if (preg_match('/^('.$part.')$/','after'))
			event::$hook[$type][$event]['after'][] = $callback;
	}
}