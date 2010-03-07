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
		return auth::$o->getUser();
	}
}