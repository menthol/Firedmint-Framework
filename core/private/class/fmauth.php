<?php
if (!defined('FM_SECURITY')) die();

class FmAuth
{
	public  static  $o;
	
	static function factory()
	{
		if (!is_object(FmAuth::$o))
			FmAuth::$o = _subClass('FmAuth',FmConfig::$config['auth']['engine']);
		
		return FmAuth::$o;
	}
	
	static function getUser()
	{
		return FmAuth::$o->getUser();
	}
}