<?php
if (!defined('FM_SECURITY')) die();

class FmEvent
{
	public  static  $hook = array();
	
	function __construct()
	{
		if (_clear('event') || !is_array(FmEvent::$hook = FmCache::get('event','cached_hook')))
		{
			// compile event
			if (!is_array($hook = FmCache::getStatic('event','static_hook')))
				$hook = array();
						
			foreach (_getPaths('private/hook.php') as $file)
				include $file;
			
			FmCache::set('event','cached_hook',FmEvent::$hook = $hook,FmConfig::$config['event']['cache_lifetime']);
		}
	}
	
	static function trigger($type,$event,$states='before|after',&$arg1 = null, &$arg2 = null, &$arg3 = null, &$arg4 = null, &$arg5 = null)
	{
		if (isset(FmEvent::$hook[$type][$event]['before']) && preg_match('/^('.$states.')$/','before'))
			foreach (FmEvent::$hook[$type][$event]['before'] as $callback)
				if (is_callable($callback) && is_string($__ = call_user_func($callback,&$arg1,&$arg2,&$arg3,&$arg4,&$arg5)))
					return $__;
		
		if (isset(FmEvent::$hook[$type][$event]['after']) && preg_match('/^('.$states.')$/','after'))
			foreach (FmEvent::$hook[$type][$event]['after'] as $callback)
				if (is_callable($callback) && is_string($__ = call_user_func($callback,&$arg1,&$arg2,&$arg3,&$arg4,&$arg5)))
					return $__;
	}
	
	static function hook($type,$event,$callback,$part = 'main')
	{
		if (preg_match('/^('.$part.')$/','before'))
			FmEvent::$hook[$type][$event]['before'][] = $callback;
			
		if (preg_match('/^('.$part.')$/','after'))
			FmEvent::$hook[$type][$event]['after'][] = $callback;
	}
}