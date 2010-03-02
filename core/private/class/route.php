<?php
if (!defined('FM_SECURITY')) die();

class route
{
	public  static  $o;
	
	static function factory()
	{
		if (!is_object(route::$o))
			route::$o = _subClass('route',config::$config['route']['engine']);
			
		return route::$o;
	}
	
	static function getView($uri,$GetArgs,$magicRoute)
	{
		if (is_object(route::$o))
			return route::$o->getView($uri,$GetArgs,$magicRoute);
	}
}