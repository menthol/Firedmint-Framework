<?php
if (!defined('FM_SECURITY')) die();

class route
{
	public  static  $o;
	public  static  $pageRoute; 
	
	static function factory()
	{
		if (!is_object(route::$o))
			route::$o = _subClass('route',config::$config['route']['engine']);
			
		return route::$o;
	}
	
	static function getView($uri,$GetArgs,$magicRoute)
	{
		return route::$o->getView($uri,$GetArgs,$magicRoute);
	}
	
	static function getUrl($view,$arguments = array(),$decorators = array(),$magicRoute = null)
	{
		return route::$o->getUrl($view,$arguments,$decorators,$magicRoute);
	}
}