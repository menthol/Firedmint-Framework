<?php
if (!defined('FM_SECURITY')) die();

class FmRoute
{
	public  static  $o;
	public  static  $pageRoute; 
	
	static function factory()
	{
		if (!is_object(FmRoute::$o))
			FmRoute::$o = _subClass('FmRoute',FmConfig::$config['route']['engine']);
			
		return FmRoute::$o;
	}
	
	static function getView($uri,$GetArgs,$magicRoute)
	{
		return FmRoute::$o->getView($uri,$GetArgs,$magicRoute);
	}
	
	static function getUrl($view,$arguments = array(),$decorators = array(),$magicRoute = null)
	{
		return FmRoute::$o->getUrl($view,$arguments,$decorators,$magicRoute);
	}
}