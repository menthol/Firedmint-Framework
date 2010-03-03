<?php
if (!defined('FM_SECURITY')) die();

class event
{
	public  static  $event = array();
	
	function __construct()
	{
		if (_clear('event') || !is_array(event::$event = cache::get('event','cached_event')))
		{
			// compile event
			if (!is_array($event = cache::getStatic('event','static_event')))
				$event = array();
						
			foreach (_getPaths('private/event'.FM_PHP_EXTENSION) as $file)
				include $file;
			
			cache::set('event','cached_event',event::$event = $event,config::$config['event']['cache_lifetime']);
		}
	}
	
	static function trigger($type,$event,$states='before|main|after',&$arg1 = null, &$arg2 = null, &$arg3 = null, &$arg4 = null, &$arg5 = null)
	{
		log::message("$type,$event,$states");
		if (array_key_exists($type,event::$event) && array_key_exists($event,event::$event[$type]))
			foreach (array('before','main','after') as $state)
				if (array_key_exists($state,event::$event[$type][$event]) && preg_match('/^('.$states.')$/',$state))
					foreach (event::$event[$type][$event][$state] as $callback)
						if (is_string($__ = call_user_func($callback,&$arg1,&$arg2,&$arg3,&$arg4,&$arg5)))
							return $__;
	}
	
	static function hook($type,$event,$callback,$part = 'main')
	{
		if (preg_match('/^('.$part.')$/','before'))
			event::$event[$type][$event]['before'][] = $callback;
		
		if (preg_match('/^('.$part.')$/','main'))
			event::$event[$type][$event]['main'][] = $callback;
		
		if (preg_match('/^('.$part.')$/','after'))
			event::$event[$type][$event]['after'][] = $callback;
	}
}