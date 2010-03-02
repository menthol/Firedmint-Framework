<?php
if (!defined('FM_SECURITY')) die();

class auth
{
	public  static  $o;
	
	static function factory()
	{
		if (!is_object(auth::$o))
			auth::$o = _subClass('auth',config::$config['auth']['engine']);
		
		return auth::$o;
	}
	
	static function getUser()
	{
		if (is_object(auth::$o))
			return auth::$o->getUser();
	}
}